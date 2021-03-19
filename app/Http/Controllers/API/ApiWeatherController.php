<?php
namespace App\Http\Controllers\API;

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

class ApiWeatherController extends Controller
{
    public function weatherForecast(Request $request)
    {

        $token = $request->token;
        $validator = Validator::make($request->all() , ['token' => 'required']);

        if ($validator->fails())
        {
            $message = $validator->errors()
                ->all();

            $status = ['error' => true, 'message' => $message[0]];
            return Response($status);
        }
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
            $poi = LocationPointOfInterest::join('locations', 'locations.id', '=', 'location_point_of_interests.location_id')->join('point_of_interests', 'point_of_interests.id', '=', 'location_point_of_interests.point_of_interest_id')
                ->select('locations.name','point_of_interests.country_name as countryName', DB::raw('ROUND(avg(point_of_interests.latitude),2) as lat') , DB::raw('ROUND(avg(point_of_interests.longitude),2) as lang'))
                ->groupBy('locations.name','point_of_interests.country_name')
                ->where(['location_point_of_interests.tour_package_id' => $tour_package_id,'location_point_of_interests.status' => 1])->get();
                //dd($poi); 
            if (count($poi)>0)
            {
                $j = 0;
                $arr = array();
                $values = array();
                foreach ($poi as $value)
                {
                    $jsonfile = file_get_contents("https://api.darksky.net/forecast/eae5ba4bd67736276fb8ae9d98c42f68/" . $value->lat . "," . $value->lang);
                    $jsondata = json_decode($jsonfile);
                    $timezone = $jsondata->timezone;
                    $location = $value->name.', '.$value->countryName;
                    foreach ($jsondata->daily->data as $weathers)
                    {
                        $datetime = $weathers->time;
                        $unix_timestamp = $datetime;
                        $datetime = new DateTime("@$unix_timestamp");
                        $date_time_format = $datetime->format('Y-m-d');
                        $date_time_formats = $datetime->format('H:i:s');
                        $time_zone_from = "UTC";
                        $time_zone_to = $timezone;
                        $display_date = new DateTime($date_time_format, new DateTimeZone($time_zone_from));
                        $display_date->setTimezone(new DateTimeZone($time_zone_to));
                        $day = $display_date->format('l');
                        $date = $display_date->format('j, F Y');
                        $time = $display_date->format('H:i');
                        if(isset($weathers->temperatureLow)) {
                            $temperatureLow = $weathers->temperatureLow;
                        }
                        else{
                            $temperatureLow = 0;
                        }
                        $fahrenheitTemMin = round($temperatureLow,1);
                        $celsiusTempMin = round(($fahrenheitTemMin - 32) * 5 / 9,1);
                        $fahrenheitTemMax = round($weathers->temperatureHigh,1);
                        $celsiusTempMax = round(($fahrenheitTemMax - 32) * 5 / 9,1);
                        $wind = number_format($weathers->windSpeed,1) . ' ' . 'km/h';
                        $iconTypes = $weathers->icon;
                        $iconTypess=str_replace("-"," ",$iconTypes);
                        $iconType=ucwords($iconTypess);
                        $icons = $weathers->icon;
                        $icon = url("images/uploads/weather/" . $icons . '.png');

                        $ddsg[] = ['day' => $day, 'date' => $date, 'celsiusTempMin' => $celsiusTempMin, 'fahrenheitTemMin' => $fahrenheitTemMin, 'celsiusTempMax' => $celsiusTempMax, 'fahrenheitTemMax' => $fahrenheitTemMax,'wind' => $wind,'locationName' => $location,'iconType' => $iconType, 'icon' => $icon];
                    }
                    $arr["locationName"] = $location;
                    $arr["dayWiseweather"] = $ddsg;
                    array_push($values, $arr);
                    $ddsg = null;
                }
                $status = array(
                    'error' => false,
                    'message' => 'Bingo! Success!!',
                    'traveler' => $traveler,
                    'weather' => $values,

                );
                return response()->json($status, 200);
            }
            else
            {
                $status = array(
                    'error' => true,
                    'message' => 'Opps! Invalid response'
                );
                return response()->json($status, 200);
            }
            }

            else
            {
                $status = array(
                    'error' => true,
                    'message' => 'Opps! Invalid response'
                );
                return response()->json($status, 200);
            }
        }
    }
}