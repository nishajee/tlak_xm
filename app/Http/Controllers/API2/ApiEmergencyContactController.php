<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Country;
use DB;
use App\Traveler;
use App\EmergencyContact;
use App\PointOfInterest;
use App\Location;
use App\LocationPointOfInterest;
use App\ItineraryLocation;
class ApiEmergencyContactController extends Controller
{   
    public function emergencyContact(Request $request){

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
        $traveler = Traveler::where('token',$token)->select('token','tour_package_id')->first();
        //$T = count($traveler);
        if($traveler){ 

          $tour_package_id=$traveler->tour_package_id;
          $tenane_id=$traveler->tenant_id;
          $tourPackage= TourPckage::where('id', $tour_package_id)
                        ->where(function($q) {
                              $q->where('status', 2);
                        })->first();
          if($tourPackage){

            $emergencyContacts = ItineraryLocation::join('location_point_of_interests','location_point_of_interests.location_id','=','itinerary_locations.location_id')
                  ->join('locations','locations.id','=','location_point_of_interests.location_id')
                  ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->distinct()
                  ->select('point_of_interests.iso_2 as iso')
                  ->where( 'location_point_of_interests.tour_package_id', $tour_package_id)
                  ->get();
              
              if(count($emergencyContacts) >0){
                foreach ($emergencyContacts as $value) {
                  $emergency[] = EmergencyContact::where('iso_2',$value->iso)
                        ->select('country_name as countryName','police as Police','ambulance as Ambulance','fire as Fire','others as Others')
                        ->first()->toArray();
                }

                $end_data = [];
                foreach ($emergency as $key => $value) {
                    $country_name = [
                          "countryName" => $value['countryName'],
                        ];
                    $dataaa = [];
                    $data = [];
                    foreach ($value as $key => $value1) {
                        if($key == 'countryName'){
                        }
                        else{
                          $data = [
                            "name" => $key,
                            "value" => $value1,
                          ];

                        array_push($dataaa, $data);
                        }

                    }
                    $emergency_array = [
                      "countryName" => $value['countryName'],
                      "emergencyContacts" => $dataaa
                    ];

                    array_push($end_data, $emergency_array);
                }
              }
              else{
                $end_data = [];
              }
              $status = array(
                  'status' => true,
                  'message' => 'Bingo! Success!!',
                  'traveler' => $traveler,
                  'country' =>$end_data
                );  
            }else{
              $status = array(
                'status' => false, 
                'message' => 'Opps! Invalid Response!!',
                );        
            }

        }else{
          $status = array(
            'status' => false, 
            'message' => 'Opps! Invalid Response!!',
            );    
        }
        return response()->json($status, 200);   
    }     
}

