<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Traveler;
use App\Incluson;
use App\InclusionTourPckage;

class ApiInclusionController extends Controller
{   
    public function Inclusion(Request $request){

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
          
            $tour_package_id=$traveler->pkgId;
            $pkg = TourPckage::where('id', $tour_package_id)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                            ->first();
          if($pkg){
            $inclusions = InclusionTourPckage::where('tour_package_id', $tour_package_id)
                ->select('name as inclusionName')
                ->get();
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'hotels' => $inclusions
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid Response!!'
           );
          return response()->json($status, 200);
        }        
    }         
}       
