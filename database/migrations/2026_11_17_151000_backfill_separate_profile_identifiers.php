<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Preserve legacy identity and only copy unambiguous values into empty fields.
        DB::transaction(function () {
            DB::table('users')->select(['id', 'nip_nik', 'nip', 'nik'])->orderBy('id')->chunkById(250, function ($users) {
                foreach ($users as $user) {
                    $identity = trim((string) $user->nip_nik);
                    $field = preg_match('/^[0-9]{18}$/D', $identity) ? 'nip'
                        : (preg_match('/^[0-9]{16}$/D', $identity) ? 'nik' : null);
                    if (!$field || filled($user->{$field})) continue;
                    if (DB::table('users')->where($field, $identity)->where('id', '!=', $user->id)->exists()) continue;
                    // Duplicated legacy numbers need manual verification; never pick an arbitrary owner.
                    if (DB::table('users')->where('id', '!=', $user->id)->whereRaw('TRIM(nip_nik) = ?', [$identity])->exists()) continue;
                    DB::table('users')->where('id', $user->id)->whereNull($field)->update([$field => $identity]);
                }
            });
        });
    }

    public function down(): void
    {
        // Copied identifiers may have since been verified by users; keep them intact.
    }
};
