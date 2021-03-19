<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PointOfInterest;
use App\PointOfInterestImage;
use App\Location;
use App\Traveler;
use App\Tenant;
use App\TourPckage;

class DataTransferController extends Controller
{
    public function transferPoi(Request $request)
    {
      $payload = array(
                     "data"=>array("body"=> "Please wake up at time.", "title"=> "Alarm Test","Tag"=>"Alarm"),
                     "to"=>"cxvXHijoRzidHbPOkV827S:APA91bGK6GBn8H_wzK-4zt02IZBERjgkmIu2-E1e72dqxudmqkEXZwBC960-gRyGhLaf-mjaUnihV3JXKHkPOUujUxQJfH50iR0cJdXXplkTBIjFEPMYc_52HT9Gjc6sRJTm8w517PMm"
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
      print_r($result);
      curl_close( $ch );
      die();

      $ids = ['eMzfZlUySMmcHmqqppz_1-:APA91bFEACPGP2Okd5p3C_eDKVrNFIxmwRtJpbr2yKPUUItchm7pQ_lQnB4rdinQ5FmnsD6h3bCRYA9JjQgNtmH8nvFzU31KvtkrbYIRbLx9MURe5y6WVAFu1hxtHRggLxhoa9DjBCVh'];
      Traveler::getLatLong($ids);
      $ids = ['chAyCaowRYKimlUVYIOp87:APA91bGMrWZCT_g0vFx3Rvac_hUcV8LEYhdaxmALO-d1YUwD3y3VX6J5f5VO1uaqDyzDmaS5jK8it24ES8--0WmPstvkjD9ADHW7z3hpoQHT5MAHfoVfAYnJ02KOXbLPO4obx-LywXna'];
      Traveler::getLatLong($ids); 
      $payload = array(
                     "data"=>array("body"=> "Test Notification", "title"=> "Tlak Test","Tag"=>"Tracking"),
                     "notification"=>array("body"=> "Welcome to TLAK APP","title"=> "Welcome Notification", "click_action"=> "YOUR_ACTION" ),
                     "to"=>"ddBfSu12TLiBGTcuCFXT9s:APA91bEF8ujkJ9ZofCaNSTtP-j6oo3nfRvYqrUMFBI7B0X0zx74gsby96vyRpWugchFzMPI8tJ71Y5K8whi7wboW8GvrKSjBJvG74PXZmaKoMCzTikBkvIkdrQFEb2cnaOFJNSXvze5p"
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
      print_r($result);
      curl_close( $ch );
      die();
      // $poi = PointOfInterest::where('tenant_id','HtNdtjNLRd1585898535')->where('country_name', 'like','%India%')->get();
      // foreach ($poi as $key => $value) {
      //     $poi_details = new PointOfInterest();
      //     $poi_details->name = $value->name;
      //     $poi_details->country_name = $value->country_name;
      //     $poi_details->latitude = $value->latitude;
      //     $poi_details->longitude = $value->longitude;
      //     $poi_details->description = $value->description;
      //     $poi_details->location_name = $value->location_name;
      //     $poi_details->locality = $value->locality;
      //     $poi_details->point_of_interest_icon_id = $value->point_of_interest_icon_id;
      //     $poi_details->address = $value->address;
      //     $poi_details->place_id = $value->place_id;
      //     $poi_details->hour_status = $value->hour_status;
      //     $poi_details->banner_image = $value->banner_image;
      //     $poi_details->address = $value->address;
      //     $poi_details->iso_2 = $value->iso_2;
      //     $poi_details->utc_offset = $value->utc_offset;
      //     $poi_details->user_id = '154';
      //     $poi_details->tenant_id = 'nJtAKgntFP1588851855';
      //     $poi_details->point_type = $value->point_type;
      //     $poi_details->status = $value->status;
      //     $poi_details->save();
      //     $last_id = $poi_details->id;

      //     $desti = Location::where('name',$value->location_name)
      //               ->where(function($q) {
      //                   $q->where('tenant_id', 'nJtAKgntFP1588851855');
      //        })->first();
      //     if($desti == null)
      //     {
      //       $processes = Location::create([
      //           'name' => $value->location_name,
      //           'country_name' => $value->country_name,
      //           'utc_offset' => $value->utc_offset,
      //           'tenant_id' => 'nJtAKgntFP1588851855',
      //           'user_id' => '154'
      //       ]); 
      //     }

      //     $images = PointOfInterestImage::where('point_of_interest_id',$value->id)->get();
      //     foreach ($images as $key => $img_t) {
      //         $img = new PointOfInterestImage;
      //         $img->point_of_interest_id=$last_id;
      //         $img->poi_image=$img_t->poi_image;
      //         $img->save();
      //     }
      // }

      print_r("Successfully Executed");
    }
}
