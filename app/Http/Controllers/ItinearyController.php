<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Storage;
use Image;
use Auth;
use finfo;
use App\TourPckage;
use App\Tenant;
use App\User;
use App\LocationPointOfInterest;
use App\PointOfInterest;
use App\Location;
use App\Itinerary;
use App\ItineraryLocation;
use App\HotelItinerary;
use App\Hotel;
class ItinearyController extends Controller
{
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createItineary(Request $request, $id)
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
                $itineary = Itinerary::where('tour_package_id', $route_id)->orderBy('day_number', 'ASC')->get();
                $dd = count($itineary);
                if($dd > 0 || $dd != '' || $dd != null){
                    foreach ($itineary as $value) {
                         $itineary_loc[] = ItineraryLocation::join('locations','locations.id','=','itinerary_locations.location_id')->select('locations.name','itinerary_locations.itinerary_id')->where('itinerary_locations.itinerary_id',$value->id)->get();
                         //dd($itineary_loc);
                    }
                     $data_loc = array_flatten($itineary_loc);
                 }
                    foreach ($itineary as $key => $itnr){
                        $itineary_row = ItineraryLocation::where('itinerary_id',$itnr->id)->pluck('location_id')->toArray();
                        $itnr['location_id'] = $itineary_row;

                        $location_name = array();
                        foreach ($itineary_row as $key => $value) {
                            $loc_name = Location::where('id', $value)->first();
                            array_push($location_name, $loc_name->name);
                        }

                        $itnr['location_name'] = $location_name;
                    } 
                    $tourPkgs = TourPckage::where('id',$route_id)->value('total_days');

                                //dd($tourPkgs);
                    $pkgItinery = Itinerary::where('tour_package_id',$route_id)->count()+1;
                    //dd($pkgItinery);

                    $locationss = Location::get();    
                    $penandcomitem = TourPckage::completedAndPendingItem($route_id);
                    $HotelSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/itineary/';
                    $current_dates = date('Y-m-d');
                    $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();

