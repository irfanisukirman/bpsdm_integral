<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\User;
use App\Support\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserRoleController extends Controller
{
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'distinct', Rule::in(array_keys(UserRoles::LABELS))],
            'role_bidang' => ['nullable', 'array'],
        ]);
        if (! in_array($user->primaryRole(), $data['roles'], true)) {
            throw ValidationException::withMessages(['roles' => 'Peran utama tetap dipertahankan. Pilih peran tambahan untuk akun ini.']);
        }
        $scopes = [];
        foreach ($data['roles'] as $role) {
            if ($role === $user->primaryRole()) {
                continue;
            }
            if (in_array($role, UserRoles::SCOPED, true)) {
                $validated = validator($request->all(), ['role_bidang.'.$role => ['required', 'string', Rule::in(Bidang::names())]])->validate();
                $scopes[$role] = $validated['role_bidang'][$role];
            } else {
                $scopes[$role] = $role === 'admin_aset' ? 'Pengelola Aset' : null;
            }
        }
        DB::transaction(function () use ($user, $data, $scopes) {
            // Serialize permission changes, including removal of the last superadmin.
            User::whereKey(User::withRoles(['superadmin'])->select('users.id'))->orderBy('id')->lockForUpdate()->get();
            $target = User::lockForUpdate()->findOrFail($user->id);
            if ($target->hasAssignedRole('superadmin') && ! in_array('superadmin', $data['roles'], true)
                && ! User::withRoles(['superadmin'])->whereKeyNot($target->id)->exists()) {
                throw ValidationException::withMessages(['roles' => 'Minimal satu akun harus tetap memiliki peran Superadmin.']);
            }
            $target->roleAssignments()->whereNotIn('role', array_keys($scopes))->delete();
            foreach ($scopes as $role => $bidang) {
                $target->roleAssignments()->updateOrCreate(['role' => $role], ['bidang' => $bidang]);
            }
        });

        return back()->with('success', 'Peran tambahan '.$user->name.' berhasil diperbarui. Identitas dan riwayat akun tetap tersimpan.');
    }

    public function switch(Request $request)
    {
        $data = $request->validate(['role' => ['required', 'string', Rule::in(array_keys(UserRoles::LABELS))]]);
        $user = $request->user();
        $user->unsetRelation('roleAssignments');
        abort_unless($user->hasAssignedRole($data['role']), 403, 'Peran ini belum diberikan pada akun Anda.');
        $request->session()->put('user_role_context', ['user_id' => $user->id, 'role' => $data['role']]);

        return redirect()->route(UserRoles::home($data['role']));
    }
}
