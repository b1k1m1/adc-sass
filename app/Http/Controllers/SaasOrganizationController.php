<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Auth;

class SaasOrganizationController extends Controller
{
    /**
     * Display SaaS organizations.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Get Organizations From Master Database
        |--------------------------------------------------------------------------
        */

        $organizations = DB::connection('master')
            ->table('organizations')
            ->orderBy('organization_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Get SaaS User Counts By Organization
        |--------------------------------------------------------------------------
        |
        | Keep this separate from organizations instead of using a JOIN.
        |
        */

        $userCounts = DB::connection('master')
            ->table('tenant_users')
            ->select(
                'organization_id',
                DB::raw('COUNT(*) as total_users')
            )
            ->groupBy('organization_id')
            ->pluck('total_users', 'organization_id');

        /*
        |--------------------------------------------------------------------------
        | Get Active User Counts
        |--------------------------------------------------------------------------
        */

        $activeUserCounts = DB::connection('master')
            ->table('tenant_users')
            ->select(
                'organization_id',
                DB::raw('COUNT(*) as active_users')
            )
            ->where('status', 'Active')
            ->groupBy('organization_id')
            ->pluck('active_users', 'organization_id');

        /*
        |--------------------------------------------------------------------------
        | Add Counts To Organization Records
        |--------------------------------------------------------------------------
        */

        $organizations = $organizations->map(function ($organization) use (
            $userCounts,
            $activeUserCounts
        ) {

            $organization->total_users =
                (int) ($userCounts[$organization->id] ?? 0);

            $organization->active_users =
                (int) ($activeUserCounts[$organization->id] ?? 0);

            return $organization;
        });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Totals
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_organizations' => $organizations->count(),

            'active_organizations' => $organizations
                ->where('status', 1)
                ->count(),

            'ready_organizations' => $organizations
                ->where('is_ready', 1)
                ->count(),

            'total_users' => DB::connection('master')
                ->table('tenant_users')
                ->count(),

            'active_users' => DB::connection('master')
                ->table('tenant_users')
                ->where('status', 'Active')
                ->count(),
        ];

