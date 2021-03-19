<?php

namespace App\Http\Controllers\API;

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
use DB;
class ApiTravelerLoginController extends Controller
{   
    public function login(Request $request){

        $passcode = $request->passcode; 

        $validator = Validator::make($request->all(),[
            'passcode' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
    }
   
    // $tourpackage = TourPckage::where('passcode',$passcode)->first();

    $tourpackage = TourPckage::where('passcode',$passcode)->orWhere('manager_passcode',$passcode)->first();
        if($tourpackage && $tourpackage->count() > 0){
            $tenantid = $tourpackage->tenant_id;
            $tenant = Tenant::where('tenant_id',$tenantid)->select('company_name as companyName')->first();
            if($tourpackage->manager_passcode == $passcode) {
                $guide = DepartureGuide::where('tour_package_id',$tourpackage->id)->select('name as peopleName','id as peopleId','occupied')->get()->toArray();
                
                $peoples = DepartureManager::where('tour_package_id',$tourpackage->id)->select('name as peopleName','id as peopleId','occupied')->get()->toArray();
                $people = array_merge($peoples, $guide);
                $countryISO = DB::table('location_point_of_interests')
                            ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                            ->distinct()
                            ->where('location_point_of_interests.tour_package_id',$tourpackage->id)
                            ->select('point_of_interests.iso_2 as iso2')
                            ->get();
                $tourpackage = array(
                              //'companyName' =>$tenantid,
                              'pkgId' => $tourpackage->id,
                              'userId' => $tourpackage->user_id,
                              'pkgName' => $tourpackage->pname,
                              'companyName' =>$tourpackage->agent_name,
                              'tenantId' => $tourpackage->tenant_id,
                              'passcode' => $passcode,
                              'peoples' => $people,
                              
                              'selectedName' => 0,
                              'travelerType' => "Manager",
                          );
                $status = array(
                            'error' => false,
                            'message' => 'Login succesfull',
                            'tourpackage' => $tourpackage,
                            'countryISO' => $countryISO,
                            );
                return response()->json($status, 200);
            }
            else{
                $people = People::where('tour_package_id',$tourpackage->id)->select('name as peopleName','id as peopleId','occupied')->get()->toArray();
                //$guide = DepartureGuide::where('tour_package_id',$tourpackage->id)->select('name as peopleName','id as peopleId','occupied')->get()->toArray();
                //$people = array_merge($peoples, $guide);
                $countryISO = DB::table('location_point_of_interests')
                            ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                            ->distinct()
                            ->where('location_point_of_interests.tour_package_id',$tourpackage->id)
                            ->select('point_of_interests.iso_2 as iso2')
                            ->get();
                // $countryISOs = DB::table('location_point_of_interests')
                //             ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                //             ->distinct()
                //             ->where('location_point_of_interests.tour_package_id',$tourpackage->id)
                //             ->pluck('point_of_interests.iso_2 as iso2');
                //             $countryISO = [];
                //             foreach ($countryISOs as $value) {
                //                 array_push($countryISO, $value);
                //             }
                $tourpackage = array(
                              //'companyName' =>$tenantid,
                              'pkgId' => $tourpackage->id,
                              'userId' => $tourpackage->user_id,
                              'pkgName' => $tourpackage->pname,
                              'companyName' =>$tourpackage->agent_name,
                              'tenantId' => $tourpackage->tenant_id,
                              'passcode' => $passcode,
                              'peoples' => $people,
                              'selectedName' => 0,
                              'travelerType' => "Traveller",

                          );
                $status = array(
                            'error' => false,
                            'message' => 'Login succesfull',
                            'tourpackage' => $tourpackage,
                            'countryISO' => $countryISO,
                            );
                return response()->json($status, 200);
            }
        }
                 
        else{

               $status = array(
                    'error' => true,
                    'message' => 'Opps! Please enter valid Passcode!!'
                    );

                    return Response($status);
            }

    }
    public function registerTraveler(Request $request){
        $data = $request->all(); 
        $validator = Validator::make($request->all(),[
            'peopleName' => 'required'
            ]);
        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
        return Response($status);
        }
        $tenant_id = $request->tenantId;
        $pname = $request->peopleName;       
        $email = $request->travelerEmail;
        $people_id = $request->peopleId;
        $pkgid = $request->pkgId;
        $passcode = $request->passcode;
        $user_id = $request->userId;
        $device_id = $request->DeviceId;
        $device_type = $request->DeviceType;

        $tourpackage = TourPckage::where('id',$pkgid)->first();
        if($people_id == null){
            if($tourpackage->passcode == $passcode){
                $people = new People; 
                $people->name = $pname;
                $people->tour_package_id = $pkgid;
                $people->tenant_id = $tenant_id;
                $people->user_id = $user_id;
                $people->occupied = 1;
                $people->save();

                $plast_id = $people->id;
                $traveler = new Traveler; 
                $traveler->name = $pname;
                if($email){
                    $traveler->traveler_email = $email;
                }
                $traveler->people_id = $plast_id;
                $traveler->tour_package_id = $pkgid;
                $traveler->tenant_id = $tenant_id;
                $traveler->device_id = $device_id;
                $traveler->device_type = $device_type;
                $traveler->type = "Traveller";
                $traveler->token = Str::random(64);
                $traveler->tpassword = "tlak2020app";
                $traveler->userid = str_random(5) . time();
                $traveler->save();
        
                $traveler_name=$traveler->name;
                $traveler_id=$traveler->id;
                $token=$traveler->token;
                $pkg_id=$traveler->tour_package_id;
                $tenant_id=$traveler->tenant_id;
                $type=$traveler->type;
                $user = $tourpackage->user_id;
                $deviceid = $traveler->device_id;
                $devicetype = $traveler->device_type;
                if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                    $profileImg = url("images/uploads/male.png");
                }
                else{
                    $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                }

                $status = array(
                'error' => false,
                'message' => 'Submit details successfully!',
                'packageId' => $pkg_id,
                'userId' => $user,
                'tenantId' => $tenant_id,
                     'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                        ),
                );
                return Response($status);
            }
            else{
                $people = new DepartureManager; 
                $people->name = $pname;
                $people->email = $email;
                $people->tour_package_id = $pkgid;
                $people->tenant_id = $tenant_id;
                $people->user_id = $user_id;
                $people->manager_passcode = $passcode;
                $people->type = "Manager";
                $people->occupied = 1;
                $people->save();

                $plast_id = $people->id;
                $traveler = new Traveler; 
                $traveler->name = $pname;
                if($email){
                    $traveler->traveler_email = $email;
                }
                $traveler->people_id = $plast_id;
                $traveler->tour_package_id = $pkgid;
                $traveler->tenant_id = $tenant_id;
                $traveler->type = "Manager";
                $traveler->device_id = $device_id;
                $traveler->device_type = $device_type;
                $traveler->token = Str::random(64);
                $traveler->tpassword = "tlak2020app";
                $traveler->userid = str_random(5) . time();
                $traveler->save();
        
                $traveler_name=$traveler->name;
                $traveler_id=$traveler->id;
                $token=$traveler->token;
                $pkg_id=$traveler->tour_package_id;
                $tenant_id=$traveler->tenant_id;
                $type=$traveler->type;
                $user = $tourpackage->user_id;
                $deviceid = $traveler->device_id;
                $devicetype = $traveler->device_type;
                if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                    $profileImg = url("images/uploads/male.png");
                }
                else{
                    $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                }
                $status = array(
                'error' => false,
                'message' => 'Submit details successfully!',
                'packageId' => $pkg_id,
                'userId' => $user,
                'tenantId' => $tenant_id,
                     'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                            
                        ),
                );
                return Response($status);
            }
        }

        else if($people_id != null) {
            $traveler =  Traveler::where(['people_id' => $people_id,'name' => $pname])->first();
            if($traveler || $traveler != null){
                if($traveler->type == "Traveller"){
                   $travelerUserId =  Traveler::where(['people_id' => $people_id,'id' => $traveler->id])->select('userid','tpassword')->first();
                    if(($travelerUserId->tpassword == '' || $travelerUserId->tpassword == null) && ($travelerUserId->userid == '' || $travelerUserId->userid == null)){
                        if($email || $email != '' || $email != null){
                          
                             $traveler->update([
                                    'name' =>$pname,
                                    'traveler_email' => $email,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'tpassword' => "tlak2020app",
                                    'userid' => str_random(5) . time(),
                              ]);
                            } 
                            else{
                              
                                $traveler->update([
                                    'name' =>$pname,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'tpassword' => "tlak2020app",
                                    'userid' => str_random(5) . time(),
                              ]);
                            }
                    }
                    elseif($travelerUserId->tpassword == '' || $travelerUserId->tpassword == null){
                        if($email || $email != '' || $email != null){
                          
                             $traveler->update([
                                    'name' =>$pname,
                                    'traveler_email' => $email,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'tpassword' => "tlak2020app",
                              ]);
                        }
                        else{
                          
                            $traveler->update([
                                    'name' =>$pname,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'tpassword' => "tlak2020app",
                              ]);
                        } 
                    }
                    elseif($travelerUserId->userid == '' || $travelerUserId->userid == null){
                        if($email || $email != '' || $email != null){
                          
                             $traveler->update([
                                    'name' =>$pname,
                                    'traveler_email' => $email,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'userid' => str_random(5) . time(),
                              ]); 
                        }
                        else{
                            
                            $traveler->update([
                                    'name' =>$pname,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                                    'userid' => str_random(5) . time(),
                              ]);
                        }
                    }
                    else{
                        
                        if($email || $email != '' || $email != null){
                            $traveler->update([
                                    'name' =>$pname,
                                    'traveler_email' => $email,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                            ]); 
                        }
                        else{
                            
                            $traveler->update([
                                    'name' =>$pname,
                                    'people_id' => $people_id,
                                    'tour_package_id' => $pkgid,
                                    'tenant_id' => $tenant_id,
                                    'type' => "Traveller",
                                    'device_id' => $device_id,
                                    'device_type' => $device_type,
                            ]);
                        }           
                    }
                    $people = People::where('id', $people_id)
                    ->update([
                    'occupied' => 1,
                    ]);


                    $traveler_name=$traveler->name;
                    $traveler_id=$traveler->id;
                    $token=$traveler->token;
                    $pkg_id=$traveler->tour_package_id;
                    $tenant_id=$traveler->tenant_id;
                    $type = $traveler->type;
                    $user = $tourpackage->user_id;
                    $deviceid = $traveler->device_id;
                    $devicetype = $traveler->device_type;
                    if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                    $profileImg = url("images/uploads/male.png");
                    }
                    else{
                        $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                    }
                    $status = array(
                    'error' => false,
                    'message' => 'Submit details successfully!',
                    'packageId' => $pkg_id,
                    'userId' => $user,
                    'tenantId' => $tenant_id,
                    'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                            
                            ),
                    );
                   
                    return Response($status);
                }

                elseif($traveler->type == "Manager"){
                    $travelerUserId =  Traveler::where(['people_id' => $people_id,'id' => $traveler->id])->select('userid','tpassword')->first();
                    if(($travelerUserId->tpassword == '' || $travelerUserId->tpassword == null) && ($travelerUserId->userid == '' || $travelerUserId->userid == null)){
                       if($email || $email != '' || $email != null){ 
                        $traveler->update([
                            'name' =>$pname,
                            'traveler_email' => $email,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'tpassword' => "tlak2020app",
                            'userid' => str_random(5) . time(),
                        ]); 
                      }
                      else{
                        $traveler->update([
                            'name' =>$pname,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'tpassword' => "tlak2020app",
                            'userid' => str_random(5) . time(),
                        ]);
                      }
                    }
                    elseif($travelerUserId->tpassword == '' || $travelerUserId->tpassword == null){
                        if($email || $email != '' || $email != null){
                        $traveler->update([
                            'name' =>$pname,
                            'traveler_email' => $email,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'tpassword' => "tlak2020app",
                        ]); 
                        }
                        else{
                            $traveler->update([
                            'name' =>$pname,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'tpassword' => "tlak2020app",
                        ]);
                        }

                    }
                     elseif($travelerUserId->userid == '' || $travelerUserId->userid == null){
                        if($email || $email != '' || $email != null){
                        $traveler->update([
                            'name' =>$pname,
                            'traveler_email' => $email,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'userid' => str_random(5) . time(),
                        ]); 
                        }
                        else{
                            $traveler->update([
                            'name' =>$pname,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'userid' => str_random(5) . time(),
                        ]);
                        }
                    }
                    else{
                        if($email || $email != '' || $email != null){
                        $traveler->update([
                            'name' =>$pname,
                            'traveler_email' => $email,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                        ]);
                        }
                        else{
                            $traveler->update([
                            'name' =>$pname,
                            'people_id' => $people_id,
                            'tour_package_id' => $pkgid,
                            'tenant_id' => $tenant_id,
                            'type' => "Manager",
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                        ]);
                        }
                    }
                    $manager = DepartureManager::where('id', $people_id)
                    ->update([
                    'occupied' => 1,
                    ]);

                    $traveler_name = $traveler->name;
                    $traveler_id = $traveler->id;
                    $token = $traveler->token;
                    $pkg_id = $traveler->tour_package_id;
                    $tenant_id = $traveler->tenant_id;
                    $type = $traveler->type;
                    $user = $tourpackage->user_id;
                    $deviceid = $traveler->device_id;
                    $devicetype = $traveler->device_type;
                    if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                        $profileImg = url("images/uploads/male.png");
                    }
                    else{
                        $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                    }
                    $status = array(
                    'error' => false,
                    'message' => 'Submit details successfully!',
                    'packageId' => $pkg_id,
                    'userId' => $user,
                    'tenantId' => $tenant_id,
                    'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                            
                            ),
                    );
                   
                    return Response($status);
                }
            }
            else{

                $manager =  DepartureManager::where(['tour_package_id' => $pkgid,'id' => $people_id,'name' => $pname, 'manager_passcode' => $passcode])->first();
                
                if($manager){
                   //dd("cond3");
                    $traveler = new Traveler; 
                    $traveler->name = $pname;
                    if($email){
                        $traveler->traveler_email = $email;
                    }
                    $traveler->people_id = $people_id; 
                    $traveler->tour_package_id = $pkgid;
                    $traveler->tenant_id = $tenant_id;
                    $traveler->device_id = $device_id;
                    $traveler->device_type = $device_type;
                    $traveler->type = "Manager";
                    $traveler->token = Str::random(64);
                    $traveler->tpassword = "tlak2020app";
                    $traveler->userid = str_random(5) . time();
                    $traveler->save();
                    
                    $people = DepartureManager::where('id', $people_id)
                    ->update([
                    'occupied' => 1,
                    ]);
                      // //dd()
                    $traveler_name=$traveler->name;
                    $traveler_id=$traveler->id;
                    $token=$traveler->token;
                    $pkg_id=$traveler->tour_package_id;
                    $tenant_id=$traveler->tenant_id;
                    $type=$traveler->type;
                    $user = $tourpackage->user_id;
                    $deviceid = $traveler->device_id;
                    $devicetype = $traveler->device_type;
                    if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                        $profileImg = url("images/uploads/male.png");
                    }
                    else{
                        $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                    }
                    $status = array(
                    'error' => false,
                    'message' => 'Submit details successfully!',
                    'packageId' => $pkg_id,
                    'userId' => $user,
                    'tenantId' => $tenant_id,
                    'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                            
                            ),
                    );
                    return Response($status);
                }
                else{
                    //dd("cond4");
                    //dd("false");
                    $traveler = new Traveler; 
                    $traveler->name = $pname;
                    if($email){
                        $traveler->traveler_email = $email;
                    }
                    $traveler->people_id = $people_id; 
                    $traveler->tour_package_id = $pkgid;
                    $traveler->tenant_id = $tenant_id;
                    $traveler->type = "Traveller";
                     $traveler->device_id = $device_id;
                    $traveler->device_type = $device_type;
                    $traveler->token = Str::random(64);
                    $traveler->tpassword = "tlak2020app";
                    $traveler->userid = str_random(5) . time();
                    $traveler->save();
                    
                    $people = People::where('id', $people_id)
                    ->update([
                    'occupied' => 1,
                    ]);
                      // dd()
                    $traveler_name=$traveler->name;
                    $traveler_id=$traveler->id;
                    $token=$traveler->token;
                    $pkg_id=$traveler->tour_package_id;
                    $tenant_id=$traveler->tenant_id;
                    $type=$traveler->type;
                    $user = $tourpackage->user_id;
                    $deviceid = $traveler->device_id;
                    $devicetype = $traveler->device_type;
                    if($traveler->profile_picture == '' || $traveler->profile_picture == null){
                        $profileImg = url("images/uploads/male.png");
                    }
                    else{
                        $profileImg = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/traveller/profile/'.$traveler->profile_picture;
                    }
                    $status = array(
                    'error' => false,
                    'message' => 'Submit details successfully!',
                    'packageId' => $pkg_id,
                    'userId' => $user,
                    'tenantId' => $tenant_id,
                    'travellers' => array(
                            'travelerId' => $traveler_id,
                            'travelerName' => $traveler_name,
                            'type' => $type,
                            'token' => $token,
                            'DeviceId' => $deviceid,
                            'DeviceType' => $devicetype,
                            'password' =>$traveler->tpassword,
                            'quickUserID' =>$traveler->userid,
                            'travelerEmail' =>$traveler->traveler_email,
                            'profilePicture'=> $profileImg,
                            
                            ),
                    );
                    return Response($status);
                }
            }          
        }
    } 

    public function logoutTraveler(Request $request){

        $traveler_id = $request->travelerId;
        $type = $request->type; 
        if($traveler_id){
            $travelers =  Traveler::where(["id" => $traveler_id, "type" => $type])->first();
                if($travelers){
                    if($travelers->type == "Traveller"){
                     // $travel =  Traveler::find($traveler_id); 
                        $travelers->token = Str::random(64);
                        $travelers->save();
                        $travelId = $travelers->id;
                        $type = $travelers->type;
                        $token = $travelers->token;
                        $people =  People::where('id',$travelers->people_id)
                                   ->update([
                                    'occupied' => 0,
                                    ]);
                        $status = array(
                            'error' => false,
                            'message' => 'Bingo! Logout successfully!',
                            'travelerId' => $travelId,
                            'type' => $type,
                            'token' => $token
                        );
                        return Response($status);
                    }
                    else{
                     // $travel =  Traveler::find($traveler_id);
                        $travelers->token = Str::random(64);
                        $travelers->save();
                        $travelId = $travelers->id;
                        $type = $travelers->type;
                        $token = $travelers->token;
                        $people =  DepartureManager::where('id',$travelers->people_id)
                                   ->update([
                                    'occupied' => 0,
                                    ]);
                        $status = array(
                            'error' => false,
                            'message' => 'Bingo! Logout successfully!',
                            'travelerId' => $travelId,
                            'type' => $type,
                            'token' => $token
                        );
                        return Response($status);
                    }
                }
                else{
                    $status = array(
                    'error' => true,
                    'message' => 'Opps! Invalid response!!'
                     );
                    return Response($status);
                }
        }
        else{
            $status = array(
                'error' => true,
                'message' => 'Opps! Invalid response!!'
                 );
                return Response($status);
        }   
    }
    public function occupantIdUpdate(Request $request){

        $traveler_id = $request->travelerId;
        $tenant_id = $request->TenantID;
        $tour_package_id = $request->PackageID;
        $occupant_id = $request->occupantId;
        $userid = $request->quickUserID;

        $travelers =  Traveler::where(["id" => $traveler_id, "userid" => $userid,"tour_package_id" => $tour_package_id])->value('id');
            if($travelers){
                $travellers =  Traveler::find($travelers);

                $travellers->occupant_id = $occupant_id;
                $travellers->save();
                $status = array(
                            'error' => false,
                            'message' => 'Bingo! occupantId updated successfully!',
                            'occupantId' => $travellers->occupant_id,
                            'quickUserID' => $travellers->userid
                );
                return Response($status);
                
            }
            else{
                    $status = array(
                    'error' => true,
                    'message' => 'Opps! Invalid response!!'
                     );
                    return Response($status);
            }
    }
}

