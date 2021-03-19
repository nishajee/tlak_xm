<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\ExpressCheckout;
use App\PaypalPayment;
use Auth;
use App\Tenant;
use App\Balance;
use App\Country;
use App\PaxCreditDetail;
use App\PaymentTransaction;
use Redirect;

class PayPalPaymentController extends Controller
{
    public function handlePayment(Request $request)
    {
    	$invoice_id = $this->randInvoiceId(8);
        $product = [];
        $product['items'] = [
            [
                'name' => Auth()->user()->name,
                'price' => $request->total_doller,
                'desc'  => 'Purchase Credit',
                'qty' => 1
            ]
        ];
  
        $product['invoice_id'] = $invoice_id;
        $product['invoice_description'] = "Order #{$product['invoice_id']} Bill";
        $product['return_url'] = route('success.payment');
        $product['cancel_url'] = route('cancel.payment');
        $product['tenant_id'] = Auth()->user()->tenant_id;
        $product['total'] = $request->total_doller;
  
        $paypalModule = new ExpressCheckout;
  
        $res = $paypalModule->setExpressCheckout($product);
        $res = $paypalModule->setExpressCheckout($product, true);
  
        return redirect($res['paypal_link']);
    }
   
    public function paymentCancel()
    {
        dd('Your payment has been declend. The payment cancelation page goes here!');
    }
  
    public function paymentSuccess(Request $request)
    {
        $paypalModule = new ExpressCheckout;
        $response = $paypalModule->getExpressCheckoutDetails($request->token);
        $odr_id = $this->randStringOrderId(12);
        $order_id = "TLAK".$odr_id;
        $tenant_id = Auth()->user()->tenant_id;
        $transaction_id = $this->randString(20);
        while(PaymentTransaction::where('transaction_id', $transaction_id)->count() > 0) { 
           $transaction_id = randString(7);
        }

  
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
        	$paypal_payment = new PaypalPayment();
        	$paypal_payment->tenant_id = $tenant_id;
        	$paypal_payment->name = Auth()->user()->name;
        	$paypal_payment->order_id = $order_id;
        	$paypal_payment->token = $response['TOKEN'];
        	$paypal_payment->first_name = $response['FIRSTNAME'];
        	$paypal_payment->last_name = $response['LASTNAME'];
        	$paypal_payment->email = $response['EMAIL'];
        	$paypal_payment->correlation_id = $response['CORRELATIONID'];
        	$paypal_payment->payer_id = $response['PAYERID'];
        	$paypal_payment->payer_status = $response['PAYERSTATUS'];
        	$paypal_payment->country_code = $response['COUNTRYCODE'];
        	$paypal_payment->address_status = $response['ADDRESSSTATUS'];
        	$paypal_payment->address_status = $response['ADDRESSSTATUS'];
        	$paypal_payment->currency_code = $response['CURRENCYCODE'];
        	$paypal_payment->amount = $response['AMT'];
        	$paypal_payment->item_amout = $response['ITEMAMT'];
        	$paypal_payment->shipping_amount = $response['SHIPPINGAMT'];
        	$paypal_payment->handling_amount = $response['HANDLINGAMT'];
        	$paypal_payment->tax_amount = $response['TAXAMT'];
        	$paypal_payment->time_stamp = $response['TIMESTAMP'];
        	$paypal_payment->save();
        	$last_id = $paypal_payment->id;

        	$purchased_credit = 100 * $response['AMT'];

            $peymant_transaction = new PaymentTransaction();
            $peymant_transaction->tenant_id = $tenant_id;
            $peymant_transaction->reason = '108';
            $peymant_transaction->credit = $purchased_credit;
            $peymant_transaction->payment_date = date("Y-m-d h:m:s");
            $peymant_transaction->transaction_id = $transaction_id;
            $peymant_transaction->save();

            if (Balance::where('tenant_id', '=', $tenant_id)->count() > 0) {
                $balance = Balance::where('tenant_id', $tenant_id)->value('total_credit');
                $total_credit = $balance + $purchased_credit;
                $add_balance = Balance::where('tenant_id', $tenant_id)->update(['total_credit' => $total_credit]);
                return Redirect::action('BillingController@thank_you', [$last_id]);

            }
            else{
                $peymant_transaction = new PaymentTransaction();
                $peymant_transaction->tenant_id = $tenant_id;
                $peymant_transaction->reason = '109';
                $peymant_transaction->credit = '500';
                $peymant_transaction->payment_date = date("Y-m-d h:m:s");
                $peymant_transaction->transaction_id = $transaction_id;
                $peymant_transaction->save();
                $total_credit = $purchased_credit + 500;
                $balance = new Balance;
                $balance->tenant_id = $tenant_id;
                $balance->total_credit = $total_credit;
                $balance->save();

                return Redirect::action('BillingController@thank_you', [$last_id]);
            }


            dd('Payment was successfull. The payment success page goes here!');
        }
  
        dd('Error occured!');
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

    function randInvoiceId($length, $charset='0123456789')
    {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }
}
