<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{


    // Permission related Methods

    public function AllPermission(){

        $permissions = Permission::all();
        return view('admin.permission.all_permission', compact('permissions'));
    }
    // End Method

    public function AddPermission(){

        return view('admin.permission.add_permission');

    }
    //End Method

    public function StorePermission(Request $request){

        $permission = Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

            $notification = array(
                'message' => 'Permission Created Successfully',
                'aleart-type' =>'success',

            );

        return redirect()->route('all.permission')->with($notification);
    }
    // End Method

    public function EditPermission($id){

        $permission = Permission::findOrFail($id);
        return view('admin.permission.edit_permission', compact('permission'));

    }
    // End Method

    public function UpdatePermission(Request $request){

        $per_id = $request->id;
        Permission::findOrFail($per_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

            $notification = array(
                'message' => 'Permission Updated Successfully',
                'aleart-type' =>'success',

            );
            return redirect()->route('all.permission')->with($notification);
    }
    // End Method

    public function DeletePermission($id){

        Permission::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Permission Deleted Successfully',
            'aleart-type' =>'success',

        );
        return redirect()->back()->with($notification);

    }
    // End Method

    // Role related methods

    public function AllRole(){

        $roles = Role::all();
        return view('admin.permission.all_role', compact('roles'));

    }
    // End Method

    public function AddRole(){

        return view('admin.permission.add_role');
    }
    // End Method

    public function StoreRole(Request $request){

        $role = Role::create([
            'name' => $request->name,
        ]);

            $notification = array(
                'message' => 'Role Created Successfully',
                'aleart-type' =>'success',

            );

           return redirect()->route('all.role')->with($notification);

    }
    // End Method

    public function EditRole($id){

        $role = Role::findOrFail($id);
        return view('admin.permission.edit_role', compact('role'));

    }
    // End Method

    public function UpdateRole(Request $request){

        $role_id = $request->id;
        Role::findOrFail($role_id)->update([
            'name' => $request->name,

        ]);

            $notification = array(
                'message' => 'Role Updated Successfully',
                'aleart-type' =>'success',

            );
            return redirect()->route('all.role')->with($notification);
    }
    // End Method

    public function DeleteRole($id){

        Role::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Role Deleted Successfully',
            'aleart-type' =>'success',

        );
        return redirect()->back()->with($notification);
    }
    // End Method

    // Role Permission related methods

    public function AddRolesPermission(){

        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getPermissionGroups();
        return view('admin.permission.add_roles_permission',compact('roles', 'permissions','permission_groups'));
    }
    // End Method

    public function StoreRolesPermission(Request $request){

        $data = array();
        $permissions = $request->permission;

        foreach($permissions as $key => $item){
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);

        } // end foreach

        $notification = array(
            'message' => 'Role Permission Added Successfully',
            'aleart-type' =>'success',

        );
        return redirect()->route('all.roles.permission')->with($notification);

    }
    // End Method

    public function AllRolesPermission(){

        $roles = Role::all();
        return view('admin.permission.all_roles_permission', compact('roles'));
    }
    //End Method

    public function EditRolesPermission($id){

        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $permission_groups = User::getPermissionGroups();
        return view('admin.permission.edit_roles_permission',compact('role','permissions','permission_groups'));

    }
    // End Method

    public function UpdateRolesPermission(Request $request, $id){

        $data = array();
        $role = Role::findOrFail($id);
        $permissions = $request->permission;
        foreach ($permissions as $key => $item){
               $data[$key] = (int)$item;
        }
        //dd($permissions);
        if (!empty($data)) {
            $role->syncPermissions($data);
        }

        $notification = array(
            'message' => 'Role Permission Updated Successfully',
            'aleart-type' =>'success',

        );
        return redirect()->route('all.roles.permission')->with($notification);
    }
    // End Method

    public function DeleteRolePermission($id){

        $role = Role::findOrFail($id);
        if(!is_null($role)){
            $role->delete();

        }

        $notification = array(
            'message' => 'Role Permission Deleted Successfully',
            'aleart-type' =>'success',

        );
        return redirect()->back()->with($notification);

    }
    //End Method


}
