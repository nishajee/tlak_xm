<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Image;
use Storage;
use Auth;
use DateTime;
use App\Tenant;
use App\FlightTiket;
use App\User;
use App\Flight;
use App\TourPckage;
use App\People;
use App\FlightPeople;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexFlight()
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
          $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
          $flights = Flight::where('tenant_id', Auth::User()->tenant_id)
                    ->paginate(35);
          $penandcomitem = TourPckage::completedAndPendingItem($route_id); 
          $current_dates = date('Y-m-d');
          $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
          return view('flight.index',compact('flights','tenant','penandcomitem','disableDeparture'));
        }
        else{
            return view('401.401');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */ 
    public function createFlight(Request $request)
    {
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $tourpackageID = TourPckage::where('tenant_id', '=', Auth::User()->tenant_id)
                        ->pluck('id');
        $depID = [];
            foreach ($tourpackageID as $value) {
                array_push($depID, $value);
            }
        if(in_array($route_id, $depID)){
          $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
          $flights = Flight::where('tour_package_id', $route_id)->where('tenant_id', Auth()->user()->tenant_id)->get();

          $penandcomitem = TourPckage::completedAndPendingItem($route_id);
          $pkg = TourPckage::where('tenant_id', Auth()->User()->tenant_id)->get();
         //$people = People::where('tenant_id', Auth()->User()->tenant_id)->get();
          $people = DB::table("peoples")
                          ->where('peoples.tour_package_id', $route_id)
                              ->where(function($q) {
                                  $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                              })
                          ->select("peoples.id","peoples.name")
                          ->get();
          foreach ($flights as $key => $flight){
              $flight_row = FlightPeople::where('flight_id',$flight->id)->where('tour_package_id',$route_id)->pluck('people_id')->toArray();
              $flight['people_id'] = $flight_row;

              $people_name = array();
              foreach ($flight_row as $key => $value) {
                  $ppl_name = People::where('id', $value)->first();
                  array_push($people_name, $ppl_name->name);
              }

              $flight['people_name'] = $people_name;
          }
          foreach ($flights as $key => $passes){
              $passes_row = FlightTiket::where('flight_id', $passes->id)->where('tour_package_id',$route_id)->pluck('document')->toArray();
              $passes['flight_ticket'] = $passes_row;

          }
          $current_dates = date('Y-m-d');
          $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
          return view('flight.create',compact('flights','tenant','penandcomitem','people','disableDeparture'));   
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchFlight(Request $request)
    {

        $data        = $request->all();
        $airline_code = $request->airline;
        $flight_no = $request->flight;
        $depar_date = $request->departure;
        $dep_date = date('Y-m-d', strtotime($depar_date));

        $current_date = date('d-m-Y');
        $curr_date = date('Y-m-d', strtotime($current_date. ' + 2 days'));
        if ($dep_date<=$curr_date) {
          $date=explode('-',$dep_date);
          $year= strval($date[0]);
          $month= strval($date[1]);
          $day= strval($date[2]);
          $url = 'https://api.flightstats.com/flex/flightstatus/rest/v2/json/flight/status/'.$airline_code.'/'.$flight_no.'/dep/'.$year.'/'.$month.'/'.$day.'?appId=ee26e160&appKey=1207fce2236ca2f155959cdf16009bab&utc=false';
          $json=file_get_contents($url);
          $api_data=json_decode($json);

          if(isset($api_data->error->errorCode)){
            $request->session()->flash('baderrors', 'Please enter valid details! Invalid value for airline cade or flight number or flight date');
                return redirect()->back();
          }
          else{
            if(isset($api_data->error)){
              $airlineCode = $api_data->request->airline->fsCode; //fill
              $flightNumber = $api_data->request->flight->requested;
              $dateYear = $api_data->request->date->year;
              $dateMonth = $api_data->request->date->month;
              $dateDay = $api_data->request->date->day;
              $departuredate = $dateYear.'-'.$dateMonth.'-'.$dateDay;
              return redirect()->back()->with([
                  'false' => "false",
                  'flightNumber' => $flightNumber,
                  'airlineCode' => $airlineCode,
                  'departuredate' => $departuredate
              ]);
            }
            else{
              if(isset($api_data->flightStatuses)){
                $air=$api_data->flightStatuses;
                $fs = count($air);
                if($fs > 0){
                  $departuredelayss=$air[0]->delays;
                  if(empty((array)$departuredelayss)){
                    $delayTime = '--';
                  }
                  else{   
                    $tdelays=$departuredelayss->departureGateDelayMinutes;
                    $delayTime = floor($tdelays / 60).' Hours '.($tdelays - floor($tdelays / 60) * 60).' Min';
                  }
                  $Terminal=$air[0]->airportResources;
                  if(empty((array)$Terminal)){
                    $terminalD ='--';
                    $departureG ='--';
                  }
                  else{
                    if(isset($Terminal->departureTerminal)){
                      $terminalD =$Terminal->departureTerminal;
                    }
                    else{
                      $terminalD='--';
                    }
                    if(isset($Terminal->departureGate)){
                      $departureG =$Terminal->departureGate;
                    }
                    else{
                      $departureG='--';
                    }
                  }
                  $totalTravel=$air[0]->flightDurations->scheduledBlockMinutes;
                  $timetotals = floor($totalTravel / 60).' Hours '.($totalTravel - floor($totalTravel / 60) * 60).' Min';
                  $airlineCode=$air[0]->carrierFsCode;
                  $flightNumber=$air[0]->flightNumber;
               
                  $departure=$air[0]->operationalTimes->publishedDeparture->dateLocal;
                  $departureTD=explode('T',$departure);
                  $departuredate= date('Y-m-d',strtotime($departureTD[0]));
                  $departuretim= strval($departureTD[1]);
                  $departuretime=explode(':',$departuretim);
                  $departuretimes= $departuretime[0]. ':' .$departuretime[1];
 
                  $arrival=$air[0]->operationalTimes->publishedArrival->dateLocal;
                  $arrivalTD=explode('T',$arrival);
                  $arrivaldate= date('Y-m-d',strtotime($arrivalTD[0]));
                  $arrivaltim= strval($arrivalTD[1]);
                  $arrivaltime=explode(':',$arrivaltim);
                  $arrivaltimes= $arrivaltime[0]. ':' .$arrivaltime[1];
                }
                else{
                  $airlineCode = $api_data->request->airline->fsCode; //fill
                  $flightNumber = $api_data->request->flight->requested;
                  $dateYear = $api_data->request->date->year;
                  $dateMonth = $api_data->request->date->month;
                  $dateDay = $api_data->request->date->day;
                  $departuredate = $dateYear.'-'.$dateMonth.'-'.$dateDay;
                  return redirect()->back()->with([
                      'false' => "false",
                      'flightNumber' => $flightNumber,
                      'airlineCode' => $airlineCode,
                      'departuredate' => $departuredate
                  ]);
                }

              }
              else{
                $route_ids = $request->route('id'); 
                $route_id = (int)$route_ids;
                $request->session()->flash('statuss', 'Please enter valid details!');
                return redirect()->route('add_flight',$route_id);
              }
              $airlines=$api_data->appendix->airlines;
              $airline=$airlines[0]->name;

              $airport=$api_data->appendix->airports;
              $airpor = count($airport);
              if($airpor > 0){
                  if($api_data->flightStatuses[0]->departureAirportFsCode == $airport[0]->fs){
                     $departure_airport_name=$airport[0]->name;
                     $departure_airport_city_country=$airport[0]->city. ', ' .$airport[0]->countryName;
                     $departure_latitude=$airport[0]->latitude;
                     $departure_longitude=$airport[0]->longitude;
                     $departure_iata=$airport[0]->iata;
                    // AIRPORT Arrival
                     $arrival_airport_name=$airport[1]->name;
                     $arrival_airport_city_country=$airport[1]->city. ', ' .$airport[1]->countryName;
                     $arrival_latitude=$airport[1]->latitude;
                     $arrival_longitude=$airport[1]->longitude;
                     $arrival_iata=$airport[1]->iata; 
                  }
                  else{
                     $departure_airport_name=$airport[1]->name;
                     $departure_airport_city_country=$airport[1]->city. ', ' .$airport[1]->countryName;
                     $departure_latitude=$airport[1]->latitude;
                     $departure_longitude=$airport[1]->longitude;
                     $departure_iata=$airport[1]->iata;
                    // AIRPORT Arrival
                     $arrival_airport_name=$airport[0]->name;
                     $arrival_airport_city_country=$airport[0]->city. ', ' .$airport[0]->countryName;
                     $arrival_latitude=$airport[0]->latitude;
                     $arrival_longitude=$airport[0]->longitude;
                     $arrival_iata=$airport[0]->iata;
                  }
              
                 $route_ids = $request->route('id'); 
                 $route_id = (int)$route_ids;
              }
              else{
                 $route_ids = $request->route('id'); 
                 $route_id = (int)$route_ids;
                 $request->session()->flash('statuss', 'Please enter valid details!');
                 return redirect()->route('add_flight',$route_id);
              }
              return redirect()->back()->with([
                'status' => "status",
                'flightNumber' => $flightNumber,
                'airline' => $airline, 
                'airlineCode' => $airlineCode,
                'departuredate' => $departuredate, 
                'departuretimes' => $departuretimes, 
                'arrivaldate' => $arrivaldate,
                'arrivaltimes' => $arrivaltimes, 
                'departure_airport_name' => $departure_airport_name, 
                'departure_airport_city_country' => $departure_airport_city_country,
                'departure_latitude' => $departure_latitude, 
                'departure_longitude' => $departure_longitude, 
                'departure_iata' => $departure_iata, 
                'arrival_airport_name' => $arrival_airport_name,
                'arrival_airport_city_country' => $arrival_airport_city_country, 
                'arrival_latitude' => $arrival_latitude,  
                'arrival_longitude' => $arrival_longitude,
                'arrival_iata' => $arrival_iata, 
                'timetotals' => $timetotals, 
                'terminalD' => $terminalD, 
                'departureG' => $departureG, 
                'delayTime' => $delayTime]);
            }
        }
      }
    else{
        $date=explode('-',$dep_date);
        $year= strval($date[0]);
        $month= strval($date[1]);
        $day= strval($date[2]);
        echo $url = 'https://api.flightstats.com/flex/schedules/rest/v1/json/flight/'.$airline_code.'/'.$flight_no.'/departing/'.$year.'/'.$month.'/'.$day.'?appId=ee26e160&appKey=1207fce2236ca2f155959cdf16009bab';
        $json=file_get_contents($url);
        $api_data=json_decode($json);
        if(isset($api_data->error->errorCode)){
          $request->session()->flash('baderrors', 'Please enter valid details! Invalid value for airline cade or flight number or flight date');
                return redirect()->back();
        }
        else{
          if($api_data->scheduledFlights){
            $air=$api_data->scheduledFlights;
            $airlineCode=$air[0]->carrierFsCode;
            $flightNumber=$air[0]->flightNumber;
           
            $departure=$air[0]->departureTime;
            $departureTD=explode('T',$departure);
            $departuredate= strval($departureTD[0]);
            $departuretim= strval($departureTD[1]);
            $departuretime=explode(':',$departuretim);
            $departuretimes= $departuretime[0]. ':' .$departuretime[1];
 
            $arrival=$air[0]->arrivalTime;
            $arrivalTD=explode('T',$arrival);
            $arrivaldate= strval($arrivalTD[0]);
            $arrivaltim= strval($arrivalTD[1]);
            $arrivaltime=explode(':',$arrivaltim);
            $arrivaltimes= $arrivaltime[0]. ':' .$arrivaltime[1];
            $airlines=$api_data->appendix->airlines;
            $airline=$airlines[0]->name;

            $airport=$api_data->appendix->airports;
            
            if($api_data->scheduledFlights[0]->departureAirportFsCode == $api_data->appendix->airports[0]->fs){
             // AIRPORT DEPARTURE
             $departure_airport_name=$airport[0]->name;
             $departure_airport_city_country=$airport[0]->city. ', ' .$airport[0]->countryName;
             $departure_latitude=$airport[0]->latitude;
             $departure_longitude=$airport[0]->longitude;
             $departure_iata=$airport[0]->iata;
             // AIRPORT ARRIVAL
             $arrival_airport_name=$airport[1]->name;
             $arrival_airport_city_country=$airport[1]->city. ', ' .$airport[1]->countryName; 
             $arrival_latitude=$airport[1]->latitude;
             $arrival_longitude=$airport[1]->longitude;
             $arrival_iata=$airport[1]->iata;
           }
           else{
             // AIRPORT DEPARTURE
             $departure_airport_name=$airport[1]->name;
             $departure_airport_city_country=$airport[1]->city. ', ' .$airport[1]->countryName;
             $departure_latitude=$airport[1]->latitude;
             $departure_longitude=$airport[1]->longitude;
             $departure_iata=$airport[1]->iata;
             // AIRPORT ARRIVAL
             $arrival_airport_name=$airport[0]->name;
             $arrival_airport_city_country=$airport[0]->city. ', ' .$airport[0]->countryName; 
             $arrival_latitude=$airport[0]->latitude;
             $arrival_longitude=$airport[0]->longitude;
             $arrival_iata=$airport[0]->iata;
           }
            $route_ids = $request->route('id'); 
            $route_id = (int)$route_ids;
            $dt=$departuredate. " " . $departuretimes;
            $dtime=str_replace('"','',$dt);

            $at=$arrivaldate. " " . $arrivaltimes;
            $atime=str_replace('"','',$at);

            $sd = new DateTime($dtime);
            $ad = new DateTime($atime);

            $interval =date_diff($sd,$ad);
            $hour_diff = $interval->format('%h:%i');
            $datetimes=explode(':',$hour_diff);
            $timetotals= $datetimes[0]. ' ' .'Hours'. ' ' .$datetimes[1]. ' ' .'Min';
        
            return redirect()->back()->with([
                'status' => "status",
                'flightNumber' => $flightNumber,
                'airline' => $airline, 
                'airlineCode' => $airlineCode,
                'departuredate' => $departuredate, 
                'departuretimes' => $departuretimes, 
                'arrivaldate' => $arrivaldate,
                'arrivaltimes' => $arrivaltimes, 
                'departure_airport_name' => $departure_airport_name, 
                'departure_airport_city_country' => $departure_airport_city_country,
                'departure_latitude' => $departure_latitude, 
                'departure_longitude' => $departure_longitude, 
                'departure_iata' => $departure_iata, 
                'arrival_airport_name' => $arrival_airport_name,
                'arrival_airport_city_country' => $arrival_airport_city_country, 
                'arrival_latitude' => $arrival_latitude,  
                'arrival_longitude' => $arrival_longitude,
                'arrival_iata' => $arrival_iata, 
                'timetotals' => $timetotals
            ]);
          }
          else{
              $route_ids = $request->route('id'); 
              $route_id = (int)$route_ids;
              $request->session()->flash('statuss', 'Please enter valid details!');
              return redirect()->route('add_flight',$route_id);
          }
        }
      }    
    }
    public function storeFlight(Request $request)
    {

        $data        = $request->all();
        $validatedData = $request->validate([
           //'flight_passes.*' => 'file|mimes:doc,docx,pdf,xls,xlsx,csv,txt',      
        ]);
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $fno = $request->flight_number;
        $ddate = $request->departure_date;
        $dep_date = date('Y-m-d', strtotime($ddate));
        //dd($dep_date);
        $adate = $request->departure_date;
        $ari_date = date('Y-m-d', strtotime($adate));
        $fUnique  = Flight::where('tour_package_id',$route_id)->where('flight_number',$fno)->where('departure_date',$dep_date)->first();
        if($fUnique){
            $request->session()->flash('sussess', 'That flight already added!');
            return redirect()->route('add_flight',$route_id);
        }
        else{
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        $flight  = new Flight;

        $flight->flight_number = $request->flight_number;
        $flight->airline = $request->airline;
        $flight->airline_code = $request->airline_code;
        $flight->departure_date = $dep_date;
        $flight->schedule_departure = $request->schedule_departure; 
        $flight->arrival_date = $ari_date;
        $flight->schedule_arrival = $request->arrivaltimes;
        $flight->departure_airport = $request->departure_airport;
        $flight->departure_city_country = $request->departure_city_country;
        $flight->departure_latitude = $request->departure_latitude;
        $flight->departure_longitude = $request->departure_longitude;
        $flight->departure_iata = $request->departure_iata;
        $flight->arrival_airport = $request->arrival_airport;
        $flight->arrival_city_country = $request->arrival_airport_city_country;
        $flight->arrival_latitude = $request->arrival_latitude;
        $flight->arrival_longitude = $request->arrival_longitude;
        $flight->arrival_iata = $request->arrival_iata;
        $flight->total_travel_time = $request->timetotals;
        $flight->departure_terminal = $request->terminalD;
        $flight->gate_number = $request->departureG;
        $flight->delay_time = $request->delayTime;
        $flight->tour_package_id = $route_id;
        $user = auth()->user();
        $flight->tenant_id = $user->tenant_id;
        $flight->user_id = $user->id;
        
        $flight->save();
        $flightNumber = $flight->flight_number;
        $last_id = $flight->id;
       // dd($last_id);
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        $file = $request->file('flight_passes');
        //$file = json_decode($request->docFiles);
        //dd($file);
        if($file){
            foreach ($file as $key => $fdocs) {
                  $namedoc = $fdocs->getClientOriginalName();
                  $part0 = substr("$namedoc",0, strrpos($namedoc,'.'));
                  $part2 = substr("$namedoc", (strrpos($namedoc,'.') + 1));
                  $arr = explode(" ",$part0); 
                  $part1 = implode("-",$arr);
                  $filename = $part1.'-'.time().'.'.$part2;
                //$filename = $fdocs->getClientOriginalName();
                // print_r($filename);
                // die;
                $storagePath = Storage::disk('s3')->put('flight'.'/'.'passes'.'/'.$filename, file_get_contents($fdocs), 'public');
                //$storagePath = Storage::disk('s3')->put('flight'.'/'.'passes'.'/'.$filename, $imagebase, 'public');

                $flightdocss = new FlightTiket;
                //print_r($last_id);die;
                $flightdocss->flight_id=$last_id;
                $flightdocss->document=$filename;
                $flightdocss->tour_package_id=$route_id;

                $flightdocss->save();  
              //}              
            } 
        }
            $people=$request->peoples;
             if($people){                
                foreach ($people as $value) {
                     $user = auth()->user();
                     $people = new FlightPeople;
                     $people->flight_id=$last_id;
                     $people->people_id=$value;
                     $people->tour_package_id=$route_id;
                     $people->tenant_id = $user->tenant_id;
                     $people->user_id = $user->id;
                     $people->save();
                }   
            }
          if($last_id == '' || $last_id == null) { 
            return response()->json([
               'already' => 'Flight details already exist!'
           ]);
          }
          else{
            return response()->json([
               'already' => 'Flight details submited successfully!'
           ]);
          }
        }
    }
    public function updateFlight(Request $request, $id)
    {

        $data        = $request->all();
        $fligtpp  = Flight::where('id',$id)->first();
        $pkg_id = $fligtpp->tour_package_id;
        $validatedData = $request->validate([
           //'flight_passes.*' => 'file|mimes:doc,docx,pdf,xls,xlsx,csv,txt|max:2048',      
        ]);
       
       
        $flight  = Flight::find($id);
        $flight->tour_package_id = $pkg_id;
        $user = auth()->user();
        $flight->tenant_id = $user->tenant_id;
        $flight->user_id = $user->id;
        
        $flight->save();
        $last_id = $flight->id;
        $file = json_decode($request->docFiles);
        // print_r($file);
        // die;
        FlightTiket::where('flight_id', '=', $last_id)->delete();
        if($file){
         // FlightTiket::where('flight_id', '=', $last_id)->delete();
            foreach($file as $fdocs) {
              if($fdocs != null){
                $tdoc = $fdocs->Content;
                
                $namedoc = $fdocs->FileName;
                $part0 = substr("$namedoc",0, strrpos($namedoc,'.'));
                $part2 = substr("$namedoc", (strrpos($namedoc,'.') + 1));
                $arr = explode(" ",$part0); 
                $part1 = implode("-",$arr);
                $imagebase = base64_decode(preg_replace('#^data:application/\w+;base64,#i', '',$tdoc));
                $filename = $part1.'-'.time().'.'.$part2;

                $storagePath = Storage::disk('s3')->put('flight'.'/'.'passes'.'/'.$filename, $imagebase, 'public');
                //$storagePath = Storage::disk('s3')->put('testing'.'/'.$filename, $imagebase, 'public');
                //print_r($filename);die;
                
                $flightdocss= new FlightTiket;
                $flightdocss->flight_id=$last_id;
                $flightdocss->document=$filename;
                $flightdocss->tour_package_id=$pkg_id;
                $flightdocss->save();   
              }             
            } 
        }
           
            $people=$request->people_edit;
             if($people){   
             FlightPeople::where('flight_id', '=', $last_id)->delete();             
                foreach ($people as $value) {
                     $user = auth()->user();
                     $people = new FlightPeople;
                     $people->flight_id=$last_id;
                     $people->people_id=$value;
                     $people->tour_package_id=$pkg_id;
                     $people->tenant_id = $user->tenant_id;
                     $people->user_id = $user->id;
                     $people->save();
                }   
            }
        $request->session()->flash('status', 'Flight updated successfully.');
            return redirect()->back();
        
    }
    public function getPeopleAjaxEdit(Request $request)
        {
            $data = [];
            $route_ids = $request->id; 
            //dd($route_ids);
            if($request->has('q')){
                $search = $request->q;
                
                $data = DB::table("peoples")
                        ->where('peoples.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                            })
                        ->select("peoples.id","peoples.name")
                        ->where('peoples.name','LIKE',"%$search%")
                        
                        ->get();
            }
            else{
              $data = DB::table("peoples")
                        ->select("peoples.id","peoples.name")
                         ->where('peoples.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                            })
                        ->limit(10)
                        ->get();
            }
            return response()->json($data);
        }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteFlight(Request $request, $id)
    {
      //dd($id);
        Flight::find($id)->delete();
        FlightTiket::where('flight_id',$id)->delete();
        return response()->json([
           'success' => 'Flight deleted successfully!'
       ]);
    }
}
