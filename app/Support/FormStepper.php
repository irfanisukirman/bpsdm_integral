<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * Membantu menentukan langkah wizard yang harus ditampilkan ulang ketika
 * halaman form dirender, baik pada load pertama maupun setelah validasi gagal.
 */
class FormStepper
{
    /**
     * Field password tidak pernah ikut di-flash ke input lama demi keamanan,
     * jadi kelengkapan field ini tidak boleh menentukan langkah tujuan.
     *
     * @var array<int, string>
     */
    private const NEVER_FLASHED = ['password', 'password_confirmation'];

    /**
     * Langkah pertama yang belum terisi, atau langkah terakhir bila semua lengkap.
     *
     * Bila ada validator error, langkah yang memuat error pertama diprioritaskan
     * agar pengguna langsung melihat isian yang perlu diperbaiki setelah submit.
     *
     * @param  array<int, array{key: string, label: string, fields: array<int, string>}>  $steps
     */
    public static function initialStep(array $steps, ?ViewErrorBag $errors = null): int
    {
        if ($steps === []) {
            return 0;
        }

        if ($errors !== null) {
            $messages = $errors->getMessages();

            foreach ($steps as $index => $step) {
                foreach (self::errorFields($step) as $field) {
                    if (isset($messages[$field])) {
                        return $index;
                    }
                }
            }
        }

        foreach ($steps as $index => $step) {
            if (! self::isStepFilled($step['fields'] ?? [])) {
                return $index;
            }
        }

        return count($steps) - 1;
    }

    /**
     * Field yang error-nya ditampilkan pada langkah ini. Berbeda dengan
     * "fields" (penentu kelengkapan), daftar ini boleh memuat field opsional
     * seperti golongan atau username yang tetap bisa menghasilkan error.
     *
     * @param  array{key?: string, label?: string, fields?: array<int, string>, error_fields?: array<int, string>}  $step
     * @return array<int, string>
     */
    private static function errorFields(array $step): array
    {
        return $step['error_fields'] ?? $step['fields'] ?? [];
    }

    /**
     * @param  array<int, string>  $fields
     */
    public static function isStepFilled(array $fields): bool
    {
        $fields = array_values(array_diff($fields, self::NEVER_FLASHED));

        if ($fields === []) {
            return false;
        }

        foreach ($fields as $field) {
            $value = old($field);

            if ($value === null) {
                // Field yang belum pernah diisi tidak punya nilai lama.
                return false;
            }

            if (is_string($value) && trim($value) === '') {
                return false;
            }

            if (is_array($value) && Arr::where($value, fn ($item) => ! is_string($item) || trim($item) !== '') === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pada wizard, error untuk langkah yang belum pernah dibuka membingungkan.
     * Method ini menyisakan pesan milik langkah 1..langkah aktif, sedangkan
     * pesan di luar rentang itu tetap bisa dilihat lewat ringkasan di stepper.
     *
     * @param  array<int, array{key: string, label: string, fields: array<int, string>}>  $steps
     */
    public static function errorsUpToStep(ViewErrorBag $errors, array $steps, int $activeStep): ViewErrorBag
    {
        $messages = $errors->getMessages();
        $keep = [];

        foreach (array_slice($steps, 0, $activeStep + 1) as $step) {
            foreach (self::errorFields($step) as $field) {
                if (isset($messages[$field])) {
                    $keep[$field] = $messages[$field];
                }
            }
        }

        $filtered = new ViewErrorBag;

        if ($keep !== []) {
            $filtered->put('default', new MessageBag($keep));
        }

        return $filtered;
    }

    /**
     * Jumlah pesan error per langkah, untuk ringkasan pada stepper.
     *
     * @param  array<int, array{key: string, label: string, fields: array<int, string>}>  $steps
     * @return array<int, int>
     */
    public static function errorCountsPerStep(ViewErrorBag $errors, array $steps): array
    {
        $messages = $errors->getMessages();
        $counts = [];

        foreach ($steps as $index => $step) {
            $count = 0;

            foreach (self::errorFields($step) as $field) {
                $count += count($messages[$field] ?? []);
            }

            $counts[$index] = $count;
        }

        return $counts;
    }
}