<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use Image;
use finfo;
use App\Itinerary;
use App\Location;
use App\LocationPointOfInterest;
use App\TourPckage;
use App\Tenant;
use App\ScheduledNotification;
use App\InstantNotification;
use App\LocationNotification;
use App\Traveler;
class NotificationController extends Controller
{
    public function index(Request $request, $id)
    {
        // dd($id);
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth()->User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $penandcomitem = TourPckage::completedAndPendingItem($id); 
            $itinerary = Itinerary::where('tour_package_id', $id)->get();
            //$locations = Location::where('tour_package_id', $id)->where('tenant_id', Auth()->user()->tenant_id)->get();
            $locations = LocationPointOfInterest::join('locations','locations.id','=','location_point_of_interests.location_id')
                        ->where('location_point_of_interests.tour_package_id', $id)
                        ->where('locations.tenant_id', Auth()->user()->tenant_id)
                        ->distinct()
                        ->select("location_point_of_interests.location_id as id","locations.name")
                        ->get();
            $tourpackage = TourPckage::where('id', $id)->first();
            $start_dates = TourPckage::where('id', $id)->value('start_date');
            //dd();
            $start_date = date("d-m-Y", strtotime($start_dates));
            //dd($start_date);
            $end_dates = TourPckage::where('id', $id)->value('end_date');
            $end_date = date("d-m-Y", strtotime($end_dates));
            $scheduled_notification = ScheduledNotification::where('tour_package_id', $id)->where('status', '1')->where('tenant_id', Auth()->user()->tenant_id)->get();
            $instant_notification = InstantNotification::where('tour_package_id', $id)->where('status', '1')->where('tenant_id', Auth()->user()->tenant_id)->get();
            $location_notification = LocationNotification::where('tour_package_id', $id)->where('status', '1')->where('tenant_id', Auth()->user()->tenant_id)->get();
            foreach ($location_notification as $key => $location_notify){
                $name = Location::where('id', $location_notify->poi_id)->value('name');
                $location_notify['location_name'] = $name;
            }
            $notificationImage = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/notifications/';
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();

            return view('notifications.index', compact('itinerary','locations','tourpackage', 'start_date','end_date','scheduled_notification','instant_notification','location_notification','tenant','penandcomitem','notificationImage','disableDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }

    public function addScheduledNotifications(Request $request, $id)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $validatedData = $request->validate([
           'notification_text' => 'required',
           'itineary_day' => 'required|max:255',
           'start_day' => 'required',
           'time' =>'required',
           'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]); 
        $startDay = $request->start_day;
        $start_day = date("Y-m-d", strtotime($startDay));
        $notification = new ScheduledNotification;
        $notification->tour_package_id = $route_id;
        $notification->text = $request->notification_text;
        $notification->day = $request->itineary_day;
        $notification->date = $start_day;
        $notification->time = $request->time;
        $user = auth()->user();
        $notification->tenant_id = $user->tenant_id;
        $notification->user_id = $user->id;

        if($request->file('image')){ 
            $file = $request->file('image');
            $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
            $image = Image::make($file);
            Storage::disk('s3')->put('notifications/'.$imageName, $image->stream(), 'public');
            $notification->image = $imageName;                
        }

        $notification->save();
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $request->session()->flash('message', 'Notification added successfully.');
        return redirect()->route('notification',$route_id);

    }

    public function deleteScheduleNotification(Request $request, $id)
    {
        $sch_notification = ScheduledNotification::find($id);
        $sch_notification->status = 0;
        $sch_notification->save();
        return response()->json([
           'success' => 'Scheduled notification deleted successfully!'
        ]);
    }

    public function addInstantNotifications(Request $request, $id)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
                     
        $notification = new InstantNotification;
        $notification->tour_package_id = $route_id;
        $notification->text = $request->ins_notification_text;
        $user = auth()->user();
        $notification->tenant_id = $user->tenant_id;
        $notification->user_id = $user->id;
        if($request->file('image')){ 
            $files = $request->file('image');
            $imageName = str_random(5).time().'.'.$files->getClientOriginalExtension();
            $img = Image::make($files);
            $img->stream();
            $storagePath = Storage::disk('s3')->put('notifications/'.$imageName, (string)$img, 'public');
            $notification->image = $imageName;                    
        }
        $notification->save();
        $last_id = $notification->id;
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $path = '';
        if($notification){
            $innotif = InstantNotification::where('id', $last_id)->first();
            if($innotif->image == '' || $innotif->image == null){
                $path = '';
            }
            else{
                $path = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/notifications/'.$innotif->image;
            }
        }
        $message = $request->ins_notification_text;
        

        // ios notification
        $iosDeviceId = Traveler::select('device_id')
                            ->where('tour_package_id', $route_id)
                            ->where('device_type', 'ios')
                            ->whereNotNull('device_id')
                            ->get()
                            ->unique('device_id'); 

        if(count($iosDeviceId)>0){
            foreach ($iosDeviceId as $value1) {
                $url = "https://fcm.googleapis.com/fcm/send";
                $serverKey ='AAAAGga8BYU:APA91bFg_bIkfgA-XcRaemMHgK-xFIpBwXR9ncB1kKIl7ubh8oZdSxQw9guzZoqU5SQAa08sGUtyOdRidxMSZjXFhqqHiVpyjOnROTGnVr-0_8HL93t446bpt2VJr1XiOsQBGV3TbBcA';
                $title = "Instant Notification";
                $body = $message;

                $notification = array('title'=> $title, 'body'=> $body,'mutable_content'=>false, 'sound'=>'Tri-tone');
                $data = array('image'=>$path);
                $fcm_options = array('image'=>$path);
                $apns = array('payload'=>array('aps'=>array('mutable-content'=>1)));

                $arrayToSend = array('to'=>$value1->device_id,'notification'=>$notification,'data'=>$data,'apns'=>$apns,'fcm_options'=>$fcm_options);
                $json = json_encode($arrayToSend);
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: key='. $serverKey;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                //Send the request
                $result = curl_exec($ch);
                curl_close( $ch );

            }
            
        }

        // android notification
        $travelersDeviceId = Traveler::select('device_id')
                        ->where('tour_package_id', $route_id)
                        ->where('device_type', 'android')
                        ->whereNotNull('device_id')
                        ->get()
                        ->unique('device_id');             

        if(count($travelersDeviceId)>0){
            foreach ($travelersDeviceId as $value) {

              // $deviceId[] = $value->device_id;

              $payload = array(
                     "data"=>array("body"=> $message, "title"=> "Instant notification","Tag"=>"Instant", "image" => $path),
                     "to"=>$value->device_id
                );
              
            $headers = array(
              'Authorization:key=AAAAGga8BYU:APA91bFg_bIkfgA-XcRaemMHgK-xFIpBwXR9ncB1kKIl7ubh8oZdSxQw9guzZoqU5SQAa08sGUtyOdRidxMSZjXFhqqHiVpyjOnROTGnVr-0_8HL93t446bpt2VJr1XiOsQBGV3TbBcA',
              'Content-Type: application/json'
            );
            
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $payload ) );
            $result = curl_exec($ch );
            curl_close( $ch );
            }

        }

        $request->session()->flash('message', 'Instant notification sent successfully.');
        return redirect()->route('notification',$route_id);

    }

    public function deleteInstantNotification(Request $request, $id)
    {
        $ins_notification = InstantNotification::find($id);
        $ins_notification->status = 0;
        $ins_notification->save();
        return response()->json([
           'success' => 'Instant notification deleted successfully!'
        ]);
    }
    public function addLocationNotifications(Request $request, $id)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $notification = new LocationNotification;
        $notification->tour_package_id = $route_id;
        $notification->day = $request->iti_day_number;
        $notification->text = $request->loc_notification_text;
        $notification->poi_id = $request->poi_name;
        $user = auth()->user();
        $notification->tenant_id = $user->tenant_id;
        $notification->user_id = $user->id;
        $notification->save();
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        
        $request->session()->flash('message', 'Location based notification added successfully.');
        return redirect()->route('notification',$route_id);

    }

    public function deleteLocationNotification(Request $request, $id)
    {
        $loc_notification = LocationNotification::find($id);
        $loc_notification->status = 0;
        $loc_notification->save();
        return response()->json([
           'success' => 'Location based notification deleted successfully!'
        ]);
    }
}
