<?php

namespace App\Http\Controllers;

use App\Exports\ComplaintInboxExport;
use App\Models\ComplaintTicket;
use App\Models\NotificationRead;
use App\Models\Training;
use App\Models\User;
use App\Services\ComplaintPresence;
use App\Services\NotificationCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ComplaintController extends Controller
{
    public function __construct(private ComplaintPresence $presence) {}

    public function heartbeat(Request $request, NotificationCenter $notifications)
    {
        $this->presence->heartbeat($request->user());
        $inbox = $request->boolean('inbox');

        return $this->privateJson([
            'waiting' => $notifications->waitingComplaints($request->user(), $request->integer('active_chat') ?: null, !$inbox),
            'unread_chat_ids' => $inbox ? $notifications->unreadStaffChatIds($request->user()) : [],
        ]);
    }

    public function availability()
    {
        return $this->privateJson(['online' => $this->presence->isOnline()]);
    }

    public function unreadReplies(Request $request, NotificationCenter $notifications)
    {
        $tickets = $notifications->unreadComplaintReplies($request->user());
        $latest = $tickets->first();

        return $this->privateJson([
            'count' => $tickets->count(),
            'url' => $latest ? route('complaints.show', ['ticket' => $latest, 'widget' => 1]) : null,
        ]);
    }

    public function index(Request $request, NotificationCenter $notifications)
    {
        $staff = $request->routeIs('complaints.inbox');
        $statusCounts = [];
        if ($staff) {
            abort_unless(in_array($request->user()->role, ComplaintTicket::STAFF_ROLES, true), 403);
            $query = ComplaintTicket::staffInbox($request->user());
            $statusCounts = (clone $query)->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')->pluck('total', 'status')->all();
            $query->with(['user:id,name', 'assignee:id,name']);
        } else {
            $query = ComplaintTicket::where('user_id', $request->user()->id);
        }

        $status = $request->string('status')->toString();
        if ($staff && in_array($status, ['', 'received'], true) && $request->boolean('read')) {
            $notifications->markWaitingComplaintsRead($request->user());

            return redirect()->route('complaints.inbox', ['status' => $status ?: null]);
        }
        $tickets = $query->with('training:id,nama_pelatihan')
            ->when(isset(ComplaintTicket::STATUSES[$status]), fn ($query) => $query->where('status', $status))
            ->latest('updated_at')->orderByDesc('id')->paginate(15)->withQueryString();

        $widget = !$staff && $request->boolean('widget');

        $unreadChatIds = $staff ? array_fill_keys($notifications->unreadStaffChatIds($request->user()), true) : [];

        return $this->privateView('complaints.index', compact('tickets', 'staff', 'status', 'widget', 'statusCounts', 'unreadChatIds'));
    }

    public function export(Request $request)
    {
        abort_unless(in_array($request->user()->role, ComplaintTicket::STAFF_ROLES, true), 403);
        $filters = $request->validate(['status' => ['nullable', Rule::in(array_keys(ComplaintTicket::STATUSES))]]);

        return Excel::download(
            new ComplaintInboxExport($request->user(), $filters['status'] ?? null),
            'rekap-pengaduan-'.now('Asia/Jakarta')->format('Ymd').'.xlsx',
            null,
            ['Cache-Control' => 'private, no-store']
        );
    }

    public function create(Request $request)
    {
        return $this->form($request, false);
    }

    public function guestCreate(Request $request)
    {
        return $this->form($request, true);
    }

    private function form(Request $request, bool $guest)
    {
        $category = $request->string('category')->toString();
        $trainingId = $request->integer('training_id');
        $mode = $request->query('mode');
        $mode = in_array($mode, ['chat', 'report'], true) ? $mode : null;
        $trainings = $guest || $mode === 'chat' ? collect() : $this->availableTrainings($request->user())
            ->orderBy('nama_pelatihan')->get(['id', 'nama_pelatihan', 'angkatan']);

        $widget = $request->boolean('widget');

        $view = $widget && $mode === 'chat' ? 'complaints.chat' : 'complaints.create';

        return $this->privateView($view, compact('guest', 'category', 'trainingId', 'trainings', 'widget', 'mode'));
    }

    public function store(Request $request)
    {
        return $this->createTicket($request, $request->user());
    }

    public function guestStore(Request $request)
    {
        return $this->createTicket($request, null);
    }

    private function createTicket(Request $request, ?User $owner)
    {
        $mode = $request->query('mode');
        $chat = $mode === 'chat';
        $contactRules = $chat ? [
            'chat_name' => ['required', 'string', 'max:100'],
            'chat_institution' => ['required', 'string', 'max:150'],
        ] : [
            'category' => ['required', Rule::in(array_keys(ComplaintTicket::CATEGORIES))],
            'training_id' => ['nullable', 'integer', 'min:1'],
            'guest_name' => ['nullable', 'string', 'max:100'],
        ];
        $data = $request->validate([
            ...$contactRules,
            ...$this->messageRules(),
        ], [], ['chat_name' => 'nama', 'chat_institution' => 'instansi']);
        $category = $chat ? 'other' : $data['category'];
        $trainingId = null;
        if ($owner && in_array($category, ComplaintTicket::TRAINING_CATEGORIES, true) && !empty($data['training_id'])) {
            $trainingId = $this->availableTrainings($owner)->whereKey($data['training_id'])->value('id');
            if (!$trainingId) {
                throw ValidationException::withMessages(['training_id' => 'Pilih pelatihan yang dapat Anda akses.']);
            }
        }

        $token = $owner ? null : bin2hex(random_bytes(24));
        $ticket = $this->writeMessage($request, function ($attachment) use ($owner, $data, $trainingId, $token, $category) {
            $ticket = ComplaintTicket::create([
                'user_id' => $owner?->id,
                'training_id' => $trainingId,
                'guest_name' => $owner ? null : ($data['guest_name'] ?? null),
                'chat_name' => $data['chat_name'] ?? null,
                'chat_institution' => $data['chat_institution'] ?? null,
                'tracking_hash' => $token ? hash('sha256', $token) : null,
                'category' => $category,
                'status' => 'received',
            ]);
            $ticket->messages()->create([
                'user_id' => $owner?->id,
                'body' => $data['message'] ?? '',
                'from_staff' => false,
                'attachment' => $attachment,
            ]);

            return $ticket;
        });

        $liveChat = $chat && $this->presence->isOnline();
        $reportOnly = $mode === 'report';
        $params = ['widget' => $request->boolean('widget') ? 1 : null, 'mode' => $reportOnly ? 'report' : null];

        return $token
            ? redirect()->route('complaints.guest.show', ['token' => $token] + $params)
                ->with('success', $liveChat ? 'Pesan diterima. Admin akan membalas di sini.' : 'Pengaduan diterima. Simpan tautan pelacakan ini.')
            : redirect()->route('complaints.show', ['ticket' => $ticket] + $params)
                ->with('success', $liveChat ? 'Pesan diterima. Admin akan membalas di sini.' : 'Pengaduan diterima.');
    }

    public function track(Request $request)
    {
        return $this->privateView('complaints.track', ['widget' => $request->boolean('widget')]);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'regex:/^[a-f0-9]{48}$/i']]);
        $token = strtolower($data['code']);
        if (!ComplaintTicket::where('tracking_hash', hash('sha256', $token))->exists()) {
            throw ValidationException::withMessages(['code' => 'Kode pelacakan tidak ditemukan.']);
        }

        return redirect()->route('complaints.guest.show', ['token' => $token, 'widget' => $request->boolean('widget') ? 1 : null]);
    }

    public function show(Request $request, ComplaintTicket $ticket, NotificationCenter $notifications)
    {
        $staff = $this->authorizeTicket($request->user(), $ticket);
        $this->loadConversation($ticket, $staff);
        if ($staff) {
            $notifications->markStaffComplaintRead($request->user(), $ticket);
            $this->markStaffChatRead($ticket, $request->user(), $notifications);
        } else {
            $this->markConversationRead($ticket, $request->user()->id);
        }

        $canManage = in_array($request->user()->role, ComplaintTicket::STAFF_ROLES, true);
        $staffOptions = $canManage ? User::withRoles(ComplaintTicket::STAFF_ROLES)->orderBy('name')->get(['id', 'name']) : collect();

        $data = $this->conversationData($ticket, $staff);
        $chatPage = $staff && $request->query('view') === 'chat';
        $view = $ticket->chat_name && ($data['widget'] || $chatPage) ? 'complaints.chat' : 'complaints.show';

        return $this->privateView($view, $data + compact('canManage', 'staffOptions'));
    }

    private function loadConversation(ComplaintTicket $ticket, bool $staff, int $after = 0): void
    {
        $ticket->load(['messages' => fn ($query) => $query->where('id', '>', $after)->orderBy('id'), 'training', 'user']);
        if ($staff) {
            $ticket->messages->load('author:id,name');
        }
    }

    private function markConversationRead(ComplaintTicket $ticket, int $userId): void
    {
        $reply = $ticket->messages->last(fn ($message) => $message->from_staff);
        if ($reply) {
            NotificationRead::firstOrCreate(
                ['user_id' => $userId, 'notification_key' => 'complaint-reply-'.$reply->id],
                ['read_at' => now()]
            );
        }
    }

    private function conversationData(ComplaintTicket $ticket, bool $staff, ?string $token = null): array
    {
        $guest = $token !== null;
        $online = $this->presence->isOnline();

        $widget = !$staff && request()->boolean('widget');
        $reportOnly = !$staff && blank($ticket->chat_name) && request()->query('mode') === 'report';

        return compact('ticket', 'staff', 'guest', 'token', 'online', 'widget', 'reportOnly');
    }

    public function guestShow(string $token)
    {
        $ticket = $this->guestTicket($token);
        $this->loadConversation($ticket, false);

        $data = $this->conversationData($ticket, false, $token);
        $view = $data['widget'] && $ticket->chat_name ? 'complaints.chat' : 'complaints.show';

        return $this->privateView($view, $data);
    }

    public function updates(Request $request, ComplaintTicket $ticket)
    {
        return $this->conversationUpdates($request, $ticket, $this->authorizeTicket($request->user(), $ticket));
    }

    public function guestUpdates(Request $request, string $token)
    {
        return $this->conversationUpdates($request, $this->guestTicket($token), false, $token);
    }

    private function conversationUpdates(Request $request, ComplaintTicket $ticket, bool $staff, ?string $token = null)
    {
        $lastMessageId = (int) $ticket->messages()->max('id');
        $data = $this->conversationData($ticket, $staff, $token);
        $payload = [
            'online' => $data['online'],
            'status' => ComplaintTicket::STATUSES[$ticket->status],
            'chat_ended' => filled($ticket->chat_name) && $ticket->isResolved(),
            'ticket_ended' => $ticket->isResolved(),
            'last_message_id' => $lastMessageId,
        ];
        $after = max(0, $request->integer('after'));
        if ($lastMessageId > $after) {
            if ($after && $ticket->chat_name) {
                $previous = $ticket->messages()->where('id', '<=', $after)->latest('id')->first(['created_at']);
                $data['previousDay'] = $previous?->created_at?->timezone('Asia/Jakarta')->toDateString();
            }
            $this->loadConversation($ticket, $staff, $after);
            $payload['messages'] = view('complaints.messages', $data)->render();
            $payload['last_message_id'] = (int) ($ticket->messages->last()?->id ?? $lastMessageId);
            if ($staff) {
                $this->markStaffChatRead($ticket, $request->user(), app(NotificationCenter::class));
            } elseif ($token === null) {
                $this->markConversationRead($ticket, $request->user()->id);
            }
        }

        return $this->privateJson($payload);
    }

    private function markStaffChatRead(ComplaintTicket $ticket, User $staff, NotificationCenter $notifications): void
    {
        if ($ticket->chat_name) {
            $message = $ticket->messages->last(fn ($message) => !$message->from_staff);
            if ($message) {
                $notifications->markStaffChatRead($staff, $message->id);
            }
        }
    }

    public function reply(Request $request, ComplaintTicket $ticket)
    {
        $staff = $this->authorizeTicket($request->user(), $ticket);
        $this->saveReply($request, $ticket, $staff, $request->user()->id);
        if ($request->expectsJson()) {
            return $this->privateJson(['saved' => true]);
        }

        return redirect()->route('complaints.show', [
            'ticket' => $ticket,
            'widget' => !$staff && $request->boolean('widget') ? 1 : null,
            'view' => $staff && $ticket->chat_name && $request->query('view') === 'chat' ? 'chat' : null,
        ])
            ->with('success', 'Balasan tersimpan.');
    }

    public function guestReply(Request $request, string $token)
    {
        $this->saveReply($request, $this->guestTicket($token), false, null);
        if ($request->expectsJson()) {
            return $this->privateJson(['saved' => true]);
        }

        return redirect()->route('complaints.guest.show', ['token' => $token, 'widget' => $request->boolean('widget') ? 1 : null])
            ->with('success', 'Balasan tersimpan.');
    }

    private function saveReply(Request $request, ComplaintTicket $ticket, bool $staff, ?int $authorId): void
    {
        if (!$staff && !$this->presence->isOnline()) {
            throw ValidationException::withMessages(['message' => 'Admin sedang offline. Anda masih dapat memantau tiket atau membuat pengaduan baru.']);
        }
        $rules = $this->messageRules();
        if ($staff) {
            $rules['message'] = ['nullable', 'string', 'max:5000'];
            $rules['status'] = ['nullable', Rule::in(array_keys(ComplaintTicket::STATUSES))];
        }
        $data = $request->validate($rules);
        $this->writeMessage($request, function ($attachment) use ($ticket, $staff, $authorId, $data) {
            $ticket = ComplaintTicket::lockForUpdate()->findOrFail($ticket->id);
            if ($ticket->isResolved()) {
                throw ValidationException::withMessages(['message' => $ticket->chat_name
                    ? 'Percakapan ini sudah berakhir. Mulai chat baru jika masih membutuhkan bantuan.'
                    : 'Pengaduan ini sudah selesai. Ajukan pengaduan baru jika masih membutuhkan bantuan.']);
            }
            $status = $staff ? ($data['status'] ?? $ticket->status) : $ticket->status;
            $message = $data['message'] ?? null;
            if ($staff && filled($ticket->chat_name) && $ticket->status === 'received'
                && $status === 'received' && ($message !== null || $attachment)) {
                $status = 'in_progress';
            }
            if ($message === null && !$attachment && $status === $ticket->status) {
                throw ValidationException::withMessages(['message' => 'Tulis balasan, lampirkan gambar, atau pilih status lain.']);
            }
            $ticket->messages()->create([
                'user_id' => $authorId,
                'from_staff' => $staff,
                'body' => $message ?? ($status !== $ticket->status ? 'Status tiket diubah menjadi '.ComplaintTicket::STATUSES[$status].'.' : ''),
                'attachment' => $attachment,
            ]);
            $ticket->status = $status;
            $ticket->touch();
        });
    }

    public function forward(Request $request, ComplaintTicket $ticket)
    {
        abort_unless(in_array($request->user()->role, ComplaintTicket::STAFF_ROLES, true), 403);
        $data = $request->validate(['assigned_to' => ['required', 'integer']]);
        $assignee = User::withRoles(ComplaintTicket::STAFF_ROLES)->find($data['assigned_to']);
        if (!$assignee) {
            throw ValidationException::withMessages(['assigned_to' => 'Pilih akun admin yang dapat menangani pengaduan.']);
        }
        DB::transaction(function () use ($ticket, $assignee) {
            $locked = ComplaintTicket::lockForUpdate()->findOrFail($ticket->id);
            if ((int) $locked->assigned_to !== (int) $assignee->id) {
                NotificationRead::where('user_id', $assignee->id)
                    ->where('notification_key', 'complaint-assigned-'.$locked->id)->delete();
            }
            $locked->update(['assigned_to' => $assignee->id]);
        });

        return redirect()->route('complaints.show', $ticket)->with('success', 'Pengaduan diteruskan kepada '.$assignee->name.'.');
    }

    public function resolve(Request $request, ComplaintTicket $ticket)
    {
        abort_unless(in_array($request->user()->role, ComplaintTicket::STAFF_ROLES, true), 403);
        DB::transaction(function () use ($request, $ticket) {
            $locked = ComplaintTicket::lockForUpdate()->findOrFail($ticket->id);
            if ($locked->status === 'resolved') {
                return;
            }

            $locked->messages()->create([
                'user_id' => $request->user()->id,
                'from_staff' => true,
                'body' => 'Status tiket diubah menjadi '.ComplaintTicket::STATUSES['resolved'].'.',
            ]);
            $locked->update(['status' => 'resolved']);
        });

        return redirect()->route('complaints.inbox')->with('success', 'Tiket ditandai selesai.');
    }

    public function destroy(Request $request, ComplaintTicket $ticket)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $paths = DB::transaction(function () use ($ticket) {
            $locked = ComplaintTicket::lockForUpdate()->findOrFail($ticket->id);
            $paths = $locked->messages()->whereNotNull('attachment')->pluck('attachment')->all();
            $locked->delete();

            return $paths;
        });
        if ($paths) {
            Storage::disk('local')->delete($paths);
        }

        return redirect()->route('complaints.inbox')->with('success', 'Pengaduan telah dihapus.');
    }

    private function messageRules(): array
    {
        return [
            'message' => ['required_without:attachment', 'nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function writeMessage(Request $request, callable $write)
    {
        $attachment = $request->file('attachment')?->store('complaints', 'local');
        if ($request->hasFile('attachment') && !$attachment) {
            throw ValidationException::withMessages(['attachment' => 'Lampiran gagal disimpan. Coba unggah kembali.']);
        }

        try {
            return DB::transaction(fn () => $write($attachment));
        } catch (\Throwable $error) {
            if ($attachment) {
                Storage::disk('local')->delete($attachment);
            }
            throw $error;
        }
    }

    public function download(Request $request, ComplaintTicket $ticket, int $message)
    {
        $this->authorizeTicket($request->user(), $ticket);

        return $this->downloadAttachment($ticket, $message, $request->boolean('preview'));
    }

    public function guestDownload(Request $request, string $token, int $message)
    {
        return $this->downloadAttachment($this->guestTicket($token), $message, $request->boolean('preview'));
    }

    private function downloadAttachment(ComplaintTicket $ticket, int $message, bool $preview)
    {
        $path = $ticket->messages()->findOrFail($message)->attachment;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $headers = [
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ];
        $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        if ($preview && isset($mime[$extension])) {
            return Storage::disk('local')->response($path, 'lampiran.'.$extension, $headers + ['Content-Type' => $mime[$extension]]);
        }

        return Storage::disk('local')->download($path, 'lampiran.'.$extension, $headers);
    }

    private function guestTicket(string $token): ComplaintTicket
    {
        return ComplaintTicket::whereNull('user_id')->where('tracking_hash', hash('sha256', $token))->firstOrFail();
    }

    private function authorizeTicket(User $user, ComplaintTicket $ticket): bool
    {
        if ($ticket->user_id && (int) $ticket->user_id === (int) $user->id) {
            return false;
        }
        abort_unless(ComplaintTicket::staffInbox($user)->whereKey($ticket->id)->exists(), 404);

        return true;
    }

    private function availableTrainings(User $user): Builder
    {
        return Training::query()->when($user->role !== 'superadmin', fn ($query) => $query->where(
            function ($scope) use ($user) {
                $scope->whereHas('participants', fn ($participants) => $participants->where('user_id', $user->id))
                    ->orWhereHas('schedules', fn ($schedules) => $schedules->where('pengajar_id', $user->id));
                if ($user->role === 'admin_bidang' && filled($user->bidang)) {
                    $scope->orWhere('bidang', $user->bidang);
                }
            }
        ));
    }

    private function privateView(string $view, array $data = [])
    {
        return response()->view($view, $data, 200, [
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    private function privateJson(array $data)
    {
        return response()->json($data, 200, ['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer']);
    }
}
