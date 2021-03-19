<?php

namespace App\Http\Controllers\API;

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

class ApiItinearyController extends Controller
{   
   public function itineary(Request $request)
   {
        $token = $request->token;
        //dd($token);
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
        $traveler = Traveler::where('token',$token)->select('tour_package_id as pkgId','id as travelerId','token','tenant_id as tenantId')->first();
        //$TR = count($traveler);
      if($traveler){

      //dd("hhh");
        $tour_package_id=$traveler->pkgId;
        $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2); 
                            })
                  ->select('tour_pckages.pname as pkgName','tour_pckages.agent_name as companyName','tour_pckages.start_date as startDate','tour_pckages.end_date as endDate','tour_pckages.total_days as totalDays','tour_pckages.total_nights as totalNight','tour_pckages.start_time as startTime')->first();
      if($tourPackage){
        $itineary = Itinerary::select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','itineraries.name as dayHeading','itineraries.description','itineraries.banner_image as itinearyImage')->where(['tour_package_id' =>$tour_package_id])->orderBy('day_number','ASC')->get();
        $itin = count($itineary);
            if($itin >= 1){
                  foreach($itineary as $iti){
                     $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'itineary/';

                      $itinary[] = ['itinearyId'=>$iti->itinearyId,'dayNumber'=>$iti->dayNumber,'dayHeading'=>$iti->dayHeading,'description'=>$iti->description,'itinearyImage'=>$avatar_url.$iti->itinearyImage];
                    }
                }
                else{
                  $itinary =[];
                }
        $optionalpoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->join('locations','locations.id','=','location_point_of_interests.location_id')
                  ->select('locations.name as locationName','point_of_interests.name as poiName','point_of_interests.banner_image as poiImage')
                  ->where('location_point_of_interests.tour_package_id', '=', $tour_package_id)
                        ->where(function($q) {
                                $q->where('location_point_of_interests.status', 2);
                            })
                  ->get();
            if(count($optionalpoi)>0){
                  foreach($optionalpoi as $optpoi){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/';                    
                      $optional[] = ['locationName'=>$optpoi->locationName,'poiName'=>$optpoi->poiName,'poiImage'=>$avatar_url.$optpoi->poiImage];
                    }
                }
                else{
                  $optional=[];
                }
        $start_date = $tourPackage->startDate;

        // $end_date = $tourPackage->endDate;
        // $total_days = $tourPackage->totalDays;
        // $final = [];

        // for ($x = 0; $x < $total_days; $x++) {

        //   $date = date('Y-m-d', strtotime($start_date. ' + '.$x.' days'));
        //   $current_date = date('Y-m-d');
        //   if($date == $current_date){
        //     $final = array();
        //       $itipoi = ItineraryLocation::join('location_point_of_interests','location_point_of_interests.location_id','=','itinerary_locations.location_id')
        //       ->join('itineraries','itineraries.id','=','itinerary_locations.itinerary_id')
        //           ->join('locations','locations.id','=','location_point_of_interests.location_id')
        //           ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
        //           ->distinct()
        //           ->select('itineraries.day_number as dayNumber','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.banner_image as poiImage')
        //           ->where( ['location_point_of_interests.tour_package_id' => $tour_package_id,'location_point_of_interests.status' => 1])
        //           ->get();
        //         if(count($itipoi) > 0){
        //           foreach($itipoi as $pois){
        //             $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
        //               $avatar_url = $src.'poi/';
        //               $locationpoi[] = ['dayNumber'=>$pois->dayNumber,'locationName'=>$pois->locationName,'poiName'=>$pois->poiName,'poiImage'=>$avatar_url.$pois->poiImage];
        //               //$pois['location'] = $locationpoi;
        //           }
        //         }
        //         else{
        //           $locationpoi = [];
        //         }
        //       $status = array(
        //         'error' => false,
        //         'message' => 'Bingo! Success!!',
        //         'traveler' => $traveler,
        //         'tourPackage' => $tourPackage,
        //         'itinearies' => $itinary,
        //         'optionalPoi' =>$optional,
        //         'poi' => $locationpoi,

        //       );

        //       $final = $status;
        //       break; 

        //   }
        //   else{
            $final = [];
            //print_r($final);
              $poi = ItineraryLocation::join('location_point_of_interests','location_point_of_interests.location_id','=','itinerary_locations.location_id')
                  ->join('itineraries','itineraries.id','=','itinerary_locations.itinerary_id')
                  ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->distinct()
                  ->select('point_of_interests.name as poiName','point_of_interests.banner_image as poiImage')
                  ->where( ['location_point_of_interests.tour_package_id' => $tour_package_id,'location_point_of_interests.status' => 1])
                  ->get();
                if($poi){
                  foreach($poi as $key => $lpoi){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/';
                        $lpoi['locationName'] = '';
                        $lpoi['poiImage'] = $avatar_url.$lpoi->poiImage;
                  }
                }
              $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!',
                'traveler' => $traveler,
                'tourPackage' => $tourPackage,
                'itinearies' => $itinary, 
                'optionalPoi' =>$optional,
                'poi' => $poi,

              );
              $final = $status;
          // }
          // }
          return response()->json($final, 200);
        }
        else{
          $final = array(
           'error' => true,
           'message' => 'Opps! Invalid response'
           );
         // return response()->json($status, 200);
        }        
    } 
      else{
          $final = array(
           'error' => true,
           'message' => 'Opps! Invalid response'
           );
          //return response()->json($status, 200);
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
            if($itineary){
              $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
              $avatar_url = $src.'itineary/';
              $itinerary = ['itinearyId'=>$itineary->itinearyId,'dayNumber'=>$itineary->dayNumber,'dayHeading'=>$itineary->dayHeading,'descriptionHeading'=>"Day Highlights",'description'=>$itineary->description,'inclusionsHeading'=>"Additional Info",'inclusions'=>$itineary->inclusions,'exclusionHeading'=>"Day Notes",'exclusions'=>$itineary->exclusions,'itinearyImage'=>$avatar_url.$itineary->itinearyImage];
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
  