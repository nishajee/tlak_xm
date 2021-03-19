<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Traveler;
use App\Incluson;
use App\InclusionTourPckage;
use App\ExclusionTourPackage;

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
                'status' => false,
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
            foreach ($inclusions as $key => $inc) {
                if($inc->inclusionName == 'Visa')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/passport.png';
                else if($inc->inclusionName == 'Accommodation')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/hotel.png';
                else if($inc->inclusionName == 'Air Ticket')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/flight.png';
                else if($inc->inclusionName == 'Breakfast')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/coffee.png';
                else if($inc->inclusionName == 'Lunch')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/food.png';
                else if($inc->inclusionName == 'Dinner')
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/dinner.png';
                else{
                    $inc->image = 'https://account.tlakapp.com/media/itinerary/inclusion.png';
                }
            }
            if(count($inclusions) > 0)
            {
                $status = array(
                    'status' => true,
                    'message' => 'Bingo! Success!!', 
                    'traveler' => $traveler,
                    'inclusions' => $inclusions
                ); 
                return response()->json($status, 200);
            }
            else{
                $status = array(
                    'status' => false,
                    'message' => 'No Data Found', 
                ); 
                return response()->json($status, 200);
            }
        }
        else{
            $status = array(
                'status' => false,
                'message' => 'Opps! Invalid Response!!'
            );
            return response()->json($status, 200);
        }        
    }

    public function Exclusion(Request $request)
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
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          
        $tour_package_id=$traveler->pkgId;
        $pkg = TourPckage::where('id', $tour_package_id)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                            ->first();
        if($pkg){
            $exclusions = ExclusionTourPackage::where('tour_package_id', $tour_package_id)
                ->select('name as exclusionName','exclusion as description')
                ->get();
            if(count($exclusions) > 0 )
            {   
                foreach ($exclusions as $key => $value) {
                    $name = ($value->exclusionName == '' || $value->exclusionName == null)?'':$value->exclusionName;
                    $description = ($value->description == '' || $value->description == null)?'':$value->description;
                    $data[] = ['exclusionName'=> $name, 'description'=>$description];
                 } 
                $status = array(
                    'status' => true,
                    'message' => 'Bingo! Success!!', 
                    'traveler' => $traveler,
                    'exclusions' => $data
                ); 
                return response()->json($status, 200);
            }
            else{
                $status = array(
                    'status' => false,
                    'message' => 'No Data Found', 
                ); 
                return response()->json($status, 200);
            }    
        }
        else{
            $status = array(
                'status' => false,
                'message' => 'Opps! Invalid Response!!'
            );
            return response()->json($status, 200);
        }        
    }         
}       
