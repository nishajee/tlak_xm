<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Storage;
use Image;
use Auth;
use finfo;
use App\Tenant;
use App\HotelTiket;
use App\User;
use App\Hotel;
use App\People;
use App\HotelPeople;
use App\ElectricalSocket;
use App\HotelSocket;
use App\LocationPointOfInterest;
use App\TourPckage;
use App\Location;
use App\HotelAmenity;
use App\Amenity;
use App\Restaurent;
class HotelController extends Controller
{
    
    public function createHotel(Request $request, $id)
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
            $amenities = Amenity::get();
            $penandcomitem = TourPckage::completedAndPendingItem($route_id);
            $user = auth()->user();
            $hotels = Hotel::where([ ['tenant_id', auth()->user()->tenant_id], ['tour_package_id', $route_id]])->orderBy('id','DESC')->get();
            $HotelSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/hotel/';
            //$HotelSRCPath = $HotelSRC.'hotel/'.$imageName;
            //$HotelSRCPath = $HotelSRC.'hotel/passes/'.$imageName;

            $locationpoi = LocationPointOfInterest::join('locations','locations.id','=','location_point_of_interests.location_id')
                        ->where([ ['location_point_of_interests.tenant_id', auth()->user()->tenant_id], ['location_point_of_interests.tour_package_id', $route_id]])->distinct()->select('locations.id as loc_id','locations.name as loc_name')->paginate(15);
            //dd($locationpoi);
            $sockets = ElectricalSocket::all();

            foreach ($hotels as $key => $hotel){
                $hotel_row = HotelPeople::where('hotel_id',$hotel->id)->where('tour_package_id',$route_id)->pluck('people_id')->toArray();
                $hotel['people_id'] = $hotel_row;

                $people_name = array();
                foreach ($hotel_row as $key => $value) {
                    $ppl_name = People::where('id', $value)->first();
                    array_push($people_name, $ppl_name->name);
                }

                $hotel['people_name'] = $people_name;
            }

            foreach ($hotels as $key => $hotel){
                $socket_row = HotelSocket::where('hotel_id',$hotel->id)->where('tour_package_id',$route_id)->pluck('socket_id')->toArray();
                $hotel['socket_id'] = $socket_row;

                $socket_name = array();
                foreach ($socket_row as $key => $value) {
                    $sct_name = ElectricalSocket::where('id', $value)->first();
                    array_push($socket_name, $sct_name->name);
                }

                $hotel['socket_name'] = $socket_name;
            }
            foreach ($hotels as $key => $value) {
              $location_name = Location::where('id', $value->location)->value('name');
              $value['location_name'] = $location_name;
            }

