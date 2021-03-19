<?php

namespace App\Http\Controllers\API2;
use App\Http\Controllers\Controller;
use App\User; 
use App\Tenant; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str;
use Validator;
use Hash;
use Mail;

use App\PointOfInterest;
use App\Location;
use App\PointOfInterestImage;


class PassportUserController extends Controller
{
    public $successStatus = 200;

    public function login() { 
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $data = ['name'=>'nisha', 'email'=>'nisha.kumari@watconsultingservices.com', 
            'company_name' => 'test', 'company_id'=>'1', 'mob_no' => '7418529630', 
            'address_street'=> 'demo','address_city' => 'jsr',
            'address_zip' => '832108','address_country' => 'india','company_website' =>'www.gmail.com', 
            'referred_by'=>'me'];
            Mail::send('emails.user_register_confirmation_mail',$data,function($mail) use($data){
                $mail->to('nisha.kumari@watconsultingservices.com')->subject("Tlak new user registration details");
                $mail->cc('ajay.sharma@watconsultingservices.com');
            });
            return response()->json(['success' => $success, 'message'=>"User Loggedin Sucessfully"], $this-> successStatus);
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }

    public function register(Request $request) { 
        $data = $request->all();
        $validator = Validator::make($request->all(), [ 
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'string', 'min:6', 'same:password'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string','min:3', 'max:6', 'unique:users'],
            //'contact_person' => ['required', 'string'],
            'phone' => ['required', 'numeric'],
            'locality' => ['required', 'string'],
            'postal_code' => ['required', 'numeric'],
            'country' => ['required', 'string'],
            'term_conditions' =>['required','in:1'],
            'street_address' =>['required','string']
        ]);
if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }


    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $str = '';
  
    for ($i = 0; $i <= 4; $i++) {
         $str .= $characters[rand(0, strlen($characters) - 1)];
      }
$tenant = Tenant::create([
        'name' => $data['name'],
        'tenant_id' => str::random(10) . time(),
        'tenant_code' =>  $str,  
        'company_name' => $data['company_name'],
        'company_id' => $data['company_id'],
        'phone' => $data['phone'],
        'email' => $data['email'],
        //'contact_person' => $data['contact_person'],
        'address_street' => $data['street_address'],
        'address_city' => $data['locality'],
        'address_zip' => $data['postal_code'],
        'address_country' => $data['country'],
        'company_website' => $data['company_website'],
        'hear_about' => $data['hear_about'],
        'referred_by' => $data['referred_by'],
    ]);

    //return $tenant->name;

   $user = new User();
   $user->name = $tenant->name;
   $user->email = $data['email'];
   $user->password = Hash::make($data['password']);
   $user->company_name = $tenant->company_name;
   $user->company_id = $tenant->company_id;
   $user->tenant_id = $tenant->tenant_id;
   $user->tenant_code = $tenant->tenant_code;
   $user->phone = $tenant->phone;
   $user->address_street = $tenant->address_street;
   $user->address_city = $tenant->address_city;
   $user->address_zip = $tenant->address_zip;
   $user->address_country = $tenant->address_country;
   $user->company_website = $tenant->company_website;        
   $user->referred_by = $tenant->referred_by;        
   $user->verified = '1';  
   $user->save();
   

    // User::create([
    //     'name' => $tenant->name,
    //     'email' => $data['email'],
    //     'password' => Hash::make($data['password']),
    //     'tenant_id' => $tenant->tenant_id,
    //     'tenant_code' => $tenant->tenant_code,
    //     'company_name' => $tenant->company_name,
    //     'company_id' => $tenant->company_id,
    //     'phone' => $tenant->phone,
    //     //'contact_person' => $tenant->contact_person,
    //     'address_street' => $tenant->address_street,
    //     'address_city' => $tenant->address_city,
    //     'address_zip' => $tenant->address_zip,
    //     'address_country' => $tenant->address_country,
    //     'company_website' => $tenant->company_website,        
    //     'referred_by' => $tenant->referred_by,        
    //     'verified' => '1',        
    //     //'remember_token' => $data['_token'],
    //     //'remember_token' => $user->createToken('MyApp')-> accessToken,
    // ]);

