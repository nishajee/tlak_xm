<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Storage;
use Image;
use App\TourPckage;
use App\Traveler;
use App\DepartureGuide;
use App\DepartureManager;
use App\Communication;
use App\Placard;
use App\GroupChat;
use App\GroupTraveler;
use App\TravelerToManagerChat;
class ApiChatController extends Controller
{   
    public function chatPeoplegroupList(Request $request){
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $user_type = $request->UserType;
        $user_id = $request->UserID;
        //dd($traveller_manager);

        $validator = Validator::make($request->all(),[
            'TenantID' => 'required',
            'PackageID' => 'required',
            'UserType' => 'required',
            'UserID' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => "$message[0]"
            ];
            return Response($status);
        }
        $tourPackage = TourPckage::where('id',$tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first();
          
          if($tourPackage){
            $groupT = GroupTraveler::where('tour_package_id', $tourPackage->id)->select('type','tour_package_id as PackageID','id as groupId','group_name as groupName')->first();

            $tra = Traveler::where('tour_package_id', $tour_package_id)
                ->select('id as travelerId','name as travelerName','tpassword as password','userid as quickUserID','occupant_id as occupantId','type')
                ->get(); 
                $groupst = array();
                foreach($tra as $key => $grp){
                    if($grp->occupantId != '' || $grp->occupantId != null){
                    	$aaa = ['travelerId'=>$grp->travelerId,'travelerName'=>$grp->travelerName,'password'=>$grp->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>$grp->occupantId,'type'=>$grp->type];
                  	}
                  	else{
                  		$aaa = ['travelerId'=>$grp->travelerId,'travelerName'=>$grp->travelerName,'password'=>$grp->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>'','type'=>$grp->type];
                  	}
                  	array_push($groupst, $aaa);
                  	//array_push($groupT['travellers'], $aaa);
               }
               $groupT['travellers'] = $groupst;

            	$tourManagers = Traveler::where('tour_package_id', $tour_package_id)
                ->where('type', "Manager")
                ->select('id as travelerId','name as travelerName','tpassword as password','userid as quickUserID','occupant_id as occupantId','type')
                ->get();
                $tm = count($tourManagers);
             if($tm >= 1){
                foreach($tourManagers as $Tmgr){
                    if($Tmgr->occupantId != '' || $Tmgr->occupantId != null){
                    	$tourManager[] = ['ChatType'=>"Individual",'travelerId'=>$Tmgr->travelerId,'travelerName'=>$Tmgr->travelerName,'password'=>$Tmgr->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>$Tmgr->occupantId,'type'=>$Tmgr->type];
                  	}
                  	else{
                  		$tourManager[] = ['ChatType'=>"Individual",'travelerId'=>$Tmgr->travelerId,'travelerName'=>$Tmgr->travelerName,'password'=>$Tmgr->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>'','type'=>$Tmgr->type];
                  	}
                  }
               }
                else{
                  $tourManager = '';
                }

            $inditravellers = Traveler::where('tour_package_id', $tour_package_id)
                ->where('type', "Traveller")
                ->select('id as travelerId','name as travelerName','tpassword as password','userid as quickUserID','occupant_id as occupantId','type')
                ->get();
                $it = count($inditravellers);
              if($it >= 1){
                foreach($inditravellers as $traveler){  
                	if($traveler->occupantId != '' || $traveler->occupantId != null){  
                    	$individualTravellers[] = ['ChatType'=>"Individual",'travelerId'=>$traveler->travelerId,'travelerName'=>$traveler->travelerName,'password'=>$traveler->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>$traveler->occupantId,'type'=>$traveler->type];
                    }
                    else{
                    	$individualTravellers[] = ['ChatType'=>"Individual",'travelerId'=>$traveler->travelerId,'travelerName'=>$traveler->travelerName,'password'=>$traveler->password,'quickUserID'=>$grp->quickUserID,'occupantId'=>'','type'=>$traveler->type];
                    }
                }
              }
              else{
                $individualTravellers = '';
              }

           // dd($traveller);
         
            $status = array(
            'Status' => 1,
            'message' => 'Bingo! Success!!', 
            'ChatBoardList' =>array(
                'group' => $groupT,
                'individualTourManager' => $tourManager,
                'individualTravellers' => $individualTravellers
               ),
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'Status' => 0,
           'message' => 'Opps! Authentication failed!!'
           );
          return response()->json($status, 200);
        } 
    }                  
    public function getGroupData(Request $request){
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $user_id = $request->UserID;
        $user_type = $request->UserType;
        $group_id = $request->GroupID;

        $validator = Validator::make($request->all(),[
            'TenantID' => 'required',
            'PackageID' => 'required',
            'UserID' => 'required',
            'GroupID' => 'required',
            'UserType' => 'required',
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => "$message[0]"
            ];
            return Response($status);
        }
            $GetGroupChats = GroupChat::where(['tour_package_id' => $tour_package_id, 'group_id' => $group_id])
            ->select('id as GroupChatId','tour_package_id as PackageID','sender_id as UserID','group_id as GroupId','sender_type as type','message_type as MessageType','message','image as ImagePath','created_at as DateTime','sender_name as SenderName')->get();
            $groupchat = count($GetGroupChats);
            if($groupchat >= 1){
              foreach ($GetGroupChats as $value) {
                if($value->ImagePath != null){
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                  $msgs = '';
                  $GetGroup[] = ['GroupChatId' => $value->GroupChatId,'PackageID' => $value->PackageID,'UserID' => $value->UserID,'GroupId' => $value->GroupId,'SenderName' => $value->SenderName,'type' => $value->type,'MessageType' => $value->MessageType,'message' => $msgs,'DateTime' => $value->DateTime,'ImagePath' => $src.'chat/'.$value->ImagePath];
                }
                else{
                  $ImagePaths = '';
                  $GetGroup[] = ['GroupChatId' => $value->GroupChatId,'PackageID' => $value->PackageID,'UserID' => $value->UserID,'GroupId' => $value->GroupId,'SenderName' => $value->SenderName,'type' => $value->type,'MessageType' => $value->MessageType,'message' => $value->message,'ImagePath' => $ImagePaths,'DateTime' => $value->DateTime];
                }
              }
            }
            else{
              $GetGroup = '';
            }
            $status = array(
                'Status' => false,
                'message' => 'Bingo! Success!!',
                'ChatList' => $GetGroup,
            ); 
            return response()->json($status, 200);     
            
    }
    public function postGroupData(Request $request){
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $user_id = $request->UserID;
        $user_type = $request->UserType;
        $group_id = $request->GroupID;
        $sender_name = $request->SenderName;
        $message_type = $request->MessageType;
        $message = $request->Message;
        //$image_path = $request->ImagePath;

        $validator = Validator::make($request->all(),[
            'TenantID' => 'required',
            'PackageID' => 'required',
            'UserType' => 'required',
            'UserID' => 'required',
            'GroupID' => 'required',
            'SenderName' => 'required'
            ]);

            if($validator->fails()){
                $message = $validator->errors()->all();

                $status = [
                    'error' => true,
                    'message' => "$message[0]"
                ];
                return Response($status);
            }
           
            //$GroupChats->image = $image_path;
            //$pictureName = $request->pictureName; //file name
            if($request->ImagePath){
                
                //$GroupChats->message = $message;
                $base64String= $request->ImagePath; //64 bit code
                //foreach ($base64String as $value) {
                  $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));
                  $imageName = str_random(5).time() . '.png';

                  $p = Storage::disk('s3')->put('chat'.'/'.$imageName, $image, 'public'); 
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'. '/';
                  $avatar_url = $src.'chat/'.$imageName;

                  $GroupChats = new GroupChat;
                  $GroupChats->tour_package_id = $tour_package_id;
                  $GroupChats->tenant_id = $tenant_id;
                  $GroupChats->sender_type = $user_type;
                  $GroupChats->group_id = $group_id;
                  $GroupChats->sender_id = $user_id;
                  $GroupChats->message_type = $message_type;
                  $GroupChats->sender_name = $sender_name;
                  $GroupChats->image = $imageName;
                  $GroupChats->save();
                //}
                  $pkgid = $GroupChats->tour_package_id;
                  $tenantid = $GroupChats->tenant_id;
                  $last_id = $GroupChats->id;
                  $userId = $GroupChats->sender_id;
                  $GroupID = $GroupChats->group_id;
                  $message = '';
                  $name = $GroupChats->sender_name;
                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'GroupChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'SenderName' => $name,
                  'UserID' => $userId,
                  'GroupID' => $GroupID,
                  'Message' => $message,
                  'ImagePath' => $avatar_url
                ); 
                return response()->json($status, 200);  
            }else{
                  $GroupChats = new GroupChat;
                  $GroupChats->tour_package_id = $tour_package_id;
                  $GroupChats->tenant_id = $tenant_id;
                  $GroupChats->sender_type = $user_type;
                  $GroupChats->group_id = $group_id;
                  $GroupChats->sender_id = $user_id;
                  $GroupChats->message_type = $message_type;
                  $GroupChats->sender_name = $sender_name;
                  $GroupChats->message = $message;
                  $GroupChats->save();
                  $pkgid = $GroupChats->tour_package_id;
                  $tenantid = $GroupChats->tenant_id;
                  $last_id = $GroupChats->id;
                  $userId = $GroupChats->sender_id;
                  $GroupID = $GroupChats->group_id;
                  $message = $GroupChats->message;
                  $image = '';
                  $name = $GroupChats->sender_name;
                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'GroupChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'SenderName' => $name,
                  'UserID' => $userId,
                  'GroupID' => $GroupID,
                  'Message' => $message,
                  'ImagePath' => $image,
                ); 
                return response()->json($status, 200);
            }
    }   

    public function postIndividualData(Request $request){
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $user_id = $request->UserID;
        $user_type = $request->UserType;
        $chat_with = $request->ChatWithID;
        $sender_name = $request->SenderName;
        $message_type = $request->MessageType;
        $message = $request->Message;
        $image_path = $request->ImagePath;

        $validator = Validator::make($request->all(),[
            'TenantID' => 'required',
            'PackageID' => 'required',
            'UserType' => 'required',
            'UserID' => 'required',
            'ChatWithID' => 'required',
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => "$message[0]"
            ];
            return Response($status);
        }
            $GetIndividualChats = TravelerToManagerChat::where(function($q) use ($user_id) {
                    $q->where('sender_id', $user_id)
                      ->orWhere('chat_with', $user_id);
                })->where(function ($q) use ($chat_with) {
                    $q->where('sender_id', $chat_with)
                      ->orWhere('chat_with', $chat_with);
                })->first();
            //dd($GetIndividualChats);
            if($GetIndividualChats == null)
            {
                
                //$pictureName = $request->pictureName; //file name
                if($request->ImagePath){
                  $base64String= $request->ImagePath; //64 bit code
                  //foreach ($base64String as $value) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));

                    $imageName = str_random(5).time() . '.png';

                    $p = Storage::disk('s3')->put('chat'.'/'.$imageName, $image, 'public'); 
                    $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                    $avatar_url = $src.'chat/'.$imageName;

                    $IndividualChats = new TravelerToManagerChat;
                    $IndividualChats->tour_package_id = $tour_package_id;
                    $IndividualChats->tenant_id = $tenant_id;
                    $IndividualChats->sender_type = $user_type;
                    $IndividualChats->chat_with = $chat_with;
                    $IndividualChats->sender_id = $user_id;
                    $IndividualChats->message_type = $message_type;
                    //$IndividualChats->message = $message;
                    $IndividualChats->same_sender_reciver = Str::random(16);
                    $IndividualChats->image = $imageName;
                  
                  $IndividualChats->save();
                  $last_id = $IndividualChats->id;
                  $userId = $IndividualChats->sender_id;
                  $ChatWithID = $IndividualChats->chat_with;
                  $message = '';
                  $pkgid = $IndividualChats->tour_package_id;
                  $tenantid = $IndividualChats->tenant_id;

                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'IndividualChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'UserID' => $userId,
                  'ChatWithID' => $ChatWithID,
                  'Message' => $message,
                  'ImagePath' => $avatar_url
                  ); 
                  return response()->json($status, 200);
                }
                else{
                  $IndividualChats = new TravelerToManagerChat;
                  $IndividualChats->tour_package_id = $tour_package_id;
                  $IndividualChats->tenant_id = $tenant_id;
                  $IndividualChats->sender_type = $user_type;
                  $IndividualChats->chat_with = $chat_with;
                  $IndividualChats->sender_id = $user_id;
                  $IndividualChats->message_type = $message_type;
                  $IndividualChats->message = $message;
                  $IndividualChats->same_sender_reciver = Str::random(16);
                  //$IndividualChats->image = $imageName;
                
                  $IndividualChats->save();
                  $last_id = $IndividualChats->id;
                  $userId = $IndividualChats->sender_id;
                  $ChatWithID = $IndividualChats->chat_with;
                  $message = $IndividualChats->message;
                  $image = '';
                  $pkgid = $IndividualChats->tour_package_id;
                  $tenantid = $IndividualChats->tenant_id;

                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'IndividualChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'UserID' => $userId,
                  'ChatWithID' => $ChatWithID,
                  'Message' => $message,
                  'ImagePath' => $image
                  ); 
                  return response()->json($status, 200);
                }
              }
            else{

              if($request->ImagePath != null){
                $base64String= $request->ImagePath; //64 bit code
                //foreach ($base64String as $value) {
                  $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64String));

                  $imageName = str_random(5).time() . '.png';

                  $p = Storage::disk('s3')->put('chat'.'/'.$imageName, $image, 'public'); 
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                  $avatar_url = $src.'chat/'.$imageName;
                  $IndividualChats = new TravelerToManagerChat;
                  $IndividualChats->tour_package_id = $tour_package_id;
                  $IndividualChats->tenant_id = $tenant_id;
                  $IndividualChats->sender_type = $user_type;
                  $IndividualChats->chat_with = $chat_with;
                  $IndividualChats->sender_id = $user_id;
                  $IndividualChats->message_type = $message_type;
                  $IndividualChats->same_sender_reciver = $GetIndividualChats->same_sender_reciver;
                  $IndividualChats->image = $imageName;
                  $IndividualChats->save();
                //}
                
                $last_id = $IndividualChats->id;
                $userId = $IndividualChats->sender_id;
                $ChatWithID = $IndividualChats->chat_with;
                $message = '';
                $pkgid = $IndividualChats->tour_package_id;
                $tenantid = $IndividualChats->tenant_id;

                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'IndividualChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'UserID' => $userId,
                  'ChatWithID' => $ChatWithID,
                  'Message' => $message,
                  'ImagePath' => $avatar_url
                  ); 
                  return response()->json($status, 200);
              }
              else{
                  $IndividualChats = new TravelerToManagerChat;
                  $IndividualChats->tour_package_id = $tour_package_id;
                  $IndividualChats->tenant_id = $tenant_id;
                  $IndividualChats->sender_type = $user_type;
                  $IndividualChats->chat_with = $chat_with;
                  $IndividualChats->sender_id = $user_id;
                  $IndividualChats->message_type = $message_type;
                  $IndividualChats->same_sender_reciver = $GetIndividualChats->same_sender_reciver;
                  $IndividualChats->message = $message;
                  $IndividualChats->save();
                
                  $last_id = $IndividualChats->id;
                  $userId = $IndividualChats->sender_id;
                  $ChatWithID = $IndividualChats->chat_with;
                  $message = $IndividualChats->message;
                  $pkgid = $IndividualChats->tour_package_id;
                  $tenantid = $IndividualChats->tenant_id;
                  $image = '';

                  $status = array(
                  'Status' => false,
                  'message' => 'Bingo! Success!!',
                  'IndividualChatId' => $last_id,
                  'PackageID' => $pkgid,
                  'TenantID' => $tenantid,
                  'UserID' => $userId,
                  'ChatWithID' => $ChatWithID,
                  'Message' => $message,
                  'ImagePath' => $image
                  ); 
                  return response()->json($status, 200);
              }
            }
      }       
     public function getIndividualData(Request $request){
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $user_id = $request->UserID;
        $chat_with = $request->ChatWithID;

        $validator = Validator::make($request->all(),[
            'TenantID' => 'required',
            'PackageID' => 'required',
            'UserID' => 'required',
            'ChatWithID' => 'required',
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => "$message[0]"
            ];
            return Response($status);
        }
            $GetIndividualChats = TravelerToManagerChat::where(function($q) use ($user_id) {
                    $q->where('sender_id', $user_id)
                      ->orWhere('chat_with', $user_id);
                })->where(function ($q) use ($chat_with) {
                    $q->where('sender_id', $chat_with)
                      ->orWhere('chat_with', $chat_with);
                })
              ->select('id as IndividualChatId','tour_package_id as PackageID','sender_id as SenderId','chat_with as ReceiverId','sender_type as type','message_type as MessageType','message','image as ImagePath','created_at as DateTime')
              ->get();
              $singlechat = count($GetIndividualChats);
            if($singlechat >= 1){
              foreach ($GetIndividualChats as $value) {
               if($value->ImagePath != null || $value->ImagePath != ''){
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
                $messagess = '';
                  $GetIndividual[] = ['IndividualChatId' => $value->IndividualChatId,'PackageID' => $value->PackageID,'SenderId' => $value->SenderId,'ReceiverId' => $value->ReceiverId,'type' => $value->type,'MessageType' => $value->MessageType,'message' => $messagess,'ImagePath' => $src.'chat/'.$value->ImagePath,'DateTime' => $value->DateTime];
                }
                
                else{
                  $ImagePathss = '';
                  $GetIndividual[] = ['IndividualChatId' => $value->IndividualChatId,'PackageID' => $value->PackageID,'SenderId' => $value->SenderId,'ReceiverId' => $value->ReceiverId,'type' => $value->type,'MessageType' => $value->MessageType,'message' => $value->message,'ImagePath' => $ImagePathss,'DateTime' => $value->DateTime];
                }
              }
            }
            else{
              $GetIndividual = '';
            }
            //dd($GetIndividualChats);
           
              $status = array(
              'Status' => false,
              'message' => 'Bingo! Success!!',
              'ChatList' => $GetIndividual,
              ); 
              return response()->json($status, 200);
           
    }       
}       
  