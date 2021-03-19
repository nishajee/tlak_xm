<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\DepartureGuide;
use App\DepartureManager;
use App\Communication;
use App\Placard;

class ApiSOSController extends Controller
{   
    public function sosApp(Request $request){

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
        
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first(); 
            if($tourPackage){
              $depMana = DepartureManager::where('tour_package_id', $tour_package_id)->select('phone as depManagerPhone')->get();
              if(count($depMana)>0){
                foreach ($depMana as $value) {
                  $mngr[] = [$value->depManagerPhone];
                }
              }
              else{
                $mngr = [];
              }
              $depGuide = DepartureGuide::where('tour_package_id', $tour_package_id)->select('phone as depGuidePhone')->get();
              if(count($depGuide)>0){
                foreach ($depGuide as $value) {
                  $guide[] = [$value->depGuidePhone];
                }
              }
              else{
                $guide =[];
              }
              $comanyContact = Communication::where('tour_package_id', $tour_package_id)->select('phone as agentPhone')->get();
              if(count($comanyContact) > 0){
                foreach ($comanyContact as $value) {
                  $agent[] = [$value->agentPhone];
                }
              }
              else{
                $agent= [];
              }
              $sosContacts = array_merge($mngr,$guide,$agent);
              $characters = '123456789';
              $otp = '';
              for ($i = 0; $i <= 3; $i++) {
                      $otp .= $characters[rand(0, strlen($characters) - 1)];
              }
              if($sosContacts){
                foreach($sosContacts as $mob){     
                  $route = "default"; 
                  $post_data = array(   
                      'From'   => 'ADBOEK',
                      'To'    => $mob,
                      'Body'  => "OTP to authenticate your ADBOEK account: ".$otp."", 
                  );
                  $api_key = "6c1be7e3169cbe631761d6bd74f14aa77b3c7071fc624e09"; // Your `API KEY`.
                  $api_token = "9c5ec6cc176eede226191bc41b2629e8c03f42af25515d52"; // Your `API TOKEN`
                  $exotel_sid = "watconsultingservices1" ;// Your `Account Sid`        
                  $url = "https://".$api_key.":".$api_token."@api.exotel.com/v1/Accounts/".$exotel_sid."/Sms/send";
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_VERBOSE, 1);
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_POST, 1);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_FAILONERROR, 0);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));        
                  $http_result = curl_exec($ch);
                  $error = curl_error($ch);
                  $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);        
                  curl_close($ch);
                }
              }
              $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!'
              ); 
                return response()->json($status, 200);
            }
            else{
              $status = array(
               'error' => true,
               'message' => 'Opps! No itinerary found!!'
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

    
}       
  