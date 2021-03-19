<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\Flight;
use App\FlightTiket;
use App\Location;
use App\Transport;
use App\TransferMode;
class ApiFlightController extends Controller
{   
    public function flight(Request $request){

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
            $pkg = TourPckage::where('id', $traveler->pkgId)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                      ->select('id as pkgId')
                      ->first();
            if($pkg){      
              //$dep_date = date('Y-m-d', strtotime($depar_date));
            $tour_package_id=$traveler->pkgId;
            $flight = Flight::where('tour_package_id', $tour_package_id)->select('flights.id as flightId','flights.flight_number as flightNumber','flights.airline_code as airlinCode','flights.airline','flights.departure_date as departureDate','flights.schedule_departure as departureTime','flights.departure_iata as departureIata','flights.departure_terminal as departureTerminal','flights.arrival_date as arrivalDate','flights.schedule_arrival as arrivalTime','flights.arrival_iata as arrivalIata','flights.total_travel_time as totalTime','flights.delay_time as delayTime','flights.gate_number as gateNumber','flights.departure_airport as departureAirport','flights.arrival_airport as arrivalAirport','flights.airline_icon_id as airlineIcon','flights.trip_status as tripStatus')->get();
            $flightr = count($flight);
            if($flightr >= 1){
              $list = [];
              foreach ($flight as $value) {
                $delay_time = ($value->delayTime == '0 Hours 0 Mins' || $value->delayTime == '')?'No Delay':$value->delayTime;
                $departureTerminal = ($value->departureTerminal == '' || $value->departureTerminal == null)?'':$value->departureTerminal;
                $gateNumber = ($value->gateNumber == '' || $value->gateNumber == null)?'':$value->gateNumber;
                $tripStatus = ($value->tripStatus == '' || $value->tripStatus == null)?'':$value->tripStatus;

                if (file_exists(public_path() . '/images/airlineLogo/' . $value->airlinCode . '.jpg')) {
                  $flights = ['flightId'=>$value->flightId,'flightNumber'=>$value->flightNumber,'airlinCode'=>$value->airlinCode,'airline'=>$value->airline,'departureAirport'=>$value->departureAirport,'departureDate'=>date('d-m-Y', strtotime($value->departureDate)),'departureTime'=>$value->departureTime,'departureIata'=>$value->departureIata,'departureTerminal'=>$departureTerminal,'arrivalAirport'=>$value->arrivalAirport,'arrivalDate'=>date('d-m-Y', strtotime($value->arrivalDate)),'arrivalTime'=>$value->arrivalTime,'arrivalIata'=>$value->arrivalIata,'totalTime'=>$value->totalTime,'delayTime'=>$delay_time,'gateNumber'=>$gateNumber,'airlineIcon'=>url('/images/airlineLogo/' . $value->airlinCode .'.jpg'),'tripStatus'=>$tripStatus];

                  $flights['peoples'] = Flight::join('flight_people', 'flight_people.flight_id', '=','flights.id')
                                    ->join('peoples','peoples.id','=','flight_people.people_id')
                                    ->orderBy('peoples.name')
                                    ->where('flights.id', $value->flightId)->select('peoples.name as peopleName')
                                    ->get();
                }
                else {
                  $flights = ['flightId'=>$value->flightId,'flightNumber'=>$value->flightNumber,'airlinCode'=>$value->airlinCode,'airline'=>$value->airline,'departureAirport'=>$value->departureAirport,'departureDate'=>date('d-m-Y', strtotime($value->departureDate)),'departureTime'=>$value->departureTime,'departureIata'=>$value->departureIata,'departureTerminal'=>$departureTerminal,'arrivalAirport'=>$value->arrivalAirport,'arrivalDate'=>date('d-m-Y', strtotime($value->arrivalDate)),'arrivalTime'=>$value->arrivalTime,'arrivalIata'=>$value->arrivalIata,'totalTime'=>$value->totalTime,'delayTime'=>$delay_time,'gateNumber'=>$gateNumber,'airlineIcon'=>url('images/airlineLogo/default.png'),'tripStatus'=>$tripStatus];

                  $flights['peoples'] = Flight::join('flight_people', 'flight_people.flight_id', '=','flights.id')
                                    ->join('peoples','peoples.id','=','flight_people.people_id')
                                    ->orderBy('peoples.name')
                                    ->where('flights.id', $value->flightId)->select('peoples.name as peopleName')
                                    ->get();
                }
                array_push($list, $flights);
              }
            }
            else{
              $list = [];
            }
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'flight' => $list
            ); 
          return response()->json($status, 200);
        }else{
          $status = array(
           'status' => false,
           'message' => 'Opps! No flight found!!'
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

    public function flightDetails(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
           
            $flights = Flight::where('id', $id)->select('flights.id as flightId','flights.flight_number as flightNumber','flights.airline_code as airlinCode','flights.airline','flights.departure_airport as departureAirport','flights.arrival_airport as arrivalAirport','flights.departure_date as departureDate','flights.schedule_departure as departureTime','flights.departure_iata as departureIata','flights.departure_terminal as departureTerminal','flights.arrival_date as arrivalDate','flights.schedule_arrival as arrivalTime','flights.arrival_iata as arrivalIata','flights.total_travel_time as totalTime','flights.delay_time as delayTime','flights.gate_number as gateNumber','flights.airline_icon_id as airlineIcon')->first();

            if($flights){
                if (file_exists(public_path() . '/images/airlineLogo/' . $flights->airlinCode . '.jpg')) {
                  $flight = ['flightId'=>$flights->flightId,'flightNumber'=>$flights->flightNumber,'airlinCode'=>$flights->airlinCode,'airline'=>$flights->airline,'departureAirport'=>$flights->departureAirport,'departureDate'=>date('d-m-Y', strtotime($flights->departureDate)),'departureTime'=>$flights->departureTime,'departureIata'=>$flights->departureIata,'departureTerminal'=>$flights->departureTerminal,'arrivalAirport'=>$flights->arrivalAirport,'arrivalDate'=>date('d-m-Y', strtotime($flights->arrivalDate)),'arrivalTime'=>$flights->arrivalTime,'arrivalIata'=>$flights->arrivalIata,'totalTime'=>$flights->totalTime,'delayTime'=>$flights->delayTime,'gateNumber'=>$flights->gateNumber,'airlineIcon'=>url('/images/airlineLogo/' . $flights->airlinCode .'.jpg')];
                }
                else {
                  $flight = ['flightId'=>$flights->flightId,'flightNumber'=>$flights->flightNumber,'airlinCode'=>$flights->airlinCode,'airline'=>$flights->airline,'departureAirport'=>$flights->departureAirport,'departureDate'=>date('d-m-Y', strtotime($flights->departureDate)),'departureTime'=>$flights->departureTime,'departureIata'=>$flights->departureIata,'departureTerminal'=>$flights->departureTerminal,'arrivalAirport'=>$flights->arrivalAirport,'arrivalDate'=>date('d-m-Y', strtotime($flights->arrivalDate)),'arrivalTime'=>$flights->arrivalTime,'arrivalIata'=>$flights->arrivalIata,'totalTime'=>$flights->totalTime,'delayTime'=>$flights->delayTime,'gateNumber'=>$flights->gateNumber,'airlineIcon'=>url('images/photos/account/default.png')];
                }
            }
            $passes = FlightTiket::where('flight_id',$flights->flightId)
                                ->select('document')
                                ->get();
                                //dd($passes);
            // $passess = count($passes);
            //   if($passess >=1){
            //     foreach ($passes as $value) {
            //       $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket' . '/';
            //           $avatar_url = $src.'flight/passes/';
            //        $flight['flightTickets'][] = ['ticket'=>$avatar_url.$value->document];
            //       // print_r($flights['flightTickets']); 
            //     }
            //   }
            //   else{
            //     $passes = '';
            //   }

            $flight['people'] = People::where('tour_package_id',$tour_package_id)
                                ->select('peoples.name as peopleName')
                                ->get();
            
            
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'flights' => $flight
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

    public function flightStatus(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
           
            $flights = Flight::where('id', $id)->select('flights.id as flightId','flights.flight_number as flightNumber','flights.airline_code as airlinCode','flights.airline','flights.departure_airport as departureAirport','flights.departure_city_country as departureCityCountry','flights.departure_date as departureDate','flights.schedule_departure as departureTime','flights.departure_iata as departureIata','flights.departure_terminal as departureTerminal','flights.arrival_airport as arrivalAirport','flights.arrival_city_country as arrivalCityCountry','flights.arrival_date as arrivalDate','flights.schedule_arrival as arrivalTime','flights.arrival_iata as arrivalIata','flights.total_travel_time as totalTime','flights.delay_time as delayTime','flights.gate_number as gateNumber','flights.airline_icon_id as airlineIcon')->first();
            if($flights){
              if (file_exists(public_path() . '/images/airlineLogo/' . $flights->airlinCode . '.jpg')) {
                $flight = ['flightId'=>$flights->flightId,'flightNumber'=>$flights->flightNumber,'airlinCode'=>$flights->airlinCode,'airline'=>$flights->airline,'departureAirport'=>$flights->departureAirport,'departureCityCountry'=>$flights->departureCityCountry,'departureDate'=>date('d-m-Y', strtotime($flights->departureDate)),'departureTime'=>$flights->departureTime,'departureIata'=>$flights->departureIata,'departureTerminal'=>$flights->departureTerminal,'arrivalAirport'=>$flights->arrivalAirport,'arrivalCityCountry'=>$flights->arrivalCityCountry,'arrivalDate'=>date('d-m-Y', strtotime($flights->arrivalDate)),'arrivalTime'=>$flights->arrivalTime,'arrivalIata'=>$flights->arrivalIata,'totalTime'=>$flights->totalTime,'delayTime'=>$flights->delayTime,'gateNumber'=>$flights->gateNumber,'airlineIcon'=>url('/images/airlineLogo/' . $flights->airlinCode .'.jpg')];
              }
                else{
                  $flight = ['flightId'=>$flights->flightId,'flightNumber'=>$flights->flightNumber,'airlinCode'=>$flights->airlinCode,'airline'=>$flights->airline,'departureAirport'=>$flights->departureAirport,'departureCityCountry'=>$flights->departureCityCountry,'departureDate'=>date('d-m-Y', strtotime($flights->departureDate)),'departureTime'=>$flights->departureTime,'departureIata'=>$flights->departureIata,'departureTerminal'=>$flights->departureTerminal,'arrivalAirport'=>$flights->arrivalAirport,'arrivalCityCountry'=>$flights->arrivalCityCountry,'arrivalDate'=>date('d-m-Y', strtotime($flights->arrivalDate)),'arrivalTime'=>$flights->arrivalTime,'arrivalIata'=>$flights->arrivalIata,'totalTime'=>$flights->totalTime,'delayTime'=>$flights->delayTime,'gateNumber'=>$flights->gateNumber,'airlineIcon'=>url('images/photos/account/default.png')];
                }
            }
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'flights' => $flight
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
    public function flightDocuments(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
           
            $flights = Flight::where('id', $id)->select('flights.id as flightId')->first();
            $flight = ['flightId'=>$flights->flightId];
            $passes = FlightTiket::where('flight_id',$flights->flightId)
                                ->select('document')
                                ->get();
                                //dd($passes);
            $passess = count($passes);
              if($passess >=1){
                foreach ($passes as $value) {
                  $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/';
                      $avatar_url = $src.'flight/passes/';
                      $fname = explode('.', $value->document);
                      //dd($fname);
                   $flight['flightTickets'][] = ['ticketName'=>$fname[0].'-'.$flights->flightId.'.'.$fname[1],'ticket'=>$avatar_url.$value->document];
                }
              }
              else{
                $flight['flightTickets'] = [];
              }


            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'flights' => $flight
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
    
    public function transports(Request $request)
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
        if ($traveler) {
            $transport = Transport::where('tour_package_id', $traveler->pkgId)->get();
            $data = [];
            foreach ($transport as $key => $value) {
                $location = Location::where('id', $value->location_id)->first();
                $mode = TransferMode::where('id', $value->transfer_mode)->first();

                $data[] = ['mode'=>$mode->name,'name'=>$value->name,'latitude'=>$value->latitude,'longitude'=>$value->longitude, 'location'=>$location->name,'city'=>$value->city, 'zipcode'=>$value->zipcode,'country'=>$value->country,'address'=>$value->address,'image'=>'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/itineary/fKr771587034786.jpg'];
            }

            if(!empty($data)){
                $status = array(
                  'status' => true,
                  'message' => 'Bingo! Success!!',
                  'traveler' => $traveler,
                  'transports' => $data
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
               'message' => 'Opps! Invalid response!!'
               );
            return response()->json($status, 200);
        }
    }
}       