            foreach ($hotels as $key => $hotel){
                $amety_row = HotelAmenity::where('hotel_id', $hotel->id)->where('tour_package_id',$route_id)->pluck('amenity_id')->toArray();
                $hotel['amety_id'] = $amety_row;
            }
            foreach ($hotels as $key => $passes){
                $passes_row = HotelTiket::where('hotel_id', $passes->id)->where('tour_package_id',$route_id)->pluck('document')->toArray();
                $passes['hotel_ticket'] = $passes_row;

            }
            // echo "<pre>";
            // print_r($hotels);
            // die();
            $people = DB::table("peoples")
                            ->where('peoples.tour_package_id', $route_ids)
                                ->where(function($q) {
                                    $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                                })
                            ->select("peoples.id","peoples.name")
                            ->get();
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();
            return view('hotel.create',compact('hotels','sockets', 'route_id','locationpoi','tenant','penandcomitem','amenities','people','HotelSRCPath','disableDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }



    public function storeHotel(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $validatedData = $request->validate([
            'hotel_name' =>'required',
            //'address' => 'required',
            'country' => 'required',
            //'state' => 'required',
            'location' => 'required',
            //'place_id' => 'required',
            //'lat' => 'required',
            //'long' => 'required',
            'total_room' => 'required',
            'peoples.*' => 'required',
            'rating' => 'required',
            
        ]);
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids; 
        $user = auth()->user(); 
        $hotel = new Hotel;
        $hotel->tour_package_id = $route_id;
        $hotel->name = $request->hotel_name;
        $hotel->address = $request->address;
        $hotel->country = $request->country;
        $hotel->state = $request->state;
        $hotel->hotel_rating = $request->rating;
        $hotel->location = $request->location;
        $hotel->latitude = $request->lat;
        $hotel->longitude = $request->long;
        //$hotel->place_id = $request->place_id;
        $hotel->type = $request->hotel_type;
        $hotel->description = $request->description;
        $hotel->total_room = $request->total_room;
        $hotel->tenant_id = $user->tenant_id;
        $hotel->user_id = $user->id;

            if($request->file('hotel_image_upld')){ 
                $file = $request->file('hotel_image_upld');
                $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
                $image = Image::make($file);
                Storage::disk('s3')->put('hotel/'.$imageName, $image->stream(), 'public');
                $hotel->hotel_image = $imageName;                  
            }
            else{
                $url = $request->hotel_image;
                $type = pathinfo($url, PATHINFO_EXTENSION);
                $data = file_get_contents($url);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
                $imageName = str_random(5).time() . '.png';
                $img = Storage::disk('s3')->put('hotel'.'/'.$imageName, $image, 'public');
               $hotel->hotel_image =  $imageName;
            }

        $hotel->save();
        $last_id = $hotel->id;
        $last_location = $hotel->location;
        $pkg_ids = $hotel->tour_package_id;
            //Pdf

            $file = $request->file('hotel_pass');
            if($file){
                   
            foreach ($file as $hdocs) {
                $namedoc = $hdocs->getClientOriginalName();
                $part0 = substr("$namedoc",0, strrpos($namedoc,'.'));
                $part2 = substr("$namedoc", (strrpos($namedoc,'.') + 1));
                $arr = explode(" ",$part0); 
                $part1 = implode("-",$arr);
                $filename = $part1.'-'.time().'.'.$part2;
                //$filename = str_random(5).time().'.'.$hdocs->getClientOriginalExtension();
                $storagePath = Storage::disk('s3')->put('hotel/passes/'.$filename, file_get_contents($hdocs), 'public'); 
                
                $hoteldocss= new HotelTiket;
                $hoteldocss->hotel_id=$last_id;
                $hoteldocss->tour_package_id=$route_id;
                $hoteldocss->document=$filename;

                $hoteldocss->save();                
                } 
            }
        $people=$request->peoples;
        //dd($people);
             if($people){                
                foreach ($people as $value) {
                     $user = auth()->user();
                     $people = new HotelPeople;
                     $people->hotel_id=$last_id;
                     $people->people_id=$value;
                     $people->location_id=$last_location;
                     $people->tour_package_id=$route_id;
                     $people->tenant_id = $user->tenant_id;
                     $people->user_id = $user->id;
                     $people->save();
                }   
             }
         $amenity=$request->amenities;
        //dd($people);
             if($amenity){                
                 foreach ($amenity as $value) {
                     $user = auth()->user();
                     $amenitys = new HotelAmenity;
                     $amenitys->hotel_id=$last_id;
                     $amenitys->amenity_id=$value;
                     $amenitys->tour_package_id=$route_id;
                     $amenitys->tenant_id = $user->tenant_id;
                     $amenitys->user_id = $user->id;
                     $amenitys->save();
                 }   
             }
          $sockets=$request->socket_type;
        //dd($people);
        if($sockets){                
            foreach ($sockets as $value) {
                $user = auth()->user();
                $sockets = new HotelSocket;
                $sockets->hotel_id=$last_id;
                $sockets->socket_id=$value;
                $sockets->tour_package_id=$route_id;
                $sockets->tenant_id = $user->tenant_id;
                $sockets->user_id = $user->id;
                $sockets->save();
            }   
        }
        //Restaurant add
        $url = file_get_contents('https://api.tomtom.com/search/2/search/restaurant.json?language=en-US&limit=20&lat='.$request->lat.'&lon='.$request->long.'&radius=5000&categorySet=7315&key=6vdxVANLJketgjeoT3dvURPnu4ny3VWy');
        $jsondata = json_decode($url);
        $i = 1;
        foreach ($jsondata->results as $key => $value) {
            $restaurant = new Restaurent();
            if((array_key_exists('phone', $value->poi))){
                $restaurant->phone = $value->poi->phone;
            }
            if((array_key_exists('municipalitySubdivision', $value->address))){
                $restaurant->location = $value->address->municipalitySubdivision;
            }

            if((array_key_exists('postalCode', $value->address))){
                $restaurant->postal_code = $value->address->postalCode;
            }

            $categories = $value->poi->categories[0];
            $res_image = $categories.'_'.$i.'.jpg';

            $restaurant->name = $value->poi->name;
            $restaurant->latitude = $value->position->lat;
            $restaurant->longitude = $value->position->lon;
            $restaurant->country = $value->address->country;
            $restaurant->address = $value->address->freeformAddress;
            $restaurant->local_name = $value->address->localName;
            $restaurant->hotel_id = $last_id;
            $restaurant->tour_package_id = $route_id;
            $restaurant->tenant_id = $user->tenant_id;
            $restaurant->type = $value->poi->categories[0];
            $restaurant->poi_id = $value->id;
            $restaurant->image = $res_image;
            $restaurant->save();
            if($i > 3){
                $i = 0;
            }
            $i++;
        }
        //$updatestatus = TourPckage::completeStatus($route_id);    // Closed 19-May-2020 
        // $request->session()->flash('status', 'Hotel created successfully.');
        return Redirect::back();
    }

    public function updateHotel(Request $request, $id)
    {

        $hotel  = Hotel::where('id',$id)->first();
        $pkg_id = $hotel->tour_package_id;
        
        $data        = $request->all();
        $validatedData = $request->validate([
           'edit_name' => 'required',
           'country_name' => 'required|max:255',
           //'state_name' => 'required',
           'location_name' =>'required',
           'total_room_no' =>'required',
           'people_edit' =>'required',
           
        ]); 
            $hotel       = Hotel::find($id);
            $hotel->name = $request->edit_name;
            $hotel->address = $request->hotel_address;
            $hotel->country = $request->country_name;
            $hotel->state = $request->state_name;
            $hotel->location = $request->location_name;
            $hotel->description = $request->hotel_description;
            $hotel->total_room = $request->total_room_no;
            $hotel->type = $request->edit_hotel_type;
            //$hotel->amenities = $request->amenities;
            // $hotels=substr($request->edit_name,0,4);
            //     $file = $request->file('update_hotel_image');

            if($request->file('update_hotel_image')){ 
                $file = $request->file('update_hotel_image');
                $imageName = str_random(5).time().'.'.$file->getClientOriginalExtension();
                $image = Image::make($file);
                Storage::disk('s3')->put('hotel/'.$imageName, $image->stream(), 'public');
                $hotel->hotel_image = $imageName;
            }
                
            $hotel->save();
            $last_id = $hotel->id;
            $file = json_decode($request->docFiles);
        if($file){
            HotelTiket::where('hotel_id', $last_id)->delete();
            foreach ($file as $key => $hdocs) {
                if($hdocs != null){
                    $tdoc = $hdocs->Content;
                    $namedoc = $hdocs->FileName;
                    $part0 = substr("$namedoc",0, strrpos($namedoc,'.'));
                    $part2 = substr("$namedoc", (strrpos($namedoc,'.') + 1));
                    $arr = explode(" ",$part0); 
                    $part1 = implode("-",$arr);
                    $imagebase = base64_decode(preg_replace('#^data:application/\w+;base64,#i', '',$tdoc));
                    $filename = $part1.'-'.time().'.'.$part2;
                    $storagePath = Storage::disk('s3')->put('hotel/passes/'.$filename, $imagebase, 'public'); 

                    $hoteldocss= new HotelTiket;
                    $hoteldocss->hotel_id=$last_id;
                    $hoteldocss->document=$filename;
                    $hoteldocss->tour_package_id=$pkg_id;
                    $hoteldocss->save(); 
                }
            }
        }
       
        
            HotelPeople::where('hotel_id', '=', $last_id)->delete();

            $peoples=$request->people_edit;

             if($peoples){                
                foreach ($peoples as $value) {
                     $people = new HotelPeople;
                     $people->hotel_id=$last_id;
                     $people->people_id=$value;
                     $people->tour_package_id=$pkg_id;
                     $user = auth()->user();
                     $people->tenant_id = $user->tenant_id;
                     $people->user_id = $user->id;
                     $people->save();
                }   
             }

            HotelSocket::where('hotel_id', '=', $last_id)->delete();
            $sockets=$request->socket_edit;
            if($sockets){                
                 foreach ($sockets as $value) {
                     $socket = new HotelSocket;
                     $socket->hotel_id=$last_id;
                     $socket->socket_id=$value;
                     $socket->tour_package_id=$pkg_id;
                     $user = auth()->user();
                     $socket->tenant_id = $user->tenant_id;
                     $socket->user_id = $user->id;
                     $socket->save();
                 }   
            }
            HotelAmenity::where('hotel_id', '=', $last_id)->delete();
            $amenity=$request->amenities;
            if($amenity){                
                 foreach ($amenity as $value) {
                     $amenity = new HotelAmenity;
                     $amenity->hotel_id=$last_id;
                     $amenity->amenity_id=$value;
                     $amenity->tour_package_id=$pkg_id;
                     $user = auth()->user();
                     $amenity->tenant_id = $user->tenant_id;
                     $amenity->user_id = $user->id;
                     $amenity->save();
                 }   
            }
            $request->session()->flash('status', 'Amenity updated successfully.');
            return redirect()->back();
        }
    

    public function getPeopleAjax(Request $request)
        {
            $data = [];
            $route_ids = $request->id; 
            //$d = HotelPeople::pluck('people_id','location_id')->all();
            //dd()
            if($request->has('q')){
                $search = $request->q;
                
                $data = DB::table("peoples")
                        ->where('peoples.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                            })
                        ->select("peoples.id","peoples.name")
                        //->whereNotIn('id',$d)
                        //->whereNotIn('id')
                        ->where('peoples.name','LIKE',"%$search%")
                        
                        ->get();
            }
            else{
              //$d = HotelPeople::pluck('people_id','location_id')->all();
              $data = DB::table("peoples")
                        ->select("peoples.id","peoples.name")
                        //->whereNotIn('id',$d)
                        //->whereNotIn('id')
                         ->where('peoples.tour_package_id', $route_ids)
                            ->where(function($q) {
                                $q->where('peoples.tenant_id', auth()->user()->tenant_id);
                            })
                        ->limit(10)
                        ->get();
            }
            return response()->json($data);
        } 

        public function getPeopleAjaxEdit(Request $request)
        {
            $data = [];
            $route_ids = $request->id; 
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

        public function getSocketAjax(Request $request)
        {
            $data = []; 
            if($request->has('q')){
                $search = $request->q;
                $data = DB::table("electrical_sockets")
                        ->select("id","name")
                        ->where('electrical_sockets.name','LIKE',"%$search%")
                        ->get();
            }
            else{
              $data = $data = DB::table("electrical_sockets")
                        ->select("id","name")
                        ->limit(10)
                        ->get();
            }
            return response()->json($data);
        } 

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
    public function deleteHotel(Request $request, $id)
    {
      //dd($id);
        $hotel = Hotel::find($id)->delete();
        $aa = HotelAmenity::where('hotel_id',$id)->delete();
        $pp = HotelPeople::where('hotel_id', $id)->delete();
        $ss = HotelSocket::where('hotel_id', $id)->delete();
        $aa = HotelTiket::where('hotel_id',$id)->delete();
        return response()->json([
           'success' => 'Itineary deleted successfully!'
       ]);
    }
}
