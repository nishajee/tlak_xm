<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Hash;
use Image;
use Auth;
use App\Tenant;
use App\User;
use finfo;
class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {  
        $settings = Auth::User();
        $tenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        return view('setting.view',compact('settings','tenant'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function companyInfoEdit($id)
    {     
        //$tourpackage  = TourPckage::where('id',$id)->first();
        $company =  Auth::User();
        $tenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        return view('setting.edit',compact('company','tenant'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function companyInfoUpdate(Request $request, $id)
    {
        $data = $request->all();
        $validatedData = $request->validate([
            'company_name' =>'required',
            'phone' => 'required',
            'name' => 'required',
            //'contact_person' => 'required',
        ]); 
        $tenantId = Tenant::where('tenant_id',$id)->value('id');
        $setting = Tenant::find($tenantId);
        $setting->name = $request->name;
        $setting->phone = $request->phone;
        $setting->company_name = $request->company_name;
        $setting->facebook = $request->facebook;
        $setting->twitter = $request->twitter;
        $setting->instagram = $request->instagram;
        $setting->company_website = $request->company_website;
           
        // $user = auth()->user();

        // $setting->tenant_id = $user->tenant_id;
        $setting->save();
        //dd($setting);
        //$settingtenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        // $settingtenant->passenger_booking_no_year = $request->passenger_booking_no_year;
        // $settingtenant->travel_booking_no_year = $request->travel_booking_no_year;
         
        // $user = auth()->user();
        // $settingtenant->tenant_code = $user->tenant_code;
        // $settingtenant->tenant_id = $user->tenant_id;
        // $settingtenant->save();

        $request->session()->flash('status', 'Company updated successfully.');
        return Redirect::to('settings');
    }

    public function settingLogoEdit($id)
    {     
        $tenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        $notificationImage = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/company/';

        return view('setting.editlogo',compact('tenant','notificationImage'));
    }

    public function settingLogoUpdate(Request $request, $id)
    {
        $data = $request->all();
        
        if($request->cropedimage){
            $url = $request->cropedimage;
            $basename = basename($request->cropedimage);
            $type = pathinfo($url, PATHINFO_EXTENSION);
            $data = file_get_contents($url);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
            $imageName = str_random(5).time() . '.png'; 
            
            Storage::disk('s3')->put('company/'.$imageName, $image, 'public');
            $user = auth()->user();
            Tenant::where('tenant_id', $user->tenant_id)->update(['company_logo' => $imageName]);
        }

       return response()->json([
           'success' => 'Logo updated successfully'
       ]);
    }

    public function settingsEmailPasswordEdit($id)
    {     
        $emailpwd =  Auth::User();
        $tenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        return view('setting.email_password',compact('emailpwd','tenant'));
    }

    public function settingsEmailPasswordUpdate(Request $request, $id)
    {
        $data = $request->all();
        $validatedData = $request->validate([
          'name' => 'required',
          'email' => 'required',
          'password' => 'required|string|min:6',
          'password_confirmation' => 'required|string|min:6|same:password',
          'current_pwd' => 'required|string|min:6',
        ]);
    
         
        $email_password = User::find(auth()->user()->id);
        if(!Hash::check($data['current_pwd'], auth()->user()->password)){
            return back()->with('status','The current password does not match the database password');
        }
        else{
            $email_password->password = Hash::make($request->password);
            $email_password->name = $request->name;
            $email_password->email = $request->email;
        }        
        $user = auth()->user();
        $email_password->tenant_id = $user->tenant_id;
        $email_password->tenant_code = $user->tenant_code;
        $email_password->save();
        $request->session()->flash('status', 'Password updated successfully.');
        //return Redirect::to('settings');
        return redirect()->route('company_emailpwd_edit',Auth::User());
    }

    public function settingsHeaderFooterEdit($id)
    {
        $tenant = Tenant::where('tenant_id','=',Auth::User()->tenant_id)->first();
        $user =  Auth::User();
        $notificationImage = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/company/';
        //dd($company_creation);
        return view('setting.company_creation',compact('tenant','user','notificationImage'));
    }

    public function settingsHeaderFooterUpdate(Request $request, $id)
    {
        $data = $request->all(); 
        //$HeaderFooter = Tenant::find($id);
        $tenantId = Tenant::where('tenant_id',$id)->value('id');
        $HeaderFooter = Tenant::find($tenantId);
        //dd($tenantId);
        if($request->hasFile('company_header')){
            
            $files = $request->file('company_header');
            $imageName = str_random(5).time().'.'.$files->getClientOriginalExtension();
            //dd($imageName);
            $img = Image::make($files);
            $img->stream();
            $storagePath = Storage::disk('s3')->put('company'.'/'.$imageName, (string)$img, 'public');

            $HeaderFooter->company_header = $imageName; 
            $HeaderFooter->save();
        }
        if($request->hasFile('company_footer')){
             
            $files1 = $request->file('company_footer');
            $imageName1 = str_random(5).time().'.'.$files1->getClientOriginalExtension();
            $img1 = Image::make($files1);
            $img1->stream();
            $storagePath = Storage::disk('s3')->put('company'.'/'.$imageName1, (string)$img1, 'public');
            $HeaderFooter->company_footer = $imageName1;
            $HeaderFooter->save();
        }  
        $request->session()->flash('status', 'Company Header Footer updated!');
        return redirect()->route('edit_company',Auth::User());
    }

    //Image cropper for create form
    public function cropImageLogo(Request $request){

            $data = $request->image;
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $imageName = time() . '.png';
            $relPath = 'images/cropdumyimages/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                Image::make($data)->save( public_path($relPath . $imageName ) );
            //file_put_contents($imageName, $data);
                $urls = public_path("images/cropdumyimages/".$imageName);
                $url = url("images/cropdumyimages/".$imageName);
            //echo '<img src="'.$url.'" class="img-thumbnail" />';
            return response()->json(['img' => $url, 'url' => $urls]);

    }
}