                    return view('itineary.create',compact('itineary','locationss','tenant','penandcomitem','pkgItinery','tourPkgs','data_loc','HotelSRCPath','disableDeparture'));
        
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeItineary(Request $request)
    {
        $data        = $request->all();
        $validatedData = $request->validate([
           'day_number' => 'required',
           'name' => 'required',
           //'inclusions' => 'required',
           'description' => 'required',
           'banner_image' =>'required|max:2048',
           
        ]); 
           
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $tourpackages = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                ->where('id', $route_id)
                ->first();
        if($tourpackages->timezone == null or $tourpackages->timezone == ''){
            $tourpackage  =  TourPckage::find($tourpackages->id);
            //dd('uu');
            $tourpackage->timezone = $request->utc_offset;
            $tourpackage->save();
        }
        
        $itineary  = new Itinerary;
        $itineary->name = $request->name;
        $itineary->inclusions = $request->inclusions;
        $itineary->exclusions = $request->exclusions;
        $itineary->description = $request->description;
       
        $itineary->day_number = $request->day_number;
        $itineary->tour_package_id =$route_id;
        $user = auth()->user();
        $itineary->tenant_id = $user->tenant_id;
        $itineary->user_id = $user->id;
        if($request->file('banner_image')){ 
            $file = $request->file('banner_image');
            $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
            $image = Image::make($file);
            Storage::disk('s3')->put('itineary/'.$imageName, $image->stream(), 'public');
            $itineary->banner_image = $imageName;                 
         }
        $itineary->save();

        $last_id = $itineary->id;
        $locations=$request->location;
             if($locations){                
                 foreach ($locations as $value) {
                     $user = auth()->user();
                     $locations = new ItineraryLocation;
                     $locations->itinerary_id=$last_id;
                     $locations->location_id=$value;
                     $locations->tour_package_id=$route_id;
                     $locations->tenant_id = $user->tenant_id;
                     $locations->user_id = $user->id;
                     $locations->save();
                 }   
             }
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $request->session()->flash('status', 'Itineary created successfully.');
            return redirect()->route('add_itineary',$route_id);
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
    public function editItineary(Request $request,$id)
    {
        $route_ids = $request->route('id'); 
        $route_id  = (int)$route_ids;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $itineary  = Itinerary::where('id',$route_id)->first();
            $pkg_id = $itineary->tour_package_id;
            //dd($pkg_id);
            $itineary = Itinerary::where('id',$id)->first();
            $locationss = Location::get();
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            return view('itineary.edit',compact('itineary','locationss','tenant','disableDeparture'));
        }
        else{
            return view('401.401');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateItineary(Request $request, $id)
    {

        $route_ids = $request->route('id'); 
        $route_id  = (int)$route_ids;

        $itineary  = Itinerary::where('id',$route_id)->first();
        $pkg_id = $itineary->tour_package_id;
        
        $data        = $request->all();
        $validatedData = $request->validate([
           'edit_day_number' => 'required',
           'edit_day_heading' => 'required|max:255',
           'edit_description' => 'required',
           'location_edit' =>'required',
           
        ]); 
            $itineary       = Itinerary::find($id);
            $itineary->name = $request->edit_day_heading;
            $itineary->inclusions = $request->edit_inclusions;
            $itineary->exclusions = $request->edit_exclusions;
            $itineary->description = $request->edit_description;
            $itineary->day_number = $request->edit_day_number;
            if($request->hasFile('edit_file_image')){ 
                $file = $request->file('edit_file_image');
                $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
                $image = Image::make($file);
                Storage::disk('s3')->put('itineary/'.$imageName, $image->stream(), 'public');
                $itineary->banner_image = $imageName;                  
             }
            $itineary->save();
            $itineary->locations()->sync($request->locationss);

             $last_id = $itineary->id;
             $locations=$request->location_edit;
             if($locations){                
                 foreach ($locations as $value) {
                     $user = auth()->user();
                     $locations = new ItineraryLocation;
                     $locations->itinerary_id=$last_id;
                     $locations->location_id=$value;
                     $locations->tour_package_id = $pkg_id;
                     $locations->tenant_id = $user->tenant_id;
                     $locations->user_id = $user->id;
                     $locations->save();
                 }   
             }
            $request->session()->flash('status', 'Itineary updated successfully.');
            return redirect()->route('add_itineary',$pkg_id);
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteItineary(Request $request, $id)
    {
        Itinerary::find($id)->delete();
        ItineraryLocation::where('itinerary_id',$id)->delete();
        return response()->json([
           'success' => 'Itineary deleted successfully!'
       ]);
        
    }
    /////////////////////AJAX QUERIES///////////////////

    public function getHotelAjax(Request $request)
        {
            $data = [];
            if($request->has('q')){
                $search = $request->q;
                $data = DB::table("hotels")->where('tenant_id','=', Auth::User()->tenant_id)
                        ->select("id","name")
                        ->where('name','LIKE',"%$search%")
                        ->get();
            }
            else{
               $data =  $data = DB::table("hotels")->where('tenant_id','=', Auth::User()->tenant_id)
                        ->select("id","name")
                        ->limit(10)
                        ->get();
            }
            return response()->json($data);
        }

     public function getDestinationsAjax(Request $request)
        {
            $data = [];
            $route_ids = $request->id; 
            //$route_id = (int)$route_ids;

            if($request->has('q')){
                $search = $request->q;
                $data = DB::table("location_point_of_interests")
                        ->join('locations','locations.id','=','location_point_of_interests.location_id')
                        ->where('location_point_of_interests.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                            })
                        ->distinct()
                        ->select("location_point_of_interests.location_id as dest_id","locations.name as dest_name")
                        ->where('location_point_of_interests.name','LIKE',"%$search%")
                        ->get();
            }
            else{
              $data = DB::table("location_point_of_interests")

                        ->join('locations','locations.id','=','location_point_of_interests.location_id')


                        ->where('location_point_of_interests.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                            })
                        ->distinct()
                        ->select("location_point_of_interests.location_id as dest_id","locations.name as dest_name")
                        ->limit(10)
                        ->get();
            }
            return response()->json($data);
        }

    public function getLocationPoiAjax(Request $request){
        $ids=explode(',',$request->location_id);
        $locations = DB::table("location_point_of_interests")
          ->join('locations','locations.id','=','location_point_of_interests.destination_id')
          ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
          ->whereIn("location_point_of_interests.destination_id",$ids)
          ->select("point_of_interests.name as poi_name","point_of_interests.id as poi_id","location_point_of_interests.destination_id as dest_id","location_point_of_interests.id as loc_id","location_point_of_interests.point_of_interest_id","locations.name as dest_name","locations.id as dest_id")->orderBy('location_point_of_interests.name', 'ASC')->get();
          //dd($locations);
            return response()->json($locations);
    }

    public function getLocationUTC(Request $request){
            $locations=$request->location;
            $type_icon = DB::table("locations")
                ->where("id",$request->id)
                ->select("utc_offset")->first();
            return response()->json($type_icon);
        }

    public function positionShifting(Request $request){

//print_r(Input::has('position'));
//exit;

        if(Input::has('position'))
            {
                $pos = Input::get('position');
                $i = 0;
                foreach($pos as $k=>$v)
                {
                    $i++;
                    $item = Itinerary::find($v);
                    $item->day_number = $i;
                    $item->save();
                }
                exit;
                return response()->json([
                   'success' => "success"
               ]);
            }
            else
            {
                return response()->json([
                   'success' => "false"
               ]);
            }
     }
}
