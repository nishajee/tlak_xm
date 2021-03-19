<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\Tenant;
use App\Itinerary;
use App\DepartureManager;
use App\DepartureGuide;
use App\Communication;
use App\Placard;
use App\TourPckageUpcommingTourPackage;
use App\FeedDetail;
use App\Banner;
use App\Avatar;
use App\GetFeed;
use App\HistoryTraveler;
use App\RealTimeTracking;
use App\VersionControl;
use App\TimeSlot;
use App\TermAndCondition;
use DB;
class ApiCommanController extends Controller
{  

    public function comman(Request $request)
    {
        $token = $request->token; 
        // $travelerid = $request->travelerId;
        // $travelerid = 643;
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
                'message' => $message[0]
            ];
            return Response($status);
        }

        $trvrl_details =  Traveler::where('token', $token)->select('id', 'name', 'crown_no','tour_package_id')->first();
        $crown_no = Traveler::getCrownNo($trvrl_details->id);

        $tour_details =  TourPckage::where('id', $trvrl_details->tour_package_id)->select('pname','user_id','tenant_id','start_date','end_date','agent_name','banner_image')->first();

        //Support Details
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.id as travelerId','travelers.token','travelers.tenant_id as tenantId')->first();
        if($traveler){
        $tour_package_id=$traveler->pkgId;
        $tourPackage= TourPckage::where('id', $tour_package_id)
                ->where(function($q) {
                            $q->where('status', 2);
                        })
                ->first();
        $logo = Tenant::select('company_logo')->where('tenant_id', $traveler->tenantId)->first();
        $company_logo = ($logo->company_logo == '' || $logo->company_logo == null)?'': 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/company/'.$logo->company_logo;     
        if($tourPackage){
            $depMana = DepartureManager::where('tour_package_id', $tour_package_id)->select('id as depManagerId','name as depManagerName','email as depManagerEmail','phone as depManagerPhone')->get();

            if(count($depMana) >= 1){
                foreach ($depMana as $value) {
                    $trvl_manag = Traveler::where(['tour_package_id'=> $tour_package_id,'people_id'=>$value->depManagerId, 'type'=>'Manager'])->first();
                    if($trvl_manag){
                        $profileImage = ($trvl_manag->profile_picture == '' || $trvl_manag->profile_picture == null)? url('images/uploads/male.png'):'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$trvl_manag->profile_picture;
                        $managerEmail = ($trvl_manag->traveler_email == '' || $trvl_manag->traveler_email == null)?'':$trvl_manag->traveler_email;
                        $managerPhone = ($trvl_manag->phone == '' || $trvl_manag->phone == null)?'':$trvl_manag->phone;

                        $depManager[] = ['depManagerName'=>$trvl_manag->name,'depManagerPhone'=>$managerPhone,'depManagerEmail'=>$managerEmail, 'profileImage'=>$profileImage];
                    }
                    else{
                        $managerEmail = ($value->depManagerEmail == '' || $value->depManagerEmail == null)?'':$value->depManagerEmail;
                        $managerPhone = ($value->depManagerPhone == '' || $value->depManagerPhone == null)?'':$value->depManagerPhone;

                        $depManager[] = ['depManagerName'=>$value->depManagerName,'depManagerPhone'=>$managerPhone,'depManagerEmail'=>$managerEmail, 'profileImage'=>url('images/uploads/male.png')];
                    }
                        
                }
            }
            else{
                $depManager = [];
            }

            $depGuide = DepartureGuide::where('tour_package_id', $tour_package_id)->select('id as depGuideId','name as depGuideName','location as depGuideLocation','phone as depGuidePhone')->get();

            if(count($depGuide) >= 1){
                foreach ($depGuide as $value) {
                    $trvl_guide = Traveler::where(['tour_package_id'=> $tour_package_id,'people_id'=>$value->depGuideId, 'type'=>'Manager'])->first();

                    if($trvl_guide){
                        $profileImage = ($trvl_guide->profile_picture == '' || $trvl_guide->profile_picture == null)? url('images/uploads/male.png'):'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$trvl_guide->profile_picture;

                        $guidePhone = ($trvl_guide->phone == '' || $trvl_guide->phone == null)?'':$trvl_guide->phone;

                        $depGuides[] = ['depGuideName'=>$trvl_guide->name,'depGuideLocation'=>$value->depGuideLocation ,'depGuidePhone'=>$guidePhone, 'profileImage'=>$profileImage];
                    }

                    else{
                        $guidePhone = ($value->depGuidePhone == '' || $value->depGuidePhone == null)?'':$value->depGuidePhone;

                        $depGuides[] = ['depGuideName'=>$value->depGuideName,'depGuideLocation'=>$value->depGuideLocation ,'depGuidePhone'=>$guidePhone, 'profileImage'=>url('images/uploads/male.png')];
                    }
                }
            }
            else{
                $depGuides = [];
            }
            $comanyContact = Communication::where('tour_package_id', $tour_package_id)->select('name as companyPersonName','email as companyPersonEmail','phone as companyPersonPhone')->get();

            if(count($comanyContact) >= 1){
                foreach ($comanyContact as $value) {
                    if($value->companyPersonEmail == '' || $value->companyPersonEmail == null){
                    $comanyContacts[] = ['companyPersonName'=>$value->companyPersonName,'companyPersonPhone'=>$value->companyPersonPhone,'companyPersonEmail'=>'','profileImage'=>'https://account.tlakapp.com/tlak/images/uploads/male.png'];
                    }
                    else{
                    $comanyContacts[] = ['companyPersonName'=>$value->companyPersonName,'companyPersonPhone'=>$value->companyPersonPhone,'companyPersonEmail'=>$value->companyPersonEmail,'profileImage'=>'https://account.tlakapp.com/tlak/images/uploads/male.png'];
                    }
                }
            }
            else{
                $comanyContacts = [];
            }
            $plaCards = Placard::where('tour_package_id', $tour_package_id)->select('placard as placardName','placard_detail as placardDetail')->first();
            if($plaCards){
                if($plaCards->placardDetail == null || $plaCards->placardDetail == ''){
                    $plaCard = ['placardName' => $plaCards->placardName, 'placardDetail' => ''];
                }
                else{
                    $plaCard = ['placardName' => $plaCards->placardName, 'placardDetail' => $plaCards->placardDetail];
                }
            }
            else{
                $plaCard = (object)[];
            }

            }
            }
            //End support details

        $optionalDeparture = TourPckageUpcommingTourPackage::join('upcomming_tour_packages','upcomming_tour_packages.id','=','tour_pckage_upcomming_tour_packages.upcomming_tour_package_id')
              ->where('tour_pckage_upcomming_tour_packages.tour_pckage_id', $trvrl_details->tour_package_id)
              ->where('tour_pckage_upcomming_tour_packages.status', 1)
              ->select('upcomming_tour_packages.id as optionalDepartureId','upcomming_tour_packages.pname as optionalDepartureName','upcomming_tour_packages.promo_content as promoContent','upcomming_tour_packages.contact_email as email','upcomming_tour_packages.contact_phone as phone','upcomming_tour_packages.description','upcomming_tour_packages.background_image as optionalDepartureImage')->get();
        // dd($optionalDeparture);
        $optionalDepartures = count($optionalDeparture);
        if($optionalDepartures >= 1){
            foreach($optionalDeparture as $otionalD){
                $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                $avatar_url = $src.'upcommeingpkg/';                    
                $itinerary[] = ['optionalDepartureId'=>$otionalD->optionalDepartureId,'optionalDepartureName'=>$otionalD->optionalDepartureName,'promoContent'=>$otionalD->promoContent,'description'=>$otionalD->description,'email'=>$otionalD->email,'phone'=>$otionalD->phone,'optionalDepartureImage'=>$avatar_url.$otionalD->optionalDepartureImage];
            }
        }
        else{
          $itinerary = 'Upcoming Tours Not available right now you will be notified whenever available';
        }

        $terms = TermAndCondition::where('tour_package_id', $trvrl_details->tour_package_id)->first();
        if($terms){
            $data = ($terms->terms == '' || $terms->terms == null)?'':$terms->terms;
            $termandconditions = $data;
        }
        else{
            $termandconditions = '';
        }

        $bannerImage = ($tour_details->banner_image == '' || $tour_details->banner_image == null)?'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/banner_image/AjyYe1610100911.jpg':'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/banner_image/'.$tour_details->banner_image;
        $agent_name = ($tour_details->agent_name == '' || $tour_details->agent_name == null)?'':$tour_details->agent_name;
        if($trvrl_details){
            $status = array(
                'status' => true,
                'message' => 'Bingo! Success!',
                'packageId' => $trvrl_details->tour_package_id,
                'packageName' => $tour_details->pname,
                'agentOrCompanyName' => $agent_name,
                'startDate' => $tour_details->start_date,
                'endDate' => $tour_details->end_date,
                'userId' => $tour_details->user_id,
                'tenantId' => $tour_details->tenant_id,
                'logo' => $company_logo,
                'bannerImage' => $bannerImage,
                'crownNumber' => $crown_no,
                    'supportDetails' => array(
                        'departureManager' => $depManager,
                        'departureGuide' => $depGuides,
                        'comanyContact' => $comanyContacts,
                        'placard' => $plaCard
                    ),
                'upcomingTours' => $itinerary,
                'terms' => $termandconditions,
                'aboutTlak' => 'https://www.tlakapp.com/faqs.php'
            );
        }
        else{
            $status = array(
                        'status' => false,
                        'message' => 'Invalid Response',
                      );
        }
        return response()->json($status, 200);

    }

    public function travelerList(Request $request)
    {
        $token = $request->token;
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
                'message' => $message[0]
            ];
            return Response($status);
        }

        $pkg_id =  Traveler::where('token', $token)->select('tour_package_id','tenant_id','id')->first();
        if($pkg_id == null || $pkg_id == ''){
            
            $status = array(
                'status' => false,
                'message' => 'Invalid Response1',
            );
            return response()->json($status, 200);
        }
        $traveler =  Traveler::where('tour_package_id', $pkg_id->tour_package_id)->where('id','!=',$pkg_id->id)->orderBy('name')->get();
        $tourpackage = TourPckage::where('id',$pkg_id->tour_package_id)->first();
        $trvlr_list = array();
        foreach ($traveler as $value) {
            $total_likes = FeedDetail::where('traveler_id', $value->id)->sum('likes');
            $total_shares = FeedDetail::where('traveler_id', $value->id)->sum('shares');
            $totalContribution = GetFeed::where('traveler_id', $value->id)->count();
            $historyTraveler = HistoryTraveler::where('traveler_id',$value->id)->get();
            $travelerDOB = ($value->birth == '' || $value->birth == null) ? ('') : ($value->birth);
            $travelerEmail = ($value->traveler_email == '' || $value->traveler_email == null) ? ('') : ($value->traveler_email);
            $travelerPhone = ($value->phone == '' || $value->phone == null) ? ('') : ($value->phone);
            $total = count($historyTraveler);
            if($value->gender == 'male'){
                $gender = 'male';
            }
            elseif($value->gender == 'female') {
                $gender = 'female';
            }
            else{
                $gender = 'undefined';
            }
            $rand_image = $this->getRandomImage($value->gender);
            $profilePicture = "https://account.tlakapp.com/media/avatars/".$gender."/".$rand_image;
            $bannerImage = ($tourpackage->banner_image == '' || $tourpackage->banner_image == null) ? (url("images/uploads/banner_image.jpeg")) : ('https://s3-tlak-bucket.s3-us-west-2.amazonaws.com/banner_image/'.$tourpackage->banner_image);
            // $bannerImage =  url("images/uploads/banner_image.jpeg") ;

            $crown_no = Traveler::getCrownNo($value->id);
            
            $traveler_name = ($value->type == 'Manager')?$value->name.' (Manager)':$value->name;
            if($value->profile_picture == '' || $value->profile_picture == null) {
                $trvlr_list[] = ['token'=>$value->token,'travelerId'=>$value->id,'travelerName'=>$traveler_name,'travelerEmail'=>$travelerEmail,'travelerPhone'=>$travelerPhone,'crownNo'=>$crown_no,'travelerGender'=>$gender,'travelerDOB'=>$travelerDOB,'profilePicture'=>$profilePicture,'like'=>$total_likes,'share'=>$total_shares,'totalContribution'=>$totalContribution,'totalDeparture'=>$total,'bannerImage'=>$bannerImage,'type'=>$value->type];
            }
            else{
                $trvlr_list[] = ['token'=>$value->token,'travelerId'=>$value->id,'travelerName'=>$traveler_name,'travelerEmail'=>$travelerEmail,'travelerPhone'=>$travelerPhone,'travelerGender'=>$gender,'travelerDOB'=>$travelerDOB,'crownNo'=>$crown_no,'profilePicture'=>'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$value->profile_picture,'like'=>$total_likes,'share'=>$total_shares,'totalContribution'=>$totalContribution,'totalDeparture'=>$total,'bannerImage'=>$bannerImage,'type'=>$value->type];
            }
        }
        if($pkg_id && !empty($trvlr_list)){
            $status = array(
                'status' => true,
                'message' => 'Bingo! Success!',
                'packageId' => $pkg_id->tour_package_id,
                'tenantId' => $pkg_id->tenant_id,
                'travelerList' => $trvlr_list
                );
        }
        else{
            $status = array(
                        'status' => false,
                        'message' => 'Invalid Response',
                      );
        }
        return response()->json($status, 200);
    }
    public function banner(Request $request)
    {
        $token = $request->token; 
        // $travelerid = $request->travelerId;
        $travelerid = 643;
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'status' => false,
                'message' => $message[0]
            ];
            return Response($status);
        }

        $status = array(
                        'status' => false,
                        'message' => 'No data found!',
                    );
        return response()->json($status, 200);

        // $banner = Banner::select('title', 'description', 'banner_image', 'content_image')->where('id',1)->first();

        // if($banner){
        //     $status = array(
        //         'status' => true,
        //         'message' => 'Success!',
        //         'title' => $banner->title,
        //         'description' => $banner->description,
        //         'bannerImage' => $banner->banner_image,
        //         'contentImage' => $banner->content_image,
        //         );
        // }
        // else{
        //     $status = array(
        //                 'status' => true,
        //                 'message' => 'No data found!',
        //             );
        // }
        // return response()->json($status, 200);

    }

    public function realTimeData(Request $request)
    {
        $current_time = date("H:i:s");
        $slots = TimeSlot::all();
        foreach ($slots as $key => $value) {
            $exist = TimeSlot::where('start_time', '<=', $current_time)->where('end_time','>=', $current_time)->first();
            if($exist){
                $slot_id = $exist->id;
            }
        }

        $timezone = date_default_timezone_get();
        $date = date('Y-m-d H:i:s');
        $data = new RealTimeTracking();
        $data->tour_package_id = $request->pkgId;
        $data->traveler_id = $request->travelerId;
        $data->tenant_id = $request->tenantId;
        $data->name = $request->travelerName;
        $data->lattitude = $request->latitude;
        $data->longitude = $request->longitude;
        $data->adress = $request->address;
        $data->datetime = $date;
        $data->timezone = $timezone;
        $data->time_slot_id = $slot_id;
        $result = $data->save();
        if($result){
            $status = array(
                        'status' => true,
                        'message' => 'Successfully!!',
            );
        }
        else{
            $status = array(
                        'status' => false,
                        'message' => 'Something wrong!!',
            );
        }
        return response()->json($status, 200);

    }

    public function versionCode(Request $request)
    {
        if(isset($request->versionCode))
        {
            $version = new VersionControl();
            $version->version_code = $request->versionCode;
            $result = $version->save();
        }
        $response_data = VersionControl::latest()->first();
        $status = array(
            'status' => true,
            'message' => 'Successfully!!',
            'versionCode' => $response_data->version_code
        );
        return response()->json($status, 200);
    }


    function getRandomImage($gender)
    {
        if($gender == 'male'){
            $images = Avatar::where('gender', 'male')->pluck('images')->toArray();
            $randomElement = $images[array_rand($images, 1)];
            return $randomElement;
        }
        elseif ($gender == 'female') {
            $images = Avatar::where('gender', 'female')->pluck('images')->toArray();
            $randomElement = $images[array_rand($images, 1)];
            return $randomElement;
        }
        else{
            $images = Avatar::pluck('images')->toArray();
            $randomElement = $images[array_rand($images, 1)];
            return $randomElement;
        }
    }
}
