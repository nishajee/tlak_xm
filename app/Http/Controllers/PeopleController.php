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
use Excel;
use App\TourPckage;
use App\Tenant;
use App\HotelPeople;
use App\User;
use App\People;
use App\Balance;
use App\Country;
use App\CountryPerPax;
use App\LocationPointOfInterest;
use App\PaymentTransaction;

class PeopleController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPeople(Request $request)
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
            $package_status = TourPckage::where('id', $route_id)->value('status');
            $peoples = DB::table('peoples')->where('tour_package_id', $route_id)->paginate(15);
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();

            return view('people.add_people',compact('peoples','tenant','penandcomitem','package_status','route_id','disableDeparture'));
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
    public function storePeople(Request $request)
    {

        $data = $request->all();
        $request->validate(['import_file' => 'required|mimes:xls,xls,xlsx,csv,txt'],
                           ['import_file.required'  => 'Please select People lists CSV file!'],
                           ['import_file.mimes'  => 'Please select csv type file!']
    );

        $path = $request->file('import_file')->getRealPath();

        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $data = Excel::load($path)->get();
        $user = auth()->user();
        if($data->count()){
            foreach ($data as $key => $value) {
               
                $arr[] = ['name' => $value->name, 'tour_package_id' => $route_id,'tenant_id' => $user->tenant_id,'user_id' => $user->id];
            }
        
            //dd($arr);
            if(!empty($arr)){
                People::insert($arr);
            }
        }
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $request->session()->flash('status', 'People added successfully.');
        return back()->with('success', 'Insert Record successfully.');
    }
    public function updatePeople(Request $request, $id){
        $data        = $request->all();
        //dd($data);
        $user = auth()->user();
            $people = People::findOrFail($id);
            $people->name = $request->name;
            $people->save();
            return response()->json($people);
    }
    public function storePeopleSingle(Request $request)
    {
        $data  = $request->all();
        $user = auth()->user();
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $people = new People;
        $people->name = $request->name;
        $people->tour_package_id = $route_id;
        $people->tenant_id=$user->tenant_id;
        $people->user_id=$user->id;
        //dd($people);

        $people->save();
       // $updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        return response()->json($people);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePeople(Request $request, $id)
    {
        $people = People::find($id)->delete();
        $hPeople = HotelPeople::where('people_id',$id)->delete();
        //$people->delete();
        return response()->json([
           'success' => 'People deleted successfully!'
       ]);
    }

    public function getDetailsPeople(Request $request)
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

    public function activateTraveler(Request $request)
    {
        $people_id = $request['people-id'];
        $dep_id = $request['departure-id'];
        $consumption_credit = $request['consumption-credit'];
        $balance = $request['balance-credit'];

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(20);
        }
        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason_id = '3';
        $payment_transaction->debit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $left_over_credit = $balance-$consumption_credit;
        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $left_over_credit]);
        People::where('id', $people_id)->update(['departure_access' => '1']);
        return back()->with('status', 'Traveler activated successfully!');
    }

    public function deactivateTraveler(Request $request)
    {
        $people_id = $request['id'];
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
        $payment_transaction->reason_id = '4';
        $payment_transaction->credit = $consumption_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $dep_id ;
        $payment_transaction->save();
        $add_credit = $balance+20;

        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $add_credit]);
        People::where('id', $people_id)->update(['departure_access' => '0']);
        return back()->with('status', 'Traveler Deactivated successfully!');
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
