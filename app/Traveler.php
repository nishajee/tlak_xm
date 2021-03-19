<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traveler;
use App\FeedDetail;
use App\GetFeed;

class Traveler extends Model
{  
	protected $fillable = [
        'name','traveler_email','phone','birth','address','profile_picture','tour_package_id','tenant_id','token','type','device_id','device_type','tpassword','userid'];

    public static function welcomeNotification($device_id, $title, $message)
    {

        if($device_id){

            $payload = array(
                     "data"=>array("body"=> $message, "title"=> $title,"Tag"=>"Itineary"),
                     "notification"=>array("body"=> "Welcome to TLAK APP","title"=> "Welcome Notification", "click_action"=> "YOUR_ACTION" ),
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

    public static function likeNotification($device_id, $title, $traveler_name)
    {

        if($device_id){

            $payload = array(
                     "data"=>array("body"=> "Your feed liked by ".$traveler_name, "title"=> $title,"Tag"=>"feed"),
                     "to"=>$device_id
                     // "to"=>'cFrqoXgqS2ypEN5kTqLIHp:APA91bFPw1VZY_NkZMgjBXFibjQr2pSQsWS7upM3fDFGRRB_aVaFFS2znXahP7HMt_gyWq28MIde8iPHVoyYST6unCkh81QkM6PJLPZMrP46q9SLenYJPHnCqeHh59ss8H3SbVv3FnJ_'//for temprary
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

    public static function commentNotification($device_id, $title, $traveler_name,$feed_id)
    {

        if($device_id){

            $payload = array(
                     "data"=>array("body"=> $traveler_name." commented on your feed", "title"=> "1 new comment","Tag"=>"comment","feed_id"=>$feed_id),
                     "to"=>$device_id
                     // "to"=>'cFrqoXgqS2ypEN5kTqLIHp:APA91bFPw1VZY_NkZMgjBXFibjQr2pSQsWS7upM3fDFGRRB_aVaFFS2znXahP7HMt_gyWq28MIde8iPHVoyYST6unCkh81QkM6PJLPZMrP46q9SLenYJPHnCqeHh59ss8H3SbVv3FnJ_'//for temprary
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
    public static function feedUploadNotification($device_id, $title, $traveler_name,$feedType)
    {

        if($device_id){

            $payload = array(
                     "data"=>array("body"=> $traveler_name." posted 1 new ".$feedType, "title"=> "1 new feed","Tag"=>"feed"),
                     "to"=>$device_id
                     // "to"=>'cFrqoXgqS2ypEN5kTqLIHp:APA91bFPw1VZY_NkZMgjBXFibjQr2pSQsWS7upM3fDFGRRB_aVaFFS2znXahP7HMt_gyWq28MIde8iPHVoyYST6unCkh81QkM6PJLPZMrP46q9SLenYJPHnCqeHh59ss8H3SbVv3FnJ_'//for temprary
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

    public static function feedUploadNotificationIos($device_id, $title, $traveler_name,$feedType)
    {

        if($device_id){
            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey ='AAAAGga8BYU:APA91bFg_bIkfgA-XcRaemMHgK-xFIpBwXR9ncB1kKIl7ubh8oZdSxQw9guzZoqU5SQAa08sGUtyOdRidxMSZjXFhqqHiVpyjOnROTGnVr-0_8HL93t446bpt2VJr1XiOsQBGV3TbBcA';

            $notification = array('title'=> '1 new feed', 'body'=> $traveler_name." posted 1 new ".$feedType,'mutable_content'=>false, 'sound'=>'Tri-tone');
            $data = array('image'=>'');
            $fcm_options = array('image'=>'');
            $apns = array('payload'=>array('aps'=>array('mutable-content'=>1)));

            $arrayToSend = array('to'=>$device_id,'notification'=>$notification,'data'=>$data,'apns'=>$apns,'fcm_options'=>$fcm_options);
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
        else{
            print_r("Device Id Missing");
        }

    }

    public static function sosAndroidNotification($device_id, $title, $message)
    {

        if($device_id){
            $payload = array(
                     "data"=>array("body"=> $message, "title"=> $title,"Tag"=>"SOS"),
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

    public static function sosIosNotification($device_id, $title, $message)
    {

        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey ='AAAAGga8BYU:APA91bFg_bIkfgA-XcRaemMHgK-xFIpBwXR9ncB1kKIl7ubh8oZdSxQw9guzZoqU5SQAa08sGUtyOdRidxMSZjXFhqqHiVpyjOnROTGnVr-0_8HL93t446bpt2VJr1XiOsQBGV3TbBcA';

        $notification = array('title'=> $title, 'body'=> $message,'mutable_content'=>false, 'sound'=>'Tri-tone');
        $data = array('image'=>'');
        $fcm_options = array('image'=>'');
        $apns = array('payload'=>array('aps'=>array('mutable-content'=>1)));

        $arrayToSend = array('to'=>$device_id,'notification'=>$notification,'data'=>$data,'apns'=>$apns,'fcm_options'=>$fcm_options);
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

    public static function getCrownNo($traveler_id)
    {
        $total_likes = FeedDetail::where('traveler_id', $traveler_id)->sum('likes');
        $total_shares = FeedDetail::where('traveler_id', $traveler_id)->sum('shares');
        $total_contribution = GetFeed::where('traveler_id',$traveler_id)->count();
        $total_comment = FeedDetail::where('traveler_id', $traveler_id)->sum('comments');
        $contribution_crown = $total_contribution/.50;
        $share_crown = $total_shares/.15;
        $comment_crown = $total_comment/.25;
        $like_crown = $total_likes/.10;
        $total_crown = $contribution_crown + $share_crown + $comment_crown + $like_crown;
        if($total_crown < 3){
          $crown_no = 1;
        }
        elseif($total_crown < 7){
          $crown_no = 2;
        }
        elseif($total_crown < 14){
          $crown_no = 3;
        }
        elseif($total_crown < 25){
          $crown_no = 4;
        }
        elseif($total_crown < 35){
          $crown_no = 5;
        }
        else{
          $crown_no = 6;
        }

        return $crown_no;
    } 

    public static function getLatLong($device_id)
    {
        $payload = array(
                     "data"=>array("body"=> "Happy Journey", "title"=> "TLAK","Tag"=>"Tracking"),
                     "registration_ids"=>$device_id
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
