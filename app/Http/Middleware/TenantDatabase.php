<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Single-tenant fallback mode
        |--------------------------------------------------------------------------
        */

        if (!config('adc.multi_tenant_enabled')) {

            $defaultDatabase = config('adc.default_database');

            config([
                'database.connections.mysql.database' => $defaultDatabase,
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Public / authentication routes
        |--------------------------------------------------------------------------
        |
        | Login must be allowed before a tenant has been selected.
        |
        */

        if (
            $request->is('login') ||
            $request->is('logout') ||
            $request->is('register') ||
            $request->is('forgot-password') ||
            $request->is('reset-password*') ||
            $request->is('verify-email*')
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | No authenticated user
        |--------------------------------------------------------------------------
        */

        if (!Auth::guard('web')->check()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Get authenticated central SaaS user
        |--------------------------------------------------------------------------
        */

        $user = Auth::guard('web')->user();

        if (!$user || empty($user->organization_id)) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'No organization is assigned to this user.');
        }

        /*
        |--------------------------------------------------------------------------
        | Organization must come from authenticated user
        |--------------------------------------------------------------------------
        |
        | Do NOT trust organization_id stored in session as the authority.
        |
        */

        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $user->organization_id)
            ->where('status', 1)
            ->where('is_ready', 1)
            ->first();

        if (!$organization || empty($organization->database_name)) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your organization is unavailable.');
        }

        /*
        |--------------------------------------------------------------------------
        | Protect against session manipulation
        |--------------------------------------------------------------------------
        */

        if (
            session()->has('organization_id') &&
            (int) session('organization_id') !== (int) $user->organization_id
        ) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Organization session mismatch.');
        }

        /*
        |--------------------------------------------------------------------------
        | Store trusted organization information
        |--------------------------------------------------------------------------
        */

        session([
            'organization_id'   => $organization->id,
            'organization_name' => $organization->organization_name,
            'tenant_database'   => $organization->database_name,
            'media_folder'      => $organization->media_folder,
            'has_shift'         => $organization->has_shift,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Switch tenant database
        |--------------------------------------------------------------------------
        */

        config([
            'database.connections.mysql.database'
            => $organization->database_name,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        /*
        |--------------------------------------------------------------------------
        | Verify physical database connection
        |--------------------------------------------------------------------------
        */

        $currentDatabase = DB::connection('mysql')->getDatabaseName();

        if ($currentDatabase !== $organization->database_name) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Tenant database connection mismatch.');
        }

        /*
        |--------------------------------------------------------------------------
        | Verify tenant database structure
        |--------------------------------------------------------------------------
        */

        if (!Schema::connection('mysql')->hasTable('members')) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Invalid tenant database.');
        }

        return $next($request);
    }
}
