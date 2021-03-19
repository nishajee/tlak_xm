<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    public static function sendScheduleNotification($device_id, $message, $title, $path)
    {
    	if($device_id){

            $payload = array(

                     "data"=>array("body"=> $message, "title"=> $title,"Tag"=>"Scheduled Notification", "image" => $path),
                     "to"=>$device_id
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
        else{
        	print_r("Device Id Missing");
        }
    }

    public static function sendScheduleNotificationIos($iosId, $message, $title, $path)
    {

        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey ='AAAAGga8BYU:APA91bFg_bIkfgA-XcRaemMHgK-xFIpBwXR9ncB1kKIl7ubh8oZdSxQw9guzZoqU5SQAa08sGUtyOdRidxMSZjXFhqqHiVpyjOnROTGnVr-0_8HL93t446bpt2VJr1XiOsQBGV3TbBcA';

        $notification = array('title'=> $title, 'body'=> $message,'mutable_content'=>false, 'sound'=>'Tri-tone');
        $data = array('image'=>$path);
        $fcm_options = array('image'=>$path);
        $apns = array('payload'=>array('aps'=>array('mutable-content'=>1)));

        $arrayToSend = array('to'=>$iosId,'notification'=>$notification,'data'=>$data,'apns'=>$apns,'fcm_options'=>$fcm_options);
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
