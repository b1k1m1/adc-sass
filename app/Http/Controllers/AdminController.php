<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function AdminDashboard(){

        return view('admin.admin_index');

    } // End Method

    public function AdminEdit(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return "<h1> you are in Admin Edit<h1>" . $id;
    } // End Method

    public function AdminProfile(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile_view', compact('profileData'));
    } // End Method


    public function AdminProfileStore(Request $request){

        $id = Auth::user()->id;
        $data = User::find($id);
        $data->firstname = $request->firstname;
        $data->lastname = $request->lastname;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully',
            'aleart-type' =>'success',

        );

        return redirect()->back()->with($notification);

    } // End Method

    
}
