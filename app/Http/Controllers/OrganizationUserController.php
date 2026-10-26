<?php


namespace App\Http\Controllers;

use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OrganizationUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify Organization Administrator
    |--------------------------------------------------------------------------
    */

    private function authorizeOrganizationAdmin(): TenantUser
    {
        /** @var TenantUser|null $user */
        $user = Auth::guard('web')->user();

        if (!$user) {
            abort(403);
        }

        if (!$user->hasAnyRole([
            'superadmin',
            'admin',
            'genadmin',
        ])) {
            abort(
                403,
                'You do not have permission to manage organization users.'
            );
        }

        return $user;
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Find User Inside Current Organization
    |--------------------------------------------------------------------------
    */

    private function findOrganizationUser(
        int $id,
        TenantUser $currentUser
    ): object {

        $user = DB::connection('master')
            ->table('tenant_users')
            ->where('id', $id)
            ->where(
                'organization_id',
                $currentUser->organization_id
            )
            ->first();

        if (!$user) {
            abort(
                404,
                'User not found in your organization.'
            );
        }

        return $user;
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | User List
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $currentUser =
            $this->authorizeOrganizationAdmin();

        $users = DB::connection('master')
            ->table('tenant_users')
            ->where(
                'organization_id',
                $currentUser->organization_id
            )
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Roles From Current Tenant Database
        |--------------------------------------------------------------------------
        */

        $roles = DB::connection('mysql')
            ->table('roles')
            ->orderBy('name')
            ->get();

        $roleMap =
            $roles->pluck('name', 'id');


        $users = $users->map(
            function ($user) use ($roleMap) {

                $user->role_name =
                    $roleMap[$user->role]
                    ?? 'Role #' . $user->role;

                return $user;
            }
        );


        return view(
            'organization_users.index',
            compact('users')
        );
    } // End Method

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->authorizeOrganizationAdmin();

        $roles = DB::connection('mysql')
            ->table('roles')
            ->orderBy('name')
            ->get();

        return view(
            'organization_users.create',
            compact('roles')
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Store User
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $currentUser =
            $this->authorizeOrganizationAdmin();


        $validated = $request->validate([
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

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Role
        |--------------------------------------------------------------------------
        */

        $roleExists = DB::connection('mysql')
            ->table('roles')
            ->where(
                'id',
                $validated['role']
            )
            ->exists();

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
        | Normalize Login Fields
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($validated['email'])
        );

        $username =
            !empty($validated['username'])
            ? trim($validated['username'])
            : null;

        $phone =
            !empty($validated['phone'])
            ? trim($validated['phone'])
            : null;


        /*
        |--------------------------------------------------------------------------
        | Check Login Identifier Uniqueness
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


        if ($username !== null) {

            $usernameExists =
                DB::connection('master')
                ->table('tenant_users')
                ->where(function ($query)
                use ($username) {

                    $value =
                        strtolower($username);

                    $query
                        ->whereRaw(
                            'LOWER(TRIM(username)) = ?',
                            [$value]
                        )
                        ->orWhereRaw(
                            'LOWER(TRIM(email)) = ?',
                            [$value]
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


        if ($phone !== null) {

            if (
                DB::connection('master')
                ->table('tenant_users')
                ->where(
                    'phone',
                    $phone
                )
                ->exists()
            ) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'phone'
                        => 'This phone number is already being used.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Next User Code Per Organization
        |--------------------------------------------------------------------------
        */

        $lastUserCode =
            DB::connection('master')
            ->table('tenant_users')
            ->where(
                'organization_id',
                $currentUser->organization_id
            )
            ->whereNotNull('user_cd')
            ->selectRaw(
                'MAX(CAST(user_cd AS UNSIGNED)) as max_user_cd'
            )
            ->value('max_user_cd');


        $nextUserCode =
            $lastUserCode
            ? ((int) $lastUserCode + 1)
            : 1001;


        if ($nextUserCode > 9999) {

            return back()
                ->withInput()
                ->withErrors([
                    'name'
                    => 'The organization user-code range has been exhausted.',
                ]);
        }


        $userCode =
            str_pad(
                (string) $nextUserCode,
                4,
                '0',
                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Insert User
        |--------------------------------------------------------------------------
        */

        DB::connection('master')
            ->table('tenant_users')
            ->insert([

                'organization_id'
                => $currentUser->organization_id,

                'is_platform_admin'
                => 0,

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
            ->route(
                'organization.users.index'
            )
            ->with(
                'success',
                'Organization user created successfully.'
            );
    } // End Method

    /*
    |--------------------------------------------------------------------------
    | Edit User
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): View
    {

        $currentUser =
            $this->authorizeOrganizationAdmin();


        $user =
            $this->findOrganizationUser(
                $id,
                $currentUser
            );


        $roles = DB::connection('mysql')
            ->table('roles')
            ->orderBy('name')
            ->get();


        return view(
            'organization_users.edit',
            compact(
                'user',
                'roles'
            )
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, int $id): RedirectResponse
    {

        $currentUser =
            $this->authorizeOrganizationAdmin();


        $user =
            $this->findOrganizationUser(
                $id,
                $currentUser
            );


        $validated = $request->validate([
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
        ]);


        /*
        |--------------------------------------------------------------------------
        | Prevent Self Deactivation
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->id
            ===
            (int) $currentUser->id
            &&
            $validated['status']
            !== 'Active'
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'status'
                    => 'You cannot deactivate your own account.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Role
        |--------------------------------------------------------------------------
        */

        $roleExists =
            DB::connection('mysql')
            ->table('roles')
            ->where(
                'id',
                $validated['role']
            )
            ->exists();


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
        | Normalize Fields
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        $username =
            !empty($validated['username'])
            ? trim($validated['username'])
            : null;


        $phone =
            !empty($validated['phone'])
            ? trim($validated['phone'])
            : null;


        /*
        |--------------------------------------------------------------------------
        | Duplicate Email
        |--------------------------------------------------------------------------
        */

        $emailExists =
            DB::connection('master')
            ->table('tenant_users')
            ->where(
                'id',
                '<>',
                $id
            )
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
        | Duplicate Username
        |--------------------------------------------------------------------------
        */

        if ($username !== null) {

            $usernameExists =
                DB::connection('master')
                ->table('tenant_users')
                ->where(
                    'id',
                    '<>',
                    $id
                )
                ->where(function ($query)
                use ($username) {

                    $value =
                        strtolower($username);

                    $query
                        ->whereRaw(
                            'LOWER(TRIM(username)) = ?',
                            [$value]
                        )
                        ->orWhereRaw(
                            'LOWER(TRIM(email)) = ?',
                            [$value]
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
        | Duplicate Phone
        |--------------------------------------------------------------------------
        */

        if ($phone !== null) {

            $phoneExists =
                DB::connection('master')
                ->table('tenant_users')
                ->where(
                    'id',
                    '<>',
                    $id
                )
                ->where(
                    'phone',
                    $phone
                )
                ->exists();


            if ($phoneExists) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'phone'
                        => 'This phone number is already being used.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | organization_id is NOT changed.
        | is_platform_admin is NOT changed.
        |
        */

        DB::connection('master')
            ->table('tenant_users')
            ->where('id', $id)
            ->where(
                'organization_id',
                $currentUser->organization_id
            )
            ->update([

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

                'updated_at'
                => now(),
            ]);


        return redirect()
            ->route(
                'organization.users.index'
            )
            ->with(
                'success',
                'Organization user updated successfully.'
            );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Password Reset Screen
    |--------------------------------------------------------------------------
    */

    public function passwordEdit(int $id): View
    {

        $currentUser =
            $this->authorizeOrganizationAdmin();


        $user =
            $this->findOrganizationUser(
                $id,
                $currentUser
            );


        return view(
            'organization_users.password',
            compact('user')
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    public function passwordUpdate(Request $request, int $id): RedirectResponse
    {

        $currentUser =
            $this->authorizeOrganizationAdmin();


        $user =
            $this->findOrganizationUser(
                $id,
                $currentUser
            );


        $validated =
            $request->validate([
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ]);


        DB::connection('master')
            ->table('tenant_users')
            ->where(
                'id',
                $user->id
            )
            ->where(
                'organization_id',
                $currentUser->organization_id
            )
            ->update([

                'password'
                => Hash::make(
                    $validated['password']
                ),

                'updated_at'
                => now(),
            ]);


        return redirect()
            ->route(
                'organization.users.index'
            )
            ->with(
                'success',
                'User password changed successfully.'
            );
    } // End Method

}
