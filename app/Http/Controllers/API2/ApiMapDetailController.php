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
use App\Hotel;
use App\Traveler;
use App\Location;
use App\Transport;
use App\TransportLocation;
use App\TransferMode;
use App\Restaurent;
use App\Shopping;
use App\LocationPointOfInterest;

class ApiMapDetailController extends Controller
{
	public function mapDetail(Request $request)
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
    if($request->latitude == '' || $request->latitude == ''){
      //Hotels
      $hotels = Hotel::where('tour_package_id', $traveler->pkgId)
                    ->select('id','name', 'latitude','longitude')
                    ->get();
      $list = [];
      foreach($hotels as $value){ 
          $hotel = ['id'=>$value->id,'name'=>$value->name,'latitude'=>$value->latitude, 'longitude'=>$value->longitude];
          array_push($list, $hotel);
      }
      //Transports
      $transport = TransportLocation::where('tour_package_id', $traveler->pkgId)->get();
      $transports = [];
      foreach ($transport as $key => $t_value) {
          $transports[] = ['id'=>$t_value->id,'name'=>$t_value->name,'latitude'=>$t_value->latitude,'longitude'=>$t_value->longitude];
      }
      //Shoppings
      $shopping = Shopping::where('tour_package_id', $traveler->pkgId)->get();
      $shoppings = [];
      foreach ($shopping as $key => $s_value) {
          $shoppings[] = ['id'=>$s_value->id,'name'=>$s_value->name,'latitude'=>$s_value->latitude, 'longitude'=>$s_value->longitude];
      }
      //restuarents
      $restaurent = Restaurent::where('tour_package_id', $traveler->pkgId)->get();
      $restuarents = [];
      foreach ($restaurent as $key => $r_value) {
          $restuarents[] = ['id'=>$r_value->id,'name'=>$r_value->name,'latitude'=>$r_value->latitude, 'longitude'=>$r_value->longitude];
      }

      //poi list
      $locPoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                    ->join('locations','locations.id','=','location_point_of_interests.location_id')
                     ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                    ->select('point_of_interests.id as poiId','point_of_interests.name as poiName','point_of_interests.latitude','point_of_interests.longitude'
                    )
                    ->where('location_point_of_interests.tour_package_id',$traveler->pkgId)
                    ->whereIn('location_point_of_interests.status', [1, 2])
                    ->get();
      $poi_data = [];
      foreach ($locPoi as $p_value) {
          $poi = ['id'=>$p_value->poiId,'name'=>$p_value->poiName,'latitude'=>$p_value->latitude,'longitude'=>$p_value->longitude];
          array_push($poi_data, $poi);
      }
    }
    else{
      //Hotels
      $hotels = Hotel::where('tour_package_id', $traveler->pkgId)
                    ->select('id','name', 'latitude','longitude')
                    ->get();
      $list = [];
      foreach($hotels as $value){
          $in_range = $this->distance($request->latitude,$request->longitude,$value->latitude,$value->longitude);
          if($in_range < 5){
            $hotel = ['id'=>$value->id,'name'=>$value->name,'latitude'=>$value->latitude, 'longitude'=>$value->longitude];
            array_push($list, $hotel);
          }
      }
      //Transports
      $transport = Transport::where('tour_package_id', $traveler->pkgId)->get();
      $transports = [];
      foreach ($transport as $key => $t_value) {
          $in_range = $this->distance($request->latitude,$request->longitude,$t_value->latitude,$t_value->longitude);
          if($in_range < 5){
            $transports[] = ['id'=>$t_value->id,'name'=>$t_value->name,'latitude'=>$t_value->latitude,'longitude'=>$t_value->longitude];
          }
      }
      //Shoppings
      $shopping = Shopping::where('tour_package_id', $traveler->pkgId)->get();
      $shoppings = [];
      foreach ($shopping as $key => $s_value) {
          $in_range = $this->distance($request->latitude,$request->longitude,$s_value->latitude,$s_value->longitude);
          if($in_range < 5){
            $shoppings[] = ['id'=>$s_value->id,'name'=>$s_value->name,'latitude'=>$s_value->latitude, 'longitude'=>$s_value->longitude];
          }
      }
      //restuarents
      $restaurent = Restaurent::where('tour_package_id', $traveler->pkgId)->get();
      $restuarents = [];
      foreach ($restaurent as $key => $r_value) {
          $in_range = $this->distance($request->latitude,$request->longitude,$r_value->latitude,$r_value->longitude);
          if($in_range < 5){
            $restuarents[] = ['id'=>$r_value->id,'name'=>$r_value->name,'latitude'=>$r_value->latitude, 'longitude'=>$r_value->longitude];
          }
      }

      //poi list
      $locPoi = LocationPointOfInterest::join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                    ->join('locations','locations.id','=','location_point_of_interests.location_id')
                     ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                    ->select('point_of_interests.id as poiId','point_of_interests.name as poiName','point_of_interests.latitude','point_of_interests.longitude'
                    )
                    ->where('location_point_of_interests.tour_package_id',$traveler->pkgId)
                    ->get();
      $poi_data = [];
      foreach ($locPoi as $p_value) {
          $in_range = $this->distance($request->latitude,$request->longitude,$p_value->latitude,$p_value->longitude);
          if($in_range < 5){
            $poi = ['id'=>$p_value->poiId,'name'=>$p_value->poiName,'latitude'=>$p_value->latitude,'longitude'=>$p_value->longitude];
            array_push($poi_data, $poi);
          }
      }
    }


    if ($traveler) {
      $status = array(
          'status' => true,
          'message' => 'Success!!',
          'hotels' => [
          	'markerUrl'=>"https://account.tlakapp.com/media/icons/hotel_map_marker.png",
          	"details"=>$list
          ],
          'restaurants' => [
            "markerUrl"=>"https://account.tlakapp.com/media/icons/restaurants_map_marker.png",
            "details"=>$restuarents
          ],
          'shoppings' => [
            "markerUrl"=>"https://account.tlakapp.com/media/icons/shopping_map_marker.png",
            "details"=>$shoppings
          ],
          'transports' => [
            "markerUrl"=>"https://account.tlakapp.com/media/icons/transport_map_marker.png",
            "details"=>$transports
          ],
          'pois' => [
            'markerUrl'=>"https://account.tlakapp.com/media/icons/poi_map_marker.png",
            "details"=>$poi_data
          ]
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

  function distance($lat1, $lon1, $lat2, $lon2) 
  { 
    $pi80 = M_PI / 180; 
    $lat1 *= $pi80; 
    $lon1 *= $pi80; 
    $lat2 *= $pi80; 
    $lon2 *= $pi80; 
    $r = 6372.797; // mean radius of Earth in km 
    $dlat = $lat2 - $lat1; 
    $dlon = $lon2 - $lon1; 
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2); 
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
    $km = $r * $c; 
    //echo ' '.$km; 
    return $km; 
  }
}