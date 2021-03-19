<?php
namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\TourPckage;
use App\People;
use DB;
use DateTime;
use DateTimeZone;
use App\Traveler;
use App\GetFeed;
use App\FeedComment;
use App\FeedImage;
use App\FeedVideo;
// use App\FeedText;
use App\FeedDetail;
use App\ItineraryLocation;
use App\Location;

class ApiFeedController extends Controller
{
	public function setContribution(Request $request)
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
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.name as travelerName', 'travelers.token', 'travelers.tenant_id as tenantId', 'travelers.device_id as deviceId')
            ->first();
        $date = date("Y-m-d H:i:s");
        $pieces = explode(" ", $date);
            
        $tour_package_id = $traveler->pkgId;
        $traveler_id = $traveler->travelerId;
        $getfeed = new GetFeed();
        $getfeed->traveler_id = $request->travelerId;
        $getfeed->feed_type = $request->feedType;
        $getfeed->tour_package_id = $tour_package_id;
        $getfeed->tenant_id = $traveler->tenantId;
        $getfeed->feed_created_date = $pieces[0];
        $getfeed->feed_created_time = $pieces[1];
        $getfeed->address = $request->address;
        $getfeed->description = $request->description;
        $getfeed->save();
        
        $last_id = $getfeed->id;
        $android_devices = Traveler::where('tour_package_id', $tour_package_id)
                            ->where('device_type', 'android')
                            ->whereNotNull('device_id')
                            ->get()
                            ->unique('device_id');
        $ios_devices = Traveler::where('tour_package_id', $tour_package_id)
                            ->where('device_type', 'ios')
                            ->whereNotNull('device_id')
                            ->get()
                            ->unique('device_id');                    

        foreach ($android_devices as $key => $devices) {
            if($devices->device_id != $traveler->deviceId){
                Traveler::feedUploadNotification($devices->device_id,"TLAK",$traveler->travelerName,$request->feedType);
            }
        }

        foreach ($ios_devices as $key => $devices_ios) {
            if($devices_ios->device_id != $traveler->deviceId){
                Traveler::feedUploadNotificationIos($devices_ios->device_id,"TLAK",$traveler->travelerName,$request->feedType);
            }
        }
        
