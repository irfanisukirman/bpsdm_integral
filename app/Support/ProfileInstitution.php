<?php

namespace App\Support;

use App\Models\Institution;
use App\Models\InstitutionMunicipality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileInstitution
{
    public const SCOPES = [
        'province' => 'Pemerintah Provinsi Jawa Barat',
        'municipality' => 'Pemerintah Kabupaten/Kota Jawa Barat',
        'other' => 'Instansi/Lembaga Lainnya',
    ];

    public static function municipalities(User $user)
    {
        return InstitutionMunicipality::where(fn ($query) => $query->where('is_active', true)
            ->orWhere('id', $user->institution_municipality_id))
            ->orderBy('name')->get();
    }

    public static function rules(Request $request, User $user): array
    {
        // Form lama hanya mengirim ID instansi master.
        if (! $request->has('institution_scope') && $request->filled('institution_id')) {
            $request->merge(['institution_scope' => 'province']);
        }

        return [
            'institution_scope' => ['required', Rule::in(array_keys(self::SCOPES))],
            'institution_id' => ['exclude_unless:institution_scope,province', 'required', 'integer',
                Rule::exists('institutions', 'id')->where('is_active', true)],
            'institution_municipality_id' => ['exclude_unless:institution_scope,municipality', 'required', 'integer',
                Rule::exists('institution_municipalities', 'id')->where(fn ($query) => $query
                    ->where(fn ($choices) => $choices->where('is_active', true)->orWhere('id', $user->institution_municipality_id)))],
            'institution_name' => ['exclude_if:institution_scope,province', 'required', 'string', 'max:255'],
        ];
    }

    public static function attributes(array $validated): array
    {
        $institution = isset($validated['institution_id']) ? Institution::findOrFail($validated['institution_id']) : null;

        return [
            'institution_scope' => $validated['institution_scope'],
            'institution_id' => $institution?->id,
            'institution_municipality_id' => $validated['institution_municipality_id'] ?? null,
            'instansi' => $institution?->name ?? $validated['institution_name'],
        ];
    }
}
