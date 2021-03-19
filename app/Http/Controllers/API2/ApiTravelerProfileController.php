<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageServiceProvider;
use Illuminate\Support\Facades\Storage;
use Image;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\Itinerary;
use App\HistoryTraveler;
use App\GetFeed;

class ApiTravelerProfileController extends Controller
{   
    public function travelerProfile(Request $request){

        $token = $request->token;
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
                'message' => $message[0]
            ];
            return Response($status);
        } 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.token','travelers.tenant_id as tenantId','travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.name as travelerName','travelers.traveler_email as travelerEmail','travelers.phone as travelerPhone','travelers.birth as travelerDOB','travelers.address as travelerAddress','profile_picture as profilePicture','crown_no as crownNo')->first();
            $tourpackage = TourPckage::where('id',$traveler->pkgId)->first();
            if($traveler){
                $historyTraveler = HistoryTraveler::where('traveler_id',$traveler->travelerId)->get();
                $total = count($historyTraveler);
                $totalContribution = GetFeed::where('traveler_id', $traveler->travelerId)->count();
                $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                $travelerName = ($traveler->travelerName == '' || $traveler->travelerName == null) ? ('') : ($traveler->travelerName);
                $travelerEmail = ($traveler->travelerEmail == '' || $traveler->travelerEmail == null) ? ('') : ($traveler->travelerEmail);
                $travelerPhone = ($traveler->travelerPhone == '' || $traveler->travelerPhone == null) ? ('') : ($traveler->travelerPhone);
                $travelerDOB = ($traveler->travelerDOB == '' || $traveler->travelerDOB == null) ? ('') : ($traveler->travelerDOB);
                $travelerAddress = ($traveler->travelerAddress == '' || $traveler->travelerAddress == null) ? ('') : ($traveler->travelerAddress);
                $profilePicture = ($traveler->profilePicture == '' || $traveler->profilePicture == null) ? (url("images/uploads/male.png")) : ($src.'traveller/profile/'.$traveler->profilePicture);
                $bannerImage = ($tourpackage->banner_image == '' || $tourpackage->banner_image == null) ? 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/banner_image/AjyYe1610100911.jpg' : 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/banner_image/'.$tourpackage->banner_image;

                $crown_no = Traveler::getCrownNo($traveler->travelerId);

                $profileTraveller = ['token'=>$traveler->token,'tenantId'=>$traveler->tenantId,'pkgId'=>$traveler->pkgId,'travelerId'=>$traveler->travelerId,'travelerName'=>$travelerName,'travelerEmail'=>$travelerEmail,'travelerPhone'=>$travelerPhone,'travelerDOB'=>$travelerDOB,'travelerAddress'=>$travelerAddress,'crownNo'=>$crown_no,'totalDeparture'=>$total,'totalContribution'=>$totalContribution,'profilePicture'=>$profilePicture,'bannerImage'=>$bannerImage];
            //dd($total);
            $status = array(
            'status' => true, 
            'traveler' => $profileTraveller
            ); 
              return response()->json($status, 200);
            }else{
              $status = array(
               'status' => false,
               'message' => 'Opps! Invalid response!!'
               );
              return response()->json($status, 200);
            }  
        }
      else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }     
    }

    public function updateTravelerProfile(Request $request)
    {

        $token = $request->token;
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
                'message' => $message[0]
            ];
            return Response($status);
        } 
        $travelerid = $request->travelerId;
        $traveleremail = $request->travelerEmail;
        $travelerphone = $request->travelerPhone; 
        $travelerbirth = $request->travelerDOB;
        $traveleraddress = $request->travelerAddress;
//dd($profilepic);
        $travelers = Traveler::where('id',$travelerid)->value('id');
        
        if($travelers){
        $traveler = Traveler::find($travelers);
        //dd($traveler);
                if($request->picture != null || $request->picture != ''){
                  $base64String= $request->picture; //64 bit code
                
    //dd($base64String);
                  $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));
                  $imageName = str_random(5).time() . '.png';

                  $p = Storage::disk('s3')->put('traveller/profile'.'/'.$imageName, $image, 'public'); 
                  $traveler->profile_picture = $imageName;
                  }
                  //$GroupChats->image = $imageName;
                  if($traveleremail != '' || $traveleremail != null){
                    $traveler->traveler_email = $traveleremail;
                  }
                  if($travelerphone != ''){
                    $traveler->phone = $travelerphone;
                  }
                  if($travelerbirth != ''){
                    $traveler->birth = $travelerbirth;
                  }
                  if($traveleraddress != ''){
                    $traveler->address = $traveleraddress;
                  }
                  $traveler->save();

                  // $traveler->update([
                  //     'traveler_email' => $traveleremail,
                  //     'phone' => $travelerphone,
                  //     'birth' => $travelerbirth,
                  //     'address' => $traveleraddress,
                  //     'profile_picture' =>$imageName
                  // ]);
                //}
                // else{
                //  if($traveleremail != ''){
                //     $traveler->traveler_email = $traveleremail;
                //   }
                //   if($travelerphone != ''){
                //     $traveler->phone = $travelerphone;
                //   }
                //   if($travelerbirth != ''){
                //     $traveler->birth = $travelerbirth;
                //   }
                //   if($traveleraddress != ''){
                //     $traveler->address = $traveleraddress;
                //   }
                //   $traveler->save();
                // }

            if ($traveler->traveler_email == '' || $traveler->traveler_email == null) {
                $traveler->traveler_email = '';
            }
            if ($traveler->phone == '' || $traveler->phone == null) {
                $traveler->phone = '';
            }
            if ($traveler->birth == '' || $traveler->birth == null) {
                $traveler->birth = '';
            }
            if ($traveler->address == '' || $traveler->address == null) {
                $traveler->address = '';
            }
            $crown_no = Traveler::getCrownNo($traveler->id);
            $status = array(
                'status' => true, 
                'message' => 'Bingo! Profile updated successfully!',
                'travelerId' =>$traveler->id,
                'token' =>$traveler->token,
                'crownNo' =>$crown_no,
                'travelerEmail' =>$traveler->traveler_email,
                'phone' =>$traveler->phone,
                'birth' =>$traveler->birth,
                'address' =>$traveler->address,
                'profileImage' => 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture,
                ); 
                return response()->json($status, 200);
        }
        else{
                $status = array(
                'status' => false,
                'message' => 'Opps! Invalid response!!'
                );
                return response()->json($status, 200);
        }    
    }
}