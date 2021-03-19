<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;
use Storage;
use Image;
use Auth;
use finfo;
use App\Feedback;
use App\Tenant;
use App\User;
use App\TourPckage;
use App\UpcommingTourPackage;
use App\TourPckageUpcommingTourPackage;
class UpcommingTourPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permission = User::getPermissions();
        if (Gate::allows('optional_departure_view',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $upcomingtourpackage =DB::table('upcomming_tour_packages')->where('tenant_id','=', Auth::User()->tenant_id)->orderBy('id','DESC')->paginate(25);
            $upcommingSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/upcommeingpkg/';
            return view('upcomingtourpackage.index',compact('upcomingtourpackage','tenant','permission','upcommingSRCPath'));
        }
        else{
            return abort(403);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $permission = User::getPermissions();
        if (Gate::allows('optional_departure_create',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $user = auth()->user();
            $departures = TourPckage::where('tenant_id','=', Auth::User()->tenant_id)
                ->whereDate('start_date', '>=', date('Y-m-d'))
                ->select('id as departureId', 'pname as departureName')
                ->orderBy('start_date','DESC')
                ->get();
            return view('upcomingtourpackage.create',compact('user','tenant','departures'));
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
    public function store(Request $request)
    {
        $data = $request->all();
        $validatedData = $request->validate([
           'pname' => 'required|max:255',
           'background_image' => 'required',
           // 'contact_email' => 'required',
           // 'contact_phone' => 'required',
           // 'promo_content' => 'required'
        ]); 
        $startFormat = $request->start_date;
        $start_date = date("Y-m-d", strtotime($startFormat));
        $upcomingtourpackage  = new UpcommingTourPackage;
        $upcomingtourpackage->pname = $request->pname;
        $upcomingtourpackage->contact_email = $request->contact_email;
        $upcomingtourpackage->contact_phone = $request->contact_phone;
        $upcomingtourpackage->description = $request->description;
        $upcomingtourpackage->promo_content = $request->promo_content;
        $upcomingtourpackage->start_date = $start_date;
        $user = auth()->user();
        $upcomingtourpackage->tenant_id = $user->tenant_id;
        $upcomingtourpackage->user_id = $user->id;
            // $tourpkg = $request->pname;
            //     $tourpkg1 =explode(' ',$tourpkg);           
            //     $tourpkg2 =implode('_',$tourpkg1);

        if($request->hasFile('background_image')){ 
            $file = $request->file('background_image');
            $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
            $image = Image::make($file);
            Storage::disk('s3')->put('upcommeingpkg/'.$imageName, $image->stream(), 'public');
            $upcomingtourpackage->background_image = $imageName;                
        }


        $upcomingtourpackage->save();
        // $last_id = $upcomingtourpackage->id;

        // $departures_id=$request->departure_id;
        //     if($departures_id){                
        //         foreach ($departures_id as $value) {
        //              $user = auth()->user();
        //              $departures_id = new TourPckageUpcommingTourPackage;
        //              $departures_id->upcomming_tour_package_id=$last_id;
        //              $departures_id->tour_pckage_id=$value;
        //              $departures_id->tenant_id = $user->tenant_id;
        //              $departures_id->save();
        //         }   
        //     }
         
        $request->session()->flash('status', 'Upcoming Tour Package created successfully.');
        return Redirect::to('optional-departure');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $upcomingtourpackage  = UpcommingTourPackage::where('id',$id)->first();      
        $user = auth()->user();
        return view('upcomingtourpackage.edit',compact('upcomingtourpackage','user','tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        $permission = User::getPermissions();
        if (Gate::allows('optional_departure_edit',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $upcomingtourpackage  = UpcommingTourPackage::where('id',$id)->first();      
            $user = auth()->user();
            $departure  = TourPckageUpcommingTourPackage::join('tour_pckages','tour_pckages.id','=','tour_pckage_upcomming_tour_packages.tour_pckage_id')
            ->where('tour_pckage_upcomming_tour_packages.upcomming_tour_package_id',$id)
            ->select('tour_pckages.id as selectetphkId','tour_pckages.pname as selectedpkgName')
            ->get();
            $departures = TourPckage::where('tenant_id','=', Auth::User()->tenant_id)
                ->whereDate('start_date', '>=', date('Y-m-d'))
                ->select('id as departureId', 'pname as departureName')
                ->orderBy('start_date','DESC')
                ->get();
            $upcommingSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/upcommeingpkg/';
            return view('upcomingtourpackage.edit',compact('upcomingtourpackage','user','tenant','upcommingSRCPath','departure','departures'));
        }
        else{
            return abort(403); 
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       $data = $request->all();
        $validatedData = $request->validate([
         
        ]); 
        $startFormat = $request->start_date;
        $start_date = date("Y-m-d", strtotime($startFormat));
        $upcomingtourpackage = UpcommingTourPackage::find($id);
        $upcomingtourpackage->pname = $request->pname;
        $upcomingtourpackage->contact_email = $request->contact_email;
        $upcomingtourpackage->contact_phone = $request->contact_phone;
        $upcomingtourpackage->description = $request->description;
        $upcomingtourpackage->promo_content = $request->promo_content;
        $upcomingtourpackage->start_date = $start_date;
        $tourpkg = $request->pname;
        $tourpkg1 =explode(' ',$tourpkg);           
        $tourpkg2 =implode('_',$tourpkg1);

        if($request->hasFile('background_image')){ 
            $file = $request->file('background_image');
            $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
            $image = Image::make($file);
            Storage::disk('s3')->put('upcommeingpkg/'.$imageName, $image->stream(), 'public');
            $upcomingtourpackage->background_image = $imageName;                  
         };


        $upcomingtourpackage->save();
        //  $last_id = $upcomingtourpackage->id;

        // $departures_id=$request->departure_id;
        //     if($departures_id){
        //         TourPckageUpcommingTourPackage::where('upcomming_tour_package_id', '=', $last_id)->delete();                
        //         foreach ($departures_id as $value) {
        //              $user = auth()->user();
        //              $departures_id = new TourPckageUpcommingTourPackage;
        //              $departures_id->upcomming_tour_package_id=$last_id;
        //              $departures_id->tour_pckage_id=$value;
        //              $departures_id->tenant_id = $user->tenant_id;
        //              $departures_id->save();
        //         }   
        //     }
        $request->session()->flash('status', 'Upcoming Tour Package created successfully.');
        return Redirect::to('optional-departure');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request){
        $upcommingT = UpcommingTourPackage::find($request->up_id);
        $upcommingT->status = $request->status;
        $upcommingT->save();
        return response()->json(['success'=>'Status change successfully.']);
    }
    public function destroyUcomingTour(Request $request, $id)
    {
        $aa = UpcommingTourPackage::find($id)->delete();
        $bb = TourPckageUpcommingTourPackage::where('upcomming_tour_package_id',$id)->delete();
        return response()->json([
           'success' => 'Upcomming tour deleted successfully!'
       ]);
    }
    public function createDepUpcoming(Request $request)
    {   
        // $permission = User::getPermissions();
        // if (Gate::allows('optional_departure_create',$permission)) {
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
            $user = auth()->user();
            $depupcoming = UpcommingTourPackage::where('tenant_id','=', Auth::User()->tenant_id)
                ->whereDate('start_date', '>=', date('Y-m-d'))
                ->select('id as upcomingId', 'pname as upcomingName')
                ->orderBy('start_date','DESC')
                ->get();
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            return view('upcomingtourpackage.departure_upcoming_create',compact('user','tenant','depupcoming','penandcomitem','disableDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }
    public function storeDepUpcoming(Request $request)
    {   

        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $upcominglists=$request->upcoming_list;
            if($upcominglists){                
                foreach ($upcominglists as $value) {
                     $user = auth()->user();
                     $upcominglist = new TourPckageUpcommingTourPackage;
                     $upcominglist->tour_pckage_id=$route_id;
                     $upcominglist->upcomming_tour_package_id=$value;
                     $upcominglist->tenant_id = $user->tenant_id;
                     $upcominglist->save();
                }   
            }
        $request->session()->flash('status', 'Upcoming Tour Package added successfully in departure!');
        return redirect()->route('edit_dep_upcoming',$route_id);
    }

    public function editDepUpcoming(Request $request)
    {   
        // $permission = User::getPermissions();
        // if (Gate::allows('optional_departure_create',$permission)) {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $rid = $request->route('id'); 
        $routeId = (int)$rid;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $depupcoming = UpcommingTourPackage::where('tenant_id','=', Auth::User()->tenant_id)
                    ->whereDate('start_date', '>=', date('Y-m-d'))
                    ->select('id as upcomingId', 'pname as upcomingName')
                    ->orderBy('start_date','DESC')
                    ->get();
            $upcomingdeparture  = TourPckageUpcommingTourPackage::join('upcomming_tour_packages','upcomming_tour_packages.id','=','tour_pckage_upcomming_tour_packages.upcomming_tour_package_id')
                ->where('tour_pckage_upcomming_tour_packages.tour_pckage_id',$route_id)
                ->select('upcomming_tour_packages.id as selectetpkgId','upcomming_tour_packages.pname as selectedpkgName')
                ->get();
                $upcomingdepartureProcess  = TourPckageUpcommingTourPackage::join('upcomming_tour_packages','upcomming_tour_packages.id','=','tour_pckage_upcomming_tour_packages.upcomming_tour_package_id')
                ->where('tour_pckage_upcomming_tour_packages.tour_pckage_id',$route_id)
                ->select('upcomming_tour_packages.id as selectetpkgId','upcomming_tour_packages.pname as selectedpkgName','upcomming_tour_packages.start_date as startDate','upcomming_tour_packages.promo_content as promoContent','upcomming_tour_packages.contact_email as email','upcomming_tour_packages.contact_phone as phone','tour_pckage_upcomming_tour_packages.status','tour_pckage_upcomming_tour_packages.id as depUpId','upcomming_tour_packages.background_image')
                ->paginate(5);
                $upcommingSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/upcommeingpkg/';
                //dd($upcomingdeparture
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            return view('upcomingtourpackage.departure_upcoming_edit',compact('tenant','upcomingdeparture','penandcomitem','depupcoming','disableDeparture','upcommingSRCPath','upcomingdepartureProcess'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }
    public function updateDepUpcoming(Request $request)
    {   

        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;

        $upcominglists=$request->upcoming_list;
            if($upcominglists){
            TourPckageUpcommingTourPackage::where('tour_pckage_id', '=', $route_id)->delete();                
                foreach ($upcominglists as $value) {
                     $user = auth()->user();
                     $upcominglist = new TourPckageUpcommingTourPackage;
                     $upcominglist->tour_pckage_id=$route_id;
                     $upcominglist->upcomming_tour_package_id=$value;
                     $upcominglist->tenant_id = $user->tenant_id;
                     $upcominglist->save();
                }   
            }
        $request->session()->flash('status', 'Upcoming Tour Package updated successfully in departure!');
        return redirect()->route('edit_dep_upcoming',$route_id);
    }

    public function depUpChangeStatus(Request $request){
        $depupcommingT = TourPckageUpcommingTourPackage::find($request->up_id);
        $depupcommingT->status = $request->status;
        $depupcommingT->save();
        return response()->json(['success'=>'Status change successfully.']);
    }

    // public function adminFeedBack(Request $request){
    //         $route_ids = $request->route('id'); 
    //         $route_id = (int)$route_ids;
    //         $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
    //         $keywords= Input::get('search');
    //         $feedbacks = Feedback::where(function($query)use($keywords){
    //             $query->where('package_name', 'LIKE','%'.$keywords.'%');
    //             })
    //             ->where('tour_package_id', $route_id)
    //             ->select('id','traveler_name','email', 'phone','package_name','feedback')
    //             ->orderBy('id','DESC')->paginate(20);
    //         if ($request->ajax()) {
    //             return view('appFeedback.feedback_data', compact('feedbacks','tenant'));
    //         }
    //         return view('appFeedback.index',compact('feedbacks','tenant'));
    // }

    public function appFeedBack(Request $request){
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
            $feedbacks = Feedback::where('tour_package_id', $route_id)->where('status', '=', 1)
                ->select('id','traveler_name','email', 'phone','package_name','feedback','rating')
                ->orderBy('id','DESC')->paginate(20);
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            if($request->ajax()){
                return view('appFeedback.feedback_data',compact('feedbacks'));
            }
            return view('appFeedback.index',compact('feedbacks','tenant','penandcomitem','disableDeparture'));
            }
        else{
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        return view('401.401',compact('tenant'));
        }
    }
}
