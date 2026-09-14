<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHelpSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['contacts' => 'array', 'is_active' => 'boolean'];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'title' => 'Hubungi Admin via WhatsApp',
            'description' => 'Pilih admin untuk meminta bantuan reset password.',
            'contacts' => [
                ['name' => 'Sembiru', 'phone' => '6281382830814'],
                ['name' => 'Alam', 'phone' => '6281809597757'],
                ['name' => 'Rizky', 'phone' => '6281295317499'],
            ],
            'message_template' => "Halo Admin {ADMIN}, saya ingin meminta bantuan reset password akun INTEGRAL.\n\nNama Lengkap:\nNIP/NIK:\nEmail Terdaftar:\n\nMohon bantuannya. Terima kasih.",
            'is_active' => true,
        ]);
    }

    public static function normalizeWhatsapp(?string $number): string
    {
        $number = preg_replace('/\D+/', '', (string) $number);
        if (str_starts_with($number, '0')) return '62'.substr($number, 1);
        if (str_starts_with($number, '8')) return '62'.$number;
        return $number;
    }
}
