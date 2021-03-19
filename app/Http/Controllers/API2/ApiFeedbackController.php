<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Traveler;
use App\Feedback;
use App\TlakSupport;

class ApiFeedbackController extends Controller
{   
    public function feedbackApp(Request $request){

        $data=$request->all();
        // dd($data);
        $traveler_name = $request->travelerName;
        $phone = $request->travelerPhone; 
        $email = $request->travelerEmail;
        $package_name = $request->pkgName;
        $feedback = $request->message;
        $tenant_id = $request->tenantId;
        $package_id = $request->PackageID;
        $rating = $request->rating;
        //dd($package_id);


        $validator = Validator::make($request->all(),[
            'travelerName' => 'required',
            'pkgName' => 'required',
            'message' => 'required',
            'tenantId' =>'required',
            'PackageID' => 'required',
            'rating' => 'required',
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
        }
            $feedbacks = new Feedback; 
            $feedbacks->traveler_name = $traveler_name;
            $feedbacks->phone = $phone;
            $feedbacks->email = $email;
            $feedbacks->tenant_id = $tenant_id;
            $feedbacks->package_name = $package_name;
            $feedbacks->feedback = $feedback;
            $feedbacks->tour_package_id = $package_id;
            $feedbacks->rating = $rating;
            $feedbacks->status = 1;
            $feedbacks->save();
            $traveler_names= $feedbacks->traveler_name;
            $pkg_name= $feedbacks->package_name;
            $phones = $feedbacks->phone;
            $emails= $feedbacks->email;
            $ratings= $feedbacks->rating;
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!',
            'travelerName' => $traveler_names,
            'pkgName' => $pkg_name,
            'travelerPhone' => $phones,
            'travelerEmail' => $emails,
            'rating' => $ratings,
            ); 
          return response()->json($status, 200);
    }      

    public function tlaksupport(Request $request)
    {   
        $token = $request->token;
        $validator = Validator::make($request->all() , ['token' => 'required']);

        if ($validator->fails())
        {
            $message = $validator->errors()
                ->all();

            $status = ['status' => false, 'message' => $message[0]];
            return Response($status);
        }
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.name as travelerName')
            ->first();
        $support = new TlakSupport();
        $support->traveler_id =  $traveler->travelerId;
        $support->name =  $traveler->travelerName;
        $support->tour_package_id =  $traveler->pkgId;
        $support->email =  $request->email;
        $support->phone =  $request->phone;
        $support->content =  $request->content;
        $save = $support->save();
        if($save){
            $status = array(
                'error' => false,
                'message' => 'Your query has been successfully submitted. We will get back to you soon.',
            ); 
            return response()->json($status, 200);
        }
        else{
            $status = array(
                'error' => true,
                'message' => 'Opps! Invalid response!!'
            ); 
            return response()->json($status, 200);
        }
    }
  
}       
  