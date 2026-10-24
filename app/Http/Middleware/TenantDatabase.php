<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {

        if (!config('adc.multi_tenant_enabled')) {

            /*
        |--------------------------------------------------------------------------
        | Default database
        |--------------------------------------------------------------------------
        */

            $defaultDatabase = config('adc.default_database');


            /*
        |--------------------------------------------------------------------------
        | Get default organization information
        |--------------------------------------------------------------------------
        */

            $defaultOrganization = DB::connection('master')
                ->table('organizations')
                ->where('database_name', $defaultDatabase)
                ->where('status', 1)
                ->first();


            /*
        |--------------------------------------------------------------------------
        | Remove tenant selection
        |--------------------------------------------------------------------------
        */

            session()->forget([
                'organization_id',
                'organization_name',
                'tenant_database',
                'media_folder',
                'has_shift',
            ]);


            /*
        |--------------------------------------------------------------------------
        | Restore default organization information
        |--------------------------------------------------------------------------
        */

            if ($defaultOrganization) {

                session([
                    'organization_name' => $defaultOrganization->organization_name,
                    'media_folder'      => $defaultOrganization->media_folder,
                    'has_shift'         => $defaultOrganization->has_shift,
                ]);
            }



            /*
            |--------------------------------------------------------------------------
            | Force default database
            |--------------------------------------------------------------------------
            */

            config([
                'database.connections.mysql.database' => $defaultDatabase,
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');

            /*
            |--------------------------------------------------------------------------
            | Clear cached authenticated user
            |--------------------------------------------------------------------------
            */

            //Auth::guard('web')->forgetUser();



            return $next($request);
        }
        /*
         * Do not switch tenant database during authentication routes.
         * Login must complete first.
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

        if (session()->has('organization_id')) {

            $organization = DB::connection('master')
                ->table('organizations')
                ->where('id', session('organization_id'))
                ->where('status', 1)
                ->first();

            if (!$organization || empty($organization->database_name)) {
                abort(403, 'Invalid organization selected');
            }

            session([
                'organization_name' => $organization->organization_name,
                'tenant_database'   => $organization->database_name,
                'media_folder'      => $organization->media_folder,
                'has_shift'         => $organization->has_shift,
            ]);

            config([
                'database.connections.mysql.database' => $organization->database_name,
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');

            if (!Schema::connection('mysql')->hasTable('members')) {
                abort(403, 'Invalid tenant database');
            }
        }

        return $next($request);
    }
}
