<?php

namespace App\Http\Controllers;

use App\Models\AgendaSchedule;
use App\Models\AssetLoanRequest;
use App\Models\NotificationRead;
use App\Models\Schedule;
use App\Services\NotificationCenter;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(NotificationCenter $notificationCenter)
    {
        $notifications = $notificationCenter->forUser(Auth::user());

        return view('notifications.index', compact('notifications'));
    }
    public function openAssetLoan(AssetLoanRequest $loan, string $status)
    {
        $user = Auth::user();
        abort_unless(in_array($status, ['approved', 'revision', 'rejected'], true) && $loan->status === $status, 404);
        abort_unless($user->role === 'superadmin' || (int) $loan->submitted_by === (int) $user->id, 403);

        NotificationRead::updateOrCreate(
            ['user_id' => $user->id, 'notification_key' => 'asset-loan-'.$loan->id.'-'.$status],
            ['read_at' => now()]
        );

        $loan->load('requestable');
        $source = $loan->requestable;
        if ($user->role === 'superadmin') {
            return redirect()->route('asset-loans.index', ['status' => $status]);
        }
        if ($source instanceof Schedule) {
            return redirect()->route('trainings.schedules', $source->training_id);
        }
        if ($source instanceof AgendaSchedule) {
            return redirect()->route('agendas.edit', $source->agenda_id);
        }

        return redirect()->route('notifications.index');
    }
}
