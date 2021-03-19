<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Image;
use Auth;
use App\Tenant;
use App\User;
use App\LocationPointOfInterest;
use App\PointOfInterest;
use App\PointOfInterestIcon;
use App\Location;
use App\TourPckage;
use App\CountryGuide;
use App\Shopping;
use App\TransportLocation;
class LocationPointOfInterestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLocation(Request $request)
    {
        //$keywords= Input::get('search');
      //   $poi = DB::table('location_point_of_interests')->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')->join('locations','locations.id','=','location_point_of_interests.location_id')->select('locations.name as dest_name','locations.status','point_of_interests.name as poi_name','point_of_interests.point_type','point_of_interests.country_name','point_of_interest_icons.icon_image')->paginate(10);
      // // dd($location_point_of_interests);
      //   // if ($request->ajax()) {
      //   //     return view('location.location_data', compact('poi'));
      //   // }
      //   return view('locationPointOfInterest.index',compact('poi'));
    }

     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createLocation(Request $request)
    {   
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids; 
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){ 
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
            $pkg = TourPckage::where('id',$route_id)->select('id as route_id')->first();

            $poi = DB::table("location_point_of_interests")
            ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
            ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
            ->join('locations','locations.id','=','location_point_of_interests.location_id')
            ->where("location_point_of_interests.tour_package_id",$route_id)
            ->where(function($q) {
                            $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                 })
            ->select("point_of_interests.*","locations.name as dest_name","location_point_of_interests.id as dest_id","location_point_of_interests.status","point_of_interest_icons.name as pointTypeName","point_of_interest_icons.icon_image")->paginate(10);
            $locationpoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/poi/';
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();

            return view('locationPointOfInterest.create',compact('pkg','poi','tenant','penandcomitem','locationpoiSRCPath','disableDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }
    public function getDestinationsAjax(Request $request)
    {
            //$locations=$request->location;

            $loctipnss = DB::table("locations")
                ->where("country_name",$request->name)
                ->where('tenant_id', Auth()->user()->tenant_id)
                ->select("name")->orderBy('name', 'DESC')->get();
            return response()->json($loctipnss);
    }

    public function getPoiAjax(Request $request)
    {
            //$locations=$request->location;
            $pois = DB::table("point_of_interests")
                ->where("country_name",$request->name)
                ->where('tenant_id', Auth()->user()->tenant_id)
                ->select("name","id")->orderBy('name', 'DESC')->get();
            return response()->json($pois);
    }

    public function getCountryAjax(Request $request)
    {
            $data = [];
            if($request->has('q')){
                $search = $request->q;
                $data = DB::table("locations")->where('tenant_id',auth()->user()->tenant_id)
                        ->distinct()
                        ->select("country_name")
                        ->where('country_name','LIKE',"%$search%")
                        ->get();
            }
            else{
               $data = DB::table("locations")->where('tenant_id',auth()->user()->tenant_id)
                        ->distinct()
                        ->select("country_name")->orderBy('country_name', 'ASC')
                        // ->limit(10)
                        ->get();
            }
            return response()->json($data);
    }
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLocation(Request $request, $id)
    {  
        $data = $request->all();
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $user = auth()->user();
        $poi_ids = array();
        foreach ($request->data_poi as $key => $value) {
            array_push($poi_ids, $value['poiid']);
            $dest_id = $value['did'];
            if(LocationPointOfInterest::where(['place_id' => $value['place'],'tenant_id' => auth()->user()->tenant_id,'tour_package_id' => $value['routes']])->doesntExist())
            {
                $route_ids = $request->route('id'); 
                $route_id = (int)$route_ids;        
                
                $locationPois = LocationPointOfInterest::create([
                  'name' => $value['dname'],
                  'place_id' => $value['place'],
                  'tour_package_id' => $value['routes'],
                  'location_id' => $value['did'],
                  'point_of_interest_id' => $value['poiid'],
                  'tenant_id' => $user->tenant_id,
                  'user_id' => $user->id
                ]); 
            }
        }
        $poi_latitude = 0;
        $poi_longitude = 0;                            
        $poi_count = 0;
        foreach ($poi_ids as $value) {
            $latlong = PointOfInterest::where('id', $value)->first();
            $poi_latitude += $latlong->latitude;
            $poi_longitude += $latlong->longitude;
            $poi_count += 1;
        }
        $location_lat = $poi_latitude/$poi_count;
        $location_long = $poi_longitude/$poi_count;
        $shopping_url = file_get_contents('https://api.tomtom.com/search/2/search/shopping_center.json?language=en-US&limit=20&lat='.$location_lat.'&lon='.$location_long.'&radius=5000&categorySet=7373&key=6vdxVANLJketgjeoT3dvURPnu4ny3VWy');
        $jsondata_shopping = json_decode($shopping_url);

        foreach ($jsondata_shopping->results as $key => $value_shopping) {
          if(Shopping::where(['poi_id' => $value_shopping->id,'tenant_id' => auth()->user()->tenant_id,'tour_package_id' => $route_id,'location_id' => $dest_id])->doesntExist()){
             $shopping = new Shopping();
             if((array_key_exists('municipalitySubdivision', $value_shopping->address))){
                 $shopping->location = $value_shopping->address->municipalitySubdivision;
             }

             $shopping->name = $value_shopping->poi->name;
             $shopping->latitude = $value_shopping->position->lat;
             $shopping->longitude = $value_shopping->position->lon;
             $shopping->postal_code = $value_shopping->address->postalCode;
             $shopping->country = $value_shopping->address->country;
             $shopping->address = $value_shopping->address->freeformAddress;
             $shopping->local_name = $value_shopping->address->localName;
             $shopping->location_id = $dest_id;
             $shopping->tour_package_id = $route_id;
             $shopping->tenant_id = $user->tenant_id;
             $shopping->type = $value_shopping->poi->categories[0];
             $shopping->poi_id = $value_shopping->id;
             $shopping->save();
          }
        }

        $transport_url = file_get_contents('https://api.tomtom.com/search/2/search/PUBLIC_TRANSPORT_STOP.json?language=en-US&limit=20&lat='.$location_lat.'&lon='.$location_long.'&radius=10000&categorySet=9942002,9942003,9942004,9942005,7380002,7380003,7380004,7380005&key=6vdxVANLJketgjeoT3dvURPnu4ny3VWy');
        $jsondata_transport = json_decode($transport_url);

        foreach ($jsondata_transport->results as $key => $value_transport) {
          if(TransportLocation::where(['poi_id' => $value_transport->id,'tenant_id' => auth()->user()->tenant_id,'tour_package_id' => $route_id,'location_id' => $dest_id])->doesntExist()){
             $transport = new TransportLocation();
             if((array_key_exists('municipalitySubdivision', $value_transport->address))){
                $transport->location = $value_transport->address->municipalitySubdivision;
             }

             $transport->name = $value_transport->poi->name;
             $transport->latitude = $value_transport->position->lat;
             $transport->longitude = $value_transport->position->lon;
             $transport->postal_code = $value_transport->address->postalCode;
             $transport->country = $value_transport->address->country;
             $transport->address = $value_transport->address->freeformAddress;
             $transport->local_name = $value_transport->address->localName;
             $transport->location_id = $dest_id;
             $transport->tour_package_id = $route_id;
             $transport->tenant_id = $user->tenant_id;
             $transport->type = $value_transport->poi->categories[1];
             $transport->poi_id = $value_transport->id;
             $transport->save();
          }
        }
        
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $countryISO = DB::table('location_point_of_interests')
                        ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                        ->distinct()
                        ->where('location_point_of_interests.tour_package_id',$route_id)
                        ->select('point_of_interests.iso_2','point_of_interests.country_name')
                        ->get();
        if(count($countryISO) > 0){
            foreach ($countryISO as $iso2) {
                $inc  = CountryGuide::where('iso_2',$iso2->iso_2)
                    ->where('tour_package_id',$route_id)
                    ->first();
                if(is_null($inc)){
                    $countryguide  = new CountryGuide;
                    $countryguide->iso_2 = $iso2->iso_2; 
                    $countryguide->country_name = $iso2->country_name;
                    $countryguide->tour_package_id = $route_id;
                    $countryguide->status = "1";
                    $user = auth()->user();
                    $countryguide->tenant_id = $user->tenant_id;
                    $countryguide->user_id = $user->id;
                    $countryguide->save();
                }
            }
        }
        return response()->json(['success' => 'added successfully']);  
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editLocation($id)
    {
        // $locations = Location::where('id',$id)->first();

        // return view('locationPointOfInterest.edit',compact('locations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLocation(Request $request, $id)
    {
        // $data = $request->all();
        // $validatedData = $request->validate([
        //    'name' => 'required|max:255',
        // ]); 

        // $location = LocationPointOfInterest::find($id);
        // $location->name = $request->name;
        // $location->save();
      
        //  $request->session()->flash('status', 'Location poi changed!');
        //     return Redirect::to('location'); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function changeStatus(Request $request){
        $location_poi = LocationPointOfInterest::find($request->location_id);
        $location_poi->status = $request->status;
        $location_poi->save();
        return response()->json(['success'=>'Status change successfully']);
    }
    public function getPois(Request $request){

       
        $poi = DB::table("point_of_interests")
        ->join('locations','locations.name','=','point_of_interests.location_name')
        ->where("point_of_interests.location_name",$request->location_name)
        ->where(function($q) {
                        $q->where('point_of_interests.tenant_id', auth()->user()->tenant_id);
             })
        ->where(function($q) {
                        $q->where('locations.tenant_id', auth()->user()->tenant_id);
             })
        ->select("point_of_interests.*","locations.name as dest_name","locations.id as dest_id")->get();
            //dd($destination);
        return response()->json($poi);
        
    }

    public function getPoisAjax(Request $request)
    {    
        $poi_id = explode(",",$request->poi_id);
        $poi = DB::table("point_of_interests")
        ->join('locations','locations.name','=','point_of_interests.location_name')
        ->whereIn("point_of_interests.id",$poi_id)
        ->where(function($q) {
                        $q->where('point_of_interests.tenant_id', auth()->user()->tenant_id);
             })
        ->where(function($q) {
                        $q->where('locations.tenant_id', auth()->user()->tenant_id);
             })
        ->select("point_of_interests.*","locations.name as dest_name","locations.id as dest_id")->get();
            // dd($poi);
        return response()->json($poi);
        
    }  
}
