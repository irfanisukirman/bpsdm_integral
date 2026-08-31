<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;

class DocumentAccessService
{
    public function permission(User $user, Folder $folder): ?string
    {
        if ($user->role === 'superadmin' || (int) $folder->user_id === (int) $user->id) return 'owner';
        if ($user->role === 'admin_bidang' && ($folder->bidang === 'Semua Bidang' || ($user->bidang && $folder->bidang === $user->bidang))) return 'contributor';

        $cursor = $folder;
        while ($cursor) {
            $permission = $cursor->permissions()->where('user_id', $user->id)->value('permission');
            if ($permission) return $permission;
            $cursor = $cursor->parent;
        }

        return $folder->bidang === 'Semua Bidang' ? 'viewer' : null;
    }

    public function canView(User $user, Folder $folder): bool { return $this->permission($user, $folder) !== null; }
    public function canContribute(User $user, Folder $folder): bool { return in_array($this->permission($user, $folder), ['owner', 'contributor'], true); }
    public function canManage(User $user, Folder $folder): bool { return $user->role === 'superadmin' || (int) $folder->user_id === (int) $user->id; }
}