  $id = $user->id;
  if($tenant->address_country == 'India'){
    $poi = PointOfInterest::where('tenant_id','HtNdtjNLRd1585898535')->get();
    foreach ($poi as $key => $value) {
        $poi_details = new PointOfInterest();
        $poi_details->name = $value->name;
        $poi_details->country_name = $value->country_name;
        $poi_details->latitude = $value->latitude;
        $poi_details->longitude = $value->longitude;
        $poi_details->description = $value->description;
        $poi_details->location_name = $value->location_name;
        $poi_details->locality = $value->locality;
        $poi_details->point_of_interest_icon_id = $value->point_of_interest_icon_id;
        $poi_details->address = $value->address;
        $poi_details->place_id = $value->place_id;
        $poi_details->hour_status = $value->hour_status;
        $poi_details->banner_image = $value->banner_image;
        $poi_details->address = $value->address;
        $poi_details->iso_2 = $value->iso_2;
        $poi_details->utc_offset = $value->utc_offset;
        $poi_details->user_id = $id;
        $poi_details->tenant_id = $tenant->tenant_id;
        $poi_details->point_type = $value->point_type;
        $poi_details->status = $value->status;
        $poi_details->save();
        $last_id = $poi_details->id;

        $desti = Location::where('name',$value->location_name)->where('tenant_id', $tenant->tenant_id)->first();
        if($desti == null)
        {
          $processes = Location::create([
              'name' => $value->location_name,
              'country_name' => $value->country_name,
              'utc_offset' => $value->utc_offset,
              'tenant_id' => $tenant->tenant_id,
              'user_id' => $id
          ]); 
        }

        $images = PointOfInterestImage::where('point_of_interest_id',$value->id)->get();
        foreach ($images as $key => $img_t) {
            $img = new PointOfInterestImage;
            $img->point_of_interest_id=$last_id;
            $img->poi_image=$img_t->poi_image;
            $img->save();
        }
    }
  }
  $data = ['name'=>$tenant->name, 'email'=>$data['email'], 'company_name' => $tenant->company_name, 'company_id'=>$tenant->company_id, 'mob_no' => $tenant->phone, 'address_street'=> $tenant->address_street,'address_city' => $tenant->address_city,'address_zip' => $tenant->address_zip,'address_country' => $tenant->address_country,'company_website' => $tenant->company_website, 'referred_by'=>$tenant->referred_by];

    Mail::send('emails.user_register_confirmation_mail',$data,function($mail) use($data){
        $mail->to('contact@watconsultingservices.com')->subject("Tlak new user registration details");
        $mail->cc('ajay.sharma@watconsultingservices.com');
    });

     // $input = $request->all(); 
      //  $input['password'] = bcrypt($input['password']); 
       // $user = User::create($input); 
        $success['token'] =  $user->createToken('MyApp')-> accessToken; 
        $success['name'] =  $user->name;
        $user->sendApiEmailVerificationNotification();
$success['message'] = 'Please confirm yourself by clicking on verify user button sent to you on your email';

return response()->json(['success'=>$success,'message'=> $success['message']], $this-> successStatus); 
    }
//http://mylemp-nginx/oauth/token


public function details() 
{ 
    $user = Auth::user(); 
    return response()->json(['success' => $user], $this-> successStatus); 
} 

    public function getTokenAndRefreshToken(OClient $oClient, $email, $password) { 
        $oClient = OClient::where('password_client', 1)->first();
        $http =  new Client;
        $response = $http->request('POST', 'http://your-app.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oClient->id,
                'client_secret' => $oClient->secret,
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);
        return response()->json($result, $this->successStatus);
    }
}
