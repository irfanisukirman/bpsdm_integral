<?php
namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Training;
use App\Models\TrainingForumRead;
use App\Models\TrainingMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingForumController extends Controller
{
    public function index(Training $training)
    {
        $this->authorizeAccess($training);
        $this->markRead($training);
        return view('forum.index', compact('training'));
    }

    public function messages(Request $request, Training $training)
    {
        $this->authorizeAccess($training);
        $afterId = max(0, $request->integer('after_id'));
        $query = TrainingMessage::with('user:id,name,role')->where('training_id', $training->id);
        $messages = $afterId
            ? $query->where('id', '>', $afterId)->orderBy('id')->limit(100)->get()
            : $query->latest('id')->limit(100)->get()->sortBy('id')->values();
        if ($messages->isNotEmpty()) $this->markRead($training, $messages->max('id'));
        return response()->json($messages->map(fn ($message) => $this->payload($message)));
    }

    public function store(Request $request, Training $training)
    {
        $this->authorizeAccess($training);
        $data = $request->validate(['message' => 'required|string|max:2000']);
        $message = TrainingMessage::create([
            'training_id' => $training->id,
            'user_id' => Auth::id(),
            'message' => trim($data['message']),
        ])->load('user:id,name,role');
        $this->markRead($training, $message->id);
        return response()->json($this->payload($message), 201);
    }

    public function destroy(Training $training, TrainingMessage $message)
    {
        $this->authorizeAccess($training);
        abort_unless((int) $message->training_id === (int) $training->id, 404);
        abort_unless((int) $message->user_id === (int) Auth::id() || $this->canModerate($training), 403);
        $message->delete();
        return response()->json(['message' => 'Pesan dihapus.']);
    }

    private function authorizeAccess(Training $training): void
    {
        $user = Auth::user();
        if ($user->role === 'superadmin') return;
        if ($user->role === 'admin_bidang') {
            abort_unless(
                (int) $training->created_by === (int) $user->id
                || (!$training->created_by && $training->bidang === $user->bidang),
                403
            );
            return;
        }
        $isTeacher = $training->schedules()->where('pengajar_id', $user->id)->exists();
        $isParticipant = Participant::where('training_id', $training->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('nip_nik', $user->nip_nik ?: $user->username);
            })
            ->where(function ($query) {
                $query->where('registration_status', 'approved')->orWhereNull('registration_status');
            })->exists();
        abort_unless($isTeacher || $isParticipant, 403);
    }

    private function canModerate(Training $training): bool
    {
        $user = Auth::user();
        return $user->role === 'superadmin'
            || ($user->role === 'admin_bidang' && (
                (int) $training->created_by === (int) $user->id
                || (!$training->created_by && $training->bidang === $user->bidang)
            ));
    }

    private function markRead(Training $training, ?int $messageId = null): void
    {
        $messageId ??= (int) TrainingMessage::where('training_id', $training->id)->max('id');
        TrainingForumRead::updateOrCreate(
            ['training_id' => $training->id, 'user_id' => Auth::id()],
            ['last_read_message_id' => $messageId ?: null]
        );
    }

    private function payload(TrainingMessage $message): array
    {
        return [
            'id' => $message->id,
            'name' => $message->user->name,
            'role' => str_replace('_', ' ', $message->user->role),
            'message' => $message->message,
            'time' => $message->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i'),
            'mine' => (int) $message->user_id === (int) Auth::id(),
            'can_delete' => (int) $message->user_id === (int) Auth::id() || $this->canModerate($message->training),
        ];
    }
}
