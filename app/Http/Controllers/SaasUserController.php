<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SaasUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $organizationId = $request->integer('organization_id');

        /*
        |--------------------------------------------------------------------------
        | Organizations
        |--------------------------------------------------------------------------
        */

        $organizations = DB::connection('master')
            ->table('organizations')
            ->orderBy('organization_name')
            ->get();

        $organizationMap = $organizations
            ->pluck('organization_name', 'id');

        /*
        |--------------------------------------------------------------------------
        | Role Reference
        |--------------------------------------------------------------------------
        |
        | adc_template contains our standardized SaaS tenant roles.
        |
        */

        $roles = collect(
            DB::connection('master')->select(
                'SELECT id, name
                 FROM adc_template.roles
                 ORDER BY name'
            )
        );

        $roleMap = $roles->pluck('name', 'id');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        $query = DB::connection('master')
            ->table('tenant_users')
            ->orderBy('name');

        if ($organizationId > 0) {
            $query->where('organization_id', $organizationId);
        }

        $users = $query->get();

        $users = $users->map(function ($user) use (
            $organizationMap,
            $roleMap
        ) {
            $user->organization_name =
                $organizationMap[$user->organization_id]
                ?? 'Unknown Organization';

            $user->role_name =
                $roleMap[$user->role]
                ?? 'Role #' . $user->role;

            return $user;
        });

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' => DB::connection('master')
                ->table('tenant_users')
                ->count(),

            'active' => DB::connection('master')
                ->table('tenant_users')
                ->where('status', 'Active')
                ->count(),

            'inactive' => DB::connection('master')
                ->table('tenant_users')
                ->where('status', '<>', 'Active')
                ->count(),

            'platform_admins' => DB::connection('master')
                ->table('tenant_users')
                ->where('is_platform_admin', 1)
                ->count(),
        ];

        return view(
            'saas.users.index',
            compact(
                'users',
                'organizations',
                'organizationId',
                'summary'
            )
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Only Ready + Active Organizations
        |--------------------------------------------------------------------------
        */

        $organizations = DB::connection('master')
            ->table('organizations')
            ->where('status', 1)
            ->where('is_ready', 1)
            ->orderBy('organization_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Standard Tenant Roles
        |--------------------------------------------------------------------------
        */

        $roles = collect(
            DB::connection('master')->select(
                'SELECT id, name
                 FROM adc_template.roles
                 ORDER BY name'
            )
        );

        return view(
            'saas.users.create',
            compact(
                'organizations',
                'roles'
            )
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Store User
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => [
                'required',
                'integer',
            ],

            'firstname' => [
                'required',
                'string',
                'max:45',
            ],

            'lastname' => [
                'required',
                'string',
                'max:45',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'role' => [
                'required',
                'integer',
            ],

            'status' => [
                'required',
                'in:Active,Inactive',
            ],

            'is_platform_admin' => [
                'nullable',
                'boolean',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Organization
        |--------------------------------------------------------------------------
        */

        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $validated['organization_id'])
            ->where('status', 1)
            ->where('is_ready', 1)
            ->first();

        if (!$organization) {

            return back()
                ->withInput()
                ->withErrors([
                    'organization_id'
                    => 'The selected organization is not available.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Role
        |--------------------------------------------------------------------------
        */

        $roleExists = DB::connection('master')
            ->selectOne(
                'SELECT id
                 FROM adc_template.roles
                 WHERE id = ?',
                [$validated['role']]
            );

        if (!$roleExists) {

            return back()
                ->withInput()
                ->withErrors([
                    'role'
                    => 'The selected role is invalid.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Login Identifiers
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($validated['email'])
        );

        $username = !empty($validated['username'])
            ? trim($validated['username'])
            : null;

        $phone = !empty($validated['phone'])
            ? trim($validated['phone'])
            : null;


        /*
        |--------------------------------------------------------------------------
        | Email Must Be Globally Unique
        |--------------------------------------------------------------------------
        */

        if (
            DB::connection('master')
            ->table('tenant_users')
            ->whereRaw(
                'LOWER(TRIM(email)) = ?',
                [$email]
            )
            ->exists()
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'email'
                    => 'This email address is already being used.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Username Must Be Globally Unique
        |--------------------------------------------------------------------------
        */

        if ($username !== null) {

            $exists = DB::connection('master')
                ->table('tenant_users')
                ->where(function ($query) use ($username) {

                    $query->whereRaw(
                        'LOWER(TRIM(username)) = ?',
                        [strtolower($username)]
                    )
                        ->orWhereRaw(
                            'LOWER(TRIM(email)) = ?',
                            [strtolower($username)]
                        );
                })
                ->exists();

            if ($exists) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'username'
                        => 'This username is already being used.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Phone Must Be Globally Unique
        |--------------------------------------------------------------------------
        */

        if ($phone !== null) {

            $exists = DB::connection('master')
                ->table('tenant_users')
                ->where('phone', $phone)
                ->exists();

            if ($exists) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'phone'
                        => 'This phone number is already being used for login.',
                    ]);
            }
        }

        // 5. Generate next user_cd for THIS organization
        $lastUserCode = DB::connection('master')
            ->table('tenant_users')
            ->where('organization_id', $organization->id)
            ->whereNotNull('user_cd')
            ->selectRaw(
                'MAX(CAST(user_cd AS UNSIGNED)) as max_user_cd'
            )
            ->value('max_user_cd');

        $nextUserCode = $lastUserCode
            ? ((int) $lastUserCode + 1)
            : 1001;

        $userCode = str_pad(
            (string) $nextUserCode,
            4,
            '0',
            STR_PAD_LEFT
        );


        /*
        |--------------------------------------------------------------------------
        | Insert SaaS User
        |--------------------------------------------------------------------------
        */

        DB::connection('master')
            ->table('tenant_users')
            ->insert([
                'organization_id'
                => $organization->id,

                'firstname'       => strtoupper(trim($validated['firstname'])),
                'lastname'        => strtoupper(trim($validated['lastname'])),
                'user_cd'         => $userCode,

                'is_platform_admin'
                => $request->boolean(
                    'is_platform_admin'
                ) ? 1 : 0,

                'name'
                => trim($validated['name']),

                'username'
                => $username,

                'email'
                => $email,

                'phone'
                => $phone,

                'password'
                => Hash::make(
                    $validated['password']
                ),

                'role'
                => (int) $validated['role'],

                'status'
                => $validated['status'],

                'created_at'
                => now(),

                'updated_at'
                => now(),
            ]);


        return redirect()
            ->route('saas.users.index')
            ->with(
                'success',
                'SaaS user created successfully.'
            );
    } // End Method


    public function edit(int $id): View
    {
        $user = DB::connection('master')
            ->table('tenant_users')
            ->where('id', $id)
            ->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        $organizations = DB::connection('master')
            ->table('organizations')
            ->where('status', 1)
            ->where('is_ready', 1)
            ->orderBy('organization_name')
            ->get();

        $roles = collect(
            DB::connection('master')->select(
                'SELECT id, name
                FROM adc_template.roles
                ORDER BY name'
            )
        );

        return view(
            'saas.users.edit',
            compact(
                'user',
                'organizations',
                'roles'
            )
        );
    } // End Method


    public function update(Request $request, int $id): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Existing User
        |--------------------------------------------------------------------------
        */

        $user = DB::connection('master')
            ->table('tenant_users')
            ->where('id', $id)
            ->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'organization_id' => [
                'required',
                'integer',
            ],

            'firstname' => [
                'required',
                'string',
                'max:45',
            ],

            'lastname' => [
                'required',
                'string',
                'max:45',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'role' => [
                'required',
                'integer',
            ],

            'status' => [
                'required',
                'in:Active,Inactive',
            ],

            'is_platform_admin' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Organization
        |--------------------------------------------------------------------------
        */

        $organization = DB::connection('master')
            ->table('organizations')
            ->where('id', $validated['organization_id'])
            ->where('status', 1)
            ->where('is_ready', 1)
            ->first();

        if (!$organization) {
            return back()
                ->withInput()
                ->withErrors([
                    'organization_id'
                    => 'The selected organization is not available.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Role
        |--------------------------------------------------------------------------
        */

        $roleExists = DB::connection('master')
            ->selectOne(
                'SELECT id
                FROM adc_template.roles
                WHERE id = ?',
                [$validated['role']]
            );

        if (!$roleExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'role'
                    => 'The selected role is invalid.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Login Identifiers
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($validated['email'])
        );

        $username = !empty($validated['username'])
            ? trim($validated['username'])
            : null;

        $phone = !empty($validated['phone'])
            ? trim($validated['phone'])
            : null;


        /*
        |--------------------------------------------------------------------------
        | Email Must Be Unique Except Current User
        |--------------------------------------------------------------------------
        */

        $emailExists = DB::connection('master')
            ->table('tenant_users')
            ->where('id', '<>', $id)
            ->whereRaw(
                'LOWER(TRIM(email)) = ?',
                [$email]
            )
            ->exists();

        if ($emailExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'email'
                    => 'This email address is already being used.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Username Must Be Unique Except Current User
        |--------------------------------------------------------------------------
        */

        if ($username !== null) {

            $usernameExists = DB::connection('master')
                ->table('tenant_users')
                ->where('id', '<>', $id)
                ->where(function ($query) use ($username) {

                    $query->whereRaw(
                        'LOWER(TRIM(username)) = ?',
                        [strtolower($username)]
                    )
                        ->orWhereRaw(
                            'LOWER(TRIM(email)) = ?',
                            [strtolower($username)]
                        );
                })
                ->exists();

            if ($usernameExists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'username'
                        => 'This username is already being used.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Phone Must Be Unique Except Current User
        |--------------------------------------------------------------------------
        */

        if ($phone !== null) {

            $phoneExists = DB::connection('master')
                ->table('tenant_users')
                ->where('id', '<>', $id)
                ->where('phone', $phone)
                ->exists();

            if ($phoneExists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'phone'
                        => 'This phone number is already being used for login.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | If Organization Changes, Generate New User Code
        |--------------------------------------------------------------------------
        |
        | user_cd is unique within each organization.
        |
        */

        $userCode = $user->user_cd;

        if (
            (int) $user->organization_id
            !== (int) $organization->id
        ) {

            $lastUserCode = DB::connection('master')
                ->table('tenant_users')
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->whereNotNull('user_cd')
                ->selectRaw(
                    'MAX(CAST(user_cd AS UNSIGNED)) as max_user_cd'
                )
                ->value('max_user_cd');

            $nextUserCode = $lastUserCode
                ? ((int) $lastUserCode + 1)
                : 1001;

            if ($nextUserCode > 9999) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'organization_id'
                        => 'The selected organization has exhausted its user code range.',
                    ]);
            }

            $userCode = str_pad(
                (string) $nextUserCode,
                4,
                '0',
                STR_PAD_LEFT
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Protect Current Platform Administrator
    |--------------------------------------------------------------------------
    |
    | A platform administrator must not be able to:
    |
    | 1. Remove their own platform-admin access
    | 2. Deactivate their own account
    |
    */

        $currentUser = Auth::guard('web')->user();

        if (
            $currentUser &&
            (int) $currentUser->id === (int) $user->id
        ) {

            /*
        |--------------------------------------------------------------------------
        | Cannot Remove Own Platform Admin Access
        |--------------------------------------------------------------------------
        */

            if (!$request->boolean('is_platform_admin')) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'is_platform_admin'
                        => 'You cannot remove your own platform administrator access.',
                    ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Cannot Deactivate Own Account
        |--------------------------------------------------------------------------
        */

            if ($validated['status'] !== 'Active') {

                return back()
                    ->withInput()
                    ->withErrors([
                        'status'
                        => 'You cannot deactivate your own account.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        DB::connection('master')
            ->table('tenant_users')
            ->where('id', $id)
            ->update([
                'organization_id'
                => $organization->id,

                'firstname'
                => strtoupper(
                    trim($validated['firstname'])
                ),

                'lastname'
                => strtoupper(
                    trim($validated['lastname'])
                ),

                'name'
                => trim($validated['name']),

                'user_cd'
                => $userCode,

                'username'
                => $username,

                'email'
                => $email,

                'phone'
                => $phone,

                'role'
                => (int) $validated['role'],

                'status'
                => $validated['status'],

                'is_platform_admin'
                => $request->boolean(
                    'is_platform_admin'
                ) ? 1 : 0,

                'updated_at'
                => now(),
            ]);


        return redirect()
            ->route('saas.users.index')
            ->with(
                'success',
                'SaaS user updated successfully.'
            );
    } // End Method

}
