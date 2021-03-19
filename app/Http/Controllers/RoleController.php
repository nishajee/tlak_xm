<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Tenant;
use Auth;
use App\User;
use App\Permission;
use App\Role;
use App\PermissionRole;

class RoleController extends Controller
{
    public function index(Request $Request)
    {
        $permission = User::getPermissions();
        if (Gate::allows('role_view',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $user = auth()->user();
            $roles = Role::where('tenant_id',$user->tenant_id)->where('status', '1')->get();
            $permission_roles = PermissionRole::all();
            $permissions = Permission::all();
            foreach ($roles as $key => $role){
                $per_id = array();
                foreach($permission_roles as $key => $permission_role){
                  if($permission_role->role_id == $role->id){
                      array_push($per_id,$permission_role->permission_id);
                  }
                }
                $role['roles_id'] = $per_id;
            }

            return view('roles.index',compact('permissions','permission_roles','roles','tenant','permission'));
        }
        else{
            return abort(403);
        }
    }

    public function page(Request $Request)
    {   

        $user = auth()->user();
        $roles = Role::where('tenant_id',$user->tenant_id)->where('status', '1')->get();
        $permission_roles = PermissionRole::all();
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $permissions = Permission::all();
        $permission_names = array();
        foreach ($roles as $key => $role){
            $val = array();
            $per_id = array();
            foreach($permission_roles as $key => $permission_role){
              if($permission_role->role_id == $role->id){
                  $name_per = Permission::where('id', $permission_role->permission_id)->value('name');
                  array_push($val,$name_per);
                  array_push($per_id,$permission_role->permission_id);
              }
            }
            $role['roles_name'] = $val;
            $role['roles_id'] = $per_id;
        }
        return view('roles.page', compact('permissions','permission_roles','roles','tenant'));
    }

    public function store(Request $request)
    {
        // $validator = \Validator::make($request->all(), [
        //     'name' => 'required|unique:roles',
        // ]);
        // if ($validator->fails())
        // {
        //     return response()->json(['errors'=>$validator->errors()->all()]);
        // }
        $fname = $request->get('name');
        $user = auth()->user();
        $roleunique  = Role::where('tenant_id',$user->tenant_id)->where('name',$fname)->first();
        if($roleunique){
            return Response()->json(['status' => 302, 'errors' => 'The name has already been taken!!']);
            exit;
        }
        

        $role = new Role();
        $role->name = $request->get('name');
        $role->tenant_id = $user->tenant_id;
        $role->user_id = $user->id;
        $role->save();

        return response()->json(['status'=>'200']);
    }

    public function deleteRole(Request $request, $id)
    {
        $role = Role::find($id);
        $role->status = 0;
        $role->save();
        return response()->json([
           'success' => 'Role deleted successfully!'
        ]);
    }

    public function update(Request $request, $id)
    {

        $permission=$request->get('permissions');
        $role_name = Role::where('id', $id)->value('name');
        PermissionRole::where('role_id', $id)->delete();

        if($permission){                
            foreach ($permission as $value) {
                $permission = new PermissionRole;
                $permission->role_id=$id;
                $permission->permission_id=$value;
                $permission->save();
            }  
        }

        $message = $role_name." permission updated successfully!!";
        return redirect()->back()->with('success', $message);
    }
}
