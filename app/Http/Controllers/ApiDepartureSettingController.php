<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;
use Image;
use Auth;
use App\Tenant;
use App\User;
use App\TourPckage;
use App\CountryGuide;

class ApiDepartureSettingController extends Controller
{
    public function apiDepartureSetting(Request $request){
    	$route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
        	$tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        	$countryISO = DB::table('location_point_of_interests')
                        ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                        ->distinct()
                        ->where('location_point_of_interests.tour_package_id',$route_id)
                        ->select('point_of_interests.country_name','point_of_interests.iso_2 as iso2')
                        ->get();
            $country_guide = CountryGuide::where('tour_package_id',$route_id)->get();
            //dd(count($country_guide));
        	return view('apiDepartureSetting.api_dep_setting',compact('tenant','penandcomitem','countryISO','country_guide'));
        }
         else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }  
    }

    public function countryGuideIso(Request $request){

        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        if($request->iso){
            CountryGuide::where('tour_package_id',$route_id)->where('tenant_id',auth()->user()->tenant_id)->delete();
            foreach ($request->iso as $iso2) {
                $countryISO = DB::table('location_point_of_interests')
                        ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                        ->distinct()
                        ->where('point_of_interests.iso_2',$iso2)
                        ->select('point_of_interests.country_name')
                        ->first();
                $countryguide  = new CountryGuide;
                $countryguide->iso_2 = $iso2; 
                $countryguide->country_name = $countryISO->country_name;
                $countryguide->tour_package_id = $route_id;
                $countryguide->status = "1";
                $user = auth()->user();
                $countryguide->tenant_id = $user->tenant_id;
                $countryguide->user_id = $user->id;
                $countryguide->save();
            }
            return response()->json(['status'=>"Updated!"]);
        } 
        else{
            CountryGuide::where('tour_package_id',$route_id)->delete();
            return response()->json(['status'=>"Updated!"]);
        } 
    }

}
