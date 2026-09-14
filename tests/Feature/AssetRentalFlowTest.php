<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBooking;
use App\Models\AssetPublicReservation;
use App\Models\AssetRentalSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetRentalFlowTest extends TestCase
{
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->timestamps();
        });
        Schema::create('schedules', fn (Blueprint $table) => $table->id());
        foreach (['2026_08_30_000000_create_asset_management_tables.php', '2026_09_07_000002_create_public_asset_reservations.php', '2026_09_11_000001_add_tracking_events_to_asset_public_reservations.php', '2026_09_11_000006_create_asset_rental_rates_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Storage::fake('local');
        $this->asset = Asset::create(['name' => 'Lapangan', 'location' => 'Bandung', 'is_rentable' => true, 'hourly_rate' => 100000, 'rental_open_time' => '08:00', 'rental_close_time' => '22:00']);
        AssetRentalSetting::current()->update(['manager_whatsapp' => '081234567890']);
    }

    private function payload(): array
    {
        return ['full_name' => 'Pemohon', 'whatsapp' => '081234567890', 'email' => 'pemohon@example.test', 'rental_date' => now()->addDays(2)->toDateString(), 'start_time' => '10:00', 'duration_hours' => 2, 'usage_type' => 'Latihan', 'accepted_schedule' => 1, 'accepted_rules' => 1, 'accepted_cancellation' => 1, 'accepted_damage' => 1, 'payment_proof' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf')];
    }

    private function submit(): AssetPublicReservation
    {
        $this->post(route('public.asset-rentals.store', $this->asset), $this->payload())->assertSessionHasNoErrors()->assertRedirect();

        return AssetPublicReservation::firstOrFail();
    }

    public function test_payment_is_required_and_invalid_upload_is_rejected(): void
    {
        $data = $this->payload();
        unset($data['payment_proof']);
        $this->post(route('public.asset-rentals.store', $this->asset), $data)->assertSessionHasErrors('payment_proof');
        $data['payment_proof'] = UploadedFile::fake()->create('script.php', 1, 'text/plain');
        $this->post(route('public.asset-rentals.store', $this->asset), $data)->assertSessionHasErrors('payment_proof');
        $this->assertSame(0, AssetPublicReservation::count());
    }

    public function test_submit_creates_pending_payment_receipt_and_tracking(): void
    {
        $this->get(route('public.asset-rentals.show', $this->asset))->assertOk()->assertSee('Pembayaran di Awal')->assertSee('multipart/form-data')->assertSee('name="payment_proof"', false);
        $reservation = $this->submit();
        $this->assertSame('payment_uploaded', $reservation->status);
        $this->assertSame('200000.00', $reservation->total_amount);
        Storage::disk('local')->assertExists($reservation->payment_proof_path);
        $this->assertSame(0, AssetBooking::count());
        $this->get(route('public.asset-rentals.status', $reservation->public_token))->assertOk()->assertSee('Tracking Booking')->assertSee('Unduh Kwitansi')->assertSee('wa.me/6281234567890');
        $this->get(route('public.asset-rentals.receipt', $reservation->public_token))->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('Kwitansi-Booking-'.$reservation->booking_code.'.pdf');
        $this->assertStringContainsString('Pembayaran belum dinyatakan terverifikasi', view('asset-rentals.public.receipt', compact('reservation'))->render());
        $this->get(route('public.asset-rentals.receipt', 'unknown-token'))->assertNotFound();
    }

    public function test_revision_resubmission_confirmation_and_completion_preserve_history(): void
    {
        $reservation = $this->submit();
        $admin = User::create(['name' => 'Admin', 'role' => 'admin_aset']);
        $this->actingAs($admin)->put(route('asset-rentals.admin.review', $reservation), ['action' => 'revision', 'admin_note' => 'Bukti kurang jelas'])->assertRedirect();
        $previous = $reservation->fresh()->payment_proof_path;
        $this->post(route('public.asset-rentals.payment', $reservation->public_token), ['payment_proof' => UploadedFile::fake()->create('baru.pdf', 20, 'application/pdf')])->assertSessionHasNoErrors()->assertRedirect();
        Storage::disk('local')->assertMissing($previous);
        $this->put(route('asset-rentals.admin.review', $reservation), ['action' => 'confirm'])->assertRedirect();
        $this->assertSame(1, AssetBooking::count());
        $this->put(route('asset-rentals.admin.review', $reservation), ['action' => 'confirm'])->assertStatus(422);
        $this->post(route('public.asset-rentals.payment', $reservation->public_token), ['payment_proof' => UploadedFile::fake()->create('late.pdf', 20, 'application/pdf')])->assertStatus(422);
        $this->put(route('asset-rentals.admin.review', $reservation), ['action' => 'complete'])->assertRedirect();
        $reservation->refresh();
        $this->assertSame(['payment_uploaded', 'revision', 'payment_uploaded', 'confirmed', 'completed'], array_column($reservation->tracking_events, 'status'));
        $this->assertSame('Bukti kurang jelas', $reservation->tracking_events[1]['note']);
        $this->assertNull($reservation->tracking_events[2]['note']);
        $this->get(route('public.asset-rentals.status', $reservation->public_token))->assertOk()->assertSee('Bukti kurang jelas')->assertSee('Selesai');
    }

    public function test_conflicting_booking_does_not_leave_an_extra_file(): void
    {
        $reservation = $this->submit();
        $reservation->update(['status' => 'revision']);
        $this->post(route('public.asset-rentals.store', $this->asset), $this->payload())->assertSessionHasErrors('start_time');
        $this->assertSame(1, AssetPublicReservation::count());
        $this->assertCount(1, Storage::disk('local')->allFiles('asset-rental-payments'));
    }

    public function test_guest_cannot_approve_payment(): void
    {
        $reservation = $this->submit();
        $this->put(route('asset-rentals.admin.review', $reservation), ['action' => 'confirm'])->assertRedirect('/login');
        $this->assertSame('payment_uploaded', $reservation->fresh()->status);
    }

    public function test_legacy_reservation_can_continue_and_keep_prior_status(): void
    {
        $reservation = $this->submit();
        AssetPublicReservation::whereKey($reservation->id)->update(['tracking_events' => null, 'status' => 'awaiting_payment']);
        $reservation->refresh();
        $this->get(route('public.asset-rentals.status', $reservation->public_token))->assertOk()->assertSee('Menunggu Pembayaran');
        $this->post(route('public.asset-rentals.payment', $reservation->public_token), ['payment_proof' => UploadedFile::fake()->create('legacy.pdf', 20, 'application/pdf')])->assertSessionHasNoErrors()->assertRedirect();
        $events = $reservation->fresh()->tracking_events;
        $this->assertContains('awaiting_payment', array_column($events, 'status'));
        $this->assertSame('payment_uploaded', end($events)['status']);
    }

    public function test_booking_crossing_rate_ranges_uses_each_hourly_rate(): void
    {
        $this->asset->rentalRates()->createMany([
            ['start_time' => '06:00', 'end_time' => '10:00', 'hourly_rate' => 75000, 'sort_order' => 0],
            ['start_time' => '10:00', 'end_time' => '16:00', 'hourly_rate' => 50000, 'sort_order' => 1],
        ]);
        $this->asset->update(['rental_open_time' => '06:00', 'rental_close_time' => '16:00', 'hourly_rate' => 50000]);
        $payload = $this->payload();
        $payload['start_time'] = '09:00';
        $payload['duration_hours'] = 2;

        $this->post(route('public.asset-rentals.store', $this->asset), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $reservation = AssetPublicReservation::firstOrFail();
        $this->assertSame('125000.00', $reservation->total_amount);
        $this->assertCount(2, $reservation->rate_breakdown);
        $this->assertEquals(75000, $reservation->rate_breakdown[0]['rate']);
        $this->assertEquals(50000, $reservation->rate_breakdown[1]['rate']);
    
    }

    public function test_admin_can_delete_reservation_and_all_related_data(): void
    {
        $reservation = $this->submit();
        $proofPath = $reservation->payment_proof_path;
        $publicToken = $reservation->public_token;
        $admin = User::create(['name' => 'Admin Aset', 'role' => 'admin_aset']);

        $this->actingAs($admin)
            ->put(route('asset-rentals.admin.review', $reservation), ['action' => 'confirm'])
            ->assertRedirect();
        $this->assertSame(1, AssetBooking::count());
        Storage::disk('local')->assertExists($proofPath);

        $this->delete(route('asset-rentals.admin.destroy', $reservation))
            ->assertRedirect(route('asset-rentals.admin.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('asset_public_reservations', ['id' => $reservation->id]);
        $this->assertSame(0, AssetBooking::count());
        Storage::disk('local')->assertMissing($proofPath);
        $this->get(route('public.asset-rentals.status', $publicToken))->assertNotFound();
    }
}