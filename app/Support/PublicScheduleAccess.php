<?php

namespace App\Support;

class PublicScheduleAccess
{
    public static function token(): string
    {
        return hash_hmac('sha256', 'bpsdm-public-daily-schedule', (string) config('app.key'));
    }

    public static function url(): string
    {
        return route('public.daily-schedule', ['token' => self::token()]);
    }
}