        return view(
            'saas.organizations.index',
            compact('organizations', 'summary')
        );

    } // End Method

    public function create(): View
    {
        return view('saas.organizations.create');

    } // End Method

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => [
                'required',
                'string',
                'max:50',
            ],

            'database_name' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_]+$/',
            ],

            'media_folder' => [
                'nullable',
                'string',
                'max:50',
            ],

            'has_shift' => [
                'required',
                'in:0,1',
            ],
        ], [
            'organization_name.required'
                => 'Organization name is required.',

            'database_name.required'
                => 'Database name is required.',

            'database_name.regex'
                => 'Database name may contain only letters, numbers and underscore.',

            'has_shift.required'
                => 'Please select whether this organization uses shifts.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate organization name
        |--------------------------------------------------------------------------
        */

        $organizationExists = DB::connection('master')
            ->table('organizations')
            ->whereRaw(
                'LOWER(TRIM(organization_name)) = ?',
                [strtolower(trim($validated['organization_name']))]
            )
            ->exists();

        if ($organizationExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'organization_name'
                        => 'This organization already exists.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate database name
        |--------------------------------------------------------------------------
        */

        $databaseExists = DB::connection('master')
            ->table('organizations')
            ->whereRaw(
                'LOWER(TRIM(database_name)) = ?',
                [strtolower(trim($validated['database_name']))]
            )
            ->exists();

        if ($databaseExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'database_name'
                        => 'This database name is already assigned to another organization.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate new organization ID
        |--------------------------------------------------------------------------
        |
        | Current organizations.id is not AUTO_INCREMENT,
        | so we generate the next value manually for now.
        |
        */

        $nextId = (
            DB::connection('master')
                ->table('organizations')
                ->max('id') ?? 0
        ) + 1;

        /*
        |--------------------------------------------------------------------------
        | Insert Organization
        |--------------------------------------------------------------------------
        |
        | New organization starts:
        |
        | status   = 1
        | is_ready = 0
        |
        | It cannot be used until database provisioning is completed.
        |
        */

        DB::connection('master')
            ->table('organizations')
            ->insert([
                'id'                => $nextId,
                'organization_name' => trim(
                    $validated['organization_name']
                ),
                'database_name' => strtolower(
                    trim($validated['database_name'])
                ),
                'media_folder' => !empty($validated['media_folder'])
                    ? trim($validated['media_folder'])
                    : null,

                'status'    => 1,
                'is_ready'  => 0,
                'has_shift' => (int) $validated['has_shift'],

                'timestamps' => now(),
            ]);

        return redirect()
            ->route('saas.organizations.index')
            ->with(
                'success',
                'Organization created successfully. Database provisioning is still required.'
            );

    } // End Method

    public function provisionConfirm(int $id): View
    {
        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->first();

        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        if ((int) $organization->is_ready === 1) {
            abort(403, 'This organization is already provisioned.');
        }

        if (empty($organization->database_name)) {
            abort(403, 'Database name is not assigned.');
        }

        return view(
            'saas.organizations.provision',
            compact('organization')
        );

    } // End Method

    public function provision(int $id): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Load Organization
        |--------------------------------------------------------------------------
        */

        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->first();

        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        if ((int) $organization->is_ready === 1) {
            return redirect()
                ->route('saas.organizations.index')
                ->with('error', 'This organization is already provisioned.');
        }

        $databaseName = trim((string) $organization->database_name);

        if ($databaseName === '') {
            return back()
                ->with('error', 'Database name is not assigned.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Database Name
        |--------------------------------------------------------------------------
        */

        if (!preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
            return back()
                ->with('error', 'Invalid database name.');
        }

        /*
        |--------------------------------------------------------------------------
        | Check Database Does Not Already Exist
        |--------------------------------------------------------------------------
        */

        $databaseExists = DB::connection('master')
            ->selectOne(
                'SELECT SCHEMA_NAME
                FROM information_schema.SCHEMATA
                WHERE SCHEMA_NAME = ?',
                [$databaseName]
            );

        if ($databaseExists) {
            return back()
                ->with(
                    'error',
                    'The database already exists. Provisioning was stopped for safety.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Template File
        |--------------------------------------------------------------------------
        */

        $templateFile = 'C:\\ADCData\\templates\\adc_template.sql';

        if (!file_exists($templateFile)) {
            return back()
                ->with(
                    'error',
                    'Provisioning template was not found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | MySQL Executable
        |--------------------------------------------------------------------------
        */

        $mysqlExe = 'C:\\xampp\\mysql\\bin\\mysql.exe';

        if (!file_exists($mysqlExe)) {
            return back()
                ->with(
                    'error',
                    'MySQL executable was not found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Database
        |--------------------------------------------------------------------------
        */

        try {

            DB::connection('master')->statement(
                "CREATE DATABASE `{$databaseName}`
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci"
            );

        } catch (\Throwable $e) {

            return back()
                ->with(
                    'error',
                    'Unable to create tenant database: ' . $e->getMessage()
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Import Template
        |--------------------------------------------------------------------------
        |
        | We use cmd.exe because Windows shell redirection (<) is required.
        |
        */

        $command = sprintf(
            '"%s" -u root %s < "%s"',
            $mysqlExe,
            escapeshellarg($databaseName),
            $templateFile
        );

        $process = Process::fromShellCommandline(
            $command,
            null,
            null,
            null,
            300
        );

        $process->run();

        if (!$process->isSuccessful()) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup Failed Database
            |--------------------------------------------------------------------------
            */

            try {

                DB::connection('master')->statement(
                    "DROP DATABASE IF EXISTS `{$databaseName}`"
                );

            } catch (\Throwable $cleanupException) {
                //
            }

            return back()
                ->with(
                    'error',
                    'Database import failed: ' . $process->getErrorOutput()
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Configure Temporary Tenant Connection
        |--------------------------------------------------------------------------
        */

        config([
            'database.connections.provision_test' =>
                array_merge(
                    config('database.connections.mysql'),
                    [
                        'database' => $databaseName,
                    ]
                )
        ]);

        DB::purge('provision_test');

        /*
        |--------------------------------------------------------------------------
        | Verify Critical Tables
        |--------------------------------------------------------------------------
        */

        $requiredTables = [
            'members',
            'roles',
            'permissions',
            'migrations',
            'insurance_companies',
        ];

        foreach ($requiredTables as $table) {

            if (
                !Schema::connection('provision_test')
                    ->hasTable($table)
            ) {

                /*
                |--------------------------------------------------------------------------
                | Cleanup Invalid Provisioning
                |--------------------------------------------------------------------------
                */

                DB::purge('provision_test');

                try {

                    DB::connection('master')->statement(
                        "DROP DATABASE IF EXISTS `{$databaseName}`"
                    );

                } catch (\Throwable $cleanupException) {
                    //
                }

                return back()
                    ->with(
                        'error',
                        "Provisioning verification failed. Missing table: {$table}"
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Operational Tables Are Empty
        |--------------------------------------------------------------------------
        */

        $memberCount = DB::connection('provision_test')
            ->table('members')
            ->count();

        $claimCount = DB::connection('provision_test')
            ->table('claims')
            ->count();

        if ($memberCount > 0 || $claimCount > 0) {

            DB::purge('provision_test');

            try {

                DB::connection('master')->statement(
                    "DROP DATABASE IF EXISTS `{$databaseName}`"
                );

            } catch (\Throwable $cleanupException) {
                //
            }

            return back()
                ->with(
                    'error',
                    'Provisioning stopped because the template contains operational data.'
                );
        }

        DB::purge('provision_test');

        /*
        |--------------------------------------------------------------------------
        | Mark Organization Ready
        |--------------------------------------------------------------------------
        */

        DB::connection('master')
            ->table('organizations')
            ->where('id', $organization->id)
            ->update([
                'is_ready' => 1,
                'timestamps' => now(),
            ]);

        return redirect()
            ->route('saas.organizations.index')
            ->with(
                'success',
                'Organization database provisioned successfully.'
            );

    } // End Method

    public function edit(int $id)
    {
        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->first();

        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        return view(
            'saas.organizations.edit',
            compact('organization')
        );

    } //End Method

    public function update(Request $request, int $id)
    {
        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->first();

        if (!$organization) {
            abort(404, 'Organization not found.');
        }


        $validated = $request->validate([
            'organization_name' => [
                'required',
                'string',
                'max:50',
            ],

            'media_folder' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                'in:0,1',
            ],

            'has_shift' => [
                'required',
                'in:0,1',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Organization Name
        |--------------------------------------------------------------------------
        */

        $duplicateName = DB::connection('master')
            ->table('organizations')
            ->where('id', '<>', $id)
            ->whereRaw(
                'LOWER(TRIM(organization_name)) = ?',
                [strtolower(trim($validated['organization_name']))]
            )
            ->exists();

        if ($duplicateName) {
            return back()
                ->withInput()
                ->withErrors([
                    'organization_name'
                        => 'Another organization already uses this name.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Current Platform Admin Organization From Being Deactivated
        |--------------------------------------------------------------------------
        */

        /** @var \App\Models\TenantUser|null $currentUser */
        $currentUser = Auth::guard('web')->user();

        if (
            $currentUser &&
            (int) $currentUser->organization_id === (int) $organization->id &&
            (int) $validated['status'] === 0
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'status'
                        => 'You cannot deactivate the organization currently assigned to your account.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Organization
        |--------------------------------------------------------------------------
        |
        | database_name and is_ready are intentionally NOT editable here.
        |
        */

        DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->update([
                'organization_name'
                    => trim($validated['organization_name']),

                'media_folder'
                    => !empty($validated['media_folder'])
                        ? trim($validated['media_folder'])
                        : null,

                'status'
                    => (int) $validated['status'],

                'has_shift'
                    => (int) $validated['has_shift'],

                'timestamps'
                    => now(),
            ]);


        return redirect()
            ->route('saas.organizations.index')
            ->with(
                'success',
                'Organization updated successfully.'
            );

    } // End Method
    
    public function show(int $id)
    {
        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $id)
            ->first();

        if (!$organization) {
            abort(404, 'Organization not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | User Statistics
        |--------------------------------------------------------------------------
        */

        $totalUsers = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $id)
            ->count();


        $activeUsers = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $id)
            ->where('status', 'Active')
            ->count();


        $inactiveUsers = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $id)
            ->where('status', '<>', 'Active')
            ->count();


        $platformAdmins = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $id)
            ->where('is_platform_admin', 1)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Organization Users
        |--------------------------------------------------------------------------
        */

        $users = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $id)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Role Map
        |--------------------------------------------------------------------------
        */

        $roleMap = [];

        if (
            !empty($organization->database_name)
            &&
            (int) $organization->is_ready === 1
        ) {

            try {

                $connectionConfig = config('database.connections.mysql');

                $connectionConfig['database'] =
                    $organization->database_name;

                config([
                    'database.connections.organization_detail'
                        => $connectionConfig
                ]);

                DB::purge('organization_detail');

                $roles = DB::connection('organization_detail')
                    ->table('roles')
                    ->orderBy('name')
                    ->get();

                $roleMap = $roles
                    ->pluck('name', 'id')
                    ->toArray();

            } catch (\Throwable $e) {

                $roleMap = [];
            }
        }


        $users = $users->map(function ($user) use ($roleMap) {

            $user->role_name =
                $roleMap[$user->role]
                ?? 'Role #' . $user->role;

            return $user;
        });


        return view(
            'saas.organizations.show',
            compact(
                'organization',
                'totalUsers',
                'activeUsers',
                'inactiveUsers',
                'platformAdmins',
                'users'
            )
        );

    } // End Method

}
