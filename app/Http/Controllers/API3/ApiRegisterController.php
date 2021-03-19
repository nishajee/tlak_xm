<?php
namespace App\Http\Controllers\API3;


use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use DB;
use Mail;


class ApiRegisterController extends Controller
{
    public function register(Request $request){
    //     $data = $request->all();
    //     $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    //     $str = '';
      
    //     for ($i = 0; $i <= 4; $i++) {
    //          $str .= $characters[rand(0, strlen($characters) - 1)];
    //       }
    // $tenant = Tenant::create([
    //         'name' => $data['name'],
    //         'tenant_id' => str_random(10) . time(),
    //         'tenant_code' =>  $str,  
    //         'company_name' => $data['company_name'],
    //         'company_id' => $data['company_id'],
    //         'phone' => $data['phone'],
    //         'email' => $data['email'],
    //         //'contact_person' => $data['contact_person'],
    //         'address_street' => $data['street_address'],
    //         'address_city' => $data['locality'],
    //         'address_zip' => $data['postal_code'],
    //         'address_country' => $data['country'],
    //         'company_website' => $data['company_website'],
    //         'hear_about' => $data['hear_about'],
    //         'referred_by' => $data['referred_by'],
    //     ]);
    // $user = User::create([
    //         'name' => $tenant->name,
    //         'email' => $data['email'],
    //         'password' => Hash::make($data['password']),
    //         'tenant_id' => $tenant->tenant_id,
    //         'tenant_code' => $tenant->tenant_code,
    //         'company_name' => $tenant->company_name,
    //         'company_id' => $tenant->company_id,
    //         'phone' => $tenant->phone,
    //         //'contact_person' => $tenant->contact_person,
    //         'address_street' => $tenant->address_street,
    //         'address_city' => $tenant->address_city,
    //         'address_zip' => $tenant->address_zip,
    //         'address_country' => $tenant->address_country,
    //         'company_website' => $tenant->company_website,        
    //         'referred_by' => $tenant->referred_by,        
    //         'verified' => '1',        
    //         'remember_token' => $data['_token'],
    //     ]);

    //   $id = $user->id;
    //   if($tenant->address_country == 'India'){
    //     $poi = PointOfInterest::where('tenant_id','HtNdtjNLRd1585898535')->get();
    //     foreach ($poi as $key => $value) {
    //         $poi_details = new PointOfInterest();
    //         $poi_details->name = $value->name;
    //         $poi_details->country_name = $value->country_name;
    //         $poi_details->latitude = $value->latitude;
    //         $poi_details->longitude = $value->longitude;
    //         $poi_details->description = $value->description;
    //         $poi_details->location_name = $value->location_name;
    //         $poi_details->locality = $value->locality;
    //         $poi_details->point_of_interest_icon_id = $value->point_of_interest_icon_id;
    //         $poi_details->address = $value->address;
    //         $poi_details->place_id = $value->place_id;
    //         $poi_details->hour_status = $value->hour_status;
    //         $poi_details->banner_image = $value->banner_image;
    //         $poi_details->address = $value->address;
    //         $poi_details->iso_2 = $value->iso_2;
    //         $poi_details->utc_offset = $value->utc_offset;
    //         $poi_details->user_id = $id;
    //         $poi_details->tenant_id = $tenant->tenant_id;
    //         $poi_details->point_type = $value->point_type;
    //         $poi_details->status = $value->status;
    //         $poi_details->save();
    //         $last_id = $poi_details->id;

    //         $desti = Location::where('name',$value->location_name)->where('tenant_id', $tenant->tenant_id)->first();
    //         if($desti == null)
    //         {
    //           $processes = Location::create([
    //               'name' => $value->location_name,
    //               'country_name' => $value->country_name,
    //               'utc_offset' => $value->utc_offset,
    //               'tenant_id' => $tenant->tenant_id,
    //               'user_id' => $id
    //           ]); 
    //         }

    //         $images = PointOfInterestImage::where('point_of_interest_id',$value->id)->get();
    //         foreach ($images as $key => $img_t) {
    //             $img = new PointOfInterestImage;
    //             $img->point_of_interest_id=$last_id;
    //             $img->poi_image=$img_t->poi_image;
    //             $img->save();
    //         }
    //     }
    //   }
    //   $data = ['name'=>$tenant->name, 'email'=>$data['email'], 'company_name' => $tenant->company_name, 'company_id'=>$tenant->company_id, 'mob_no' => $tenant->phone, 'address_street'=> $tenant->address_street,'address_city' => $tenant->address_city,'address_zip' => $tenant->address_zip,'address_country' => $tenant->address_country,'company_website' => $tenant->company_website, 'referred_by'=>$tenant->referred_by];

      $data = ['name'=>"nisha", 'email'=>"nisha.kumari@watconsultingservices.com", 'company_name' => 'watcons', 
      'company_id'=>'1140', 'mob_no' => '7992465822', 'address_street'=> 'jsr','address_city' => 'city','address_zip' => '832108','address_country' => 'india',
      'company_website' => 'www.nic.in', 'referred_by'=>'1565abnc'];

        Mail::send('emails.user_register_confirmation_mail',$data,function($mail) use($data){
            $mail->to('contact@watconsultingservices.com')->subject("Tlak new user registration details");
            $mail->cc('ajay.sharma@watconsultingservices.com');
        });
        return response()->json(['data'=>$data,'message'=>'','status'=> 200]);
    }
}