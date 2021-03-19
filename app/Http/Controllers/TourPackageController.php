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
use finfo;
use App\TourPckage;
use App\Tenant;
use App\User;
use App\Timezone;
use App\UpcommingTourPackage;
use App\TourPckageUpcommingTourPackage;
use App\Inclusion; 
use App\Placard; 
use App\InclusionTourPckage;
use App\LocationPointOfInterest;
use App\Itinerary; 
use App\ItineraryLocation;
use App\People; 
use App\Flight;
use App\Hotel;
use App\HotelPeople; 
use App\HotelTiket;
use App\HotelSocket;
use App\HotelAmenity;
use App\ScheduledNotification;
use App\LocationNotification;
use App\InstantNotification;
use App\PdfItinerary;
use App\DepartureManager;
use App\DepartureGuide;
use App\Communication;
use App\PaymentTransaction;
use App\Balance;
use App\GroupTraveler;
use App\Country;
use App\ExclusionTourPackage;
use App\TermAndCondition;

class TourPackageController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexTourPackage(Request $request)
    {   
        $permission = User::getPermissions();
        if (Gate::allows('departure_view',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $keywords= Input::get('search');
            $tourpackages = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                ->where(function($query)use($keywords){
                $query->where('pname', 'LIKE','%'.$keywords.'%')->orWhere('tour_pckages.company_id', 'LIKE','%'.$keywords.'%');
                })
                ->orderBy('id', 'DESC')->paginate(20);
               // if ($request->ajax()) {
                //     return view('tourpackage.package_data', compact('tourpackages','tenant'));
                // }
            $permission = User::getPermissions();
            $complete = 1;
            $pending = 0;
            $pending_item = array();
            $complete_item = array();
            foreach ($tourpackages as $key => $value) {

                $departureDate = date('Y-m-d');
                $departureDate=date('Y-m-d', strtotime($departureDate));
                $tourDateBegin = date('Y-m-d', strtotime($value->start_date));
                $tourDateEnd = date('Y-m-d', strtotime($value->end_date));
                if (($departureDate >= $tourDateBegin) && ($departureDate <= $tourDateEnd)){
                    $value['is_live'] = 'yes';
                }
                else{
                    $value['is_live'] = 'no';
                }

                $total_inclusion = InclusionTourPckage::where('tour_package_id', $value->id)->count();
                $total_location =  LocationPointOfInterest::where('tour_package_id', $value->id)->count();
                $total_itineary = Itinerary::where('tour_package_id', $value->id)->count();
                $total_people = People::where('tour_package_id', $value->id)->count();
                $total_flight = Flight::where('tour_package_id', $value->id)->count();
                $total_hotel = Hotel::where('tour_package_id', $value->id)->count();
                $total_document = PdfItinerary::where('tour_package_id', $value->id)->count();
                $total_dep_manager = DepartureManager::where('tour_package_id', $value->id)->count();
                $total_dep_guide = DepartureGuide::where('tour_package_id', $value->id)->count();
                $total_dep_communication = Communication::where('tour_package_id', $value->id)->count();
                $total_dep_placard = Placard::where('tour_package_id', $value->id)->count();
                
                if($total_inclusion == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Inclusion');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Inclusion');
                }
                if($total_location == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Location');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Location');
                }
                if($total_itineary == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Itinerary');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Itinerary');
                }
                if($total_people == '0'){
                    $pending += 1;
                    array_push($pending_item, 'People');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'People');
                }
                if($total_flight == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Flight');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Flight');
                }
                if($total_hotel == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Hotel');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Hotel');
                }
                if ($total_document == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Document & Creation');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Document & Creation');
                }

                if ($total_dep_manager == '0' && $total_dep_guide == '0' && $total_dep_communication == '0' && $total_dep_placard == '0'){
                    $pending += 1;
                    array_push($pending_item, 'Communication');
                }
                else{ 
                    $complete +=1;
                    array_push($complete_item, 'Communication');
                } 

                $value['complete'] = $complete;
                $value['complete_item'] = $complete_item;
                $value['pending'] = $pending;
                $value['pending_item'] = $pending_item;
                $complete_item = array();
                $pending_item = array();
                $complete = 1;
                $pending = 0;
                $total_inclusion = 0;
                $total_location = 0;
                $total_itineary = 0;
                $total_people = 0;
                $total_flight = 0;
                $total_hotel = 0;
                $total_document = 0;
                $total_dep_manager = 0;
                $total_dep_guide = 0;
                $total_dep_communication = 0;
                $total_dep_placard = 0;

            }
            if($request->ajax()){
                return view('tourpackage.package_data',compact('tourpackages','permission'));
            }
            return view('tourpackage.index',compact('tourpackages','tenant','permission'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTourPackage()
    {  
        $permission = User::getPermissions();
        if (Gate::allows('departure_create',$permission)) { 
            // $upcommingtour = UpcommingTourPackage::where('tenant_id',auth()->user()->tenant_id)->get();
            $timezone = Timezone::get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $user = auth()->user();
            return view('tourpackage.create',compact('timezone','user','tenant'));
        }
        else{
            return abort(403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTourPackage(Request $request)
    {
        $data = $request->all();
        $validatedData = $request->validate([
           'pname' => 'required|max:255',
           'start_date' => 'required|max:255',
           'end_date' => 'required|max:255',
        ]); 
        $startFormat = $request->start_date;
        $start_date = date("Y-m-d", strtotime($startFormat));
        $endFormat = $request->end_date;
        $end_date = date("Y-m-d", strtotime($endFormat));

        //dd($end_date);
        $passcode=$request->passcode;
        $manager_passcode=$request->manager_passcode;
        $tourpackage  = new TourPckage;
        $tourpackage->pname = $request->pname;
        $tourpackage->start_date = $start_date;
        $tourpackage->end_date = $end_date;
        // $tourpackage->first_name = $request->first_name;
        $tourpackage->agent_name = $request->agent_name;
        $tourpackage->start_time = $request->start_time;
        $tourpackage->total_days = $request->total_days;
        $tourpackage->total_nights = $request->total_nights;
        $tourpackage->total_users = $request->total_users;
        $tourpackage->departure_type = $request->departure_type;
        $tourpackage->disclaimer = $request->disclaimer;
        $tourpackage->inbound_countries = json_encode($request->country_list);
        //$tourpackage->timezone = $request->timezone;
        // $tourpackage->radius = $request->radius;
        
        // if($request->hide_upcoming_tour==null){
        //    $tourpackage->upcoming_tour_notification = $request->upcoming_tour_notification;
        //     $tourpackage->hide_upcoming_tour = 1;
        //     //$tourpackage->after_day = $request->after_day;
        //     // $tourpackage->before_day = $request->before_day;
        //     // $tourpackage->device_local_time = $request->device_local_time;
        //     // $tourpackage->time_date = $request->time_date;

        // }
        // else{
            
        //     $tourpackage->hide_upcoming_tour = 0;
            
        // }

        $user = auth()->user();
        $passCodes = $user->company_id.'-'. $passcode;
        $MngpassCodes = $user->company_id.'-'. $manager_passcode;
        $tourpackage->user_id = $user->id;
        $tourpackage->tenant_id = $user->tenant_id;
        $tourpackage->company_id = $user->company_id;
        $tourPkg  = TourPckage::where('passcode',$passCodes)->orWhere('manager_passcode',$passCodes)->first();
        if($tourPkg){
            return redirect()->back()->withInput(Input::all())->with('unique', 'The Travellers passcode already exist, Enter different Passcode');
        }elseif($passCodes == $MngpassCodes){
            return redirect()->back()->withInput(Input::all())->with('unique', 'The Travellers Passcode and Ops Team Passcode should be Different');
        }
        else{
            $tourpackage->passcode = $passCodes;
        }

        $tourPkgs  = TourPckage::where('manager_passcode',$MngpassCodes)->orWhere('passcode',$MngpassCodes)->first();
        if($tourPkgs){
            return redirect()->back()->withInput(Input::all())->with('unique', 'The Ops Teams passcode already exist, Enter different Ops Teams Passcode');
        }elseif($passCodes == $MngpassCodes){
            return redirect()->back()->withInput(Input::all())->with('unique', 'The Passcode and Manager Passcode should be Different');
        }
        else{
            $tourpackage->manager_passcode = $MngpassCodes;
        }

        if($request->file('banner_image'))
        { 
            $file = $request->file('banner_image');
            $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
            $image = Image::make($file);
            Storage::disk('s3')->put('banner_image/'.$imageName, $image->stream(), 'public');
            $tourpackage->banner_image = $imageName;             
        }
    
    //    dd($tourpackage1);
        
        $tourpackage->save();
        $last_id = $tourpackage->id;
        $pkgName = $tourpackage->pname;
        $tenantId = $tourpackage->tenant_id;

        $group_unique_id = $this->generateUniqueId();

        $groupTraveler = new GroupTraveler;
        $groupTraveler->tour_package_id = $last_id;
        $groupTraveler->group_name = $pkgName;
        $groupTraveler->tenant_id = $tenantId;
        $groupTraveler->group_unique_id = $group_unique_id;
        $groupTraveler->type = 'Group';
        $groupTraveler->save();

        // if($request->hide_upcoming_tour==null){ 
           
        //     $upcomming=$request->upcomming_tour;
        //     if($upcomming){                
        //         foreach ($upcomming as $value) {

        //             $upcomming_tour = new TourPckageUpcommingTourPackage;
        //             $upcomming_tour->tour_pckage_id=$last_id;
        //             $upcomming_tour->upcomming_tour_package_id=$value;
        //             $upcomming_tour->save();
        //         }   
        //     }
        // }

        // else{
            
        // }
        if($last_id){           
            $request->session()->flash('status', 'Tour Package created successfully.');
            //return Redirect::to('tour-package');
            return redirect()->route('add_inclusion', $last_id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $tourpackages  = TourPckage::where('id',$id)->first();
        // return view('tourpackage.show',compact('tourpackages'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editTourPackage(Request $request, $id)
    {   
        $rid = $request->route('id'); 
        $routeId = (int)$rid;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($routeId, $depID)){
            $permission = User::getPermissions();
            if (Gate::allows('departure_edit',$permission)) { 
                $tourpackage  = TourPckage::where('id',$id)->first();
                $tour = $tourpackage->total_days;
                $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
                // $upcommingtour =DB::table('upcomming_tour_packages')->where('tenant_id','=', Auth::User()->tenant_id)->get();
                 
                $timezone = Timezone::get();
                $user = auth()->user();
                $penandcomitem = TourPckage::completedAndPendingItem($id);
                //dd($user->tenant_code);
                $current_dates = date('Y-m-d');
                $disableDeparture  = TourPckage::where('id',$id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
                $country_list = Country::select('country')->get();
                $inbound_country = json_decode($tourpackage->inbound_countries);
                return view('tourpackage.edit',compact('tourpackage','timezone','user','tour','tenant','penandcomitem','disableDeparture','country_list','inbound_country'));
            }
            else{
                return abort(403);
            }

            }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTourPackage(Request $request, $id)
    {
       $data = $request->all();
        $validatedData = $request->validate([
           'pname' => 'required|max:255',
           'start_date' => 'required|max:255',
           'end_date' => 'required|max:255',
        ]); 
        $startFormat = $request->start_date;
        
        $start_date = date("Y-m-d", strtotime($startFormat));
        //dd($start_date);
        $endFormat = $request->end_date;
        $end_date = date("Y-m-d", strtotime($endFormat));

        $tourpackage  = TourPckage::find($id);      
        $tourpackage->pname = $request->pname;
         $startFormat = $request->start_date;
        $tourpackage->start_date = $start_date;
        $tourpackage->end_date = $end_date;
        $tourpackage->total_users = $request->total_users;
        $tourpackage->departure_type = $request->departure_type;

        $tourpackage->agent_name = $request->agent_name;
        $tourpackage->start_time = $request->start_time;
        $tourpackage->total_days = $request->total_days;
        $tourpackage->total_nights = $request->total_nights;
        if($request->passcode != '' && $request->manager_passcode != ''){
            //dd('yes');
            $passCodes=$request->passcode;
            $MngpassCodes=$request->manager_passcode;
            $tourPkg  = TourPckage::where('id', '!=', $request->id)->where('passcode',$passCodes)->orWhere('manager_passcode',$passCodes)->first();
            if($tourPkg){
                return redirect()->back()->withInput(Input::all())->with('unique', 'The Travellers passcode already exist, Please enter different Passcode');
            }elseif($passCodes == $MngpassCodes){
                return redirect()->back()->withInput(Input::all())->with('unique', 'The Travellers Passcode and Operation Team Passcode should be Different');
            }
            else{
                $tourpackage->passcode = $passCodes;
            }

            $tourPkgs  = TourPckage::where('id', '!=', $request->id)->where('manager_passcode',$MngpassCodes)->orWhere('passcode',$MngpassCodes)->first();
            if($tourPkgs){
                return redirect()->back()->withInput(Input::all())->with('unique', 'The Operation Teams passcode already exist, Please enter different passcode');
            }elseif($passCodes == $MngpassCodes){
                return redirect()->back()->withInput(Input::all())->with('unique', 'The Passcode and Operation Teams Passcode should be Different');
            }
            else{
                $tourpackage->manager_passcode = $MngpassCodes;
            }
        }
        // else{
        //     dd('no');
        // }
        
        if($request->departure_type == 'domestic'){
            $tourpackage->departure_type = $request->departure_type;
            $tourpackage->disclaimer = $request->disclaimer;
            $tourpackage->inbound_countries = '';
        }
        if($request->departure_type == 'international_out'){
            $tourpackage->departure_type = $request->departure_type;
            $tourpackage->disclaimer = '';
            $tourpackage->inbound_countries = '';
        }
        if($request->departure_type == 'international_in'){
            $tourpackage->departure_type = $request->departure_type;
            $tourpackage->disclaimer = '';
            $tourpackage->inbound_countries = json_encode($request->country_list);
        }
        $user = auth()->user();
       
        $tourpackage->save();
        // $tourpackage->upcomming_tour_packages()->sync($request->upcommingtour);

        $last_id = $tourpackage->id;
        $pnames = $tourpackage->pname;

        $groupTraveler = GroupTraveler::where('tour_package_id', $last_id)->first();
        if ($groupTraveler) {
            $copygroupTraveler = GroupTraveler::find($groupTraveler->id);        
            $copygroupTraveler->group_name = $pnames;
            $copygroupTraveler->save();
        }
        $request->session()->flash('status', 'Tour Package updated successfully.');
        return redirect()->route('edit_inclusion', $last_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteTourPackage(Request $request, $id)
    {
        $tourpackage = TourPckage::find($id);
        $tourpackage->status = 0;
        $tourpackage->save();
        $request->session()->flash('status', 'Tour Package deleted successfully.');
        return Redirect::to('tour-package');
    }

//Inclusions
    public function createInclusion(Request $request)
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
            $inclusions = Inclusion::pluck('name')->toArray();  
            $inclusionsTenant = InclusionTourPckage::where('tenant_id', auth()->user()->tenant_id)
                                ->pluck('name')->toArray();  
            $unique_inclusions =array_unique(array_merge($inclusions,$inclusionsTenant));
            
            $inclusionpkg = InclusionTourPckage::where('tour_package_id', $route_id)
                            ->where(function($q) {
                                    $q->where('tenant_id', auth()->user()->tenant_id);
                                })
                            ->orderBy('name','ASC')
                            ->get(); 
            $inclusionpkgedits = InclusionTourPckage::where('tenant_id', auth()->user()->tenant_id)
                                ->select('name')
                                ->groupBy('name')
                                ->orderBy('name','ASC')
                                ->get();
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();               
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
            // Exclusions
            $exclusion = ExclusionTourPackage::where('tour_package_id',  $route_id)->where('tenant_id', auth()->user()->tenant_id)->first();

            $exclusions = ($exclusion === null)?'':$exclusion->exclusion;

            if(count($inclusionpkg) > 0 || $exclusion !== null) {
                return view('tourpackage.inclusion_edit',compact('inclusionpkg','tenant','penandcomitem','disableDeparture','unique_inclusions','inclusionpkgedits','exclusions'));
            }else{
                return view('tourpackage.inclusion_add',compact('tenant','penandcomitem','disableDeparture','unique_inclusions','exclusions'));
            }   
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }           
    }
    public function storeInclusion(Request $request)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $array_check = array();
        for($i = 0; $i < count($request->name); $i++) {
            if ($request->name[$i] != '') {
                array_push($array_check, $request->name[$i]);
            }
        }
        if(count($array_check) > 0){ 
            foreach ($request->names as $inclu_name) {
                $inc  = InclusionTourPckage::where('name',$inclu_name)
                        ->where('tour_package_id',$route_id)
                        ->first();
                if(is_null($inc)){
                    $inclusion  = new InclusionTourPckage;
                    $inclusion->name = $inclu_name;    
                    $inclusion->tour_package_id = $route_id;
                    $user = auth()->user();
                    $inclusion->tenant_id = $user->tenant_id;
                    $inclusion->user_id = $user->id;
                    $inclusion->save();
                }
            }
            foreach ($array_check as $ext_name) {
                $inc  = InclusionTourPckage::where('name',$ext_name)
                        ->where('tour_package_id',$route_id)
                        ->first();
                if(is_null($inc)){
                    $inclus  = new InclusionTourPckage;
                    $inclus->name = $ext_name;   
                    $inclus->tour_package_id = $route_id; 
                    $user = auth()->user();
                    $inclus->tenant_id = $user->tenant_id;
                    $inclus->user_id = $user->id;
                    $inclus->save();
                }
            } 
        }
        else{
            foreach ($request->names as $inclu_name) {
                $inc  = InclusionTourPckage::where('name',$inclu_name)
                        ->where('tour_package_id',$route_id)
                        ->first();
                if(is_null($inc)){
                    $inclusion  = new InclusionTourPckage;
                    $inclusion->name = $inclu_name;    
                    $inclusion->tour_package_id = $route_id;
                    $user = auth()->user();
                    $inclusion->tenant_id = $user->tenant_id;
                    $inclusion->user_id = $user->id;
                    $inclusion->save();
                }
            }
        }
        if($route_id){           
            // $request->session()->flash('status', 'Inclusion added.');
            // return redirect()->route('add_location', $route_id);
            return redirect()->back()->with('status', 'Inclusion added.');
        }   
    }

    public function storeExclusion(Request $request)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $data = $request->exclusion;
        $user = auth()->user();
        $exclusion = new ExclusionTourPackage();
        $exclusion->tour_package_id = $route_id;
        $exclusion->exclusion = $data;
        $exclusion->tenant_id = $user->tenant_id;
        $exclusion->user_id = $user->user_id;
        $exclusion->save();
        if($exclusion){           
            $request->session()->flash('status', 'Exclusion added.');
            return redirect()->route('add_location', $route_id);
        }   
    }

    public function editInclusion(Request $request)
    {   
        $rid = $request->route('id'); 
        $routeId = (int)$rid;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($routeId, $depID)){
            $route_ids = $request->route('id'); 
            $route_id = (int)$route_ids;
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $inclusionpkg = InclusionTourPckage::where('tour_package_id', $route_id)
                            ->where(function($q) {
                                    $q->where('tenant_id', auth()->user()->tenant_id);
                                })
                            ->orderBy('name','ASC')
                            ->get(); 
            $inclusionpkgedits = InclusionTourPckage::where('tenant_id', auth()->user()->tenant_id)
                                ->select('name')
                                ->groupBy('name')
                                ->orderBy('name','ASC')
                                ->get(); 
            
            $inclusions = Inclusion::pluck('name')->toArray();  
            $inclusionsTenant = InclusionTourPckage::where('tenant_id', auth()->user()->tenant_id)
                                ->pluck('name')->toArray();  
            $unique_inclusions =array_unique(array_merge($inclusions,$inclusionsTenant));
            // Exclusions
            $exclusion = ExclusionTourPackage::where('tour_package_id',  $route_id)->where('tenant_id', auth()->user()->tenant_id)->first();

            $exclusions = ($exclusion === null)?'':$exclusion->exclusion;

            $penandcomitem = TourPckage::completedAndPendingItem($route_id);  
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            if(count($inclusionpkg) > 0 || $exclusion !== null) {
                return view('tourpackage.inclusion_edit',compact('inclusionpkg','tenant','penandcomitem','disableDeparture','unique_inclusions','inclusionpkgedits','exclusions'));
            }else{
                return view('tourpackage.inclusion_add',compact('tenant','penandcomitem','disableDeparture','unique_inclusions','exclusions'));
            }
        }  
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }
    public function updateInclusion(Request $request)
    {
        $data = $request->all();
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $array_check = array();
        for($i = 0; $i < count($request->name); $i++) {
            if ($request->name[$i] != '') {
                array_push($array_check, $request->name[$i]);
            }
        }
        if(count($array_check) > 0){ 
            InclusionTourPckage::where('tour_package_id', $route_id)->delete();
            foreach ($request->names as $inclu_name) {
                    $inclusion  = new InclusionTourPckage;
                    $inclusion->name = $inclu_name;    
                    $inclusion->tour_package_id = $route_id;
                    $user = auth()->user();
                    $inclusion->tenant_id = $user->tenant_id;
                    $inclusion->user_id = $user->id;
                    $inclusion->save();
            }
            foreach ($array_check as $ext_name) {
                $inc  = InclusionTourPckage::where('name',$ext_name)
                        ->where('tour_package_id',$route_id)
                        ->first();
                if(is_null($inc)){
                    $inclus  = new InclusionTourPckage;
                    $inclus->name = $ext_name;   
                    $inclus->tour_package_id = $route_id; 
                    $user = auth()->user();
                    $inclus->tenant_id = $user->tenant_id;
                    $inclus->user_id = $user->id;
                    $inclus->save();
                }
            } 
        }
        else{
            InclusionTourPckage::where('tour_package_id', $route_id)->delete();
            
            foreach ($request->names as $inclu_name) {
                    $inclusion  = new InclusionTourPckage;
                    $inclusion->name = $inclu_name;    
                    $inclusion->tour_package_id = $route_id;
                    $user = auth()->user();
                    $inclusion->tenant_id = $user->tenant_id;
                    $inclusion->user_id = $user->id;
                    $inclusion->save();
            }
        }  
        if($route_id){           
            $request->session()->flash('status', 'Inclusion update.');
            return redirect()->route('add_location', $route_id);
        } 
    }

    public function updateExclusion(Request $request)
    {
        $data = $request->all();
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        if(ExclusionTourPackage::where('tour_package_id', '=', $route_id)->exists()){
            $exclusion = ExclusionTourPackage::where('tour_package_id', $route_id)
                    ->update(['exclusion' => $request->exclusion]);
        }
        else{
            $user = auth()->user();
            $exclusion = new ExclusionTourPackage();
            $exclusion->tour_package_id = $route_id;
            $exclusion->exclusion = $request->exclusion;
            $exclusion->tenant_id = $user->tenant_id;
            $exclusion->user_id = $user->user_id;
            $exclusion->save(); 
        }
        if($exclusion){           
            $request->session()->flash('status', 'Exclusion updated.');
            return redirect()->route('add_location', $route_id);
        }
    }

    public function changeStatus(Request $request)
    {

        $departure = TourPckage::find($request->departure_id);
        $departure->status = $request->status;
        $departure->save();
        return response()->json(['success'=>'Status change successfully.']);
    }

    public function disableDeparture(Request $request, $id)
    {
        $departure = TourPckage::find($id);
        // $u_credit = PaymentTransaction::select('debit')->where('tour_package_id', $id)->where('reason_id', '1')->orderBy('id', 'desc')->first();
        $debit = PaymentTransaction::where('tour_package_id', $id)->sum('debit');
        $credit = PaymentTransaction::where('tour_package_id', $id)->sum('credit');
        $used_credit = $debit - $credit;

        // $used_credit = $u_credit->debit;
        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        $total_balance = $used_credit + $balance;
        $add_balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $total_balance]);
        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(7);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason = '102';
        $payment_transaction->credit = $used_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $id;
        $payment_transaction->save();
        
        $departure->status = '3';
        $departure->save();
        return response()->json([
           'success' => 'Departure disabled successfully!'
        ]);
    }
    
    public function getCountryList(Request $request)
    {
        $data = []; 
        if($request->has('q')){
            $search = $request->q;
            $data = DB::table("countries")
                    ->select("country")
                    ->where('countries.country','LIKE',"%$search%")
                    ->get();
        }
        else{
          $data = $data = DB::table("countries")
                    ->select("country")
                    ->limit(10)
                    ->get();
        }
        return response()->json($data);
    } 

     function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }
    public function copyDeparture(Request $request)
    {
        $packageID = $request->pkgid;
        $userss = auth()->user();
        $startFormat = $request->start_date;
        $start_date = date("Y-m-d", strtotime($startFormat));
        $endFormat = $request->end_date;
        $end_date = date("Y-m-d", strtotime($endFormat));

        //dd($end_date);
        $passcode=$request->passcode;
        $manager_passcode=$request->manager_passcode;

        $passCodes = $userss->company_id.'-'.$passcode;
        $MngpassCodes = $userss->company_id.'-'.$manager_passcode;

        $tourpackage  = new TourPckage;
        $tourpackage->pname = $request->pname;
        $tourpackage->start_date = $start_date;
        $tourpackage->end_date = $end_date;

        $tourPkg  = TourPckage::where('passcode',$passCodes)->orWhere('manager_passcode',$passCodes)->first();
        if($tourPkg){
            return Response()->json(['status' => 302, 'messages' => 'The Travellers passcode already exist, Enter different Passcode']);
            exit;
        }elseif($passCodes == $MngpassCodes){
            return Response()->json(['status' => 302, 'messages' => 'The Travellers Passcode and Ops Teams Passcode should be Different']);
            exit;
        }
        else{
            $tourpackage->passcode = $passCodes;
        }

        $tourPkgs  = TourPckage::where('manager_passcode',$MngpassCodes)->orWhere('passcode',$MngpassCodes)->first();
        if($tourPkgs){
            return Response()->json(['status' => 302, 'messages' => 'The Ops Teams passcode already exist, Enter different Ops Teams Passcode']);
            exit;
        }elseif($passCodes == $MngpassCodes){
            return Response()->json(['status' => 302, 'messages' => 'The Travellers Passcode and Ops Teams Passcode should be Different']);
            exit;
        }
        else{
            $tourpackage->manager_passcode = $MngpassCodes;
        }

        $user = auth()->user();

        $package_details  = TourPckage::where('id',$packageID)->where('tenant_id', $user->tenant_id)->first();
        
        // $tourpackage->first_name = $request->first_name;
        $tourpackage->agent_name = $request->agent_name;
        $tourpackage->start_time = $request->start_time;
        $tourpackage->total_days = $request->total_days;
        $tourpackage->total_nights = $request->total_nights;
        $tourpackage->timezone = $request->edit_utc;
        $tourpackage->total_users = $request->total_users;
        $tourpackage->departure_type = $package_details->departure_type;
        $tourpackage->inbound_countries = $package_details->inbound_countries;
        //$tourpackage->passcode = $user->company_id.'-'. $passcode;
        //$tourpackage->manager_passcode = $user->company_id.'-'. $manager_passcode;
        $tourpackage->user_id = $user->id;
        $tourpackage->tenant_id = $user->tenant_id;
        $tourpackage->company_id = $user->company_id;
        
        $tourpackage->save();
        $last_id = $tourpackage->id;
        $pkgName = $tourpackage->pname;
        $tenantId = $tourpackage->tenant_id;
        $managerPasscode = $tourpackage->manager_passcode;
//Group Table for chat
        $pkgnames=explode('-copy',$request->pname);
        $copygroupTraveler = new GroupTraveler;

        $copygroupTraveler->tour_package_id = $last_id;
        $copygroupTraveler->group_name = $pkgnames[0];
        $copygroupTraveler->tenant_id = $tenantId;
        $copygroupTraveler->type = 'Group';
        $copygroupTraveler->save();

//Inclusion
        $inclusion = InclusionTourPckage::where('tour_package_id', $packageID)->get();
        if($inclusion){                
            foreach ($inclusion as $value) {
                $copypkginclusion = new InclusionTourPckage;
                $copypkginclusion->name = $value->name;
                $copypkginclusion->tour_package_id = $last_id; 
                $user = auth()->user();
                $copypkginclusion->tenant_id = $user->tenant_id;
                $copypkginclusion->user_id = $user->id;
                $copypkginclusion->save();
            }
        }
//Exclusion
        $exclusion = ExclusionTourPackage::where('tour_package_id', $packageID)->first();
        if($exclusion !== null){                
            $copypkgexclusion = new ExclusionTourPackage;
            $copypkgexclusion->tour_package_id = $last_id; 
            $copypkgexclusion->exclusion = $exclusion->exclusion; 
            $user = auth()->user();
            $copypkgexclusion->tenant_id = $user->tenant_id;
            $copypkgexclusion->user_id = $user->id;
            $copypkgexclusion->save();
        }        
//Location POI
        $locationpoi = LocationPointOfInterest::where('tour_package_id', $packageID)->get();
        if($locationpoi){                
            foreach ($locationpoi as $value) {
                $copypkglocation = new LocationPointOfInterest;
                $copypkglocation->location_id = $value->location_id;
                $copypkglocation->tour_package_id = $last_id; 
                $copypkglocation->point_of_interest_id = $value->point_of_interest_id;
                $copypkglocation->name = $value->name; 
                $copypkglocation->place_id = $value->place_id;
                $user = auth()->user();
                $copypkglocation->tenant_id = $user->tenant_id;
                $copypkglocation->user_id = $user->id;
                $copypkglocation->save();
            }
        }
//Itinerary
        $itinerary = Itinerary::where('tour_package_id', $packageID)->get();
        if($itinerary){                
            foreach ($itinerary as $value) {
                $copypkgitinerary = new Itinerary;
                $copypkgitinerary->day_number = $value->day_number;
                $copypkgitinerary->tour_package_id = $last_id; 
                $copypkgitinerary->description = $value->description;
                $copypkgitinerary->name = $value->name; 
                $copypkgitinerary->inclusions = $value->inclusions;
                $copypkgitinerary->exclusions = $value->exclusions; 
                $copypkgitinerary->banner_image = $value->banner_image;
                $user = auth()->user();
                $copypkgitinerary->tenant_id = $user->tenant_id;
                $copypkgitinerary->user_id = $user->id;
                $copypkgitinerary->save();

                $ilast_id = $copypkgitinerary->id;
                $locationItinerary = ItineraryLocation::where('itinerary_id', $value->id)->where('tour_package_id', $packageID)->get();
                if($locationItinerary){                
                    foreach ($locationItinerary as $values) {
                         $user = auth()->user();
                         $itinerarylocations = new ItineraryLocation;
                         $itinerarylocations->itinerary_id=$ilast_id;
                         $itinerarylocations->location_id=$values->location_id;
                         $itinerarylocations->tour_package_id=$last_id;
                         $itinerarylocations->tenant_id = $user->tenant_id;
                         $itinerarylocations->user_id = $user->id;
                         $itinerarylocations->save();
                    }   
                }
            }
        }
//Hotel
        $hotels = Hotel::where('tour_package_id', $packageID)->get();
        if($hotels){                
            foreach ($hotels as $hotel) {
                $copypkghotel = new Hotel;
                $copypkghotel->tour_package_id = $last_id; 
                $copypkghotel->name = $hotel->name;
                $copypkghotel->location = $hotel->location;
                $copypkghotel->latitude = $hotel->latitude; 
                $copypkghotel->longitude = $hotel->longitude; 
                $copypkghotel->address = $hotel->address; 
                $copypkghotel->description = $hotel->description; 
                $copypkghotel->total_room = $hotel->total_room; 
                $copypkghotel->country = $hotel->country; 
                $copypkghotel->state = $hotel->state; 
                $copypkghotel->hotel_rating = $hotel->hotel_rating; 
                $copypkghotel->type = $hotel->type; 
                $copypkghotel->hotel_image = $hotel->hotel_image;  
                $user = auth()->user();
                $copypkghotel->tenant_id = $user->tenant_id;
                $copypkghotel->user_id = $user->id;
                $copypkghotel->save();

                $hlast_id = $copypkghotel->id;
                $hotelPeople = HotelPeople::where('hotel_id', $hotel->id)->where('tour_package_id', $packageID)->get();
                if($hotelPeople){                
                    foreach ($hotelPeople as $hpeople) {
                         $user = auth()->user();
                         $pkghotelpeople = new HotelPeople;
                         $pkghotelpeople->hotel_id=$hlast_id;
                         $pkghotelpeople->people_id=$hpeople->people_id;
                         $pkghotelpeople->tour_package_id=$last_id;
                         $pkghotelpeople->location_id=$hpeople->location_id;
                         $pkghotelpeople->tenant_id = $user->tenant_id;
                         $pkghotelpeople->user_id = $user->id;
                         $pkghotelpeople->save();
                    }   
                }

                $hotelAmenity = HotelAmenity::where('hotel_id', $hotel->id)->where('tour_package_id', $packageID)->get();
                if($hotelAmenity){                
                    foreach ($hotelAmenity as $hamenity) {
                         $user = auth()->user();
                         $pkghotelamenity = new HotelAmenity;
                         $pkghotelamenity->hotel_id=$hlast_id;
                         $pkghotelamenity->amenity_id=$hamenity->amenity_id;
                         $pkghotelamenity->tour_package_id=$last_id;
                         $pkghotelamenity->tenant_id = $user->tenant_id;
                         $pkghotelamenity->user_id = $user->id;
                         $pkghotelamenity->save();
                    }   
                }
                $hotelSocket = HotelSocket::where('hotel_id', $hotel->id)->where('tour_package_id', $packageID)->get();
                if($hotelSocket){                
                    foreach ($hotelSocket as $hsocket) {
                         $user = auth()->user();
                         $pkghotelsocket = new HotelSocket;
                         $pkghotelsocket->hotel_id=$hlast_id;
                         $pkghotelsocket->socket_id=$hsocket->socket_id;
                         $pkghotelsocket->tour_package_id=$last_id;
                         $pkghotelsocket->tenant_id = $user->tenant_id;
                         $pkghotelsocket->user_id = $user->id;
                         $pkghotelsocket->save();
                    }   
                }
                $hotelTiket = HotelTiket::where('hotel_id', $hotel->id)->where('tour_package_id', $packageID)->get();
                if($hotelTiket){                
                    foreach ($hotelTiket as $htiket) {
                         $user = auth()->user();
                         $pkghoteltiket = new HotelTiket;
                         $pkghoteltiket->hotel_id=$hlast_id;
                         $pkghoteltiket->document=$htiket->document;
                         $pkghoteltiket->tour_package_id=$last_id;
                         $pkghoteltiket->save();
                    }   
                }
            }
        }
//Terms and conditions
        $terms = TermAndCondition::where('tour_package_id', $packageID)->first();
        if($terms !== null){
            $termscondition = new TermAndCondition();
            $termscondition->tour_package_id = $last_id;
            $termscondition->terms = $terms->terms;
            $terms->save();
        }
//Scheduled Notification
        $notifications = ScheduledNotification::where('tour_package_id', $packageID)->get();
        if($notifications){                
            foreach ($notifications as $notification) {
                $copypkgnotification = new ScheduledNotification;
                $copypkgnotification->text = $notification->text;
                $copypkgnotification->tour_package_id = $last_id; 
                $copypkgnotification->image = $notification->image;
                $copypkgnotification->day = $notification->day; 
                $copypkgnotification->date = $notification->date;
                $copypkgnotification->time = $notification->time;
                $user = auth()->user();
                $copypkgnotification->tenant_id = $user->tenant_id;
                $copypkgnotification->user_id = $user->id;
                $copypkgnotification->save();
            }
        }
//Instant Notification
        $inotifications = InstantNotification::where('tour_package_id', $packageID)->get();
        if($inotifications){                
            foreach ($inotifications as $inotification) {
                $copypkginotification = new InstantNotification;
                $copypkginotification->text = $inotification->text;
                $copypkginotification->tour_package_id = $last_id; 
                $user = auth()->user();
                $copypkginotification->tenant_id = $user->tenant_id;
                $copypkginotification->user_id = $user->id;
                $copypkginotification->save();
            }
        }
//Locaction Notification
        $lnotifications = LocationNotification::where('tour_package_id', $packageID)->get();
        if($lnotifications){                
            foreach ($lnotifications as $lnotification) {
                $copypkglnotification = new LocationNotification;
                $copypkglnotification->text = $lnotification->text;
                $copypkglnotification->tour_package_id = $last_id; 
                $copypkglnotification->poi_id = $lnotification->poi_id;
                $copypkglnotification->day = $lnotification->day; 
                $user = auth()->user();
                $copypkglnotification->tenant_id = $user->tenant_id;
                $copypkglnotification->user_id = $user->id;
                $copypkglnotification->save();
            }
        }
//Tour Manager Copy
        $departuremanagers = DepartureManager::where('tour_package_id', $packageID)->get();
        if($inotifications){                
            foreach ($departuremanagers as $departuremanager) {
                $copypkgdeparturemanager = new DepartureManager;
                $copypkgdeparturemanager->name = $departuremanager->name;
                $copypkgdeparturemanager->email = $departuremanager->email;
                $copypkgdeparturemanager->phone = $departuremanager->phone;
                $copypkgdeparturemanager->type = $departuremanager->type;
                $copypkgdeparturemanager->manager_passcode = $managerPasscode;
                $copypkgdeparturemanager->tour_package_id = $last_id; 
                $user = auth()->user();
                $copypkgdeparturemanager->tenant_id = $user->tenant_id;
                $copypkgdeparturemanager->user_id = $user->id;
                $copypkgdeparturemanager->save();
            }
        }
//Communication Agent Copy
        $communications = Communication::where('tour_package_id', $packageID)->get();
        if($communications){                
            foreach ($communications as $communication) {
                $copypkgcommunication = new Communication;
                $copypkgcommunication->name = $communication->name;
                $copypkgcommunication->tour_package_id = $last_id; 
                $copypkgcommunication->phone = $communication->phone;
                $copypkgcommunication->email = $communication->email; 
                $user = auth()->user();
                $copypkgcommunication->tenant_id = $user->tenant_id;
                $copypkgcommunication->user_id = $user->id;
                $copypkgcommunication->save();
            }
        }
//Communication Guide Copy
        $depatureguides = DepartureGuide::where('tour_package_id', $packageID)->get();
        if($depatureguides){                
            foreach ($depatureguides as $depatureguide) {
                $copypkgguide = new DepartureGuide;
                $copypkgguide->name = $depatureguide->name;
                $copypkgguide->tour_package_id = $last_id; 
                $copypkgguide->phone = $depatureguide->phone;
                $copypkgguide->location = $depatureguide->location; 
                $copypkgguide->type = $depatureguide->type;
                $user = auth()->user();
                $copypkgguide->tenant_id = $user->tenant_id;
                $copypkgguide->user_id = $user->id;
                $copypkgguide->save();
            }
        }
//Placard Copy
        $placards = Placard::where('tour_package_id', $packageID)->get();
        if($placards){                
            foreach ($placards as $placard) {
                $copypkgguide = new Placard;
                $copypkgguide->placard = $placard->placard;
                $copypkgguide->tour_package_id = $last_id; 
                $copypkgguide->placard_detail = $placard->placard_detail;
                $user = auth()->user();
                $copypkgguide->tenant_id = $user->tenant_id;
                $copypkgguide->user_id = $user->id;
                $copypkgguide->save();
            }
        }
        return Response()->json(['status' => 200, 'message' => $last_id]);
    }

    function generateUniqueId() 
    {
        $id = md5(uniqid(rand(), true));
        if ($this->uniqueIdExists($id)) 
        {
            return generateUniqueId();
        }
        return $id;
    }

    function uniqueIdExists($id) 
    {
        return groupTraveler::where('group_unique_id',$id)->exists();
    }
}