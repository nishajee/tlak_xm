<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\HistoryTraveler;
use App\Traveler;
use App\Itinerary;
class ApiGroupChangeController extends Controller
{   
    
    public function groupChange(Request $request){
        $data = $request->all(); 
        $validator = Validator::make($request->all(),[
            'peopleName' => 'required',
            'travelerId' => 'required',
            'peopleId' => 'required'
            ]);
        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
        return Response($status);
    }
        $traveler_id = $request->travelerId;
        $tenant_id = $request->tenantId;
        $pname = $request->peopleName;       
        $email = $request->travelerEmail;
        $people_id = $request->peopleId;
        $pkgid = $request->pkgId;
        
       // print_r($travelers); exit;
        if($people_id != null) {
 // dd($people_id);
            $traveler =  Traveler::where('id',$traveler_id)->first();
            if($traveler){
                         $traveler->update([
                                'name' =>$pname,
                                'traveler_email' => $email,
                                'people_id' => $people_id,
                                'tour_package_id' => $pkgid,
                                'tenant_id' => $tenant_id,
                                'token' => Str::random(64),
                          ]); 
            
            $people = People::where('id', $people_id)
            ->update([
            'occupied' => 1,
            ]);

            $historyTraveler = new HistoryTraveler;
            $historyTraveler->name = $traveler->name;
            $historyTraveler->traveler_email = $traveler->traveler_email;
            $historyTraveler->people_id = $people_id;
            $historyTraveler->traveler_id = $traveler_id;
            $historyTraveler->phone = $traveler->phone;
            $historyTraveler->birth = $traveler->birth;
            $historyTraveler->address = $traveler->address;
            $historyTraveler->tour_package_id = $traveler->tour_package_id;
            $historyTraveler->tenant_id = $traveler->tenant_id;
            $historyTraveler->token = $traveler->token;
            $historyTraveler->save();

            $traveler_name=$traveler->name;
            $traveler_id=$traveler->id;
            $token=$traveler->token;
            $pkg_id=$traveler->tour_package_id;
            $tenant_id=$traveler->tenant_id;
            $status = array(
            'error' => false,
            'message' => 'Bingo! Successfull!!',
            'packageId' => $pkg_id,
            'tenantId' => $tenant_id,
            'travellers' => array(
                    'travelerName' => $traveler_name,
                    'travelerId' => $traveler_id,
                    'token' => $token,
                    ),
            );
           
            return Response($status);
        }

        else{
            $status = array(
                'error' => true,
                'message' => 'Record does not matched!!'
                 );
                return Response($status);
        } 
    }  
        
        else{
            $status = array(
                'error' => true,
                'message' => 'Opps! Invalid responce!!'
                 );
                return Response($status);
        }       
    }
        
}

