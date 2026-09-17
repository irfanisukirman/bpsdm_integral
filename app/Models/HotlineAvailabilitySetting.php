<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class HotlineAvailabilitySetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'workdays' => 'array',
        'updated_by' => 'integer',
    ];

    public const MODE_AUTO = 'auto';
    public const MODE_MANUAL_ONLINE = 'manual_online';
    public const MODE_MANUAL_OFFLINE = 'manual_offline';

    public const DAYS = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public const DEFAULT_WORKDAYS = [1, 2, 3, 4, 5];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'mode' => static::MODE_AUTO,
            'opens_at' => '07:30',
            'closes_at' => '16:00',
            'workdays' => static::DEFAULT_WORKDAYS,
            'note' => null,
        ]);
    }

    public function isOnline(Carbon $at = null): bool
    {
        $at = $at ?: now();

        if ($this->mode === static::MODE_MANUAL_ONLINE) {
            return true;
        }
        if ($this->mode === static::MODE_MANUAL_OFFLINE) {
            return false;
        }

        $workdays = is_array($this->workdays) ? $this->workdays : static::DEFAULT_WORKDAYS;
        if (!in_array($at->dayOfWeek, $workdays, true)) {
            return false;
        }

        $opens = $this->opens_at ? static::minutesOf($this->opens_at) : null;
        $closes = $this->closes_at ? static::minutesOf($this->closes_at) : null;
        if ($opens === null || $closes === null) {
            return true;
        }

        $now = $at->hour * 60 + $at->minute;
        if ($closes <= $opens) {
            return $now >= $opens || $now < $closes;
        }
        return $now >= $opens && $now < $closes;
    }

    public function isCommunityAutoOnline(): bool
    {
        return $this->mode === static::MODE_AUTO && $this->isOnline();
    }

    public function hoursLabel(): string
    {
        $opens = $this->opens_at ? substr($this->opens_at, 0, 5) : '07:30';
        $closes = $this->closes_at ? substr($this->closes_at, 0, 5) : '16:00';
        return str_replace(':', '.', $opens).'–'.str_replace(':', '.', $closes);
    }

    public function daysLabel(): string
    {
        $days = is_array($this->workdays) ? $this->workdays : static::DEFAULT_WORKDAYS;
        $names = collect($days)->sort()->map(fn ($d) => static::DAYS[$d] ?? null)->filter()
            ->map(fn ($n) => substr($n, 0, 3))->values()->all();
        return implode('–', $names);
    }

    public function statusInfo(): array
    {
        $online = $this->isOnline();
        if ($this->mode === static::MODE_MANUAL_ONLINE) {
            $label = $this->note ?: 'Admin sedang online';
        } elseif ($this->mode === static::MODE_MANUAL_OFFLINE) {
            $label = $this->note ?: 'Admin sedang offline';
        } else {
            $label = $online
                ? 'Admin sedang online'
                : 'Di luar jam layanan ('.$this->daysLabel().', '.$this->hoursLabel().')';
        }
        return [
            'online' => $online,
            'label' => $label,
            'mode' => $this->mode,
            'hours' => $this->hoursLabel(),
            'days' => $this->daysLabel(),
            'note' => $this->note,
        ];
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    private static function minutesOf(string $time): int
    {
        $parts = explode(':', $time);
        return ((int) $parts[0]) * 60 + (int) ($parts[1] ?? 0);
    }
}