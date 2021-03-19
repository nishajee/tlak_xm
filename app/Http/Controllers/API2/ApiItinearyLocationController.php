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

class ApiItinearyLocationController extends Controller
{   
    public function itinearyLocation(Request $request)
    {
        $token = $request->token;
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
      if($traveler){
        $tour_package_id=$traveler->pkgId;
        $tourPackage= TourPckage::where('id', $tour_package_id)->where(function($q) { $q->where('status', 2); })->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.start_date as startDate','tour_pckages.end_date as endDate','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight','tour_pckages.start_time as startTime')->first();
        if($tourPackage){
        $start_date = $tourPackage->startDate;

        // $end_date = $tourPackage->endDate;
        $total_days = $tourPackage->totalDays;

        for ($x = 0; $x < $total_days; $x++) {
          $date = date('Y-m-d', strtotime($start_date. ' + '.$x.' days'));
          //dd($date);
          $current_date = date('Y-m-d');
          
          if($date == $current_date){
            $itineary = Itinerary::where('tour_package_id', $tour_package_id)->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.name as dayHeading','itineraries.description','itineraries.banner_image as itinearyImage')->get();
            if($itineary){
                  foreach($itineary as $iti){
                                          
                      $itinary[] = ['itinearyId'=>$iti->itinearyId,'dayNumber'=>$iti->dayNumber,'dayHeading'=>$iti->dayHeading,'description'=>$iti->description,'itinearyImage'=>url("images/uploads/itineary/".$iti->itinearyImage)];
                    }
                }
             $itinerary = Itinerary::where('day_number', $x+1)->where('tour_package_id', $tour_package_id)->select('day_number')->first();
              $id = Itinerary::where('day_number', $x+1)->where('tour_package_id', $tour_package_id)->value('id');
 
              $itipoi = ItineraryLocation::join('location_point_of_interests','location_point_of_interests.location_id','=','itinerary_locations.location_id')
                  ->join('locations','locations.id','=','location_point_of_interests.location_id')
                  ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->select('locations.name as locationName','point_of_interests.name as poiName','point_of_interests.banner_image as poiImage')
                  ->where( ['itinerary_locations.itinerary_id' => $id, 'location_point_of_interests.tour_package_id' => $tour_package_id])
                  ->get();
              if($itipoi){
                foreach($itipoi as $pois){
                                          
                      $locationpoi[] = ['locationName'=>$pois->locationName,'poiName'=>$pois->poiName,'poiImage'=>url("images/uploads/poibanner/".$pois->poiImage)];
                    }
                }
                $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!',
                'traveler' => $traveler,
                'tourPackage' => $tourPackage,
                'itinerary' => $itinary,
                'dayNumber' => $itinerary->day_number,
                'poi' => $locationpoi,


            ); 
          return response()->json($status, 200);

          }
          else{


              $poi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->select('point_of_interests.name as poiName','point_of_interests.banner_image as poiImage')
                  ->where('location_point_of_interests.tour_package_id', '=', $tour_package_id)
                  ->get();
              if($poi){
                foreach($poi as $lpoi){
                                          
                      $locpoi[] = ['poiName'=>$lpoi->poiName,'poiImage'=>url("images/uploads/poibanner/".$lpoi->poiImage)];
                    }
                }
                $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!',
                'traveler' => $traveler,
                'tourPackage' => $tourPackage,
                'poi' => $poi,

            ); 
            return response()->json($status, 200);
          }
        }
      }
      else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response'
           );
          return response()->json($status, 200);
        }
    } 
    else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response'
           );
          return response()->json($status, 200);
        }           
  }         
 
    public function detailItineary(Request $request,$id){
        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight')->first();
            if($tourPackage){
            $itineary = Itinerary::where('id', $id)->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.description','itineraries.inclusions','itineraries.exclusions','itineraries.banner_image as itinearyImage')->first();
            if($itineary){
              $itinerary = ['itinearyId'=>$itineary->itinearyId,'dayNumber'=>$itineary->dayNumber,'dayHeading'=>$itineary->dayHeading,'description'=>$itineary->description,'inclusions'=>$itineary->inclusions,'exclusions'=>$itineary->exclusions,'itinearyImage'=>url("images/uploads/itineary/".$itineary->itinearyImage)];
            }
            
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'tourPackage' => $tourPackage,
            'itinearies' => $itinerary, 
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Itinerary details not found!!'
           );
          return response()->json($status, 200);
        } 
        }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        } 
        
      }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }     
      }      
  }       
  