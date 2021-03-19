<?php

namespace App\Http\Controllers\API;

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
class ApiPoiMapController extends Controller
{   
    public function PointOfInterestMap(Request $request){

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
        $traveler = Traveler::where('token',$token)->select('token','tour_package_id')->first();
        if($traveler){ 

          $tour_package_id=$traveler->tour_package_id;
          $tenane_id=$traveler->tenant_id;
          $tourPackage= TourPckage::where('id', $tour_package_id)
                        ->where(function($q) {
                              $q->where('status', 2);
                        })->first();
          if($tourPackage){
            //$pois = array();
            $location= DB::table('locations')->join('location_point_of_interests','location_point_of_interests.location_id','=','locations.id')
                ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                ->select('locations.id as locationId','locations.name as locacionName','point_of_interests.country_name as countryName')
                ->groupBy('locations.id','locations.name','point_of_interests.country_name')
                ->where('location_point_of_interests.tour_package_id','=',$tour_package_id)
                ->get()->toArray();       
           //dd($location);
            if(count($location)>0){
              $prr = array(); 
              $j=0;  
              foreach($location as $locPoi){
                $location[$j]->poi=array(); 
                $location[$j]->poi =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      //->distinct()
                      ->select('location_point_of_interests.status','point_of_interests.id as poiId','point_of_interests.name as poiName','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.address as poiAddress','point_of_interest_icons.icon_image as typeIcon')
                      ->distinct()
                      ->where('location_point_of_interests.location_id',$locPoi->locationId)
                      ->get();
                    $j++;  
              }
            }
              $status = array(
                  'error' => false,
                  'message' => 'Bingo! Success!!',
                  'typeImage' => url("images/uploads/poiicons"),
                  'traveler' => $traveler,
                  'poiMap' =>$location,
                );  
            }

        else{
          $status = array(
            'error' => true, 
            'message' => 'Opps! Invalid Response!!',
            );
               
        }

        }else{
          $status = array(
            'error' => true, 
            'message' => 'Opps! Invalid Response!!',
            );
              
        }
        return response()->json($status, 200);   
    }       
}

