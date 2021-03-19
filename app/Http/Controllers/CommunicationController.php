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
use App\LocationPointOfInterest;
use App\TourPckage;
use App\Tenant;
use App\User;
use App\Communication;
use App\DepartureManager;
use App\DepartureGuide;
use App\Placard;
use App\Balance;
use App\Country;
use App\CountryPerPax;
use App\PaymentTransaction;

class CommunicationController extends Controller
{
    public function createCommunication(Request $request)
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
            $userTenant = Auth()->user();
            $EmergencyNumber = Communication::where('tour_package_id', $route_id)->paginate(5);
            $DepGuide = DepartureGuide::where('tour_package_id', $route_id)->paginate(5);
            $DepManager = DepartureManager::where('tour_package_id', $route_id)->paginate(5);
            $placard = Placard::where('tour_package_id', $route_id)->first();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first(); 
            $package_status = TourPckage::where('id', $route_id)->value('status');
            $departureForCommunication = TourPckage::where('id', $route_id)->where('tenant_id', Auth()->user()->tenant_id)->select('agent_name')->first();
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);

            $locations = DB::table("location_point_of_interests")
                            ->where('location_point_of_interests.tour_package_id', $route_ids)
                                ->where(function($q) {
                                    $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                                })
                            ->distinct()
                            ->select("location_point_of_interests.name as loc_name")
                            ->get(); 
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first(); 

            return view('communication.create',compact('EmergencyNumber','DepGuide','DepManager','placard','locations','tenant','penandcomitem','package_status','route_id','departureForCommunication','disableDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }    
    }

           //dd($blankArr); 
           
    public function storeCommunicationManager(Request $request)
    {
        $data        = $request->all();
    //dd($data); 
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $manager_passcode = TourPckage::where('id', $route_id)->value('manager_passcode');       
        $blankArr = array();
        if(isset($data["name"])){
              array_push($blankArr, $data);
            }else{
             $blankArr = $data;

            }
       
        foreach ($blankArr as $key => $value)
            {
            if($value['name']){      
              	foreach($value['name'] as $k => $v){
        	
			        $DepMng  = new DepartureManager;
			        $DepMng->name = $v;
			        $DepMng->phone = $value['phone'][$k];
			        $DepMng->email = $value['email'][$k];
			    
			        $DepMng->tour_package_id =$route_id;
			        $user = auth()->user();
                    $DepMng->tenant_id = $user->tenant_id;
                    $DepMng->manager_passcode = $manager_passcode;
			        $DepMng->user_id = $user->id;
                    $DepMng->type = "Manager";
			        $DepMng->save();
			    }
			}
		}
            //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020

            $request->session()->flash('status', 'Manager added successfully.');
            return redirect()->back();
    }

    // Departure Manager
    public function storeCommunicationGuide(Request $request)
    {
        $data        = $request->all(); 
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $blankArrguide = array();
        if(isset($data["guide_name"])){
                  array_push($blankArrguide, $data);
            }else{
             $blankArrguide = $data;

            }
       
        foreach ($blankArrguide as $keys => $values)
            {
            if($values['guide_name']){      
                foreach($values['guide_name'] as $ks => $vs){
            
                    $DepGd  = new DepartureGuide;
                    $DepGd->name = $vs;
                    $DepGd->phone = $values['guide_phone'][$ks];
                    $DepGd->location = $values['location'][$ks];
                   
                    $DepGd->tour_package_id =$route_id;
                    $user = auth()->user();
                    $DepGd->tenant_id = $user->tenant_id;
                    $DepGd->user_id = $user->id;
                    $DepGd->type = "Tour Guide";
                    $DepGd->save();
                }
            }
        }
            //$updatestatus = TourPckage::completeStatus($route_id);

            $request->session()->flash('status', 'Guide added successfully.');
            return redirect()->back();
	}      
    // Communication
    public function storeCommunicationScontact(Request $request)
    {
        $data        = $request->all(); 
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $blankArrsos = array();
            if(isset($data["contact_name"])){
              array_push($blankArrsos, $data);
            }else{
             $blankArrsos = $data;

            }
       
        foreach ($blankArrsos as $key1 => $value1)
            {
            if($value1['contact_name']){      
                foreach($value1['contact_name'] as $k1 => $v1){
            
                    $comunicatios  = new Communication;
                    $comunicatios->name = $v1;
                    $comunicatios->phone = $value1['contact_phone'][$k1];
                    $comunicatios->email = $value1['contact_email'][$k1];
                   
                    $comunicatios->tour_package_id =$route_id;
                    $user = auth()->user();
                    $comunicatios->tenant_id = $user->tenant_id;
                    $comunicatios->user_id = $user->id;
                    $comunicatios->save();
                }
            }
        }
            //$updatestatus = TourPckage::completeStatus($route_id);

            $request->session()->flash('status', 'Company support contact added successfully.');
            return redirect()->back();
    }

    //Placard
    public function storeCommunicationPlacard(Request $request)
    {
        $data        = $request->all(); 
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
            $placard  = new Placard;
            $placard->placard = $request->placard;
            $placard->placard_detail = $request->placard_detail;
            $placard->tour_package_id =$route_id;
            $user = auth()->user();
            $placard->tenant_id = $user->tenant_id;
            $placard->user_id = $user->id;
            $placard->save();

 // TourPckage Ststus update      
              
           // $updatestatus = TourPckage::completeStatus($route_id);

            $request->session()->flash('status', 'Placard added successfully.');
            return redirect()->back();
    }

	public function updateDepManager(Request $request, $id){
		    $data = $request->all();

            $manager = DepartureManager::findOrFail($id);
            $manager->name = $request->name;
            $manager->phone = $request->phone;
            $manager->email = $request->email;
            $manager->save();
            return response()->json($manager);
	   }

       public function updateDepGuide(Request $request, $id){
        $data        = $request->all();
            $guide = DepartureGuide::findOrFail($id);
            $guide->name = $request->name;
            $guide->phone = $request->phone;
            $guide->location = $request->location;
            $guide->save();
            return response()->json($guide);
       }
       public function updateCommunication(Request $request, $id){
        $data        = $request->all();
            $communication = Communication::findOrFail($id);
            $communication->name = $request->name;
            $communication->phone = $request->phone;
            $communication->email = $request->email;
            $communication->save();
            return response()->json($communication);
       }
       public function updatePlacard(Request $request, $id){
            $data        = $request->all();
            $placard = Placard::findOrFail($id);
            $placard->placard = $request->name;
            $placard->placard_detail = $request->details;
            $placard->save();
            return response()->json($placard);
       }
    
	public function deleteDepManager(Request $request, $id)
    {
        $manager = DepartureManager::findOrFail($id);
        $manager->delete();
        return response()->json([
           'success' => 'Departure Manager deleted successfully!'
       ]);
    }
    public function deleteDepGuide(Request $request, $id)
    {
        $guide = DepartureGuide::findOrFail($id);
        $guide->delete();
        return response()->json([
           'success' => 'Departure Guide deleted successfully!'
       ]);
    }
    public function deleteCommunication(Request $request, $id)
    {
        $communication = Communication::findOrFail($id);
        $communication->delete();
        return response()->json([
           'success' => 'Communication Contact deleted successfully!'
       ]);
    }
    public function deletePlacard(Request $request, $id)
    {
        $placard = Placard::findOrFail($id);
        $placard->delete();
        return response()->json([
           'success' => 'Communication Contact deleted successfully!'
       ]);
    }

    public function getDetailsGuide(Request $request)
    {
        $data = $request->all();
        $package_status = TourPckage::where('id', $data['dep_id'])->value('status');
        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        $country = Tenant::where('tenant_id', Auth()->user()->tenant_id)->value('address_country');

        $country_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->select('point_of_interests.country_name')
                      ->distinct()
                      ->where('location_point_of_interests.tour_package_id',$data['dep_id'])
                      ->pluck('country_name');                   

        if (in_array($country, json_decode(json_encode($country_list), true))) {
            $departure_type = "International";
        }
        else{
            $departure_type = "Domestic";
        }

        $currency_code = Country::where('country', $country)->value('currency_code');
        $pax = CountryPerPax::where('currency_code', $currency_code)->first();

        if ($departure_type == 'International') {
            $consumption_credit = $pax->international;
        }
        else{
            $consumption_credit = $pax->domestic;
        }

        return [$consumption_credit, $balance, $data['id'], $data['dep_id']];
    }

    public function getDetailsManager(Request $request)
    {
        $data = $request->all();
        $package_status = TourPckage::where('id', $data['dep_id'])->value('status');
        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        $country = Tenant::where('tenant_id', Auth()->user()->tenant_id)->value('address_country');

        $country_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->select('point_of_interests.country_name')
                      ->distinct()
                      ->where('location_point_of_interests.tour_package_id',$data['dep_id'])
                      ->pluck('country_name');                   

        if (in_array($country, json_decode(json_encode($country_list), true))) {
            $departure_type = "International";
        }
        else{
            $departure_type = "Domestic";
        }

        $currency_code = Country::where('country', $country)->value('currency_code');
        $pax = CountryPerPax::where('currency_code', $currency_code)->first();

        if ($departure_type == 'International') {
            $consumption_credit = $pax->international;
        }
        else{
            $consumption_credit = $pax->domestic;
        }

        return [$consumption_credit, $balance, $data['id'], $data['dep_id']];
    }

    public function activateGuide(Request $request)
    {
        $guide_id = $request['guide-id'];
        $dep_id = $request['departure-id'];
        $consumption_credit = $request['consumption-credit'];
        $balance = $request['balance-credit'];

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(20);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason_id = '5';
        $payment_transaction->debit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $left_over_credit = $balance-$consumption_credit;
        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $left_over_credit]);
        DepartureGuide::where('id', $guide_id)->update(['departure_access' => '1']);
        return back()->with('status', 'Guide activated successfully!');
    }

    public function deactivateGuide(Request $request)
    {
        $guide_id = $request['id'];
        $dep_id = $request['dep_id'];
        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        
        $country = Tenant::where('tenant_id', Auth()->user()->tenant_id)->value('address_country');

        $country_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->select('point_of_interests.country_name')
                      ->distinct()
                      ->where('location_point_of_interests.tour_package_id',$dep_id)
                      ->pluck('country_name');                   

        if (in_array($country, json_decode(json_encode($country_list), true))) {
            $departure_type = "International";
        }
        else{
            $departure_type = "Domestic";
        }

        $currency_code = Country::where('country', $country)->value('currency_code');
        $pax = CountryPerPax::where('currency_code', $currency_code)->first();

        if ($departure_type == 'International') {
            $consumption_credit = $pax->international;
        }
        else{
            $consumption_credit = $pax->domestic;
        }

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(20);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason_id = '7';
        $payment_transaction->credit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $add_credit = $balance+20;

        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $add_credit]);
        DepartureGuide::where('id', $guide_id)->update(['departure_access' => '0']);
        return back()->with('status', 'Guide Deactivated successfully!');
    }

    public function activateManager(Request $request)
    {
        $manager_id = $request['manager-id'];
        $dep_id = $request['dprtr-id'];
        $consumption_credit = $request['cnsmptn-credit'];
        $balance = $request['bal-credit'];

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(20);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason_id = '6';
        $payment_transaction->debit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $left_over_credit = $balance-$consumption_credit;
        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $left_over_credit]);
        DepartureManager::where('id', $manager_id)->update(['departure_access' => '1']);
        return back()->with('status', 'Manager activated successfully!');
    }

    public function deactivateManager(Request $request)
    {
        $manager_id = $request['id'];
        $dep_id = $request['dep_id'];
        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        
        $country = Tenant::where('tenant_id', Auth()->user()->tenant_id)->value('address_country');

        $country_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                      ->join('locations','locations.id','=','location_point_of_interests.location_id')
                      ->select('point_of_interests.country_name')
                      ->distinct()
                      ->where('location_point_of_interests.tour_package_id',$dep_id)
                      ->pluck('country_name');                   

        if (in_array($country, json_decode(json_encode($country_list), true))) {
            $departure_type = "International";
        }
        else{
            $departure_type = "Domestic";
        }

        $currency_code = Country::where('country', $country)->value('currency_code');
        $pax = CountryPerPax::where('currency_code', $currency_code)->first();

        if ($departure_type == 'International') {
            $consumption_credit = $pax->international;
        }
        else{
            $consumption_credit = $pax->domestic;
        }

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(20);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason_id = '7';
        $payment_transaction->credit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $add_credit = $balance+20;

        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $add_credit]);
        DepartureManager::where('id', $manager_id)->update(['departure_access' => '0']);
        return back()->with('status', 'Manager Deactivated successfully!');
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
}
