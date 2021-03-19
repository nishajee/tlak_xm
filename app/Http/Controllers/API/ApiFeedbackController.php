<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\Traveler;
use App\Feedback;

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
            if($feedbacks->rating == '' || $feedbacks->rating == null){
                $ratings= '';
            }
            else{
                $ratings= $feedbacks->rating;
            }
            
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
  
  }       
  