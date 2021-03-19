<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\DepartureGuide;
use App\DepartureManager;
use App\Communication;
use App\Placard;

class ApiSupportController extends Controller
{   
    public function contactSupport(Request $request){

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
        
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first();
            if($tourPackage){
              $depMana = DepartureManager::where('tour_package_id', $tour_package_id)->select('name as depManagerName','email as depManagerEmail','phone as depManagerPhone')->get();
              if(count($depMana) >= 1){
                foreach ($depMana as $value) {
                  if($value->depManagerEmail == '' || $value->depManagerEmail == null){
                    $depManager[] = ['depManagerName'=>$value->depManagerName,'depManagerPhone'=>$value->depManagerPhone,'depManagerEmail'=>''];
                  }
                  else{
                    $depManager[] = ['depManagerName'=>$value->depManagerName,'depManagerPhone'=>$value->depManagerPhone,'depManagerEmail'=>$value->depManagerEmail];
                  }
                }
              }
              else{
                $depManager = [];
              }
              $depGuide = DepartureGuide::where('tour_package_id', $tour_package_id)->select('name as depGuideName','location as depGuideLocation','phone as depGuidePhone')->get();
              if(count($depGuide) >= 1){
                foreach ($depGuide as $value) {
                  if($value->depGuidePhone == '' || $value->depGuidePhone == null){
                    $depGuides[] = ['depGuideName'=>$value->depGuideName,'depGuideLocation'=>$value->depGuideLocation,'depGuidePhone'=>''];
                  }
                  elseif($value->depGuideLocation == '' || $value->depGuideLocation == null){
                    $depGuides[] = ['depGuideName'=>$value->depGuideName,'depGuideLocation'=>'' ,'depGuidePhone'=>$value->depGuidePhone];
                  }
                  else{
                    $depGuides[] = ['depGuideName'=>$value->depGuideName,'depGuideLocation'=>$value->depGuideLocation ,'depGuidePhone'=>$value->depGuidePhone];
                  }
                }
              }
              else{
                $depGuides = [];
              }
              $comanyContact = Communication::where('tour_package_id', $tour_package_id)->select('name as companyPersonName','email as companyPersonEmail','phone as companyPersonPhone')->get();
              if(count($comanyContact) >= 1){
                foreach ($comanyContact as $value) {
                  if($value->companyPersonEmail == '' || $value->companyPersonEmail == null){
                    $comanyContacts[] = ['companyPersonName'=>$value->companyPersonName,'companyPersonPhone'=>$value->companyPersonPhone,'companyPersonEmail'=>''];
                  }
                  else{
                    $comanyContacts[] = ['companyPersonName'=>$value->companyPersonName,'companyPersonPhone'=>$value->companyPersonPhone,'companyPersonEmail'=>$value->companyPersonEmail];
                  }
                }
              }
              else{
                $comanyContacts = [];
              }

              $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!', 
                'traveler' => $traveler,
                'departureManager' => $depMana,
                'departureGuide' => $depGuide,
                'comanyContact' => $comanyContact
                //'placard' => $placard
              ); 
                return response()->json($status, 200);
            }
            else{
              $status = array(
               'error' => true,
               'message' => 'Opps! No itinerary found!!'
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

    public function Placard(Request $request){

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
        
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.token','travelers.tenant_id as tenantId')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first();
            if($tourPackage){
              $plaCards = Placard::where('tour_package_id', $tour_package_id)->select('placard as placardName','placard_detail as placardDetail')->first();
             if($plaCards){
                if($plaCards->placardDetail == null || $plaCards->placardDetail == ''){
                  $plaCard = ['placardName' => $plaCards->placardName, 'placardDetail' => ''];
                }
             else{
              $plaCard = ['placardName' => $plaCards->placardName, 'placardDetail' => $plaCards->placardDetail];
             }
           }
           else{
              $plaCard = "";
           }
              $status = array(
                      'error' => false,
                      'message' => 'Bingo! Success!!', 
                      'traveler' => $traveler,
                      'placard' => $plaCard
              ); 
              return response()->json($status, 200);
          
          }
          else{
            $status = array(
             'error' => true,
             'message' => 'Opps! Invalid Response!!'
             );
            return response()->json($status, 200);
          }        
        }  
        else{
                $status = array(
                 'error' => true,
                 'message' => 'Opps! Invalid Response!!'
                 );
                return response()->json($status, 200);
              } 
    } 
}       
  