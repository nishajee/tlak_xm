<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Mail;
use Auth;
use App\Tenant;
use App\Role;
use App\User;

class UserController extends Controller
{
    public function index(Request $Request)
    {
        $permission = User::getPermissions();
        if (Gate::allows('user_view',$permission)) {
        	$user = auth()->user();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        	$all_users = User::where('tenant_id', $user->tenant_id)->whereNotIn('role_id', [0])->get();
        	foreach ($all_users as $key => $value){
                $name = Role::where('id', $value->role_id)->value('name');
                $value['role_name'] = $name;
            }
        	return view('users.index', compact('all_users','tenant','permission'));
        }
        else{
            return abort(403);
        }
    }

    public function createUser(Request $Request)
    {	
        $permission = User::getPermissions();
        if (Gate::allows('user_create',$permission)) {
        	$user = auth()->user();
        	$roles = Role::where('tenant_id',$user->tenant_id)->where('status', '1')->get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        	return view('users.create', compact('roles','tenant'));
        }
        else{
            return abort(403);
        }
    }

    public function addUser(Request $request)
    {
        
    	$validatedData = $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'role' => 'required',
        ]);
        
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
        $token = '';
      
        for ($i = 0; $i <= 39; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

    	$user_details = auth()->user();
        $user = new User;
        $user->name = $request->name;
        $user->password = Hash::make('H3hWX64@f56');
        $user->email = $request->email;
        $user->remember_token =  $token;
        $user->phone = $request->mobile_number;
        $user->role_id = $request->role;
        $user->tenant_id = $user_details->tenant_id;
        $user->tenant_code = $user_details->tenant_code;
        $user->email_verified_at = $user_details->email_verified_at;
        $user->status = '0';
        $user->verified = '0';
        $user->save();

        $verify_link = url('/')."/verifyandresetpassword/".$token;
        $data = ['name'=>$request->name, 'sender_name'=>$user_details->name, 'contact_no'=>$request->mobile_number, 'verify_link' => $verify_link];

        Mail::send('emails.user_verification',$data,function($mail) use($request){
            $mail->from('info@tlakapp.com');
            $mail->to($request->email)->subject("User verification and reset password mail");
        });

        $request->session()->flash('message', 'User added successfully.');
        return redirect()->route('users');

    }

    public function activateUser(Request $request, $id)
    {
        $user = User::find($id);
        $user->status = 1;
        $user->save();
        return response()->json([
           'success' => 'User activated successfully!'
        ]);
    }

    public function inactivateUser(Request $request, $id)
    {
        $user = User::find($id);
        $user->status = 0;
        $user->save();
        return response()->json([
           'success' => 'User inactivated successfully!'
        ]);
    }

    public function resendEmail(Request $request, $id)
    {
        $details = User::where('id', $id)->first();
        $sender_name = auth()->user()->name;
        $verify_link = url('/')."/verifyandresetpassword/".$details->remember_token;
        $data = ['name'=>$details->name, 'sender_name'=>$sender_name, 'contact_no'=>$details->phone, 'verify_link' => $verify_link];

        Mail::send('emails.user_verification',$data,function($mail) use($request, $details){
            $mail->from('info@tlakapp.com');
            $mail->to($details->email)->subject("User verification and reset password mail");
        });

        return response()->json([
           'success' => 'Verification email successfully!'
        ]);
    }

    public function editUser(Request $Request, $id)
    {
        $permission = User::getPermissions();
        if (Gate::allows('user_edit',$permission)) {   
            $user = auth()->user();
            $roles = Role::where('tenant_id',$user->tenant_id)->where('status', '1')->get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $user_details = User::where('id', $id)->first();
            return view('users.edit', compact('roles','tenant','user_details'));
        }
        else{
            return abort(403);
        }
    }

    public function updateUser(Request $request, $id)
    {
        $validatedData = $request->validate([
           'name' => 'required|string|max:255',
           'role' => 'required',
        ]);

        $user_details = auth()->user();

        User::where('id', $id)->update(['name' => $request->name, 'phone' => $request->mobile_number, 'role_id' => $request->role]);
        $request->session()->flash('message', 'User updated successfully.');
        return redirect()->route('users');

    }

    public function verifyAndResetPassword(Request $request, $token)
    {

        if (User::where('remember_token', '=', $token)->exists()) {
           $details = User::where('remember_token', $token)->first();
           if ($details->verified == 0) {
              return view('users.verifyAndResetPassword', compact('details','token'));
           }
           else{
                abort(403, 'Unauthorized action.');
           }
           
        }
        else
        {
            abort(403, 'Unauthorized action.');
        }
    }

    public function verifyUser(Request $request)
    {
        $validatedData = $request->validate([
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'string', 'min:6', 'same:password'],
        ]);

        $update = User::where('remember_token', $request->token)->update(['password' => Hash::make($request->password), 'status' => '1','verified' => '1']);
        if ($update) {
            return view('auth.login');
        }
        else{
            abort(403, 'Unauthorized action.');
        }    
    }

}
