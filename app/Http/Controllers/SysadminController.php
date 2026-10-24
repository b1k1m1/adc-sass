<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SysadminController extends Controller
{
     public function SysadminDashboard(){

        return view('sysadmin.sysadmin_index', ['title' => 'Users Management','module' => 'sysadmin' ]);
    }// End Method


     public function UploadUserImage(){

            return view('settings.upload_users_image');


        } // End Method

    public function StoreImage(Request $request){

            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,bmp|max:2048', // 2MB max size (optional) ',
                'new_filename' => 'required|string|max:255'
            ]);

            $file = $request->file('image');
            $newName = $request->new_filename . '.jpg';

            // Save directly to public/members/images
            $file->move(public_path('users/images'), $newName);
            return back()->with('success', 'Image uploaded as ' . $newName);

    } //End Method

}
