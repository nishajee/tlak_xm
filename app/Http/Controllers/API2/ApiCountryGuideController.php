<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\CountryGuide;
use App\TourPckage;
use App\Traveler;

class ApiCountryGuideController extends Controller
{
    public function countryGuide(Request $request){

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
      
        $traveler = Traveler::where('token',$token)->select('tour_package_id as pkgId','tenant_id as tenantId','token')->first();
        $tour_package_id=$traveler->pkgId;
    	$countryISOs = CountryGuide::where('tour_package_id',$tour_package_id)
                        			->pluck('iso_2 as iso2');
        $countryISO = [];
            foreach ($countryISOs as $value) {
                array_push($countryISO, $value);
            }

        $isos['countryISO'] = $countryISO;
        if(count($countryISO) > 0){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.tutterflycrm.com/ptprog/api/travelguide');
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($isos));

    		$headers = array();
    		$headers[] = 'Content-Type: application/json';
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    		$result = curl_exec($ch);
    		//print_r(json_decode($result));
    		if (curl_errno($ch)) {
    		   echo 'Error:' . curl_error($ch);
                }
           curl_close($ch);
           return Response()->json(json_decode($result));
        } 
        else{
            $countryISO = [];
            $status = [
                'status' => false,
                'countryISO' => $countryISO,
            ];
            return Response()->json($status);
        }	
    }
}
