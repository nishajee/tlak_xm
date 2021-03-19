<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\Hotel;
use DB;
//use App\HotelTiket;
use App\HotelPeople;
use App\HotelSocket;
use App\HotelAmenity;

class ApiHotelController extends Controller
{   
    public function Hotel(Request $request){

        $token = $request->token; 
        $validator = Validator::make($request->all(),[
            'token' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
      }
      
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
          $tour_package_id=$traveler->pkgId;
          $pkg = TourPckage::where('id', $tour_package_id)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                            ->first();
          if($pkg){
            
            $hotel = Hotel::join('locations','locations.id','=','hotels.location')
                            ->select('hotels.id as hotelId','hotels.name as hotelName','locations.name as location','hotels.hotel_image as hotelImage')
                            ->where('hotels.tour_package_id', $tour_package_id)
                            ->get();
            if(count($hotel) > 0){
                foreach($hotel as $hotil){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                      $avatar_url = $src.'hotel/';                    
                      $hotels[] = ['hotelId'=>$hotil->hotelId,'hotelName'=>$hotil->hotelName,'location'=>$hotil->location,'hotelImage'=>$avatar_url.$hotil->hotelImage];
                    }
                }
              else{
                $hotels = [];
              }
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'hotels' => $hotels
            ); 
             return response()->json($status, 200);
            }else{
              $status = array(
               'error' => true,
               'message' => 'Opps! No Hotels found!!'
               );
              return response()->json($status, 200);
            }
          }
          else{
              $status = array(
               'error' => true,
               'message' => 'Opps! Invalid response!!'
               );
              return response()->json($status, 200);
             }
                 
        }         
    
    public function hotelDetails(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
           
            $hotels = Hotel::join('locations','locations.id','=','hotels.location')
                      ->select('hotels.id as hotelId','hotels.name as hotelName','locations.name as location','hotels.total_room as totalRoom','hotels.address','description','hotels.hotel_image as hotelImage')
                      ->where('hotels.id', $id)
                      ->first();

            if($hotels){
                if($hotels->description == '' || $hotels->description == null)
                  $description = '';
                else 
                  $description = $hotels->description;
                $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                $avatar_url = $src.'hotel/';                   
                $hotel = ['hotelId'=>$hotels->hotelId,'hotelName'=>$hotels->hotelName,'location'=>$hotels->location,'totalRoom'=>$hotels->totalRoom,'address'=>$hotels->address,'description'=>$description,'hotelImage'=>$avatar_url.$hotels->hotelImage];
            }
            $hotel['peoples'] = HotelPeople::join('peoples','peoples.id','=','hotel_people.people_id')
                                ->join('hotels','hotels.id','=','hotel_people.hotel_id')
                                ->where('hotel_people.hotel_id','=',$hotels->hotelId)
                                ->select('peoples.name as peopleName')
                                ->get();
            // if($peoples){
            //     foreach($peoples as $people){
                                          
            //           $hotel['people'][] = ['peopleName'=>$people->peopleName,'hotelPass'=>url("images/uploads/hotel/passes/".$people->hotelPass)];
            //         }
            //     }
            $sockets = HotelSocket::join('electrical_sockets','electrical_sockets.id','=','hotel_sockets.socket_id')
                                ->where('hotel_sockets.hotel_id','=',$hotels->hotelId)
                                ->select('electrical_sockets.name as amenityName','electrical_sockets.image as amenityIcon')
                                ->get();  
             $socketj = count($sockets);
            if($socketj >= 1){
                foreach($sockets as $socket){
                                          
                      $socketss[] = ['amenityName'=>$socket->amenityName.' '."Socket",'amenityIcon'=>url("images/uploads/hotel/socket/".$socket->amenityIcon)];
                    }
                }

              else{
                $socketss = [];
              }

            $amenity = HotelAmenity::join('amenities','amenities.id','=','hotel_amenities.amenity_id')
                                ->where('hotel_amenities.hotel_id','=',$hotels->hotelId)
                                ->select('amenities.name as amenityName','amenities.icon as amenityIcon')
                                ->get(); 

            if(count($amenity) > 0){
                foreach($amenity as $hamenity){
                                          
                      $amenities[] = ['amenityName'=>$hamenity->amenityName,'amenityIcon'=>url("images/uploads/hotel/amenity/".$hamenity->amenityIcon)];
                    }
                }                   
            else{
              $amenities = [];
            }               
             if($socketss == '' || $socketss == null){ 
              $hotel['amenities'] = $amenities;  
            }
            else{
              $hotel['amenities'] = array_merge($amenities,$socketss);
            }
            // if($amenity){
            //     foreach($amenity as $hamenity){
                                          
            //           $hotel['amenities'][] = ['amenityName'=>$hamenity->amenityName,'amenityIcon'=>url("images/uploads/hotel/amenity/".$hamenity->amenityIcon)];
            //         }
            //     }

            // $hotelPasses = DB::table('hotel_tikets')->join('hotels','hotels.id','=','hotel_tikets.hotel_id')
            //                     ->where('hotel_id','=',$hotel['hotelId'])
            //                     ->select('document as hotelPass')
            //                     ->get(); 

            //       $passess = count($hotelPasses);
            //       if($passess >=1){
            //         foreach ($hotelPasses as $value) {
            //           $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
            //           $avatar_url = $src.'hotel/passes/';
            //           $hotel['hotelPasses'][] = ['hotelPass'=>$avatar_url.$value->hotelPass];
            //         }
            //       } 
            //       else{
            //     $hotel['hotelPasses'][] = ['hotelPass'=>'Hotel Bouchers will be available after it has been added.'];
            //   }

            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'hotels' => $hotel
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }
      }
      else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
         }
             
    }
    public function hotelDocuments(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
           
            $hotels = Hotel::where('id', $id)->select('hotels.id as hotelId','hotels.name as hotelName')->first();
            $hotel = ['hotelId'=>$hotels->hotelId, 'hotelName'=>$hotels->hotelName];
            $passes = DB::table('hotel_tikets')->where('hotel_id',$hotels->hotelId)
                                ->select('document as hotelPass')
                                ->get();
                                //dd($passes);
            $passess = count($passes);
              if($passess >=1){
                foreach ($passes as $value) {
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                      $avatar_url = $src.'hotel/passes/';
                      $hname = explode('.', $value->hotelPass);
                      $hotel['hotelPasses'][] = ['boucherName'=>$hname[0].'-'.$hotels->hotelId.'.'.$hname[1],'hotelPass'=>$avatar_url.$value->hotelPass];
                }
              }
              else{
                $hotel['hotelPasses'] = [];
              }


                $status = array(
                'error' => false,
                'message' => 'Bingo! Success!!', 
                'traveler' => $traveler,
                'hotels' => $hotel
                ); 
              return response()->json($status, 200);
            }else{
              $status = array(
               'error' => true,
               'message' => 'Opps! Invalid response!!'
               );
              return response()->json($status, 200);
            }
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
