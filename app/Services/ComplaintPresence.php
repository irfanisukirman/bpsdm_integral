<?php

namespace App\Services;

use App\Models\ComplaintTicket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ComplaintPresence
{
    private const PRESENCE_KEY = 'complaints:presence:staff';

    public function heartbeat(User $staff): void
    {
        abort_unless(in_array($staff->role, ComplaintTicket::STAFF_ROLES, true), 403);

        Cache::put(self::PRESENCE_KEY, true, 65);
    }

    public function isOnline(): bool
    {
        return Cache::has(self::PRESENCE_KEY);
    }
}
