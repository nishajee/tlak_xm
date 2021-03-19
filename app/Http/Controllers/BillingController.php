<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tenant;
use App\PaymentTransaction;
use App\TourPckage;
use App\Balance;
use App\People;
use App\DepartureManager;
use App\DepartureGuide;
use App\Itinerary;
use App\ItineraryLocation;
use App\LocationPointOfInterest;
use App\Country;
use App\CountryPerPax;
use App\PaymentDetail;
use App\PaypalPayment;
use App\PaxCreditDetail;
use Auth;
use Redirect;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $payment_transactions = PaymentTransaction::where('tenant_id', Auth()->user()->tenant_id)->orderByRaw('id DESC')->get();
        $total_credit = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        foreach ($payment_transactions as $key => $value) {
        	$dep_name = TourPckage::where('id', $value->tour_package_id)->value('pname');
        	$value['departure_name'] = $dep_name;
        }
    	return view('billing.index',compact('tenant','payment_transactions','total_credit'));
    }

    public function departure_biling(Request $request, $id)
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $dep_details = TourPckage::where('id', $id)->first();
        $total_users = $dep_details->total_users;

        $departure_type = $dep_details->departure_type;
        $total_people = People::where('tour_package_id', $id)->count();
        $total_manager =  DepartureManager::where('tour_package_id', $id)->count();
        $total_guide = DepartureGuide::where('tour_package_id', $id)->count();
        $total_pax = $total_people + $total_manager + $total_guide;
        if($total_users > $total_pax){
            $total_traveler = $total_users;
        }
        else{
            $total_traveler = $total_pax;
        }
        $country = Tenant::where('tenant_id', Auth()->user()->tenant_id)->value('address_country');

        // $country_list = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
        //               ->join('locations','locations.id','=','location_point_of_interests.location_id')
        //               ->select('point_of_interests.country_name')
        //               ->distinct()
        //               ->where('location_point_of_interests.tour_package_id',$id)
        //               ->pluck('country_name');                   

        // if (in_array($country, json_decode(json_encode($country_list), true))) {
        //     $departure_type = "International";
        // }
        // else{
        //     $departure_type = "Domestic";
        // }


        $currency_code = Country::where('country', $country)->value('currency_code');
        $pax = PaxCreditDetail::where('currency_code', $currency_code)->first();

        if ($departure_type == 'international_in') {
            $consumption_credit = ($total_traveler) * $pax->inter_credit_after_con_discount;
        }
        elseif ($departure_type == 'international_out') {
            $consumption_credit = ($total_traveler) * $pax->inter_credit_after_con_discount;
        }
        else{
            $consumption_credit = ($total_traveler) * $pax->dom_credit_after_con_discount;
        }

       
        $balance_credit = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');

        return view('billing.departure_biling',compact('tenant','dep_details','total_traveler','departure_type','id','consumption_credit','balance_credit'));
    }

    public function activateDeparture(Request $request)
    {
        $total_credit = $request->total_credit;
        $activation_text = $request->activation_text;
        $id = $request->id;
        if ($activation_text != 'Activate') {
            return redirect()->back()->with('error', 'Please type Activate');
        }

        $balance = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        $left_over_credit = $balance-$total_credit;
        Balance::where('tenant_id', Auth()->user()->tenant_id)->update(['total_credit' => $left_over_credit]);

        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(7);
        }

        $payment_transaction = new PaymentTransaction();
        $payment_transaction->tenant_id = Auth()->user()->tenant_id;
        $payment_transaction->reason = '101';
        $payment_transaction->debit = $total_credit;
        $payment_transaction->payment_date = date("Y-m-d h:m:s");
        $payment_transaction->transaction_id = $transaction_id;
        $payment_transaction->tour_package_id = $id;
        $payment_transaction->save();
        if ($payment_transaction) {
            TourPckage::where('id', $id)->update(['status' => '2']);
            People::where('tour_package_id', $id)->update(['departure_access' => '1']);
            DepartureManager::where('tour_package_id', $id)->update(['departure_access' => '1']);
            DepartureGuide::where('tour_package_id', $id)->update(['departure_access' => '1']);
            // return redirect()->back()->with('success', 'Departure Activate Successfully');
            return Redirect::route('indexTour')->with('success', 'Departure Activate Successfully!!');
        }

    }

    public function addCredit()
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $total_credit = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
        return view('billing.add_credit', compact('tenant','total_credit'));
    }

    public function payCredit(Request $request)
    {
        $validatedData = $request->validate([
            'total_price' =>'required|numeric|min:100',
            'credit_purchase' =>'required|numeric|min:100',
        ]);
        
        $total_price = $request->total_price;
        $name = Auth()->user()->name;
        $tenant_id = Auth()->user()->tenant_id;
        $csrf = $request->_token;
        $email = Auth()->user()->email;
        $mobile = Auth()->user()->phone;
        $street = Auth()->user()->address_street;
        $city = Auth()->user()->address_city;
        $zip_code = Auth()->user()->address_zip;
        $country = Auth()->user()->address_country;
        $odr_id = $this->randStringOrderId(12);
        $order_id = "TLAK".$odr_id;
        $data_array = ['total_price'=>$total_price, 'name'=>$name, 'email' => $email, 'mobile'=>$mobile, 'street' => $street, 'city'=> $city,'zip_code' => $zip_code,'country' => $country,'tenant_id'=>$tenant_id, 'order_id'=>$order_id, 'csrf'=>$csrf];

        return redirect()->away('https://account.tlakapp.com/payment/dataFrom.php?data='.urlencode(serialize($data_array)));

    }

    public function paymentResponse(Request $request)
    {
        $order_status="";
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $decryptValues=explode('&', $request->form_data);

        $dataSize=sizeof($decryptValues);
        $main_array = array();
        for($i = 0; $i < $dataSize; $i++) 
        {
         $information=explode('=',$decryptValues[$i]);
         $main_array[$information[0]] = $information[1];
         if($i==3)  $order_status=$information[1];
        }
        $payment_details = new PaymentDetail();
        $amount = '';
        $tenant_id = '';
        $transaction_id = '';
        $transaction_date = '';
        foreach($main_array as $key => $element) {
            if($key == 'merchant_param1'){
                $tenant_id = $element;
                $payment_details->tenant_id = $element;
            }
            elseif($key == 'amount'){
                $amount = $element;
                $payment_details->$key = $element;
            }
            elseif($key == 'tracking_id'){
                $transaction_id = $element;
                $payment_details->$key = $element;
            }
            elseif ($key == 'merchant_param2');
            elseif($key == '_token');
            elseif($key == 'merchant_param4');
            elseif($key == 'merchant_param5');
            elseif($key == 'trans_date'){
                $tr_date = strtotime($element);
                $transaction_date = date('Y-m-d h:i:s', $tr_date);
                $payment_details->$key = $transaction_date;
            }
            else $payment_details->$key = $element;
        }
        $payment_details->save();
        $last_id = $payment_details->id;

        if ($order_status == 'Success') {
            $peymant_transaction = new PaymentTransaction();
            $peymant_transaction->tenant_id = $tenant_id;
            $peymant_transaction->reason = '108';
            $peymant_transaction->credit = $amount;
            $peymant_transaction->payment_date = $transaction_date;
            $peymant_transaction->transaction_id = $transaction_id;
            $peymant_transaction->save();

            if (Balance::where('tenant_id', '=', $tenant_id)->count() > 0) {
                $balance = Balance::where('tenant_id', $tenant_id)->value('total_credit');
                $total_credit = $balance + $amount;
                $add_balance = Balance::where('tenant_id', $tenant_id)->update(['total_credit' => $total_credit]);
                return Redirect::action('BillingController@thank_you', [$last_id]);

            }
            else{
                // $peymant_transaction = new PaymentTransaction();
                // $peymant_transaction->tenant_id = $tenant_id;
                // $peymant_transaction->reason = '109';
                // $peymant_transaction->credit = '500';
                // $peymant_transaction->payment_date = $transaction_date;
                // $peymant_transaction->transaction_id = $transaction_id;
                // $peymant_transaction->save();
                // $total_credit = $amount + 500;
                $balance = new Balance;
                $balance->tenant_id = $tenant_id;
                $balance->total_credit = $amount;
                $balance->save();

                return Redirect::action('BillingController@thank_you', [$last_id]);
            }

        }

        elseif ($order_status == 'Aborted') {
            return redirect('add_credit')->with('aborted', 'Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail');
        }

        elseif ($order_status == 'Failure') {
            return redirect('add_credit')->with('failure', 'Thank you for shopping with us.However,the transaction has been declined.');
        }

        else{
            return redirect('add_credit')->with('security', 'Security Error. Illegal access detected.');
        }

    }

    public function thank_you(Request $request, $id)
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        if (Auth()->user()->address_country == 'India') 
        {
            $details = PaymentDetail::where('id', $id)->where('tenant_id', Auth()->user()->tenant_id)->first(['amount','tracking_id']);

            $count_payment = PaymentDetail::where('tenant_id', Auth()->user()->tenant_id)->where('order_status', 'Success')->count();
            if ($details != null) {
                $amount = $details->amount;
                $transaction_id = $details->tracking_id;
                $total_credit = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
                return view('billing.thank-you', compact('tenant','total_credit','amount','transaction_id','count_payment'));
            }
            else{
                abort('404');
            }
        }
        else{
            $details = PaypalPayment::where('id', $id)->where('tenant_id', Auth()->user()->tenant_id)->first(['amount','order_id']);

            $count_payment = PaypalPayment::where('tenant_id', Auth()->user()->tenant_id)->where('address_status', 'Confirmed')->count();
            if ($details != null) {
                $amount = $details->amount;
                $transaction_id = $details->order_id;
                $total_credit = Balance::where('tenant_id', Auth()->user()->tenant_id)->value('total_credit');
                return view('billing.thank-you', compact('tenant','total_credit','amount','transaction_id','count_payment'));
            }
            else{
                abort('404');
            }
        }
    }

    public function paymentDetails(Request $request)
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        if(Auth()->user()->address_country == 'India'){
            $details = PaymentDetail::where('tenant_id', Auth()->user()->tenant_id)->orderByRaw('id DESC')->get();
        }
        else{
            $details = PaypalPayment::where('tenant_id', Auth()->user()->tenant_id)->orderByRaw('id DESC')->get();
        }
        return view('billing.payment-details', compact('details', 'tenant'));
    }

    public function invoice(Request $request, $id)
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $details = PaymentDetail::where('tracking_id', $id)->where('tenant_id', Auth()->user()->tenant_id)->get();
        if ($details != null) {
            return view('billing.invoice', compact('tenant','details'));
        }
        else{
            abort('404');
        }
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
    
    function randStringOrderId($length, $charset='0123456789')
    {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }
 
}
