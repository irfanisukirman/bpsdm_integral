<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetBooking;
use App\Models\AssetPublicReservation;
use App\Models\AssetRentalSetting;
use App\Models\NotificationRead;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetRentalController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_aset'], true), 403);
    }

    public function catalog()
    {
        return $this->catalogView();
    }

    public function lookup(Request $r)
    {
        $d = $r->validate(['lookup' => 'required|string|max:255'], ['lookup.required' => 'Masukkan kode tiket atau email yang digunakan saat reservasi.']);
        $term = trim($d['lookup']);
        $isEmail = filter_var($term, FILTER_VALIDATE_EMAIL) !== false;
        $query = AssetPublicReservation::with('asset')->latest();
        if ($isEmail) {
            $query->where('email', $term);
            $results = $query->limit(10)->get();
        } else {
            $results = $query->where('booking_code', strtoupper($term))->limit(1)->get();
        }

        return $this->catalogView($results, $term, $isEmail ? 'email' : 'code');
    }

    public function show(Asset $asset)
    {
        abort_unless($asset->is_active && $asset->is_public && $asset->is_rentable, 404);
        $asset->load(['images','rentalRates']);
        $setting = AssetRentalSetting::current();
        $reserved = $asset->publicReservations()->whereIn('status', ['pending_review', 'awaiting_payment', 'payment_uploaded', 'revision', 'confirmed'])->whereDate('rental_date', '>=', today())->orderBy('rental_date')->orderBy('start_time')->get(['rental_date', 'start_time', 'end_time', 'status']);

        $ratePayload = $asset->rentalRates->map(fn ($rate) => ['start' => substr($rate->start_time, 0, 5), 'end' => substr($rate->end_time, 0, 5), 'rate' => (float) $rate->hourly_rate])->values();

        return view('asset-rentals.public.show', compact('asset', 'setting', 'reserved', 'ratePayload'));
    }

    public function store(Request $r, Asset $asset)
    {
        abort_unless($asset->is_active && $asset->is_public && $asset->is_rentable, 404);
        $d = $r->validate(['full_name' => 'required|string|max:255', 'whatsapp' => 'required|string|max:30', 'email' => 'required|email|max:255', 'organization' => 'nullable|string|max:255', 'rental_date' => 'required|date|after_or_equal:today', 'duration_hours' => ['required', 'integer', 'min:'.max(1, $asset->rental_min_hours), 'max:'.max(1, $asset->rental_max_hours)], 'start_time' => 'required|date_format:H:i', 'usage_type' => ['required', Rule::in(['Latihan', 'Bermain Bersama', 'Sparring', 'Pertandingan/Turnamen', 'Coaching/Les', 'Lainnya'])], 'usage_other' => 'required_if:usage_type,Lainnya|nullable|string|max:255', 'additional_note' => 'nullable|string|max:2000', 'accepted_schedule' => 'accepted', 'accepted_rules' => 'accepted', 'accepted_cancellation' => 'accepted', 'accepted_damage' => 'accepted', 'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'], ['usage_other.required_if' => 'Tuliskan jenis penggunaan lainnya.', 'accepted_schedule.accepted' => 'Konfirmasi tanggal dan jam wajib disetujui.', 'accepted_rules.accepted' => 'Peraturan penggunaan wajib disetujui.', 'accepted_cancellation.accepted' => 'Ketentuan pembatalan wajib disetujui.', 'accepted_damage.accepted' => 'Tanggung jawab kerusakan wajib disetujui.']);
        $start = Carbon::parse($d['rental_date'].' '.$d['start_time'], 'Asia/Jakarta');
        $end = $start->copy()->addHours((int) $d['duration_hours']);
        if ($start->isPast()) {
            throw ValidationException::withMessages(['start_time' => 'Waktu sewa harus berada di masa mendatang.']);
        }
        if ($asset->rental_open_time && $start->format('H:i') < substr($asset->rental_open_time, 0, 5)) {
            throw ValidationException::withMessages(['start_time' => 'Jam mulai berada di luar jam operasional.']);
        }
        if (! $start->isSameDay($end) || ($asset->rental_close_time && $end->format('H:i') > substr($asset->rental_close_time, 0, 5))) {
            throw ValidationException::withMessages(['duration_hours' => 'Jam selesai melewati jam operasional fasilitas.']);
        }
        $proofPath = null;
        try {
            $reservation = DB::transaction(function () use ($r, $asset, $d, $start, $end, &$proofPath) {
                $asset = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();
                $asset->load('rentalRates');
                $this->assertAvailable($asset, $start, $end);
                $pricing = $this->calculateRentalPrice($asset, $start, $end);
                $proofPath = $r->file('payment_proof')->store('asset-rental-payments', 'local');
                abort_unless($proofPath, 500, 'Bukti pembayaran gagal disimpan.');
                $rate = $pricing['average_rate'];

                return AssetPublicReservation::create(['asset_id' => $asset->id, 'public_token' => (string) Str::uuid(), 'booking_code' => $this->nextCode(), 'full_name' => $d['full_name'], 'whatsapp' => $d['whatsapp'], 'email' => $d['email'], 'organization' => $d['organization'] ?? null, 'rental_date' => $start->toDateString(), 'start_time' => $start->format('H:i:s'), 'end_time' => $end->format('H:i:s'), 'duration_hours' => $d['duration_hours'], 'usage_type' => $d['usage_type'], 'usage_other' => $d['usage_other'] ?? null, 'additional_note' => $d['additional_note'] ?? null, 'hourly_rate' => $rate, 'total_amount' => $pricing['total'], 'rate_breakdown' => $pricing['breakdown'], 'status' => 'payment_uploaded', 'payment_proof_path' => $proofPath, 'payment_uploaded_at' => now(), 'rules_accepted_at' => now()]);
            });
        } catch (\Throwable $e) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }
            throw $e;
        }

        return redirect()->route('public.asset-rentals.status', $reservation->public_token)->with('success', 'Booking dan bukti pembayaran berhasil dikirim. Unduh kwitansi booking untuk dibawa dan hubungi admin melalui WhatsApp. Pembayaran menunggu verifikasi admin.');
    }

    public function status(string $token)
    {
        $reservation = AssetPublicReservation::with('asset.images')->where('public_token', $token)->firstOrFail();
        $setting = AssetRentalSetting::current();

        return view('asset-rentals.public.status', compact('reservation', 'setting'));
    }

    public function receipt(string $token)
    {
        $reservation = AssetPublicReservation::with('asset')->where('public_token', $token)->firstOrFail();

        return Pdf::loadView('asset-rentals.public.receipt', compact('reservation'))->setPaper('a4')->download('Kwitansi-Booking-'.$reservation->booking_code.'.pdf');
    }

    public function uploadPayment(Request $r, string $token)
    {
        $r->validate(['payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $path = null;
        $previous = null;
        try {
            DB::transaction(function () use ($r, $token, &$path, &$previous) {
                $reservation = AssetPublicReservation::where('public_token', $token)->lockForUpdate()->firstOrFail();
                abort_unless(in_array($reservation->status, ['awaiting_payment', 'revision'], true), 422);
                $previous = $reservation->payment_proof_path;
                $path = $r->file('payment_proof')->store('asset-rental-payments', 'local');
                abort_unless($path, 500, 'Bukti pembayaran gagal disimpan.');
                $reservation->update(['payment_proof_path' => $path, 'payment_uploaded_at' => now(), 'status' => 'payment_uploaded']);
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
        if ($previous) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi.');
    }

    public function adminIndex(Request $r)
    {
        $this->guard();
        $this->markRentalNotificationsRead($r->input('status'));
        $query = AssetPublicReservation::with(['asset', 'reviewer'])->latest();
        if ($r->filled('status')) {
            $query->where('status', $r->status);
        }
        if ($r->filled('date')) {
            $query->whereDate('rental_date', $r->date);
        }
        if ($r->filled('asset_id')) {
            $query->where('asset_id', $r->asset_id);
        }
        $reservations = $query->paginate(15)->withQueryString();
        $assets = Asset::where('is_rentable', true)->orderBy('name')->get();
        $setting = AssetRentalSetting::current();
        $stats = ['pending' => AssetPublicReservation::where('status', 'pending_review')->count(), 'payment' => AssetPublicReservation::where('status', 'payment_uploaded')->count(), 'confirmed' => AssetPublicReservation::where('status', 'confirmed')->count(), 'today' => AssetPublicReservation::whereDate('rental_date', today())->whereIn('status', ['confirmed', 'completed'])->count()];

        return view('asset-rentals.admin.index', compact('reservations', 'assets', 'setting', 'stats'));
    }

    public function review(Request $r, AssetPublicReservation $reservation)
    {
        $this->guard();
        $d = $r->validate(['action' => ['required', Rule::in(['approve', 'confirm', 'revision', 'reject', 'cancel', 'complete'])], 'admin_note' => 'nullable|string|max:2000']);
        $allowed = ['pending_review' => ['approve', 'reject'], 'payment_uploaded' => ['confirm', 'revision', 'reject'], 'awaiting_payment' => ['cancel'], 'revision' => ['cancel'], 'confirmed' => ['complete', 'cancel']];
        abort_unless(in_array($d['action'], $allowed[$reservation->status] ?? [], true), 422, 'Tindakan tidak sesuai dengan status reservasi saat ini.');
        $start = Carbon::parse($reservation->rental_date->format('Y-m-d').' '.$reservation->start_time);
        $end = Carbon::parse($reservation->rental_date->format('Y-m-d').' '.$reservation->end_time);
        DB::transaction(function () use ($reservation, $d, $start, $end, $allowed) {
            Asset::whereKey($reservation->asset_id)->lockForUpdate()->firstOrFail();
            $reservation = AssetPublicReservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($d['action'], $allowed[$reservation->status] ?? [], true), 422, 'Status reservasi telah berubah. Muat ulang halaman.');
            $updates = ['admin_note' => $d['admin_note'] ?? null, 'reviewed_by' => Auth::id(), 'reviewed_at' => now()];
            if ($d['action'] === 'approve') {
                $this->assertAvailable($reservation->asset, $start, $end, $reservation->id);
                $updates += ['status' => 'awaiting_payment', 'payment_due_at' => now()->addHours(AssetRentalSetting::current()->payment_deadline_hours)];
            } elseif ($d['action'] === 'confirm') {
                abort_unless($reservation->payment_proof_path, 422, 'Bukti pembayaran belum tersedia.');
                $this->assertAvailable($reservation->asset, $start, $end, $reservation->id);
                $updates['status'] = 'confirmed';
                AssetBooking::updateOrCreate(['asset_id' => $reservation->asset_id, 'bookable_type' => AssetPublicReservation::class, 'bookable_id' => $reservation->id], ['starts_at' => $start, 'ends_at' => $end, 'created_by' => Auth::id()]);
            } else {
                $updates['status'] = ['revision' => 'revision', 'reject' => 'rejected', 'cancel' => 'cancelled', 'complete' => 'completed'][$d['action']];
                if (in_array($d['action'], ['reject', 'cancel'], true)) {
                    $reservation->booking?->delete();
                }
            }$reservation->update($updates);
        });

        return back()->with('success', 'Status reservasi '.$reservation->booking_code.' berhasil diperbarui.');
    }

    public function destroy(AssetPublicReservation $reservation)
    {
        $this->guard();
        $reservationId = $reservation->id;
        $bookingCode = $reservation->booking_code;
        $paymentProofPath = $reservation->payment_proof_path;

        DB::transaction(function () use ($reservationId) {
            $lockedReservation = AssetPublicReservation::whereKey($reservationId)->lockForUpdate()->firstOrFail();
            AssetBooking::where('bookable_type', AssetPublicReservation::class)
                ->where('bookable_id', $lockedReservation->id)
                ->delete();
            $lockedReservation->delete();
        });

        if ($paymentProofPath) {
            Storage::disk('local')->delete($paymentProofPath);
        }

        return redirect()->route('asset-rentals.admin.index')
            ->with('success', 'Reservasi '.$bookingCode.' beserta booking, bukti pembayaran, dan seluruh data terkait berhasil dihapus.');
    }
    public function paymentProof(AssetPublicReservation $reservation)
    {
        $this->guard();
        $this->markRentalStatusRead('payment_uploaded');
        abort_unless($reservation->payment_proof_path && Storage::disk('local')->exists($reservation->payment_proof_path), 404);

        return Storage::disk('local')->response($reservation->payment_proof_path);
    }

    public function updateSettings(Request $r)
    {
        $this->guard();
        $d = $r->validate(['manager_whatsapp' => 'nullable|string|max:30', 'bank_name' => 'required|string|max:100', 'bank_account' => 'required|string|max:100', 'bank_account_name' => 'required|string|max:255', 'payment_deadline_hours' => 'required|integer|min:1|max:168', 'public_note' => 'nullable|string|max:3000']);
        AssetRentalSetting::current()->update($d);

        return back()->with('success', 'Pengaturan reservasi publik berhasil disimpan.');
    }

    private function markRentalNotificationsRead(?string $status): void
    {
        if (blank($status) || $status === 'pending_review') {
            $this->markRentalStatusRead('pending_review');
        }
        if (blank($status) || $status === 'payment_uploaded') {
            $this->markRentalStatusRead('payment_uploaded');
        }
    }

    private function markRentalStatusRead(string $status): void
    {
        $column = $status === 'payment_uploaded' ? 'payment_uploaded_at' : 'created_at';
        $prefix = $status === 'payment_uploaded' ? 'asset-rentals-payment-' : 'asset-rentals-pending-';
        $latest = AssetPublicReservation::where('status', $status)->whereNotNull($column)->latest($column)->first([$column]);
        $moment = $latest?->{$column};
        if ($moment) {
            NotificationRead::updateOrCreate(['user_id' => Auth::id(), 'notification_key' => $prefix.$moment->format('YmdHis')], ['read_at' => now()]);
        }
    }

    private function catalogView($lookupResults = null, ?string $lookupTerm = null, ?string $lookupType = null)
    {
        $assets = Asset::with(['images','rentalRates'])->where('is_active', true)->where('is_public', true)->where('is_rentable', true)->orderBy('name')->get();
        $setting = AssetRentalSetting::current();

        return view('asset-rentals.public.catalog', compact('assets', 'setting', 'lookupResults', 'lookupTerm', 'lookupType'));
    }

    private function calculateRentalPrice(Asset $asset, Carbon $start, Carbon $end): array
    {
        $rates = $asset->rentalRates;
        if ($rates->isEmpty()) {
            $hours = $start->diffInMinutes($end) / 60;
            $rate = (float) $asset->hourly_rate;
            return ['total' => $hours * $rate, 'average_rate' => $rate, 'breakdown' => [['start' => $start->format('H:i'), 'end' => $end->format('H:i'), 'hours' => $hours, 'rate' => $rate, 'subtotal' => $hours * $rate]]];
        }
        $cursor = $start->copy();$total = 0.0;$breakdown = [];
        while ($cursor->lt($end)) {
            $minute = $cursor->format('H:i');
            $tier = $rates->first(fn ($rate) => $minute >= substr($rate->start_time,0,5) && $minute < substr($rate->end_time,0,5));
            if (! $tier) throw ValidationException::withMessages(['start_time' => 'Seluruh durasi sewa harus berada pada rentang tarif yang tersedia.']);
            $tierEnd = Carbon::parse($cursor->toDateString().' '.substr($tier->end_time,0,5), 'Asia/Jakarta');
            $segmentEnd = $tierEnd->lt($end) ? $tierEnd : $end->copy();
            if (! $segmentEnd->gt($cursor)) throw ValidationException::withMessages(['start_time' => 'Rentang tarif aset tidak valid.']);
            $hours = $cursor->diffInMinutes($segmentEnd) / 60;$rate = (float) $tier->hourly_rate;$subtotal = $hours * $rate;
            $breakdown[] = ['start'=>$cursor->format('H:i'),'end'=>$segmentEnd->format('H:i'),'hours'=>$hours,'rate'=>$rate,'subtotal'=>$subtotal];
            $total += $subtotal;$cursor = $segmentEnd;
        }
        $duration = max(0.01, $start->diffInMinutes($end) / 60);
        return ['total'=>$total,'average_rate'=>$total/$duration,'breakdown'=>$breakdown];
    }
    private function assertAvailable(Asset $asset, Carbon $start, Carbon $end, ?int $ignoreReservation = null): void
    {
        $bookingConflict = AssetBooking::hasConflict($asset->id, $start, $end, AssetPublicReservation::class, $ignoreReservation);
        $reservationConflict = AssetPublicReservation::where('asset_id', $asset->id)->whereDate('rental_date', $start->toDateString())->whereIn('status', ['pending_review', 'awaiting_payment', 'payment_uploaded', 'revision', 'confirmed'])->when($ignoreReservation, fn ($q) => $q->whereKeyNot($ignoreReservation))->where('start_time', '<', $end->format('H:i:s'))->where('end_time', '>', $start->format('H:i:s'))->exists();
        if ($bookingConflict || $reservationConflict) {
            throw ValidationException::withMessages(['start_time' => 'Jadwal yang dipilih sudah digunakan atau sedang ditahan reservasi lain. Pilih waktu berbeda.']);
        }
    }

    private function nextCode(): string
    {
        $prefix = 'RFP-'.now()->format('Ym').'-';
        $last = AssetPublicReservation::where('booking_code', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('id')->value('booking_code');
        $number = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
