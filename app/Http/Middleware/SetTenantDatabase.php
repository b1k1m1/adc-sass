<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $databaseName = session('tenant_database');

        if (!empty($databaseName)) {
            Config::set('database.connections.tenant.database', $databaseName);

            DB::purge('tenant');
            DB::reconnect('tenant');
        }

        return $next($request);
    }
}
