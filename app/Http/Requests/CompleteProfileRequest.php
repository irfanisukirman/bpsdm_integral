<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Validasi pelengkapan profil untuk akun yang sudah login (misalnya hasil
 * impor peserta atau akun Google yang belum verfdata lengkap).
 */
class CompleteProfileRequest extends ProfileDataRequest
{
    protected function targetUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = $this->profileRules();

        $rules['password'] = [
            Rule::requiredIf(fn (): bool => (bool) $this->user()?->must_change_password),
            'nullable',
            'string',
            'min:8',
            'confirmed',
        ];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'password.required' => 'Password baru wajib dibuat sebelum melanjutkan.',
            'password.min' => 'Password minimal 8 karakter dan jangan gunakan NIP/NIK.',
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru.',
        ]);
    }
}