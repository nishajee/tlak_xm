<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\Itinerary;
use App\PointOfInterest;
use App\LocationPointOfInterest;
use App\Location;
use App\ItineraryLocation;
use App\PointOfInterestImage;

class ApiItinearyController extends Controller
{   
   public function itineary(Request $request)
   {
        $token = $request->token;
        // dd($token);
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

        $traveler = Traveler::where('token',$token)->select('tour_package_id as pkgId','id as travelerId','token','tenant_id as tenantId')->first();

      if($traveler){
        $tour_package_id=$traveler->pkgId;
        $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2); 
                            })
                  ->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.start_date as startDate','tour_pckages.end_date as endDate','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight','tour_pckages.start_time as startTime')->first();
        $start_date =  $tourPackage->startDate;

        if($tourPackage){
          $itineary = Itinerary::
                    select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.description','itineraries.banner_image as itinearyImage')
                    ->where(['itineraries.tour_package_id' =>$tour_package_id])
                    ->orderBy('itineraries.day_number','ASC')
                    ->get();
          $itin = count($itineary);
          if($itin >= 1){
            foreach($itineary as $iti){
              $location_ids = ItineraryLocation::select('location_id')->where('itinerary_id', $iti->itinearyId)->get();
              $latitude = 0;
              $longitude = 0;
              $count = 0;
              $location_name = '';
              $i = 0;

              foreach ($location_ids as $ids) {
                  $loc_name = Location::where('id', $ids->location_id)->first();
                  if($i != 0){
                      $lo_name = '-'.$loc_name->name;
                  }
                  else{
                      $lo_name = $loc_name->name;
                  }
                  $locati_name = $lo_name;
                  $location_name .= $locati_name;
                  $poi_id_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                                                ->select('point_of_interests.latitude as poiLatitude','point_of_interests.longitude as poiLongitude')
                                                ->where('location_point_of_interests.location_id', $ids->location_id)
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
                  $latlong[] = ['locationId'=>$ids->location_id,'name'=>$loc_name->name,'latitude'=>$location_lat,'longitude'=>$location_long]; 
                  $i++;
            }

            $day_number = $iti->dayNumber;
            $day = $day_number-1;
            $timestamp = date('Y-m-d', strtotime($start_date. ' + '.$day.' days'));
            $timestamp = strtotime($timestamp);
            $actual_date = date("M d, Y", $timestamp);
            $correct_foramt = ' ('.$actual_date.')';

            $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                  $avatar_url = $src.'itineary/';

            $itinary[] = ['itinearyId'=>$iti->itinearyId,'dayNumber'=>$iti->dayNumber.$correct_foramt,'locationName'=>$location_name,'description'=>$iti->description,'itinearyImage'=>$avatar_url.$iti->itinearyImage,'location'=>$latlong];
            $latlong = array();
          }
        }
        else{
            $itinary =[];
        }

          // $location = ItineraryLocation::join('itineraries','itineraries.id','=','itinerary_locations.itinerary_id')
          //         ->join('locations','locations.id','=','itinerary_locations.location_id')
          //         ->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','locations.id as locationId','locations.name as locacionName')
          //         ->distinct()
          //         ->where(['itinerary_locations.tour_package_id' => $tour_package_id])
          //         ->get();
          //         //dd($location) ;
          //   if(count($location)){
          //     foreach($location as $locPoi){ 
          //       $locPoi['poi'] =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
          //             ->join('locations','locations.id','=','location_point_of_interests.location_id')
          //             ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
          //             //->distinct()
          //             ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as poiImage','point_of_interest_icons.name as poiType','point_of_interest_icons.icon_image as typeIcon')
          //             ->distinct()
          //             ->where('location_point_of_interests.location_id',$locPoi->locationId)
          //             ->where(function($q) {
          //                       $q->where('location_point_of_interests.status','=',1);
          //                   })

          //             ->get();
          //     }
          //   }
          //   else{
          //     $location=[];
          //   } 
   
          $final = [];
          $status = array(
              'status' => true,
              'message' => 'Bingo! Success!!',
              'traveler' => $traveler,
              'tourPackage' => $tourPackage,
              'itinearies' => $itinary,
              // 'locationPoi' =>$location,

          );
          $final = $status;
          return response()->json($final, 200);
        }
        else{
          $final = array(
           'status' => false,
           'message' => 'Opps! Invalid response'
          );
        }        
      } 
      else{
          $final = array(
           'status' => false,
           'message' => 'Opps! Invalid token'
          );
      }          
      return response()->json($final, 200);
  }   
 
    public function detailItineary(Request $request,$id){
        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight')->first();
            if($tourPackage){
              $itineary = Itinerary::where('id', $id)->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.name as dayHeading','itineraries.description','itineraries.inclusions','itineraries.exclusions','itineraries.banner_image as itinearyImage')->first();
              $location_ids = ItineraryLocation::select('location_id')->where('itinerary_id', $id)->get();
              foreach ($location_ids as $ids) {
                $poi_id_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                                            ->select('point_of_interests.latitude as poiLatitude','point_of_interests.longitude as poiLongitude')
                                            ->where('location_point_of_interests.location_id', $ids->location_id)
                                            ->where('location_point_of_interests.tour_package_id', $tour_package_id)
                                            ->get();
                $poi_latitude = 0;
                $poi_longitude = 0;                            
                $poi_count = 0;                                                       
                foreach ($poi_id_list as $key => $list) {
                    $poi_latitude += $list->poiLatitude;
                    $poi_longitude += $list->poiLongitude;
                    $poi_count += 1;
                }
                $location_lat = $poi_latitude/$poi_count;
                $location_long = $poi_longitude/$poi_count;
                $loc_name = Location::where('id', $ids->location_id)->select('name')->first();
                $location_obj[] = ['locationName'=>$loc_name->name,'latitude'=>$location_lat, 'longitude'=>$location_long];                
            }

            if($itineary){
              $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
              $avatar_url = $src.'itineary/';
              $itinerary = ['itinearyId'=>$itineary->itinearyId,'dayNumber'=>$itineary->dayNumber,'dayHeading'=>$itineary->dayHeading,'descriptionHeading'=>"Day Highlights",'description'=>$itineary->description,'inclusionsHeading'=>"Additional Info",'inclusions'=>$itineary->inclusions,'exclusionHeading'=>"Day Notes",'exclusions'=>$itineary->exclusions,'itinearyImage'=>$avatar_url.$itineary->itinearyImage,'locationObj'=>$location_obj];
            }
            
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'tourPackage' => $tourPackage,
            'itinearies' => $itinerary, 
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

    public function itinearyList(Request $request){
        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight','tour_pckages.start_date as startDate')->first();
            $start_date =  $tourPackage->startDate;

            if($tourPackage){
              $itineary = Itinerary::where('tour_package_id', $tour_package_id)->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.name as dayHeading','itineraries.description','itineraries.inclusions','itineraries.exclusions','itineraries.banner_image as itinearyImage')->get();
            
            foreach ($itineary as $value) {
              $inclusion = ($value->inclusions == '' || $value->inclusions == null)?'':$value->inclusions;
              $location_ids = ItineraryLocation::select('location_id')->where('itinerary_id', $value->itinearyId)->get();
              $latitude = 0;
              $longitude = 0;
              $count = 0;
              foreach ($location_ids as $ids) {
                  $poi_id_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                                              ->select('point_of_interests.latitude as poiLatitude','point_of_interests.longitude as poiLongitude')
                                              ->where('location_point_of_interests.location_id', $ids->location_id)
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
                  $loc_name = Location::where('id', $ids->location_id)->select('name')->first();
                  $location_obj[] = ['locationId'=>$ids->location_id,'name'=>$loc_name->name,'latitude'=>$location_lat, 'longitude'=>$location_long];                
              }
              $avg_lat = $latitude/$count;
              $avg_long = $longitude/$count;

              $day_number = $value->dayNumber;
              $day = $day_number-1;
              $timestamp = date('Y-m-d', strtotime($start_date. ' + '.$day.' days'));
              $timestamp = strtotime($timestamp);
              $actual_date = date("M d, Y", $timestamp);
              $correct_foramt = ' ('.$actual_date.')';

              $jsonfile = file_get_contents("https://api.darksky.net/forecast/eae5ba4bd67736276fb8ae9d98c42f68/" . $avg_lat . "," . $avg_long);
              $jsondata = json_decode($jsonfile);
              $i = 0;
              foreach ($jsondata->daily->data as $weathers)
              {
                  if($i == 1){
                    $fahrenheitTemMin = round($weathers->temperatureLow,1);
                    $fahrenheitTemMax = round($weathers->temperatureHigh,1);
                    $celsiusTempMin = round(($fahrenheitTemMin - 32) * 5 / 9,1);
                    $celsiusTempMax = round(($fahrenheitTemMax - 32) * 5 / 9,1);
                    $currentTemp = ($celsiusTempMin + $celsiusTempMax) / 2;
                    $icons = $weathers->icon;
                    $icon = url("images/uploads/weather/" . $icons . '.png');
                  }
                  $i++;
              }
              
              if ($value->exclusions == '' || $value->exclusions == null) {
                  $exclusions = '';
              }
              else{
                  $exclusions = $value->exclusions;
              }

              $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
              $avatar_url = $src.'itineary/';
              $itinerary[] = ['itinearyId'=>$value->itinearyId,'dayNumber'=>$value->dayNumber.$correct_foramt,'dayHeading'=>$value->dayHeading,'descriptionHeading'=>"Day Highlights",'description'=>$value->description,'inclusionsHeading'=>"Additional Info",'inclusions'=>$inclusion,'exclusionHeading'=>"Day Notes",'exclusions'=>$exclusions,'itinearyImage'=>$avatar_url.$value->itinearyImage,'currentTemp'=>$currentTemp,'icon' => $icon,'location'=>$location_obj];
              unset($location_obj);
            }
            
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'tourPackage' => $tourPackage,
            'itinearies' => $itinerary, 
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
           'message' => 'Opps! Invalid token!!'
           );
          return response()->json($status, 200);
        } 
        
      }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid token!!'
           );
          return response()->json($status, 200);
        }     
      }

  public function onGoingtrip(Request $request)
  {
      $token = $request->token; 
      if($token)
      { 
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler)
          {
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight','tour_pckages.start_date as startDate')->first();
            if($tourPackage)
            {
            $itineary = Itinerary::where('tour_package_id', $tour_package_id)->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.name as dayHeading','itineraries.description','itineraries.inclusions','itineraries.exclusions','itineraries.banner_image as itinearyImage')->get();
            
            foreach ($itineary as $value)
            {
              $location_ids = ItineraryLocation::select('location_id')->where('itinerary_id', $value->itinearyId)->get();
              $latitude = 0;
              $longitude = 0;
              $count = 0;
              foreach ($location_ids as $ids)
              {
                  $loc_name = Location::where('id', $ids->location_id)->select('name')->first();
                  $poi_id = LocationPointOfInterest::where('location_id', $ids->location_id)->where('tour_package_id', $tour_package_id)->select('point_of_interest_id')->get();
                  $place_images = [];
                  foreach ($poi_id as $key => $pois) {
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/'; 
                      $poi = PointOfInterestImage::where('point_of_interest_id', $pois->point_of_interest_id)->pluck('poi_image');       
                      foreach ($poi as $value1) {
                        $place_images[] = ['image'=>$avatar_url.$value1];
                      }
                      $banner_image = PointOfInterest::select('banner_image')->where('id', $pois->point_of_interest_id)->first();
                  }

                  $location_obj[] = ['locationName'=>$loc_name->name,'itinearyId'=>$value->itinearyId,'dayNumber'=>$value->dayNumber,'dayHeading'=>$value->dayHeading,'locationImage'=>$avatar_url.$banner_image->banner_image];                
              }

              $itinerary[] = ['description'=>$value->description,'locationObj'=>$location_obj];
              unset($location_obj);
            }
            
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'tourPackage' => $tourPackage,
            'onGoingTrip' => $itinerary, 
            ); 
          return response()->json($status, 200);
        }
        else
        {
          $status = array(
           'status' => false,
           'message' => 'Opps! Itinerary details not found!!'
           );
          return response()->json($status, 200);
        } 
        }
        else
        {
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        } 
        
      }
      else
      {
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
      }     
  }         
}       
  