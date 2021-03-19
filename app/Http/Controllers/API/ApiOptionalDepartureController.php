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
use App\UpcommingTourPackage;
use App\TourPckageUpcommingTourPackage;

class ApiOptionalDepartureController extends Controller
{   
    public function optionalDeparture(Request $request){

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
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
          $tour_package_id=$traveler->pkgId;
          $pkg = TourPckage::where('id', $tour_package_id)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                      ->select('id as pkgId','after_day as dayAfter')
                      ->first();
          if($pkg){
            $optionalDeparture = TourPckageUpcommingTourPackage::join('upcomming_tour_packages','upcomming_tour_packages.id','=','tour_pckage_upcomming_tour_packages.upcomming_tour_package_id')
              ->where('tour_pckage_upcomming_tour_packages.tour_pckage_id', $tour_package_id)
              ->where('tour_pckage_upcomming_tour_packages.status', 1)
              ->select('upcomming_tour_packages.id as optionalDepartureId','upcomming_tour_packages.pname as optionalDepartureName','upcomming_tour_packages.promo_content as promoContent','upcomming_tour_packages.contact_email as email','upcomming_tour_packages.contact_phone as phone','upcomming_tour_packages.description','upcomming_tour_packages.background_image as optionalDepartureImage')->get();
//dd($optionalDeparture);
              $optionalDepartures = count($optionalDeparture);
              if($optionalDepartures >= 1){
                  foreach($optionalDeparture as $otionalD){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                      $avatar_url = $src.'upcommeingpkg/';                    
                      $itinerary[] = ['optionalDepartureId'=>$otionalD->optionalDepartureId,'optionalDepartureName'=>$otionalD->optionalDepartureName,'promoContent'=>$otionalD->promoContent,'description'=>$otionalD->description,'email'=>$otionalD->email,'phone'=>$otionalD->phone,'optionalDepartureImage'=>$avatar_url.$otionalD->optionalDepartureImage];
                    }
                }
                else{
                  $itinerary = [];
                }

            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!',
            'traveler' => $traveler,
            'optionalDeparture' => $itinerary
            ); 
          return response()->json($status, 200);
        }
        else{
          $status = array(
           'error' => true,
           'message' => 'Opps! No record match!!'
           );
          return response()->json($status, 200);
         }
      }
      else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
         }
             
    }            
}       
