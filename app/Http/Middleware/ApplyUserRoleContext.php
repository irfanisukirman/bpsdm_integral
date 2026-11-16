<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApplyUserRoleContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }
        $user->unsetRelation('roleAssignments');
        $context = $request->session()->get('user_role_context');
        if ($user->primaryRole() === 'participant' && ($user->must_complete_profile || $user->must_change_password)) {
            $context = null;
        }
        if (is_array($context) && ($context['user_id'] ?? null) === $user->id && $user->hasAssignedRole($context['role'] ?? '')) {
            $user->useRoleContext($context['role']);
        } else {
            $request->session()->forget('user_role_context');
            $user->useRoleContext(null);
        }
        try {
            return $next($request);
        } finally {
            $user->useRoleContext(null);
        }
    }
}
