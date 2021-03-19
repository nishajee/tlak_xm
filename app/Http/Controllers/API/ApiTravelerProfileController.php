<?php

namespace App\Http\Controllers\API;

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
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
        } 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.token','travelers.tenant_id as tenantId','travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.name as travelerName','travelers.traveler_email as travelerEmail','travelers.phone as travelerPhone','travelers.birth as travelerDOB','travelers.address as travelerAddress','profile_picture as profilePicture')->first();
            if($traveler){
                $historyTraveler = HistoryTraveler::where('traveler_id',$traveler->travelerId)->get();
            $total = count($historyTraveler);
                if($traveler->profilePicture ==  null || $traveler->profilePicture == ''){
                  $profileTraveller = ['token'=>$traveler->token,'tenantId'=>$traveler->tenantId,'pkgId'=>$traveler->pkgId,'travelerId'=>$traveler->travelerId,'travelerName'=>$traveler->travelerName,'travelerEmail'=>$traveler->travelerEmail,'travelerPhone'=>$traveler->travelerPhone,'travelerDOB'=>$traveler->travelerDOB,'travelerAddress'=>$traveler->travelerAddress,'profilePicture'=>url("images/uploads/male.png")];
                }
                else{
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                  $profileTraveller = ['token'=>$traveler->token,'tenantId'=>$traveler->tenantId,'pkgId'=>$traveler->pkgId,'travelerId'=>$traveler->travelerId,'travelerName'=>$traveler->travelerName,'travelerEmail'=>$traveler->travelerEmail,'travelerPhone'=>$traveler->travelerPhone,'travelerDOB'=>$traveler->travelerDOB,'travelerAddress'=>$traveler->travelerAddress,'profilePicture'=>$src.'traveller/profile/'.$traveler->profilePicture];
                }
            //dd($total);
            $status = array(
            'error' => false, 
            'traveler' => $profileTraveller,
            //'profilePicture' =>url("images/uploads/male.png"),
            'totalDeparture' => $total
            ); 
              return response()->json($status, 200);
            }else{
              $status = array(
               'error' => true,
               'message' => 'Opps! Invalid response!!'
               );
              return response()->json($status, 200);
            }  
        }
      else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }     
    }

    public function updateTravelerProfile(Request $request){


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
                // }
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


            $status = array(
                'error' => false, 
                'message' => 'Bingo! Profile updated successfully!',
                'travelerId' =>$traveler->id,
                'token' =>$traveler->token,
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
                'error' => true,
                'message' => 'Opps! Invalid response!!'
                );
                return response()->json($status, 200);
        }    
    }
}