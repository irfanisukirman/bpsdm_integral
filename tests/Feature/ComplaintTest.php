<?php

namespace Tests\Feature;

use App\Models\ComplaintTicket;
use App\Models\Participant;
use App\Models\Training;
use App\Models\User;
use App\Services\NotificationCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_inbox_summarizes_statuses_and_exports_the_selected_filter(): void
    {
        $staff = User::factory()->create(['role' => 'admin_bidang']);
        $participant = User::factory()->create(['role' => 'participant']);
        foreach (array_keys(ComplaintTicket::STATUSES) as $status) {
            $tickets[$status] = ComplaintTicket::create(['category' => 'account', 'status' => $status]);
        }
        $received = $tickets['received'];
        $received->messages()->create(['body' => '=2+2', 'from_staff' => false]);

        $this->actingAs($participant)->get(route('complaints.export'))->assertForbidden();
        $this->actingAs($staff)->get(route('complaints.inbox', ['status' => 'received']))
            ->assertOk()->assertViewHas('statusCounts', fn ($counts) => count($counts) === 3 && array_sum($counts) === 3)
            ->assertSee('complaint-inbox-received', false)->assertSee($received->reference())
            ->assertDontSee($tickets['resolved']->reference());
        $export = $this->get(route('complaints.export', ['status' => 'received']))->assertOk()
            ->assertDownload('rekap-pengaduan-'.now('Asia/Jakarta')->format('Ymd').'.xlsx');
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($export->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame(2, $sheet->getHighestDataRow());
        $this->assertSame($received->reference(), $sheet->getCell('A2')->getValue());
        $this->assertSame('=2+2', $sheet->getCell('I2')->getValue());
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $sheet->getCell('I2')->getDataType());
    }

    public function test_guest_widget_can_submit_and_track_within_the_panel(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('class="btn btn-primary complaint-launcher', false)
            ->assertSee(route('complaints.guest.availability'))
            ->assertDontSee('data-complaint-heartbeat', false);
        $this->get(route('complaints.guest.create', ['widget' => 1]))->assertOk()->assertSee('class="complaint-embedded"', false);
        $this->get(route('complaints.guest.track', ['widget' => 1]))->assertOk()->assertSee('class="complaint-embedded"', false);
        $response = $this->post(route('complaints.guest.store', ['widget' => 1]), [
            'category' => 'account',
            'message' => 'Tidak berhasil masuk.',
        ])->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertSame('widget=1', parse_url($location, PHP_URL_QUERY));
        $code = basename(parse_url($location, PHP_URL_PATH));
        $this->get($location)->assertOk()
            ->assertSee('class="complaint-embedded"', false)->assertSee('Simpan kode pelacakan');
        $this->post(route('complaints.guest.lookup', ['widget' => 1]), ['code' => $code])->assertRedirect($location);
    }

    public function test_chat_accepts_images_without_text_and_only_returns_new_messages(): void
    {
        Storage::fake('local');
        $response = $this->post(route('complaints.guest.store'), [
            'category' => 'account',
            'attachment' => UploadedFile::fake()->image('layar.png'),
        ])->assertRedirect();
        $token = basename($response->headers->get('Location'));
        $ticket = ComplaintTicket::firstOrFail();
        $first = $ticket->messages()->firstOrFail();
        $this->assertSame('', $first->body);

        $staff = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($staff)->postJson(route('complaints.reply', $ticket), [
            'attachment' => UploadedFile::fake()->image('petunjuk.png'),
        ])->assertOk();
        $reply = $ticket->messages()->latest('id')->firstOrFail();
        $this->assertSame('', $reply->body);

        auth()->logout();
        $firstImage = route('complaints.guest.attachment', [$token, $first->id, 'preview' => 1]);
        $replyImage = route('complaints.guest.attachment', [$token, $reply->id, 'preview' => 1]);
        $updates = $this->getJson(route('complaints.guest.updates', [$token, 'after' => $first->id]))
            ->assertJsonPath('last_message_id', $reply->id)->json('messages');
        $this->assertStringContainsString($replyImage, $updates);
        $this->assertStringNotContainsString($firstImage, $updates);
        $this->get($replyImage)->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_widget_separates_reports_from_live_chat(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('data-complaint-chat-option hidden', false)
            ->assertSee('Chat dengan Admin')
            ->assertSee('Ajukan Pengaduan')
            ->assertSee('Lacak Tiket');

        $message = ['category' => 'account', 'message' => 'Mohon bantuan.'];
        $chatMessage = ['chat_name' => 'Siti', 'chat_institution' => 'BPSDM', 'message' => 'Mohon bantuan chat.'];
        $sendChat = route('complaints.guest.store', ['widget' => 1, 'mode' => 'chat']);
        $sendReport = route('complaints.guest.store', ['widget' => 1, 'mode' => 'report']);

        $this->get(route('complaints.guest.create', ['widget' => 1, 'mode' => 'chat']))->assertOk()
            ->assertSee('Selamat datang, Kaka!')
            ->assertSee('Ketik nama Kaka')
            ->assertSee('data-complaint-chat-start="chat_name"', false)
            ->assertDontSee('id="complaint-chat-institution"', false)
            ->assertDontSee('name="category"', false);
        $this->get(route('complaints.guest.create', ['widget' => 1, 'mode' => 'report']))->assertOk()
            ->assertSee('name="category"', false);
        $this->postJson($sendChat, ['message' => 'Belum ada identitas.'])
            ->assertUnprocessable()->assertJsonValidationErrors(['chat_name', 'chat_institution']);

        $this->getJson(route('complaints.guest.availability'))->assertJsonPath('online', false);
        $offlineChat = $this->post($sendChat, $chatMessage)->assertRedirect()->headers->get('Location');
        $this->assertSame('widget=1', parse_url($offlineChat, PHP_URL_QUERY));
        $offlineConversation = $this->get($offlineChat)->assertOk()->assertSee('Admin sedang offline');
        $this->assertMatchesRegularExpression('/data-complaint-reply\s+hidden/', $offlineConversation->getContent());

        $admin = User::factory()->create(['role' => 'admin_bidang']);
        $this->actingAs($admin)->postJson(route('complaints.heartbeat'))->assertOk();
        auth()->logout();
        $this->getJson(route('complaints.guest.availability'))->assertJsonPath('online', true);
        $resumed = $this->get($offlineChat.'&mode=report')->assertOk()->assertSee('Ketik pesan')
            ->assertSee('data-complaint-chat-attachment', false)->assertSee('data-complaint-emoji-toggle', false);
        $this->assertDoesNotMatchRegularExpression('/data-complaint-reply\s+hidden/', $resumed->getContent());

        $report = $this->post($sendReport, $message)->assertRedirect()->headers->get('Location');
        $this->assertSame('widget=1&mode=report', parse_url($report, PHP_URL_QUERY));
        $this->get($report)->assertOk()->assertSee('Simpan kode pelacakan')
            ->assertDontSee('data-complaint-reply', false);

        $chat = $this->post($sendChat, $chatMessage)->assertRedirect()->headers->get('Location');
        $this->assertSame('widget=1', parse_url($chat, PHP_URL_QUERY));
        $this->get($chat)->assertOk()->assertSee('data-complaint-reply', false)
            ->assertSeeInOrder(['Siapa nama Kaka?', 'Siti', 'Baik, Kaka Siti. Selanjutnya, sebutkan instansi Kaka.', 'BPSDM', 'Mohon bantuan chat.'])
            ->assertSee('Ketik pesan');
        $this->assertDatabaseHas('complaint_tickets', ['chat_name' => 'Siti', 'chat_institution' => 'BPSDM', 'category' => 'other']);

        $participant = User::factory()->create(['role' => 'participant']);
        $this->actingAs($participant)->post(route('complaints.store', ['mode' => 'chat']), $chatMessage)->assertRedirect();
        $ticket = ComplaintTicket::whereBelongsTo($participant)->firstOrFail();
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        foreach ([$admin, $superadmin] as $staff) {
            $this->actingAs($staff)->get(route('complaints.show', $ticket))->assertOk()
                ->assertSee('Siti')->assertSee('BPSDM')->assertSee('Mohon bantuan chat.');
        }
        $chatPage = route('complaints.show', ['ticket' => $ticket, 'view' => 'chat']);
        $this->get(route('complaints.inbox'))->assertOk()->assertSee($chatPage)->assertSee('Hapus')->assertSee('Selesai');
        $this->actingAs($admin)->get($chatPage)->assertOk()
            ->assertSee('complaint-live--staff', false)->assertSee('Ketik pesan')->assertSee('Mohon bantuan chat.');
        $this->post(route('complaints.reply', ['ticket' => $ticket, 'view' => 'chat']), [
            'message' => 'Kami bantu cek.',
        ])->assertRedirect($chatPage);
        $this->actingAs($participant)->getJson(route('complaints.unread-replies'))->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('url', route('complaints.show', ['ticket' => $ticket, 'widget' => 1]));
        $firstMessage = $ticket->messages()->oldest('id')->firstOrFail();
        $reply = $ticket->messages()->latest('id')->firstOrFail();
        $firstMessage->forceFill(['created_at' => $reply->created_at])->save();
        $increment = route('complaints.updates', ['ticket' => $ticket, 'after' => $firstMessage->id]);
        $sameDay = $this->getJson($increment)->assertOk()->json('messages');
        $this->assertStringNotContainsString('complaint-chat-date', $sameDay);
        $firstMessage->forceFill(['created_at' => $reply->created_at->copy()->subDay()])->save();
        $nextDay = $this->getJson($increment)->assertOk()->json('messages');
        $this->assertStringContainsString('complaint-chat-date', $nextDay);
        $this->assertStringContainsString('complaint-message-avatar', $nextDay);
        $this->assertMatchesRegularExpression('/complaint-message-time"[^>]*>\s*\d{2}:\d{2}\s*<\/time>/', $nextDay);
        $this->getJson(route('complaints.updates', $ticket))
            ->assertOk()->assertJsonFragment(['status' => 'Diproses'])->assertSee('Kami bantu cek.');
        $this->assertSame('in_progress', $ticket->fresh()->status);
        $notificationCenter = app(NotificationCenter::class);
        $adminWaiting = $notificationCenter->waitingComplaints($admin);
        $superadminWaiting = $notificationCenter->waitingComplaints($superadmin);
        $this->postJson(route('complaints.reply', $ticket), ['message' => 'Ada pertanyaan lanjutan.'])->assertOk();
        $this->actingAs($admin)->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', $adminWaiting + 1);
        $this->postJson(route('complaints.heartbeat'), ['active_chat' => $ticket->id])->assertJsonPath('waiting', $adminWaiting);
        $this->getJson(route('complaints.updates', ['ticket' => $ticket, 'after' => $reply->id]))
            ->assertOk()->assertSee('Ada pertanyaan lanjutan.');
        $this->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', $adminWaiting);
        $this->actingAs($superadmin)->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', $superadminWaiting + 1);
        $this->get(route('complaints.inbox', ['read' => 1]))->assertRedirect(route('complaints.inbox'));
        $unreadIds = $this->postJson(route('complaints.heartbeat'), ['inbox' => 1])
            ->assertJsonPath('waiting', 0)->json('unread_chat_ids');
        $this->assertContains($ticket->id, $unreadIds);
        $unreadInbox = $this->get(route('complaints.inbox'))->assertOk()->assertSee($chatPage);
        $this->assertMatchesRegularExpression('/data-complaint-unread-ticket="'.$ticket->id.'"\s*>Belum dibaca/', $unreadInbox->getContent());
        $this->get($chatPage)->assertOk();
        $remainingIds = $this->postJson(route('complaints.heartbeat'), ['inbox' => 1])->json('unread_chat_ids');
        $this->assertNotContains($ticket->id, $remainingIds);
        $readInbox = $this->get(route('complaints.inbox'))->assertOk();
        $this->assertMatchesRegularExpression('/data-complaint-unread-ticket="'.$ticket->id.'"\s+hidden/', $readInbox->getContent());

        $this->actingAs($admin)->patch(route('complaints.resolve', $ticket))->assertRedirect();
        $closedPage = $this->get($chatPage)->assertOk();
        $this->assertMatchesRegularExpression('/data-complaint-reply\s+hidden/', $closedPage->getContent());
        $this->postJson(route('complaints.reply', $ticket), ['message' => 'Balasan setelah selesai.'])
            ->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->actingAs($participant)->getJson(route('complaints.updates', $ticket))
            ->assertJsonPath('status', 'Selesai')->assertJsonPath('chat_ended', true);
        $this->get(route('complaints.index', ['widget' => 1]))
            ->assertOk()->assertSee('Lihat Riwayat Chat')->assertSee($ticket->reference());
        $closedWidget = $this->get(route('complaints.show', ['ticket' => $ticket, 'widget' => 1]))
            ->assertOk()->assertSee('Mulai Chat Baru')
            ->assertSee('Kami bantu cek.');
        $this->assertMatchesRegularExpression('/data-complaint-reply\s+hidden/', $closedWidget->getContent());
        $this->postJson(route('complaints.reply', $ticket), ['message' => 'Pesan setelah selesai.'])
            ->assertUnprocessable()->assertJsonValidationErrors('message');
        $newChat = $this->post(route('complaints.store', ['widget' => 1, 'mode' => 'chat']), $chatMessage)
            ->assertRedirect()->headers->get('Location');
        $this->assertNotSame(route('complaints.show', ['ticket' => $ticket, 'widget' => 1]), $newChat);
        $this->assertSame('resolved', $ticket->fresh()->status);
    }

    public function test_guest_can_track_a_ticket_and_view_staff_reply_without_login(): void
    {
        Storage::fake('local');
        $this->get(route('login'))->assertOk()->assertSee('Pengaduan Kendala Login')->assertSee('Lacak Tiket');
        $response = $this->post(route('complaints.guest.store'), [
            'category' => 'account',
            'message' => 'Saya tidak dapat masuk.',
            'attachment' => UploadedFile::fake()->image('layar.png'),
        ])->assertRedirect();

        $url = $response->headers->get('Location');
        $token = basename($url);
        $ticket = ComplaintTicket::firstOrFail();
        $attachment = $ticket->messages()->firstOrFail();
        $this->assertSame(hash('sha256', $token), $ticket->tracking_hash);
        Storage::disk('local')->assertExists($attachment->attachment);

        $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')->assertSee('Simpan kode pelacakan');
        $this->get(route('complaints.guest.availability'))->assertJsonPath('online', false);
        $this->postJson($url, ['message' => 'Balasan saat admin offline.'])->assertUnprocessable();
        $this->post(route('complaints.guest.lookup'), ['code' => $token])->assertRedirect($url);
        $this->get(route('complaints.guest.attachment', [$token, $attachment->id]))->assertDownload('lampiran.png');
        $this->get(route('complaints.guest.show', str_repeat('a', 48)))->assertNotFound();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', 1);
        $this->actingAs($admin)->post(route('complaints.reply', $ticket), [
            'message' => 'Silakan coba masuk kembali.',
            'status' => 'resolved',
        ])->assertRedirect();
        auth()->logout();
        $this->getJson(route('complaints.guest.updates', $token))
            ->assertJsonPath('online', true)->assertJsonPath('status', 'Selesai')
            ->assertJsonPath('ticket_ended', true)->assertJsonStructure(['messages']);
        $completed = $this->get($url)->assertOk()->assertSee('Silakan coba masuk kembali.')
            ->assertSee('Ajukan Pengaduan Baru');
        $this->assertMatchesRegularExpression('/data-complaint-reply\s+hidden/', $completed->getContent());
        $this->postJson($url, ['message' => 'Saya masih membutuhkan bantuan.'])
            ->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->assertSame('resolved', $ticket->fresh()->status);
        $new = $this->post(route('complaints.guest.store', ['mode' => 'report']), [
            'category' => 'account', 'message' => 'Saya masih membutuhkan bantuan.',
        ])->assertRedirect()->headers->get('Location');
        $this->assertNotSame($url, strtok($new, '?'));
        $this->actingAs($admin)->delete(route('complaints.destroy', $ticket))->assertRedirect(route('complaints.inbox'));
        Storage::disk('local')->assertMissing($attachment->attachment);
        auth()->logout();
        $this->get($url)->assertNotFound();
        $this->getJson(route('complaints.guest.updates', $token))->assertNotFound();
        $this->postJson(route('complaints.guest.lookup'), ['code' => $token])->assertJsonValidationErrors('code');
    }

    public function test_admins_share_inbox_and_can_forward_while_only_superadmin_can_delete(): void
    {
        Storage::fake('local');
        $reporter = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang A']);
        $otherAdmin = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']);
        $otherParticipant = User::factory()->create(['role' => 'participant']);
        $training = Training::create([
            'nama_pelatihan' => 'Pelatihan Uji',
            'bidang' => 'Bidang A',
            'model' => 'standar',
            'metode' => 'klasikal',
            'lokasi' => 'BPSDM',
            'angkatan' => 'I',
            'jumlah_peserta' => 20,
            'jp' => 10,
            'tgl_mulai' => '2026-11-20',
            'tgl_selesai' => '2026-11-21',
            'created_by' => $admin->id,
        ]);

        $data = ['category' => 'attendance', 'message' => 'Presensi saya belum tercatat.', 'training_id' => $training->id];
        $this->actingAs($reporter)->postJson(route('complaints.store'), $data)->assertJsonValidationErrors('training_id');
        Participant::create([
            'training_id' => $training->id, 'user_id' => $reporter->id, 'name' => 'Peserta Uji',
            'nip_nik' => '198001012000011001', 'instansi' => 'Unit Uji', 'jabatan' => 'Analis',
            'registration_status' => 'approved',
        ]);
        $this->post(route('complaints.store'), $data + ['attachment' => UploadedFile::fake()->image('presensi.png')])->assertRedirect();
        $ticket = ComplaintTicket::firstOrFail();
        $attachment = $ticket->messages()->firstOrFail();
        $this->assertEquals($training->id, $ticket->training_id);

        $this->actingAs($otherParticipant)->get(route('complaints.inbox'))->assertForbidden();
        $this->get(route('complaints.show', $ticket))->assertNotFound();
        $this->get(route('complaints.attachment', [$ticket, $attachment->id]))->assertNotFound();
        $this->getJson(route('complaints.updates', $ticket))->assertNotFound();
        $this->patch(route('complaints.forward', $ticket), ['assigned_to' => $admin->id])->assertForbidden();
        $this->patch(route('complaints.resolve', $ticket))->assertForbidden();
        $this->actingAs($reporter)->getJson(route('complaints.updates', $ticket))->assertJsonPath('online', false);
        $this->actingAs($otherAdmin)->get(route('complaints.inbox'))->assertOk()
            ->assertSee($ticket->reference())
            ->assertSee('data-complaint-menu-count', false)
            ->assertSee('data-complaint-bell-entry', false)
            ->assertSee('Pengaduan menunggu')
            ->assertDontSee('data-complaint-waiting', false);
        $this->assertSame(1, app(NotificationCenter::class)->forUser($otherAdmin)->firstWhere('id', 'complaint-waiting')['waiting']);
        $this->get(route('complaints.attachment', [$ticket, $attachment->id]))->assertDownload('lampiran.png');
        $this->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', 1);
        $this->get(route('complaints.inbox', ['status' => 'received', 'read' => 1]))
            ->assertRedirect(route('complaints.inbox', ['status' => 'received']));
        $this->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', 0);
        $this->assertSame('received', $ticket->fresh()->status);
        $this->actingAs($admin)->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', 1);
        $this->get(route('complaints.show', $ticket))->assertOk();
        $this->postJson(route('complaints.heartbeat'))->assertJsonPath('waiting', 0);
        $this->actingAs($reporter)->getJson(route('complaints.updates', $ticket))->assertJsonPath('online', true);
        $this->get(route('complaints.show', ['ticket' => $ticket, 'widget' => 1]))->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertSee('class="complaint-embedded"', false)
            ->assertSee('Admin online');
        $this->get(route('complaints.create', ['widget' => 1, 'category' => 'attendance']))->assertOk()
            ->assertSee('class="complaint-embedded"', false);
        $this->postJson(route('complaints.heartbeat'))->assertForbidden();

        $this->actingAs($admin)->get(route('complaints.inbox'))->assertOk()->assertSee($ticket->reference());
        $this->patchJson(route('complaints.forward', $ticket), ['assigned_to' => $reporter->id])->assertJsonValidationErrors('assigned_to');
        $this->patch(route('complaints.forward', $ticket), ['assigned_to' => $otherAdmin->id])->assertRedirect();
        $this->assertEquals($otherAdmin->id, $ticket->fresh()->assigned_to);
        $this->assertTrue(app(NotificationCenter::class)->forUser($otherAdmin)->contains('id', 'complaint-assigned-'.$ticket->id));
        $this->actingAs($otherAdmin)->get(route('complaints.show', $ticket))->assertOk();
        $this->assertFalse(app(NotificationCenter::class)->forUser($otherAdmin)->contains('id', 'complaint-assigned-'.$ticket->id));
        $this->delete(route('complaints.destroy', $ticket))->assertForbidden();
        $this->get(route('complaints.show', ['ticket' => $ticket, 'view' => 'chat']))->assertOk()
            ->assertDontSee('complaint-live--staff', false)->assertSee('Simpan Tanggapan');
        $this->actingAs($otherAdmin)->post(route('complaints.reply', $ticket), ['message' => 'Sedang kami periksa.', 'status' => 'in_progress'])->assertRedirect();
        $this->assertSame('in_progress', $ticket->fresh()->status);
        $this->actingAs($reporter)->get(route('complaints.index'))->assertOk()
            ->assertSee('data-complaint-reply-count', false)->assertDontSee('Tanggapan untuk '.$ticket->reference());
        $this->getJson(route('complaints.unread-replies'))->assertJsonPath('count', 1);
        $this->actingAs($otherParticipant)->getJson(route('complaints.unread-replies'))->assertExactJson(['count' => 0, 'url' => null]);
        $this->actingAs($reporter)->get(route('complaints.show', ['ticket' => $ticket, 'widget' => 1]))
            ->assertOk()->assertSee('Sedang kami periksa.');
        $this->getJson(route('complaints.unread-replies'))->assertExactJson(['count' => 0, 'url' => null]);
        $this->actingAs($admin)->patch(route('complaints.resolve', $ticket))->assertRedirect(route('complaints.inbox'));
        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertNull(app(NotificationCenter::class)->forUser($admin)->firstWhere('id', 'complaint-waiting'));
        $messageCount = $ticket->messages()->count();
        $this->patch(route('complaints.resolve', $ticket))->assertRedirect(route('complaints.inbox'));
        $this->assertSame($messageCount, $ticket->messages()->count());
        $this->actingAs($reporter);
        $this->travel(66)->seconds();
        $this->getJson(route('complaints.updates', $ticket))->assertJsonPath('online', false);
        $this->postJson(route('complaints.reply', $ticket), ['message' => 'Tidak dapat chat langsung.'])->assertUnprocessable();
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)->delete(route('complaints.destroy', $ticket))->assertRedirect();
        $this->assertDatabaseMissing('complaint_messages', ['complaint_ticket_id' => $ticket->id]);
        Storage::disk('local')->assertMissing($attachment->attachment);
        $this->actingAs($reporter)->get(route('complaints.index'))->assertOk()->assertDontSee($ticket->reference());
        $this->get(route('complaints.show', $ticket))->assertNotFound();
    }
}
