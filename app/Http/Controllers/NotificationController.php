<?php

namespace App\Http\Controllers;

use App\Services\NotificationCenter;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(NotificationCenter $notificationCenter)
    {
        $notifications = $notificationCenter->forUser(Auth::user());

        return view('notifications.index', compact('notifications'));
    }
}
