<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRentalSetting extends Model
{
    protected $guarded = [];

    public static function normalizeWhatsapp(?string $number): string
    {
        $number = preg_replace('/\D/', '', $number ?? '');

        return str_starts_with($number, '0') ? '62'.substr($number, 1) : (str_starts_with($number, '8') ? '62'.$number : $number);
    }

    public function getManagerWhatsappNumberAttribute(): string
    {
        return self::normalizeWhatsapp($this->manager_whatsapp);
    }

    public static function current(): self
    {
        return static::firstOrCreate([], ['bank_name' => 'Bank BJB', 'bank_account' => '0025506995102', 'bank_account_name' => 'BENDAHARA PENERIMAAN BPSDM PROV JBR', 'payment_deadline_hours' => 24]);
    }
}
