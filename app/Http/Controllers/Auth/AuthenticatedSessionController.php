<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Organization;
use App\Services\LoginDashboardNoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\TenantUser;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        /*
        |--------------------------------------------------------------------------
        | SaaS Login
        |--------------------------------------------------------------------------
        |
        | Do NOT send organizations to the login page.
        |
        */

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Authenticate against adc_master.tenant_users
        |--------------------------------------------------------------------------
        */

        $request->authenticate();

        /** @var TenantUser|null $user */
        $user = Auth::guard('web')->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => 'Unable to authenticate user.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 2: Determine organization automatically
        |--------------------------------------------------------------------------
        */

        if (empty($user->organization_id)) {

            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => 'No organization is assigned to this account.',
            ]);
        }

        $organization = Organization::on('master')
            ->where('id', $user->organization_id)
            ->where('status', 1)
            ->first();

        if (!$organization) {

            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => 'Your organization account is unavailable.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 3: Verify organization is ready
        |--------------------------------------------------------------------------
        */

        if ((int) $organization->is_ready !== 1) {

            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => 'Your organization is not currently available. Please contact administrator.',
            ]);
        }

        if (empty($organization->database_name)) {

            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => 'Database configuration is missing for your organization.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 4: Switch application database to tenant
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
        | Step 5: Security checks
        |--------------------------------------------------------------------------
        */

        $currentDatabase = DB::connection('mysql')
            ->getDatabaseName();

        if ($currentDatabase !== $organization->database_name) {

            Auth::guard('web')->logout();

            abort(403, 'Database connection mismatch.');
        }

        /*
        |--------------------------------------------------------------------------
        | Make sure this is a valid ADC tenant database
        |--------------------------------------------------------------------------
        */

        if (!Schema::connection('mysql')->hasTable('members')) {

            Auth::guard('web')->logout();

            abort(403, 'Invalid ADC tenant database.');
        }

        /*
        |--------------------------------------------------------------------------
        | Step 6: Store tenant information in session
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
        | Step 7: Regenerate login session
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Step 8: Update central login information
        |--------------------------------------------------------------------------
        */

        $user->last_login_time = $user->current_login_time;
        $user->current_login_time = now();
        $user->login_ip_address = $request->ip();
        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Step 9: Existing Login Dashboard Notice
        |--------------------------------------------------------------------------
        */

        if (
            method_exists($user, 'hasAnyRole') &&
            $user->hasAnyRole([
                'superadmin',
                'admin',
                'genadmin'
            ])
        ) {

            $today = now()->toDateString();

            if (
                Schema::connection('mysql')
                ->hasTable('login_notice_logs')
            ) {

                $alreadyShown = DB::connection('mysql')
                    ->table('login_notice_logs')
                    ->where('user_id', $user->id)
                    ->where('notice_date', $today)
                    ->exists();

                if (!$alreadyShown) {

                    $service = app(
                        LoginDashboardNoticeService::class
                    );

                    $payload = $service->build();

                    if ($service->hasAnything($payload)) {

                        DB::connection('mysql')
                            ->table('login_notice_logs')
                            ->insert([
                                'user_id'     => $user->id,
                                'notice_date' => $today,
                                'shown_at'    => now(),
                                'payload'     => json_encode($payload),
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);

                        session()->flash(
                            'login_dashboard_notice',
                            $payload
                        );
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Step 10: Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        /*
        |--------------------------------------------------------------------------
        | Clear Tenant Information
        |--------------------------------------------------------------------------
        */

        $request->session()->forget([
            'organization_id',
            'organization_name',
            'tenant_database',
            'media_folder',
            'has_shift',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Restore Default Database
        |--------------------------------------------------------------------------
        */

        config([
            'database.connections.mysql.database'
            => config('adc.default_database'),
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        /*
        |--------------------------------------------------------------------------
        | Destroy Session
        |--------------------------------------------------------------------------
        */

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
