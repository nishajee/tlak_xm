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

class ApiOptionalPoiController extends Controller
{   
   public function optionalPoi(Request $request)
   {
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
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
          if($traveler){
            $tour_package_id = $traveler->pkgId;
            $optionalpoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                      ->select('locations.name as locationName','point_of_interests.id as poiId','point_of_interests.name as poiName','point_of_interests.address as poiAddress','point_of_interests.latitude','point_of_interests.longitude','point_of_interests.banner_image as poiImage','point_of_interest_icons.icon_image as typeIcon','point_of_interest_icons.name as typeName')
                      ->where('location_point_of_interests.tour_package_id', '=', $tour_package_id)
                            ->where(function($q) {
                                    $q->where('location_point_of_interests.status', 2);
                                })
                      ->get();
                if(count($optionalpoi)>0){
                  foreach($optionalpoi as $optpoi){
                    $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'poi/';
                    $optional[] = ['locationName'=>$optpoi->locationName,'poiId'=>$optpoi->poiId,'poiName'=>$optpoi->poiName,'latitude'=>$optpoi->latitude,'longitude'=>$optpoi->longitude,'poiAddress'=>$optpoi->poiAddress,'poiImage'=>$avatar_url.$optpoi->poiImage,'typeName'=>$optpoi->typeName,'typeIcon'=>url("images/uploads/poiicons/".$optpoi->typeIcon)];
                  }
                }
                else{
                  $optional=[];
                }
                if(!empty($optional)){
                  $status = array(
                      'status' => true,
                      'message' => 'Bingo! Success!!',
                      'traveler' =>$traveler,
                      'toptionalPoi' => $optional,
                  );
                }
                else{
                  $status = array(
                      'status' => false,
                      'message' => 'No Data Found'
                  );
                }
            }
            else{
                $status = array(
                 'status' => false,
                 'message' => 'Opps! Invalid response'
                 );
            }          
          return response()->json($status, 200);
      }       
}       
  