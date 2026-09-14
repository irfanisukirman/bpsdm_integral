<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetPublicReservation extends Model
{
    protected $guarded = [];

    protected $casts = ['tracking_events' => 'array', 'rate_breakdown' => 'array', 'rental_date' => 'date', 'rules_accepted_at' => 'datetime', 'payment_due_at' => 'datetime', 'payment_uploaded_at' => 'datetime', 'reviewed_at' => 'datetime', 'hourly_rate' => 'decimal:2', 'total_amount' => 'decimal:2'];

    protected static function booted(): void
    {
        static::saving(function (self $reservation) {
            if (! $reservation->exists || $reservation->isDirty('status')) {
                $events = $reservation->tracking_events ?? [];
                if (! $events && $reservation->exists) {
                    $events = $reservation->trackingTimeline();
                }
                $events[] = ['status' => $reservation->status, 'label' => $reservation->status_label, 'at' => now()->toIso8601String(), 'note' => in_array($reservation->status, ['awaiting_payment', 'revision', 'confirmed', 'completed', 'cancelled', 'rejected'], true) ? $reservation->admin_note : null];
                $reservation->tracking_events = $events;
            }
        });
    }

    public function trackingTimeline(): array
    {
        if ($this->tracking_events) {
            return $this->tracking_events;
        }
        $events = [];
        if ($this->payment_uploaded_at) {
            $events[] = ['status' => 'payment_uploaded', 'label' => 'Bukti pembayaran dikirim', 'at' => $this->payment_uploaded_at->toIso8601String(), 'note' => null];
        }
        $events[] = ['status' => $this->getRawOriginal('status') ?: $this->status, 'label' => (new self(['status' => $this->getRawOriginal('status') ?: $this->status]))->status_label, 'at' => $this->getRawOriginal('reviewed_at') ?: $this->updated_at?->toIso8601String(), 'note' => $this->getRawOriginal('admin_note')];

        return $events;
    }

    public function getWhatsappNumberAttribute(): string
    {
        return AssetRentalSetting::normalizeWhatsapp($this->whatsapp);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function booking()
    {
        return $this->morphOne(AssetBooking::class, 'bookable');
    }

    public function getStatusLabelAttribute(): string
    {
        return ['pending_review' => 'Menunggu Pemeriksaan', 'awaiting_payment' => 'Menunggu Pembayaran', 'payment_uploaded' => 'Menunggu Verifikasi Pembayaran', 'confirmed' => 'Dikonfirmasi', 'revision' => 'Perlu Perbaikan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai'][$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return ['pending_review' => 'warning', 'awaiting_payment' => 'info', 'payment_uploaded' => 'primary', 'confirmed' => 'success', 'revision' => 'warning', 'rejected' => 'danger', 'cancelled' => 'secondary', 'completed' => 'success'][$this->status] ?? 'secondary';
    }
}
