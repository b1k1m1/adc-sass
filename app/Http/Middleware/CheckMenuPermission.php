<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckMenuPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // Laravel 12 safe way
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $roleId = $user->role ?? null;
        if (!$roleId) {
            abort(403, 'Access denied');
        }

        // Session cache per role
        $cacheKey = "role_perm_names_$roleId";
        $rolePerms = session($cacheKey);

        if (!is_array($rolePerms)) {

            // NO JOIN: get permission IDs
            $permIds = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->pluck('permission_id')
                ->toArray();

            $rolePerms = [];

            if (!empty($permIds)) {
                $rolePerms = DB::table('permissions')
                    ->whereIn('id', $permIds)
                    ->where('guard_name', 'web')
                    ->pluck('name')
                    ->map(fn ($v) => strtolower(trim($v)))
                    ->toArray();
            }

            session([$cacheKey => $rolePerms]);
        }

        if (!in_array(strtolower(trim($permission)), $rolePerms, true)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
