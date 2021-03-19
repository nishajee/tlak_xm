<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use DB;
use App\Traveler;
use App\Itinerary;
use App\PointOfInterest;
use App\Location;
use App\LocationPointOfInterest;
use App\ItineraryLocation;
use App\PointOfInterestImage;
class ApiPlaceToVisitController extends Controller
{  
    public function placeToVisit(Request $request)
    {
        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;

            $tourPackage = TourPckage::where('id', $tour_package_id)->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight')->first();

            if($tourPackage){
              $all_location_ids = DB::table("location_point_of_interests")
				            ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
				            ->join('locations','locations.id','=','location_point_of_interests.location_id')
				            ->where("location_point_of_interests.tour_package_id",$tour_package_id)
				            // ->select("location_point_of_interests.location_id as location_id")
				            ->pluck('location_point_of_interests.location_id');
              $used_location_id = ItineraryLocation::where('tour_package_id' , $tour_package_id)->pluck('location_id')->toArray();      
              $array_loc = array_unique(json_decode(json_encode($all_location_ids)));
              $used_array_loc = array_unique(json_decode(json_encode($used_location_id)));
              $latitude = 0;
              $longitude = 0;
              $count = 0;
              foreach ($array_loc as  $ids) {
                if (in_array($ids, $used_array_loc)){ 
                  $location_name = Location::where('id', $ids)->first();
                  $total_days = ItineraryLocation::where('location_id', $ids)->where('tour_package_id', $tour_package_id)->count();
                  $poi_id = LocationPointOfInterest::where('location_id', $ids)->where('tour_package_id', $tour_package_id)->select('point_of_interest_id')->get();
                  $place_images = [];
                  foreach ($poi_id as $key => $value) {
                  	  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/'; 
                      $poi = PointOfInterestImage::where('point_of_interest_id', $value->point_of_interest_id)->pluck('poi_image');       
                      foreach ($poi as $value1) {
                        $place_images[] = ['image'=>$avatar_url.$value1];
                      }
                      $banner_image = PointOfInterest::select('banner_image')->where('id', $value->point_of_interest_id)->first();
                      if($banner_image){
                        $place_images[] = ['image'=>$avatar_url.$banner_image->banner_image]; 
                  	  } 
                  }
                  $final_images = [];
                  $count_img = count($place_images);
                  if($count_img == 1){
                      // $rand_images = array_rand( $place_images, 1 );
                      array_push($final_images, $place_images[0]);
                  }
                  elseif($count_img == 2){
                      $rand_images = array_rand( $place_images, 2 );
                      array_push($final_images, $place_images[$rand_images[0]]);
                      array_push($final_images, $place_images[$rand_images[1]]);
                  }  
                  elseif($count_img == 3){
                      $rand_images = array_rand( $place_images, 3 );
                      array_push($final_images, $place_images[$rand_images[0]]);
                      array_push($final_images, $place_images[$rand_images[1]]);
                      array_push($final_images, $place_images[$rand_images[2]]);
                  }  
                  else{
                      $rand_images = array_rand( $place_images, 4 );
                      array_push($final_images, $place_images[$rand_images[0]]);
                      array_push($final_images, $place_images[$rand_images[1]]);
                      array_push($final_images, $place_images[$rand_images[2]]);
                      array_push($final_images, $place_images[$rand_images[3]]);
                  }


                  // Average lat long
                  $poi_id_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                                                  ->select('point_of_interests.latitude as poiLatitude','point_of_interests.longitude as poiLongitude')
                                                  ->where('location_point_of_interests.location_id', $ids)
                                                  ->where('location_point_of_interests.tour_package_id', $tour_package_id)
                                                  ->get();
                    $poi_latitude = 0;
                    $poi_longitude = 0;                            
                    $poi_count = 0;                                                       
                    foreach ($poi_id_list as $key => $list) {
                        $latitude += $list->poiLatitude;
                        $longitude += $list->poiLongitude;
                        $count += 1;
                        $poi_latitude += $list->poiLatitude;
                        $poi_longitude += $list->poiLongitude;
                        $poi_count += 1;
                    }
                    $location_lat = $poi_latitude/$poi_count;
                    $location_long = $poi_longitude/$poi_count;


                  $destinations[] = ['locationId'=>$ids,'locationName'=>$location_name->name,'latitude'=>$location_lat, 'longitude'=>$location_long,'description'=>'', 'countryName'=>$location_name->country_name,'totalDays'=>$total_days,'totalNights'=>$total_days-1,'placesImages'=>$final_images];
                }

              }
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'tourPackage' => $tourPackage,
            'places' => $destinations, 
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Itinerary details not found!!'
           );
          return response()->json($status, 200);
        } 
        }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        } 
        
      }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }   
    }
      
}

