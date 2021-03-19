<?php
namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use DB;
use DateTime;
use DateTimeZone;
use App\Traveler;
use App\Itinerary;
use App\PointOfInterest;
use App\LocationPointOfInterest;
use App\Location;
use App\ItineraryLocation;
use App\Hotel;

class ApiEventController extends Controller
{
    public function events(Request $request)
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
        //For temprary time
        $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
                );
        return response()->json($status, 200);
        // temprary Closed
        $traveler = Traveler::where('token', $token)->select('travelers.tour_package_id as pkgId', 'travelers.id as travelerId', 'travelers.token', 'travelers.tenant_id as tenantId')
            ->first();
        $tour_package_id = $traveler->pkgId;

        if ($traveler){
         $tourPackage= TourPckage::where('id', $tour_package_id)
                  ->where(function($q) {
                                $q->where('status', 2);
                            })
                  ->first();
            if($tourPackage){
            $hotel = Hotel::select('latitude', 'longitude', 'id', 'name', 'location')->where('tour_package_id', $tour_package_id)->get();
                //dd($hotel); 
            if(count($hotel)>0)
            {
                $j = 0;
                $arr = array();
                $values = array();
                foreach ($hotel as $value)
                {
                    $location_name = Location::where('id', $value->location)->first();
                    $jsonfile = file_get_contents("http://api.eventful.com/json/events/search?app_key=QRWnrWzZjQRMfZXm&where=".$value->latitude.",".$value->longitude."&within=5");
                    $event_data = json_decode($jsonfile,TRUE);
                    $data = $event_data['events']['event'];
                    if(!is_null($data))
                    {
                        $array_data = array();
                        $final_data = array();
                        foreach ($data as $key => $ev_data) {
                            if($ev_data['image'] == '' || $ev_data['image'] == null){
                                $image = '';
                            }
                            else{
                                if(!str_contains($ev_data['image']['medium']['url'], 'http')){
                                    $image = 'http:'.$ev_data['image']['medium']['url'];
                                }
                                else{
                                    $image = $ev_data['image']['medium']['url'];
                                } 
                            }
                            $title = preg_replace('/\\\\/','_',$ev_data['title']);
                            $description = preg_replace('/\\\\/','_',$ev_data['description']);

                            $final_data[] = ['title'=>$title,'venue_name'=>$ev_data['venue_name'],'latitude'=>$ev_data['latitude'], 'longitude'=>$ev_data['longitude'],'start_time'=>$ev_data['start_time'],'description'=>$description,'venue_address'=>$ev_data['venue_address'], 'country_name'=>$ev_data['country_name'],'image'=>$image];
                        }
                        if(sizeof($final_data) != 0){
                            $arr["dayWiseEvents"] = $final_data;
                        }
                    $arr["hotelId"] = $value->id;
                    $arr["hotelName"] = $value->name;
                    $arr["locationName"] = $location_name->name;
                    array_push($values, $arr);
                    }
                    
                    
                }

                if(!empty($values))
                {
                    $status = array(
                        'status' => true,
                        'message' => 'Bingo! Success!!',
                        'traveler' => $traveler,
                        'events' => $values,

                    );
                    return response()->json($status, 200);
                }
                else{
                    $status = array(
                        'status' => false,
                        'message' => 'No data found!',

                    );
                    return response()->json($status, 200);
                }
            }
            else
            {
                $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
                );
                return response()->json($status, 200);
            }
            }

            else
            {
                $status = array(
                    'status' => false,
                    'message' => 'Opps! Invalid response'
                );
                return response()->json($status, 200);
            }
        }
    }
}