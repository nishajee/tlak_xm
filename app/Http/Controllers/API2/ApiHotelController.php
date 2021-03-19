<?php

namespace App\Http\Controllers\API2;

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
use App\ItineraryLocation;
use App\Restaurent;
use App\Shopping;

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
                'status' => false,
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
                            ->select('hotels.id as hotelId','hotels.name as hotelName','locations.name as location','hotels.location as locationId','hotels.hotel_image as hotelImage','hotels.hotel_rating as rating')
                            ->where('hotels.tour_package_id', $tour_package_id)
                            ->get();
            if(count($hotel) > 0){
                foreach($hotel as $hotal_data){
                      $rating = ($hotal_data->rating == '' || $hotal_data->rating == null)?'':$hotal_data->rating;
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                      $avatar_url = $src.'hotel/';                    
                      $hotels[] = ['hotelId'=>$hotal_data->hotelId,'hotelName'=>$hotal_data->hotelName,'location'=>$hotal_data->location,'locationId'=>$hotal_data->locationId,'hotel_rating'=>$rating,'hotelImage'=>$avatar_url.$hotal_data->hotelImage];
                    }
                }
              else{
                $hotels = [];
              }
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'hotels' => $hotels
            ); 
             return response()->json($status, 200);
            }else{
              $status = array(
               'status' => false,
               'message' => 'Opps! No Hotels found!!'
               );
              return response()->json($status, 200);
            }
          }
          else{
              $status = array(
               'status' => false,
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
                      ->select('hotels.id as hotelId','hotels.name as hotelName','locations.name as location','hotels.total_room as totalRoom','hotels.address','hotels.hotel_rating','description','hotels.hotel_image as hotelImage')
                      ->where('hotels.id', $id)
                      ->first();
            $total_stay_days = ItineraryLocation::where('tour_package_id', $tour_package_id)->where('location_id', $hotels->location)->count();        

            if($hotels){
                if($hotels->description == '' || $hotels->description == null)
                  $description = '';
                else 
                  $description = $hotels->description;
                
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                  $avatar_url = $src.'hotel/';                   
                  $hotel = ['hotelId'=>$hotels->hotelId,'hotelName'=>$hotels->hotelName,'location'=>$hotels->location,'totalRoom'=>$hotels->totalRoom,'address'=>$hotels->address,'hotel_rating'=>$hotels->hotel_rating,'stayDays'=>$total_stay_days,'description'=>$hotels->description,'hotelImage'=>$avatar_url.$hotels->hotelImage];
                }
            $hotel['peoples'] = HotelPeople::join('peoples','peoples.id','=','hotel_people.people_id')
                                ->join('hotels','hotels.id','=','hotel_people.hotel_id')
                                ->where('hotel_people.hotel_id','=',$hotels->hotelId)
                                ->select('peoples.name as peopleName')
                                ->get();
          
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

            if(count($amenity)>0){
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
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'hotels' => $hotel
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
        }
      }
      else{
          $status = array(
           'status' => false,
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

      public function hotelList(Request $request){
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
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();

          $hotels = Hotel::join('locations','locations.id','=','hotels.location')
                      ->select('hotels.id as hotelId','hotels.name as hotelName','hotels.location as locationId','locations.name as location','hotels.total_room as totalRoom','hotels.address','hotels.hotel_rating','description','hotels.hotel_image as hotelImage')
                      ->where('hotels.tour_package_id', $traveler->pkgId)
                      ->get();
          $list = [];
          foreach($hotels as $value){            
              $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
              $avatar_url = $src.'hotel/';
              $total_stay_days = ItineraryLocation::where('tour_package_id','=', $traveler->pkgId)->where('location_id','=', $value->locationId)->count();
              $description = ($value->description == '' || $value->description == null)? '' :$value->description;
              $rating = ($value->hotel_rating == '' || $value->hotel_rating == null)?'':$value->hotel_rating;

              $hotel = ['hotelId'=>$value->hotelId,'hotelName'=>$value->hotelName,'location'=>$value->location,'locationId'=>$value->locationId,'totalRoom'=>$value->totalRoom,'address'=>$value->address,'hotel_rating'=>$rating,'stayDays'=>$total_stay_days,'description'=>$description,'hotelImage'=>$avatar_url.$value->hotelImage];

              $hotel['peoples'] = HotelPeople::join('peoples','peoples.id','=','hotel_people.people_id')
                                ->join('hotels','hotels.id','=','hotel_people.hotel_id')
                                ->where('hotel_people.hotel_id','=',$value->hotelId)
                                ->select('peoples.name as peopleName')
                                ->orderBy('peoples.name')
                                ->get();
              $sockets = HotelSocket::join('electrical_sockets','electrical_sockets.id','=','hotel_sockets.socket_id')
                                ->where('hotel_sockets.hotel_id','=',$value->hotelId)
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
                                ->where('hotel_amenities.hotel_id','=',$value->hotelId)
                                ->select('amenities.name as amenityName','amenities.icon as amenityIcon')
                                ->get(); 

              if(count($amenity)>0){
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
              $total_stay_days = 0;
              array_push($list, $hotel);
          }
          if ($hotel) {
	          $status = array(
	            'status' => true,
	            'message' => 'Bingo! Success!!',
	            'hotels' => $list
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

     public function Restaurents(Request $request)
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
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
        $data = array();
        if ($traveler) {
            $restaurent = Restaurent::where('tour_package_id', $traveler->pkgId)->get();
            foreach ($restaurent as $key => $value) {
                $rating = ($value->rating == '' || $value->rating == null)?'':$value->rating;
                $location = ($value->location == '' || $value->location == null)?'':$value->location;
                $data[] = ['hotelId'=>$value->hotel_id,'name'=>$value->name, 'address'=>$value->address,'latitude'=>$value->latitude, 'longitude'=>$value->longitude,'postalCode'=>$value->postal_code,'location'=>$location,'rating'=>$rating,'image'=>'https://account.tlakapp.com/media/restaurents/'.$value->image];
            }

            if (!empty($data)) {
                $status = array(
                    'status' => true,
                    'message' => 'Bingo! Success!!',
                    'restaurents' => $data
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
               'error' => false,
               'message' => 'Opps! Invalid token!!'
               );
            return response()->json($status, 200);
        }
      }

    public function Shoppings(Request $request)
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
        $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
        $data = array();
        if ($traveler) {
            $shopping = Shopping::where('tour_package_id', $traveler->pkgId)->get();
            foreach ($shopping as $key => $value) {
              $location_name = ($value->location == '' || $value->location == null)?'':$value->location;
                $data[] = ['locationId'=>$value->location_id,'name'=>$value->name, 'address'=>$value->address,'latitude'=>$value->latitude, 'longitude'=>$value->longitude,'postalCode'=>$value->postal_code,'location'=>$location_name];
            }

            if (!empty($data)) {
                $status = array(
                    'status' => true,
                    'message' => 'Bingo! Success!!',
                    'shoppings' => $data
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
               'error' => true,
               'message' => 'Opps! Invalid token!!'
               );
            return response()->json($status, 200);
        }
    }
}       