        if($request->feedType == 'video') {
            $base64String= $request->feedData;
            $video = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));
            $videoName = str_random(5).time() . '.mp4';

            $p = Storage::disk('s3')->put('traveller/video'.'/'.$videoName, $video, 'public');
            $video = new FeedVideo();
            $video->feed_id = $last_id;
            $video->videos = $videoName;
            $video->save();
        }
        elseif($request->feedType == 'image'){
            $base64String= $request->feedData;
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));
            $imageName = str_random(5).time() . '.png';

            $p = Storage::disk('s3')->put('traveller/contribution'.'/'.$imageName, $image, 'public'); 
            $feed_image = new FeedImage();
            $feed_image->feed_id = $last_id;
            $feed_image->images = $imageName;
            $feed_image->save();
        }
        else{

        }
        if($last_id) {
            $status = array(
                'status' => true, 
                'message' => 'Bingo! Success!!'
            );
            return response()->json($status, 200);
        }
        else{
            $status = array(
                'status' => false,
                'message' => 'Opps! Invalid response!!'
            );
            return response()->json($status, 200);
        }

	}

	public function getContribution(Request $request)
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
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.token', 'travelers.tenant_id as tenantId', 'travelers.name as travelerName', 'travelers.profile_picture as profilePicture')
            ->first();
        if($traveler){
            $data = GetFeed::where('traveler_id', $traveler->travelerId)->select('get_feeds.id as feedId','get_feeds.traveler_id as travelerId', 'get_feeds.feed_type as feedType', 'get_feeds.feed_name as feedName', 'get_feeds.description as description', 'get_feeds.address as address', 'get_feeds.feed_created_date as createdDate', 'get_feeds.feed_created_time as createdTime')->get();
            $profilePicture = ($traveler->profilePicture == null || $traveler->profilePicture == '')?'':'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profilePicture;
            $response = array();
            foreach ($data as $key => $value) {
                $description = ($value->description == '' || $value->description == null) ? '' : $value->description;
                $address = ($value->address == '' || $value->address == null)?'':$value->address;
                if($value->feedType == 'image'){
                    $feeds = FeedImage::select('id','images')->where('feed_id', $value->feedId)->get();

                    $response[] = ['travelerId'=>$value->travelerId, 'travelerName'=>$traveler->travelerName,'profilePicture'=>$profilePicture, 'feedType'=>$value->feedType, 'address'=>$address, 'createdDate'=>$value->createdDate, 'createdTime'=>$value->createdTime, 'feedName'=>$feeds, 'description'=>$description];
                }
                elseif($value->feedType == 'video'){
                    $feeds = FeedVideo::select('id','videos')->where('feed_id', $value->feedId)->get();
                    $response[] = ['travelerId'=>$value->travelerId, 'travelerName'=>$traveler->travelerName, 'profilePicture'=>$profilePicture,'feedType'=>$value->feedType, 'address'=>$address, 'createdDate'=>$value->createdDate, 'createdTime'=>$value->createdTime, 'feedName'=>$feeds, 'description'=>$description];
                }
                else{
                    $response[] = ['travelerId'=>$value->travelerId, 'travelerName'=>$traveler->travelerName, 'profilePicture'=>$profilePicture,'feedType'=>$value->feedType, 'address'=>$address, 'createdDate'=>$value->createdDate, 'createdTime'=>$value->createdTime, 'feedName'=>$feeds, 'description'=>$description];
                } 
            }
            if(!empty($response)){
                $status = array(
                    'status' => true, 
                    'message' => 'Bingo! Success!',
                    'traveler' => $traveler,
                    'url' => 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/contribution/',
                    'contribution' => $response,
                    );
                return response()->json($status, 200);
            }
            else{
                $status = array(
                    'status' => false, 
                    'message' => 'No Data Found'
                    );
                return response()->json($status, 200);
            }
        }
        else{
            $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
                );
            return response()->json($status, 200);
        }
	}

    public function getFeed(Request $request)
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
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.token', 'travelers.tenant_id as tenantId')
            ->first();
        if($traveler){
            $package = TourPckage::where('id', $traveler->pkgId)->first();
            $bannerImage = ($package->banner_image == '' || $package->banner_image == null) ? (url("images/uploads/banner_image.jpeg")) : ('https://s3-tlak-bucket.s3-us-west-2.amazonaws.com/banner_image/'.$package->banner_image);
            $end_date = strtotime($package->end_date);
            $used_location_id = ItineraryLocation::where('tour_package_id' , $traveler->pkgId)->pluck('location_id')->toArray();
            $used_array_loc = array_unique(json_decode(json_encode($used_location_id)));
            $locations = array();
            $i = 0;
            foreach ($used_array_loc as  $ids) {
                if($i == 0){
                    $location_name = Location::where('id', $ids)->first();
                    array_push($locations, $location_name->name.', ');
                }
                elseif($i == 1){
                    $location_name = Location::where('id', $ids)->first();
                    array_push($locations, $location_name->name);
                }
                elseif($i == 2){
                    array_push($locations, ', +2');
                }
                else{
                }
                $i++;
            }
            $location = implode(" ",$locations);
            $durations = $package->total_days.' Days '.$package->total_nights.' Nights';
            $data = GetFeed::where('tour_package_id', $traveler->pkgId)->select('get_feeds.id as feedId','get_feeds.traveler_id as travelerId', 'get_feeds.feed_type as feedType', 'get_feeds.feed_name as feedName', 'get_feeds.description as description','get_feeds.address as feedAddress','get_feeds.feed_created_date as feedDate','get_feeds.feed_created_time as feedTime')
            ->orderBy('id', 'desc')
            ->get();
            $response = array();
            foreach ($data as $key => $value) {

                $traveler_name = Traveler::where('id', $value->travelerId)->first();
                $profilePicture = ($traveler_name->profile_picture == '' || $traveler_name->profile_picture == null) ? 'https://stage.tlakapp.com/media/users/default.jpg' : 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler_name->profile_picture;
                $feedName = ($value->feedName == '' || $value->feedName == null) ? '' : 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/contribution/'.$value->feedName;
                $description = ($value->description == '' || $value->description == null) ? '' : $value->description;
                $feedAddress = ($value->feedAddress == '' || $value->feedAddress == null) ? '' : $value->feedAddress;

                $liked = FeedDetail::where('feed_id',$value->feedId)->where('traveler_id', $traveler->travelerId)->first();

                if($liked)
                    $like = ($liked->likes == '1') ? '1' : '0';
                else
                    $like = '0';

                $total_likes = FeedDetail::where('feed_id',$value->feedId)->where('likes', 1)->count();
                $total_comments = FeedDetail::where('feed_id',$value->feedId)->sum('comments');
                $total_shares = FeedDetail::where('feed_id',$value->feedId)->sum('shares');

                $created_date_time=$value->feedDate.' '.$value->feedTime;
                $duration = $this->dateDiff($created_date_time);
                $created_date = strtotime($value->feedDate);
                $visit = ($end_date > $created_date) ? 'Visited' : 'Visiting';

                $crown_no = Traveler::getCrownNo($value->travelerId);
                $totalContribution = GetFeed::where('traveler_id', $value->travelerId)->count();


                if($value->feedType == 'image'){
                    $feeds = FeedImage::select('id','images')->where('feed_id', $value->feedId)->get();

                    $response[] = ['feedId'=>$value->feedId,'token'=>$traveler_name->token,'travelerId'=>$value->travelerId,'travelerName'=>$traveler_name->name,'profilePicture'=>$profilePicture, 'feedType'=>$value->feedType, 'url'=>'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/contribution/', 'feedName'=>$feeds, 'description'=>$description,'address'=>$feedAddress,'feedDate'=>$value->feedDate,'likes'=>$total_likes,'comments'=>$total_comments,'shares'=>$total_shares,'duration'=>$duration,'liked'=>$like,'visit'=>$visit,'locations'=>$location,'durations'=>$durations,'bannerImage'=>$bannerImage,'crownNo'=>$crown_no,'totalContribution'=>$totalContribution,'totalDeparture'=>1]; 
                }
                elseif($value->feedType == 'video'){
                    $feeds = FeedVideo::select('id','videos')->where('feed_id', $value->feedId)->get();
                    $response[] = ['feedId'=>$value->feedId,'token'=>$traveler_name->token,'travelerId'=>$value->travelerId,'travelerName'=>$traveler_name->name,'profilePicture'=>$profilePicture, 'feedType'=>$value->feedType, 'url'=>'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/contribution/', 'feedName'=>$feeds, 'description'=>$description,'address'=>$feedAddress,'feedDate'=>$value->feedDate,'likes'=>$total_likes,'comments'=>$total_comments,'shares'=>$total_shares,'duration'=>$duration,'liked'=>$like,'visit'=>$visit,'locations'=>$location,'durations'=>$durations,'bannerImage'=>$bannerImage,'crownNo'=>$crown_no,'totalContribution'=>$totalContribution,'totalDeparture'=>1]; 
                }
                else{
                    $response[] = ['feedId'=>$value->feedId,'token'=>$traveler_name->token,'travelerId'=>$value->travelerId,'travelerName'=>$traveler_name->name,'profilePicture'=>$profilePicture, 'feedType'=>$value->feedType,'url'=>'', 'feedName'=>$feedName, 'description'=>$description,'address'=>$feedAddress,'feedDate'=>$value->feedDate,'likes'=>$total_likes,'comments'=>$total_comments,'shares'=>$total_shares,'duration'=>$duration,'liked'=>$like,'visit'=>$visit,'locations'=>$location,'durations'=>$durations,'bannerImage'=>$bannerImage,'crownNo'=>$crown_no,'totalContribution'=>$totalContribution,'totalDeparture'=>1];
                }
                
            }
            if(!empty($response)){
                $status = array(
                    'status' => true, 
                    'message' => 'Bingo! Success!',
                    'traveler' => $traveler,
                    'contribution' => $response,
                    );
                return response()->json($status, 200);
            }
            else{
                $status = array(
                    'status' => false, 
                    'message' => 'No Data Found'
                    );
                return response()->json($status, 200);
            }
        }
        else{
            $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid token'
            );
        return response()->json($status, 200);
        }    
    }

    public function feedActivity(Request $request)
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
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId','travelers.name as travelerName', 'travelers.token', 'travelers.tenant_id as tenantId')
            ->first();
        $feedAction = $request->feedAction;    
        $feedId = $request->feedId;                                          
        if ($traveler) {
            $feed_detail = FeedDetail::where('traveler_id', $traveler->travelerId)->where('feed_id', $feedId)->first();
            $device_id = GetFeed::join('travelers', 'travelers.id','=','get_feeds.traveler_id')
                                        ->where('get_feeds.id', $feedId)
                                        ->select('travelers.device_id as deviceId')
                                        ->first();
            if($feed_detail){
                $no_of_comments = $feed_detail->comments;
                $no_of_shares = $feed_detail->shares;
                if($feedAction == 'share'){
                    $total_share = $no_of_shares + 1;
                    $feed_detail_obj = FeedDetail::find($feed_detail->id);
                    $feed_detail_obj->shares = $total_share;
                    $feed_detail_obj->save();
                }
                elseif ($feedAction == 'comment') {
                    $total_comments = $no_of_comments + 1;
                    $feed_detail_obj = FeedDetail::find($feed_detail->id);
                    $feed_detail_obj->comments = $total_comments;
                    $feed_detail_obj->save();

                    $feed_comments = new FeedComment();
                    $feed_comments->feed_id = $feedId;
                    $feed_comments->traveler_id = $traveler->travelerId;
                    $feed_comments->feed_details_id = $feed_detail->id;
                    $feed_comments->comments = $request->commentContent;
                    $feed_comments->comment_date = $request->date;
                    $feed_comments->time = $request->time;
                    $feed_comments->save();
                    Traveler::commentNotification($device_id->deviceId,"TLAK",$traveler->travelerName,$feedId);
                }
                else{
                    $feed_detail_obj = FeedDetail::find($feed_detail->id);
                    $feed_detail_obj->likes = $request->likeAction;
                    $feed_detail_obj->save();
                    if($request->likeAction == 1){
                        Traveler::likeNotification($device_id->deviceId,"TLAK",$traveler->travelerName);
                    }
                }
            }
            else{
                $feedDetails = new FeedDetail();
                if($feedAction == 'share'){
                    $feedDetails->traveler_id = $traveler->travelerId;
                    $feedDetails->feed_id = $feedId;
                    $feedDetails->likes = 0;
                    $feedDetails->shares = 1;
                    $feedDetails->comments = 0;
                    $feedDetails->save();
                }
                elseif ($feedAction == 'comment') {
                    $feedDetails->traveler_id = $traveler->travelerId;
                    $feedDetails->feed_id = $feedId;
                    $feedDetails->likes = 0;
                    $feedDetails->shares = 0;
                    $feedDetails->comments = 1;
                    $feedDetails->save();
                    $last_id = $feedDetails->id;
                    $feed_comments = new FeedComment();
                    $feed_comments->feed_id = $feedId;
                    $feed_comments->traveler_id = $traveler->travelerId;
                    $feed_comments->feed_details_id = $last_id;
                    $feed_comments->comments = $request->commentContent;
                    $feed_comments->comment_date = $request->date;
                    $feed_comments->time = $request->time;
                    $feed_comments->save();
                    Traveler::commentNotification($device_id->deviceId,"TLAK",$traveler->travelerName,$feedId);
                }
                else{
                    $feedDetails->traveler_id = $traveler->travelerId;
                    $feedDetails->feed_id = $feedId;
                    $feedDetails->likes = 1;
                    $feedDetails->shares = 0;
                    $feedDetails->comments = 0;
                    $feedDetails->save();
                    Traveler::likeNotification($device_id->deviceId,"TLAK",$traveler->travelerName);
                } 
            }
            $total_likes = FeedDetail::where('feed_id',$feedId)->where('likes', 1)->count();
            $total_comments = FeedDetail::where('feed_id',$feedId)->sum('comments');
            $total_shares = FeedDetail::where('feed_id',$feedId)->sum('shares');
            
            $comment_no = ($total_comments == '0')?"0":$total_comments;
            $share_no = ($total_shares == '0')?"0":$total_shares;


            $status = array(
                'status' => true, 
                'message' => 'Bingo! Success!!',
                'likes' => $total_likes,
                'comments' => $comment_no,
                'shares' => $share_no
            );
            return response()->json($status, 200);    
        }
        else{
            $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
            );
            return response()->json($status, 200);
        }
    }

    public function getComment(Request $request)
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
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.token', 'travelers.tenant_id as tenantId')
            ->first(); 
        if($traveler){
            $all_comments = FeedComment::join('travelers', 'travelers.id','=', 'feed_comments.traveler_id')
                                ->where('feed_comments.feed_id', $request->feedId)
                                ->select('travelers.name as travelerName','feed_comments.comments','feed_comments.comment_date','feed_comments.time')
                                ->orderBy('feed_comments.id','asc')
                                ->get();
            
            $comments = array();
            foreach ($all_comments as $key => $value) {
                $comments[] = ['travelerName'=>$value->travelerName,'comment'=>$value->comments, 'date'=>$value->comment_date, 'time'=>$value->time];
            }

            if(!empty($comments)){
                $status = array(
                    'status' => true, 
                    'message' => 'Bingo! Success!',
                    'traveler' => $traveler,
                    'comments' => $comments,
                    );
                return response()->json($status, 200);
            }
            else
            {
                $status = array(
                    'status' => false,
                    'message' => 'No Comments!'
                );
                return response()->json($status, 200);
            }
        }
        else{
            $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
            );
            return response()->json($status, 200);
        }    

    }

    function dateDiff($date)
    {
        $mydate= date("Y-m-d H:i:s");
        $theDiff="";
        //echo $mydate;//2014-06-06 21:35:55
        $datetime1 = date_create($date);
        $datetime2 = date_create($mydate);
        $interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%s Seconds %i Minutes %h Hours %d days %m Months %y Year    Ago')."<br>";
        $min=$interval->format('%i');
        $sec=$interval->format('%s');
        $hour=$interval->format('%h');
        $mon=$interval->format('%m');
        $day=$interval->format('%d');
        $year=$interval->format('%y');
        if($interval->format('%i%h%d%m%y')=="00000") {
            //echo $interval->format('%i%h%d%m%y')."<br>";
            return $sec." Seconds";
        } else if($interval->format('%h%d%m%y')=="0000"){
            return $min." Minutes";
        } else if($interval->format('%d%m%y')=="000"){
            return $hour." Hours";
        } else if($interval->format('%m%y')=="00"){
            
            if($day<3){
                return $day." Days";
            }
            else{
               return $date; 
            }
        }
        else  { return $date; }
         
    }
}