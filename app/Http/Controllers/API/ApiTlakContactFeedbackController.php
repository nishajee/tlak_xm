<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Traveler;
use App\FeedbackTlak;
use DB;
use Mail;
class ApiTlakContactFeedbackController extends Controller
{   
    public function tlakFeedbackApp(Request $request){

        $data=$request->all();
        // dd($data);
        $tenant_id = $request->tenantId;
        $company_name = $request->companyName;
        $package_name = $request->departureName;
        $name = $request->travelerName;
        $phone = $request->travelerPhone; 
        $email = $request->travelerEmail;
        $message = $request->Message;
        $rating = $request->rating;
        


        $validator = Validator::make($request->all(),[
            'travelerName' => 'required',
            'Message' => 'required',
            'tenantId' =>'required',
            'companyName' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
        }
            $tlakFeedbacks = new FeedbackTlak; 
            $tlakFeedbacks->name = $name;
            $tlakFeedbacks->phone = $phone;
            $tlakFeedbacks->email = $email;
            $tlakFeedbacks->company_name = $company_name;
            $tlakFeedbacks->tenant_id = $tenant_id;
            $tlakFeedbacks->package_name = $package_name;
            $tlakFeedbacks->message = $message;
            $tlakFeedbacks->rating = $rating;
            $tlakFeedbacks->save();
            $companyName= $tlakFeedbacks->company_name;
            $pkg_name= $tlakFeedbacks->package_name;
            $tenant_ids= $tlakFeedbacks->tenant_id;
            $names= $tlakFeedbacks->name;
            $phones= $tlakFeedbacks->phone;
            $emails= $tlakFeedbacks->email;
            $messages= $tlakFeedbacks->message;
            $ratings= $tlakFeedbacks->rating;
            
            
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!',
            'companyName' => $companyName,
            'DepartureName' => $pkg_name,
            'tenantId' => $tenant_ids,
            'travelerName' => $names,
            'travelerEmail' => $emails,
            'travelerPhone' => $phones,
            'rating' => $ratings,
            'Message' => $messages,
            
            ); 
          return response()->json($status, 200);
        }     

        public function tlakContactApp(Request $request){

        $data=$request->all();

        $validator = Validator::make($request->all(),[
            'travelerName' => 'required',
            'Message' => 'required',
            'companyName' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
        }
            
            Mail::send('emails.tlak_contactus',[
                'name'=>$request->travelerName,
                'company_name'=>$request->companyName,
                'email'=>$request->travelerName,
                'phone'=>$request->travelerPhone,
                'depname'=>$request->departureName,
                'msg'=>$request->Message,
              ],function($mail) use($request){
               //$mail->from('info@tlakapp.com');
                $mail->to('support@watconsultingservices.com')->subject('Contact Support from TLAK App');
               
            });
            
            $status = array(
            'error' => false,
            'message' => 'Bingo! Message Send Successfully!!'
            ); 
          return response()->json($status, 200);
        }      
  
  }       
  