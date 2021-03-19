<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\DepartureGuide;
use App\DepartureManager;
use App\Communication;
use App\Placard;
use App\Tenant;
use App\AlarmNotification;

class ApiSOSController extends Controller
{   
    // public function sosApp(Request $request){

    //     $token = $request->token;
    //     $validator = Validator::make($request->all(),[
    //         'token' => 'required'
    //         ]);

    //     if($validator->fails()){
    //         $message = $validator->errors()->all();

    //         $status = [
    //             'status' => false,
    //             'message' => $message[0]
    //         ];
    //         return Response($status);
    //     } 
        
    //       $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
    //       if($traveler){
    //         $tour_package_id=$traveler->pkgId;
    //         $tourPackage= TourPckage::where('id', $tour_package_id)
    //               ->where(function($q) {
    //                             $q->where('status', 2);
    //                         })
    //               ->first(); 
    //         if($tourPackage){
    //           $depMana = DepartureManager::where('tour_package_id', $tour_package_id)->select('phone as depManagerPhone')->get();
    //           if(count($depMana)>0){
    //             foreach ($depMana as $value) {
    //               $mngr[] = [$value->depManagerPhone];
    //             }
    //           }
    //           else{
    //             $mngr = [];
    //           }
    //           $depGuide = DepartureGuide::where('tour_package_id', $tour_package_id)->select('phone as depGuidePhone')->get();
    //           if(count($depGuide)>0){
    //             foreach ($depGuide as $value) {
    //               $guide[] = [$value->depGuidePhone];
    //             }
    //           }
    //           else{
    //             $guide =[];
    //           }
    //           $comanyContact = Communication::where('tour_package_id', $tour_package_id)->select('phone as agentPhone')->get();
    //           if(count($comanyContact) > 0){
    //             foreach ($comanyContact as $value) {
    //               $agent[] = [$value->agentPhone];
    //             }
    //           }
    //           else{
    //             $agent= [];
    //           }
    //           $sosContacts = array_merge($mngr,$guide,$agent);
    //           $characters = '123456789';
    //           $otp = '';
    //           for ($i = 0; $i <= 3; $i++) {
    //                   $otp .= $characters[rand(0, strlen($characters) - 1)];
    //           }
    //           if($sosContacts){
    //             foreach($sosContacts as $mob){     
    //               $route = "default"; 
    //               $post_data = array(   
    //                   'From'   => 'ADBOEK',
    //                   'To'    => $mob,
    //                   'Body'  => "OTP to authenticate your ADBOEK account: ".$otp."", 
    //               );
    //               $api_key = "6c1be7e3169cbe631761d6bd74f14aa77b3c7071fc624e09"; // Your `API KEY`.
    //               $api_token = "9c5ec6cc176eede226191bc41b2629e8c03f42af25515d52"; // Your `API TOKEN`
    //               $exotel_sid = "watconsultingservices1" ;// Your `Account Sid`        
    //               $url = "https://".$api_key.":".$api_token."@api.exotel.com/v1/Accounts/".$exotel_sid."/Sms/send";
    //               $ch = curl_init();
    //               curl_setopt($ch, CURLOPT_VERBOSE, 1);
    //               curl_setopt($ch, CURLOPT_URL, $url);
    //               curl_setopt($ch, CURLOPT_POST, 1);
    //               curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //               curl_setopt($ch, CURLOPT_FAILONERROR, 0);
    //               curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //               curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));        
    //               $http_result = curl_exec($ch);
    //               $error = curl_error($ch);
    //               $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);        
    //               curl_close($ch);
    //             }
    //           }
    //           $status = array(
    //             'status' => true,
    //             'message' => 'Bingo! Success!!'
    //           ); 
    //             return response()->json($status, 200);
    //         }
    //         else{
    //           $status = array(
    //            'status' => false,
    //            'message' => 'Opps! No itinerary found!!'
    //            );
    //           return response()->json($status, 200);
    //         } 
    //       }
    //       else{
    //         $status = array(
    //          'status' => false,
    //          'message' => 'Opps! Invalid response!!'
    //          );
    //         return response()->json($status, 200);
    //       }        
    // }

