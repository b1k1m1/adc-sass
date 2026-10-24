<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Events\Registered;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');

    } // End Method

    public function AllUsers(){


        $users = DB::table('users')
               ->leftJoin('roles', 'users.role', '=', 'roles.id')
               ->select('users.id', 'users.lastname', 'users.firstname', 'users.username',
                        'users.email', 'users.address', 'users.phone','roles.name as role_name')
               ->orderBy('users.id')
               ->get();

         return view('profile.users_all', compact('users'),['title' => 'Users Management', 'module' => 'Users' ]);

    }// End Method

    public function AddUser(){

       $nextid = (User::max('id')+1);

       return view('profile.users_add', compact('nextid'));

    }// End Method

    public function EditUser($id){

        $user = User::findOrFail($id);

    return view('profile.users_edit', compact('user'));

    }// End Method

    public function StoreUser(Request $request){

        // Remove all non-numeric characters from the phone number
        $cleanedPhone = preg_replace('/\D/', '', $request->input('phone'));

        $user = User::create([

            'firstname' => strtoupper($request->firstname),
            'lastname' => strtoupper($request->lastname),
            'name' => $request->username,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $cleanedPhone,
            'address' => strtoupper($request->address),
            'role' => $request->role,
            'status' => $request->status,
            'normalpassword' => $request->normalpassword,
            'password' => Hash::make($request->normalpassword),
        ]);

        event(new Registered($user));

        if($request->role){

             $roleName = Role::find($request->role)->name;
             $user->assignRole($roleName);
        }

        $notification = array(
            'message' => 'User Created Successfully',
            'aleart-type' =>'success',

        );

        return redirect()->back()->with($notification);

    } // End Method

    public function UpdateUser(Request $request){

        $cleanedPhone = preg_replace('/\D/', '', $request->input('phone'));

        $data = User::find($request->id);
        $data->firstname = strtoupper($request->firstname);
        $data->lastname = strtoupper($request->lastname);
        $data->username = $request->username;
        $data->email = $request->email;
        $data->phone = $cleanedPhone;
        $data->address = strtoupper($request->address);
        $data->role = $request->role;
        $data->status = $request->status;
        $data->normalpassword = $request->normalpassword;
        $enc_pwd = Hash::make($request->normalpassword);
        $data->password = $enc_pwd;

        $data->save();

        if($request->role){

             // First remove any previous roles
             $data->syncRoles([]);
             // Fetch the role name using Role model
             $roleName = Role::find($request->role)->name;
             // Now assign the role by name
             $data->assignRole($roleName);

        }

         $notification = array(
            'message' => 'User Updated Successfully',
            'aleart-type' =>'success',
        );

        return redirect()->back()->with($notification);

    }
}
