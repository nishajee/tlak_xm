<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use DB;


class ApiGetCountryPaxCreditController extends Controller
{
    public function getCountryPaxCredit(Request $request)
    {
    	$key =$request->key;
    	if($key == '5ece4797eaf5e'){
        	$pax_credit_details = DB::table('pax_credit_details')->orderBy('country_name',"ASC")->get(); 
        	$status = array(
                'credit_details' => $pax_credit_details,
        );
            return response()->json($status, 200);
        } 
        else{
        	$pax_credit_details = [];
        	$status = array(
                'credit_details' => $pax_credit_details,
                'error' => 'Unauthorized access!'
        	);
            return response()->json($status, 401);
        }
        
    }
}