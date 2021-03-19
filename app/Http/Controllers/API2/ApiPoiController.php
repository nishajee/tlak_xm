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
class ApiPoiController extends Controller
{   
    public function PointOfInterest(Request $request){

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

            $optionalpoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                  ->join('locations','locations.id','=','location_point_of_interests.location_id')
                  ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                  ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as poiImage','point_of_interest_icons.name as poiType','point_of_interest_icons.icon_image as typeIcon')
                  ->where(['location_point_of_interests.tour_package_id' => $tour_package_id, 'location_point_of_interests.status' => 2])
                  ->get();

            if(count($optionalpoi)>0){
                  foreach($optionalpoi as $optpoi){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/';                    
                      $optional[] = ['poiId'=>$optpoi->poiId,'countryName'=>$optpoi->countryName,'locationName'=>$optpoi->locationName,'poiName'=>$optpoi->poiName,'poiDescription'=>$optpoi->poiDescription,'latitude'=>$optpoi->latitude,'longitude'=>$optpoi->longitude,'optionPoiImage'=>$avatar_url.$optpoi->poiImage,'optionTypeName'=>$optpoi->poiType,'optionTypeImage'=>url("images/uploads/poiicons/".$optpoi->typeIcon)];
                    }
                }
                else{
                  $optional = [];
                }
        $start_date = $tourPackage->startDate;
        $total_days = $tourPackage->total_days;
      
         $location = ItineraryLocation::join('itineraries','itineraries.id','=','itinerary_locations.itinerary_id')
                  ->join('locations','locations.id','=','itinerary_locations.location_id')
                  ->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','locations.id as locationId','locations.name as locacionName')
                  ->distinct()
                  ->where(['itinerary_locations.tour_package_id' => $tour_package_id])
                  ->get();
                  //dd($location) ;
            if(count($location)){
              foreach($location as $locPoi){ 
                $locPoi['poi'] =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      //->distinct()
                      ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as poiImage','point_of_interest_icons.name as poiType','point_of_interest_icons.icon_image as typeIcon')
                      ->distinct()
                      ->where(['location_point_of_interests.location_id' => $locPoi->locationId, 'location_point_of_interests.status' => 1, 'location_point_of_interests.tour_package_id' => $tour_package_id])
                      ->get();
              }
            }
            else{
              $location=[];
            }

              $status = array(
                  'error' => false,
                  'message' => 'Bingo! Success!!',
                  'typeImagePath' => url("images/uploads/poiicons"),
                  'poiImagePath' => 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/poi',
                  'traveler' => $traveler,
                  'optionalPoi' =>$optional,
                  'locationPoi' =>$location,
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


             

    public function detailPoi(Request $request,$id)
    {
        $token = $request->token; 
        //dd($id);
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
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $poi =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                       ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.address as poiAddress','point_of_interests.point_type as poiType','point_of_interests.latitude','point_of_interests.longitude','point_of_interest_icons.icon_image as typeIcon'
                      )
                      ->where('point_of_interests.id', $id)
                      ->first();
                $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                $avatar_url = $src.'poi/'; 
                $poi_images = ['poiId'=>$poi->poiId,'countryName'=>$poi->countryName,'locationName'=>$poi->locationName,'poiName'=>$poi->poiName,'poiDescriptionHeading'=>$poi->poiName.' '.'Detail','poiDescription'=>$poi->poiDescription,'poiAddress'=>$poi->poiAddress,'latitude'=>$poi->latitude,'longitude'=>$poi->longitude,'poiType'=>$poi->poiType,'typeImage'=>url("images/uploads/poiicons/".$poi->typeIcon)
                ];

                //Poi images
                $images =PointOfInterest::join('point_of_interest_images','point_of_interest_images.point_of_interest_id','=','point_of_interests.id')
                      ->select('point_of_interest_images.poi_image as poiImage')
                      ->where('point_of_interest_images.point_of_interest_id', $id)
                      ->get();
                $all_images = [];      
                foreach ($images as $value) {
                    $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                    $avatar_url = $src.'poi/';
                    array_push($all_images, $avatar_url.$value->poiImage);
                }
                $poi_images['poiImage'] = $all_images;
           
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'poi' => $poi_images, 
            ); 
          return response()->json($status, 200);
       
        }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        } 
      }      

      public function imagesPoi(Request $request,$id)
    {
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
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $poi =PointOfInterest::join('point_of_interest_images','point_of_interest_images.point_of_interest_id','=','point_of_interests.id')
                      ->select('point_of_interests.id as poiId','point_of_interests.name as poiName','point_of_interest_images.poi_image as poiImage')
                      ->where('point_of_interest_images.point_of_interest_id', $id)
                      ->get();

            if(count($poi)){
              foreach ($poi as $value) {
                $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                $avatar_url = $src.'poi/'; 
                $poi_images[] = ['poiId'=>$value->poiId,'poiName'=>$value->poiName,'poiImage'=>$avatar_url.$value->poiImage];
              }
            }
            else{
              $poi_images = [];
            }
            
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'poi' => $poi_images, 
            ); 
          return response()->json($status, 200);
       
        }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        } 
      }      

    public function PointOfInterestLatLong(Request $request)
    {
        $token = $request->token; 
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ],
            ['token.required' => 'Authentication filed!']
          );

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
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

            $locPoi =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      //->distinct()
                      ->select('point_of_interests.name as poiName','point_of_interests.latitude','point_of_interests.longitude')
                      ->distinct()
                      ->where('location_point_of_interests.tour_package_id',$tour_package_id)
                      ->get();
     

              $status = array(
                  'status' => true,
                  'message' => 'Bingo! Success!!',
                  'locationPoi' =>$locPoi,
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
              'message' => 'Opps! Authentication filed!',
              );
                
          }
        return response()->json($status, 200);   
    }

    public function topAttraction(Request $request){
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
        if($traveler){ 

          $tour_package_id=$traveler->tour_package_id;
          $tenane_id=$traveler->tenant_id;
          $tourPackage= TourPckage::where('id', $tour_package_id)
                        ->where(function($q) {
                              $q->where('status', 2);
                        })->first();
          if($tourPackage){

        $start_date = $tourPackage->startDate;
        $total_days = $tourPackage->total_days;
      
         $location = ItineraryLocation::join('itineraries','itineraries.id','=','itinerary_locations.itinerary_id')
                  ->join('locations','locations.id','=','itinerary_locations.location_id')
                  ->select('itineraries.id as itinearyId','itineraries.day_number as dayNumber','locations.id as locationId','locations.name as locacionName')
                  ->distinct()
                  ->where(['itinerary_locations.tour_package_id' => $tour_package_id])
                  ->get();
                  //dd($location) ;
            if(count($location)){
              foreach($location as $locPoi){ 
                $locPoi['poi'] =LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      //->distinct()
                      ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as poiImage','point_of_interest_icons.name as poiType','point_of_interest_icons.icon_image as typeIcon')
                      ->distinct()
                      ->where(['location_point_of_interests.location_id' => $locPoi->locationId, 'location_point_of_interests.status' => 1, 'location_point_of_interests.tour_package_id' => $tour_package_id])
                      ->get();
              }
            }
            else{
              $location=[];
            }

              $status = array(
                  'status' => true,
                  'message' => 'Bingo! Success!!',
                  'typeImagePath' => url("images/uploads/poiicons").'/',
                  'poiImagePath' => 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/poi/',
                  'traveler' => $traveler,
                  'locationPoi' =>$location,
                );  
            }

        else{
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

    public function PointOfInterestList(Request $request){
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
        if($traveler){ 

          $tour_package_id=$traveler->tour_package_id;
          $tenane_id=$traveler->tenant_id;
          $tourPackage= TourPckage::where('id', $tour_package_id)
                        ->where(function($q) {
                              $q->where('status', 2);
                        })->first();
          if($tourPackage){

                $locPoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                       ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      ->select('point_of_interests.id as poiId','point_of_interests.country_name as countryName','locations.name as locationName','point_of_interests.name as poiName','point_of_interests.description as poiDescription','point_of_interests.address as poiAddress','point_of_interests.point_type as poiType','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as bannerImage','point_of_interest_icons.icon_image as typeIcon'
                      )
                      ->where(['location_point_of_interests.tour_package_id' => $tour_package_id, 'location_point_of_interests.status' => 1])
                      ->get();
                $list = [];
                foreach ($locPoi as $value) {
                    $poiDescription = ($value->poiDescription == '' || $value->poiDescription == null || $value->poiDescription == 'null')?'':$value->poiDescription;

                    $poiType = ($value->poiType == '' || $value->poiType == null || $value->poiType == 'null')?'':$value->poiType;
                    
                    $location = ['poiId'=>$value->poiId,'countryName'=>$value->countryName,'locationName'=>$value->locationName,'poiName'=>$value->poiName,'poiDescription'=>$poiDescription,'poiAddress'=>$value->poiAddress,'poiType'=>$poiType,'latitude'=>$value->latitude,'longitude'=>$value->longitude,'typeIcon'=>$value->typeIcon];

                    $poiImage = (object) ['poiImage' => $value->bannerImage];

                    $images =PointOfInterest::join('point_of_interest_images','point_of_interest_images.point_of_interest_id','=','point_of_interests.id')
                      ->select('point_of_interest_images.poi_image as poiImage')
                      ->where('point_of_interest_images.point_of_interest_id', $value->poiId)
                      ->get();
                    if(count($images) > 0){
                        $location['poiImage'] = $images;
                    }
                    else{
                        $location['poiImage'] = [$poiImage];
                    }
                    array_push($list, $location);
                }
                      

              $status = array(
                  'status' => true,
                  'message' => 'Bingo! Success!!',
                  'typeImagePath' => url("images/uploads/poiicons").'/',
                  'poiImagePath' => 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/poi/',
                  'traveler' => $traveler,
                  'poiList' =>$list,
                );  
          }

          else{
            $status = array(
              'status' => false, 
              'message' => 'Opps! Invalid Response!!',
              );
               
          }

        }
        else{
          $status = array(
            'status' => false, 
            'message' => 'Opps! Invalid Response!!',
            );
              
        }
        return response()->json($status, 200);   
    }
      
}