  public function sosApp(Request $request){

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
        
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId','travelers.name as travelerName')->first();
  
          if($traveler){
            $all_contacts = [];
            $tenant_email = Tenant::where('tenant_id', $traveler->tenantId)->first();
            $ten_email[] = [$tenant_email->email];
            array_push($all_contacts, $tenant_email->phone);
            $package_name = TourPckage::where('id', $traveler->pkgId)->select('pname')->first();
            $traveler_details = Traveler::where('id', $request->travelerId)->first();

            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first(); 
            if($tourPackage){
              $depMana = DepartureManager::where('tour_package_id', $tour_package_id)->select('email as depManagerEmail','phone as depManagerPhone')->get();
              if(count($depMana)>0){
                foreach ($depMana as $value) {
                  if($value->depManagerEmail != null || $value->depManagerEmail != ''){
                    $mngr[] = [$value->depManagerEmail];
                  }
                  if($value->depManagerPhone != null || $value->depManagerPhone != ''){
                    array_push($all_contacts, $value->depManagerPhone);
                  }
                }
              }
              else{
                $mngr = [];
                $mngr_phone = [];
              }
              $comanyContact = Communication::where('tour_package_id', $tour_package_id)->select('email as agentEmail','phone as agentPhone')->get();
              if(count($comanyContact) > 0){
                foreach ($comanyContact as $value1) {
                  if($value1->agentEmail != null || $value1->agentEmail != ''){
                    $agent[] = [$value1->agentEmail];
                  }
                  if($value1->agentPhone != null || $value1->agentPhone != ''){
                    array_push($all_contacts, $value1->agentPhone);
                  }
                }
              }
              else{
                $agent= [];
                $agent_phone = [];
              }
              $sosEmail = array_merge($mngr,$agent,$ten_email);
              // $result = $this->sendSosSms($all_contacts);
        
              $location_link = 'https://www.google.com/maps/place/'.$request->lat.','.$request->long.'/@'.$request->lat.','.$request->long.',17z/data=!3m1!4b1';
              
              if($sosEmail){
                foreach($sosEmail as $mail_ids){ 
                  if($mail_ids != '' || $mail_ids != null){
                      $data = ['name'=>$traveler_details->name,'email'=>$traveler_details->traveler_email,'phone'=>$traveler_details->phone,'datetime'=>$request->datetime, 'package_name'=>$package_name->pname,'location_link'=>$location_link];
                      Mail::send('emails.sos_email',$data,function($mail) use($data, $mail_ids){
                        $mail->to($mail_ids)->subject("SOS - Emergency Contact Request");
                    });
                  }
                }
              }

              //Android sos notification
              $android_device_id = Traveler::where(['tour_package_id'=> $traveler->pkgId, 'type' => 'Manager', 'device_type' => 'android'])
                            ->whereNotNull('device_id')
                            ->get()
                            ->unique('device_id');
              $msg = 'Name: '.$traveler_details->name.', Email: '.$traveler_details->traveler_email.', Phone: '.$traveler_details->phone.', Date & Time: '.$request->datetime.', Package Name: '.$package_name->pname.', Message: SOS - Emergency Contact Request, Contact with user immediately';
                            
              foreach ($android_device_id as $device) {
                Traveler::sosAndroidNotification($device->device_id,"SOS - Emergency Contact Request",$msg);
              }

              //ios sos notification
              $ios_device_id = Traveler::where(['tour_package_id'=> $traveler->pkgId, 'type' => 'Manager', 'device_type' => 'ios'])
                            ->whereNotNull('device_id')
                            ->get()
                            ->unique('device_id');
                            
              foreach ($ios_device_id as $device_ios) {
                Traveler::sosIosNotification($device_ios->device_id,"SOS - Emergency Contact Request",$msg);
              }

              $status = array(
                'status' => true,
                'message' => 'Help messages are being sent to your primary contacts.'
              ); 
                return response()->json($status, 200);
            }
            else{
              $status = array(
               'status' => false,
               'message' => 'Opps! No itinerary found!!'
               );
              return response()->json($status, 200);
            } 
          }
          else{
            $status = array(
             'status' => false,
             'message' => 'Opps! Invalid token!!'
             );
            return response()->json($status, 200);
          }        
    }


    function sendSosSms($contacts)
    {
      if($contacts){
        foreach($contacts as $mob){
          $route = "default"; 
          $post_data = array(   
              'From'   => 'WATCON',
              'To'    => $mob,
              'Body'  => 'OTP to authenticate your ADBOEK account: 638436', 
          );
          $api_key = "6c1be7e3169cbe631761d6bd74f14aa77b3c7071fc624e09";
          $api_token = "9c5ec6cc176eede226191bc41b2629e8c03f42af25515d52";
          $exotel_sid = "WATCON" ;       
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
          print_r($http_result);
          $error = curl_error($ch);
          $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);        
          curl_close($ch);
          die();
        }
      }
    }

    public function allAlarm(Request $request)
    {
        $data = AlarmNotification::where('tour_package_id', $request->package_id)->orderBy('id', 'desc')->get();
        $alarm = [];
        foreach ($data as $key => $value) {
            $alarm[] = ['id'=>$value->id, 'date'=>$value->date, 'time'=>$value->time, 'status'=>$value->status];
        }
        $status = array(
                'status' => true,
                'message' => 'Saved Successfully!!',
                'alarm_data' => $alarm
        );
        return response()->json($status, 200);

    }

    public function addAlarm(Request $request)
    {
        $data = new AlarmNotification();
        $data->tour_package_id = $request->package_id;
        $data->manager_id = $request->manager_id;
        $data->manager_name = $request->manager_name;
        $data->date = $request->date;
        $data->time = $request->time;
        $data->message = $request->message;
        $result = $data->save();
        $alarm_data = AlarmNotification::where('tour_package_id', $request->package_id)->orderBy('id', 'desc')->get();
        foreach ($alarm_data as $key => $value) {
            $alarm[] = ['id'=>$value->id, 'date'=>$value->date, 'time'=>$value->time, 'status'=>$value->status];
        }
        if($result){
            $status = array(
                        'status' => true,
                        'message' => 'Saved Successfully!!',
                        'alarm_data' => $alarm
            );
        }
        else{
            $status = array(
                        'status' => false,
                        'message' => 'Something wrong!!',
            );
        }
        return response()->json($status, 200);
    }

    public function alarmStatus(Request $request)
    {
        $alarm = AlarmNotification::find($request->id);
        $alarm->status = $request->status;
        $result = $alarm->save();
        $alarm_data = AlarmNotification::where('tour_package_id', $request->package_id)->orderBy('id', 'desc')->get();
        $alarm_notification = [];
        foreach ($alarm_data as $key => $value) {
            $alarm_notification[] = ['id'=>$value->id, 'date'=>$value->date, 'time'=>$value->time, 'status'=>$value->status];
        }
        if($result){
            $status = array(
                        'status' => true,
                        'message' => 'Updated Successfully!!',
                        'alarm_data' => $alarm_notification
            );
        }
        else{
            $status = array(
                        'status' => false,
                        'message' => 'Something wrong!!',
            );
        }
        return response()->json($status, 200);
    }

}       
  