<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
//use Validator,Redirect,Response,File;
use Intervention\Image\ImageServiceProvider;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;
use Storage;
use Image;
use Auth;
use finfo;
use App\Tenant;
use App\User;
use App\PointOfInterest;
use App\WatPointOfInterest;
use App\PointOfInterestIcon;
use App\Location;
use App\PointOfInterestImage;
use App\WatPointOfInterestImage;
use App\PoiTemp;
use App\PoiTempImage;
use App\LocationPointOfInterest;
class PointOfInterestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permission = User::getPermissions();
        if (Gate::allows('poi_view',$permission)) {
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $keywords= Input::get('search');

            $pois = DB::table('point_of_interests')->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
              ->where(function($query)use($keywords){
                $query->where('point_of_interests.name', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.location_name', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.address', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.locality', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.point_type', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.country_name', 'LIKE','%'.$keywords.'%');
                })
                ->where('point_of_interests.tenant_id',auth()->user()->tenant_id)
                ->select('point_of_interests.*','point_of_interest_icons.icon_image','point_of_interest_icons.name as iconname')->orderBy('id','DESC')->paginate(20);
          foreach ($pois as $key => $images){
            $images_row = PointOfInterestImage::where('point_of_interest_id', $images->id)
                          ->pluck('poi_image')->toArray();
            $images->poi_images = $images_row;
          }
            $PoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/poi/';
            $location = Location::where('tenant_id',auth()->user()->tenant_id)
                ->select('id','name as locName','utc_offset as timezone','country_name')->orderBy('id','DESC')->paginate(25);
            if ($request->ajax()) {
                return view('pointofinterest.poi_data', compact('pois','tenant','location','PoiSRCPath'));
            }

            // $location = Location::where('tenant_id',auth()->user()->tenant_id)
            //     ->select('id','name as locName','utc_offset as timezone')->orderBy('id','DESC')->paginate(25);
            //$PoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/poi/';
            return view('pointofinterest.index',compact('pois','location','tenant','permission','PoiSRCPath'));
        }
        else{
            return abort(403);
        }

    }
     
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = User::getPermissions();
        if (Gate::allows('poi_create',$permission)) {
            $poiicon = PointOfInterestIcon::get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $temppois = PoiTemp::where('user_id', Auth()->user()->id)->get();
            $temppoicount = count($temppois); 
            $latlong = DB::table('countries')->where('country',Auth()->user()->address_country)->select('latitude','longitude')->first();
            //dd($latlong);
            $PoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/poi/';
            return view('pointofinterest.create',compact('poiicon','tenant', 'PoiSRCPath','latlong'));
        }
        else{
            return abort(403);
        }            

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
           'name' => 'required',
           'country' => 'required',
           'lat' => 'required',
           'long' => 'required',
           'destination' =>'required',
        ]); 
      
        //dd($array_check);
        if($request->utchour == "UTCTIME"){
            $utcOffset = $request->utc_offset;
        }
        else{
          $utcOffsetInMinuts = $request->utc_offset;
        //$utcOffsetInMinuts = '330';
            
            $utcOffsetInHour = floor($utcOffsetInMinuts / 60);
            $utcOffsetRemainingMinuts = $utcOffsetInMinuts - ($utcOffsetInHour * 60);
            if($utcOffsetRemainingMinuts==0.0){
              $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts.'0';
            }
            else{
              $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts;
            }
            if($offset >= 10){
              $utcOffset = 'UTC'.' +'.$offset;
              //dd($utcOffset);
            }
            elseif($offset >= 0 && $offset <= 9 ){
              $utcOffset = 'UTC'.' +'.'0'.$offset;
              //dd($utcOffset);
            }
            elseif($offset < 0 && $offset >= -9 ){
              $dataoset=explode('-',$offset);
              //dd($dataoset[1]);
              $utcOffset = 'UTC'.' '.'-'.'0'.$dataoset[1];
              //dd($utcOffset);
            }
            else{
              $offset = $utcOffsetInMinuts/60;
              $utcOffset = 'UTC'.' '.$offset;
              //dd($utcOffset);
            }
          }
            $pointOfInterest = PointOfInterest::where('place_id',$request->place_id)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();

        if($pointOfInterest==''){ 
            $poi       = new PointOfInterest;
            $poi->name = $request->name;
            $poi->country_name = $request->country;
            $poi->latitude = $request->lat;
            $poi->longitude = $request->long;
            $poi->description = $request->description;
            $poi->location_name = $request->destination;
            $poi->point_of_interest_icon_id = $request->poiicon;
            $poi->address = $request->address;
            $poi->place_id = $request->place_id;
           // $poi->point_type = $request->point_type;
            $poi->iso_2 = $request->iso_2;
            $poi->utc_offset = $utcOffset;
            $user = auth()->user();

            $poi->tenant_id = $user->tenant_id;
            $poi->user_id = $user->id;
            if($request->cropedimage){
                $url = $request->cropedimage;
                $basename = basename($request->cropedimage);
                $type = pathinfo($url, PATHINFO_EXTENSION);
                $data = file_get_contents($url);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
                $imageName = str_random(5).time() . '.png';
                Storage::disk('s3')->put('poi/'.$imageName, $image, 'public');
                $poi->banner_image = $imageName; 
            }       
            $poi->save();
            $last_id = $poi->id;
            $someArray = json_decode($request->poiImages);
          
            if($someArray){
                foreach ($someArray as $images) {
                  if($images != null){
                    $image = $images->Content;
                    $imagebase = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image));
                    $imageName = str_random(5).time() . '.png';
                    $storagePath = Storage::disk('s3')->put('poi'.'/'.$imageName, $imagebase, 'public');
                    $img= new PointOfInterestImage;
                    $img->point_of_interest_id=$last_id;
                    $img->poi_image=$imageName;
                    $img->save(); 
                    }               
                } 
            }
          }
            $desti = Location::where('name',$request->destination)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();
              if($desti == null)//if doesn't exist: create
                {
                    $processes = Location::create([
                        'name' => $request->input('destination'),
                        'country_name' => $request->input('country'),
                        'utc_offset' => $utcOffset,
                        'tenant_id' => auth()->user()->tenant_id,
                        'user_id' => auth()->user()->id
                        ]); 
                }
                else //if exist: update
                {
                  $desti->update(['name'=>$request->destination,'utc_offset' => $utcOffset]);

                }
              
          // Forms data save in wat poi if records not founds
            $pointOfInterestss = WatPointOfInterest::where('place_id',$request->place_id)
                                ->orWhere('name',$request->name)
                                ->first();
            if($pointOfInterestss == ''){
              $watpoi       = new WatPointOfInterest;
              $watpoi->name = $request->name;
              $watpoi->country_name = $request->country;
              $watpoi->latitude = $request->lat;
              $watpoi->longitude = $request->long;
              $watpoi->description = $request->description;
              $watpoi->location_name = $request->destination;
              $watpoi->address = $request->address;
              $watpoi->place_id = $request->place_id;
              $watpoi->wat_place_id = "WAT-".Str::random(25);
              $watpoi->point_of_interest_icon_id = $request->poiicon;
              //$watpoi->point_type = $request->point_type;
              $watpoi->iso_2 = $request->iso_2;
              $watpoi->utc_offset = $utcOffset;
              if($request->cropedimage){
                $url = $request->cropedimage;
                $basename = basename($request->cropedimage);
                $type = pathinfo($url, PATHINFO_EXTENSION);
                $data = file_get_contents($url);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
                $imageName = str_random(5).time() . '.png'; 
                Storage::disk('s3')->put('poi/'.$imageName, $image, 'public');
                $watpoi->banner_image = $imageName;
              }  
              $watpoi->save();

              $wat_last_id = $watpoi->id;
              $someArray = json_decode($request->poiImages);
            
              if($someArray){
                  foreach ($someArray as $images) {
                    if($images != null){
                      $image = $images->Content;
                      $imagebase = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image));
                      $imageName = str_random(5).time() . '.png';
                      $storagePath = Storage::disk('s3')->put('poi'.'/'.$imageName, $imagebase, 'public');
                      $img= new PointOfInterestImage;
                      $img->point_of_interest_id=$last_id;
                      $img->poi_image=$imageName;

                      $img->save(); 
                      }               
                  } 
              }
            }
        return response()->json(['poiLocation' => $request->destination]);
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
        $permission = User::getPermissions();
        if (Gate::allows('poi_edit',$permission)) {
            $pois = PointOfInterest::where('id',$id)->first();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $poiicon = PointOfInterestIcon::get();
            $poiimages = PointOfInterestImage::where('point_of_interest_id',$id)->get();
            
            $imagePath = [];
            if($poiimages){
              $i = 0;
              foreach ($poiimages as $value) {

                $imagePath[$i]['link'] = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/poi/'.$value->poi_image;
                $imagePath[$i]['name'] = $value->poi_image;
                $i++;
              }
              //dd($imagePath);
            }

           // exit;
            

           $PoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/poi/';
            return view('pointofinterest.edit',compact('pois','poiicon','tenant','poiimages','PoiSRCPath','imagePath'));
        }
        else{
            return abort(403);
        }                  
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
        $validatedData = $request->validate([
           'name' => 'required',
           'country' => 'required',
           'destination' =>'required',
        ]);

            $poi       = PointOfInterest::find($id);
            $poi->name = $request->name;
            $poi->country_name = $request->country;
            $poi->description = $request->description;
            $poi->location_name = $request->destination;
            $poi->point_of_interest_icon_id = $request->poiicon;
            $poi->address = $request->address;
            $poi->point_type = $request->point_type;
            $user = auth()->user();
            if($request->cropedimage){
                $url = $request->cropedimage;
                $basename = basename($request->cropedimage);
                $type = pathinfo($url, PATHINFO_EXTENSION);
                $data = file_get_contents($url);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
                $imageName = str_random(5).time() . '.png';
                Storage::disk('s3')->put('poi/'.$imageName, $image, 'public');
                $poi->banner_image = $imageName;
              } 
           
            $poi->save();
            $last_id = $poi->id;
                //PointOfInterestImage::where('point_of_interest_id', '=', $last_id)->delete();
            $someArray = json_decode($request->poiImages);
            if(empty($someArray)){
                PointOfInterestImage::where('point_of_interest_id', '=', $last_id)->delete();
            }
            else{
              PointOfInterestImage::where('point_of_interest_id', '=', $last_id)->delete();
                foreach ($someArray as $images) {
                  if($images != null){
                    $image = $images->Content;
                    $imagebase = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image));
                    $imageName = str_random(5).time() . '.png';
                    $storagePath = Storage::disk('s3')->put('poi'.'/'.$imageName, $imagebase, 'public');
                    $img= new PointOfInterestImage;
                    $img->point_of_interest_id=$last_id;
                    $img->poi_image=$imageName;

                    $img->save();
                    }                
                } 
            }
            
            $request->session()->flash('status', 'Point of interest edited.');
            return back();
        
    }

  public function locationUpdate(Request $request, $id){
            $data        = $request->all();
            //dd($data);
            
            $location = Location::findOrFail($id);
            $destin = Location::where('name',$request->location)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();

          if($destin == null || $destin == ''){
            $location->name = $request->location;
            //$location->country_name = $request->country;
            //$location->utc_offset = $request->edit_utc;
            $location->save();
            $poi = PointOfInterest::where('location_name', $request->location_before)->where('tenant_id', Auth()->user()->tenant_id)->get('id')->toArray();
            foreach ($poi as $key => $value) {
                PointOfInterest::where('id', $value['id'])->update(['location_name' => $request->location]);
            }
            return Response()->json(['status' => 200, 'statusmsg' => 'Successfully update!!']);
          }

          else{
            // if($destin == null || $destin == ''){
            //   $location->name = $request->location;
            // }
            // $location->country_name = $request->country;
            // $location->utc_offset = $request->edit_utc;
            // $location->save();
            return response()->json(['status' => 200, 'statusms' => 'Location name already exist!!']);
          }
            
    }

  public function addLocationPoi(Request $request){

        $desti = Location::where('name',$request->location_name)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();
          if($desti == null || $desti == '')//if doesn't exist: create
          {
            $utcOffsetInMinuts = $request->utc_offset;
        //$utcOffsetInMinuts = '330';
            
            $utcOffsetInHour = floor($utcOffsetInMinuts / 60);
            $utcOffsetRemainingMinuts = $utcOffsetInMinuts - ($utcOffsetInHour * 60);
            if($utcOffsetRemainingMinuts==0.0){
              $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts.'0';
            }
            else{
              $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts;
            }
            if($offset >= 10){
              $utcOffset = 'UTC'.' +'.$offset;
              //dd($utcOffset);
            }
            elseif($offset >= 0 && $offset <= 9 ){
              $utcOffset = 'UTC'.' +'.'0'.$offset;
              //dd($utcOffset);
            }
            elseif($offset < 0 && $offset >= -9 ){
              $dataoset=explode('-',$offset);
              //dd($dataoset[1]);
              $utcOffset = 'UTC'.' '.'-'.'0'.$dataoset[1];
              //dd($utcOffset);
            }
            else{
              $offset = $utcOffsetInMinuts/60;
              $utcOffset = 'UTC'.' '.$offset;
              //dd($utcOffset);
            }
            $processes = Location::create([
              'name' => $request->input('location_name'),
              'country_name' => $request->input('country_name'),
              'utc_offset' => $utcOffset,
              'tenant_id' => auth()->user()->tenant_id,
              'user_id' => auth()->user()->id,
            ]);

          return Response()->json(['status' => 200, 'message' => 'Successfully added!!']);
          }
          else{
            return response()->json(['status' => 200, 'smessage' => 'Location already exist!!']);
          }
    }

    //Image cropper for edit poi form
        public function cropImageEditPage(Request $request){

            $data = $request->image;
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $imageName = time() . '.png';
            $relPath = 'images/cropdumyimages/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                Image::make($data)->save( public_path($relPath . $imageName ) );
            //file_put_contents($imageName, $data);
                $urls = public_path("images/cropdumyimages/".$imageName);
                $url = url("images/cropdumyimages/".$imageName);
            //echo '<img src="'.$url.'" class="img-thumbnail" />';
            return response()->json(['img' => $url, 'url' => $urls]);

    }

    //Extra features for new POI

    // poi search from database
    public function poiSearchOpenDb(Request $request){


        $search = $request->text;
            $poi_db = WatPointOfInterest::select('*')
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhere('location_name', 'like', '%'.$search.'%');
        $poi = $poi_db->get();

        $status = [
            'error' => false,
            'poi' =>$poi,
            'key'=>$search,
        ];
        return response()->json($status, 200);
    }
// poi search from database multiple images
    public function getWatPoiImages(Request $request)
        {
            //$data = [];
            $watimgeid = $request->pid; 
            //return $watimgeid;
            $datas = WatPointOfInterestImage::select("poi_image")
                        ->where('wat_point_of_interest_id', $watimgeid)
                        ->get();
            $status = [
                'imgs' =>$datas,
            ];
            return response()->json($status);
        }
    public function getRelatedWatPoi(Request $request)
    {
            //$data = [];
            $watlocname = $request->locName;
            $pids = $request->PoiId;
            //dd($pids);
            $datas = WatPointOfInterest::select('*')
                      ->where('location_name', $watlocname)
                      ->where('id', '!=', $pids)
                      ->inRandomOrder()
                      ->get();
                  $array_poi = array();
                  foreach ($datas as $value) {
                    if($value->name != PointOfInterest::where('tenant_id' , auth()->user()->tenant_id)->where('location_name', $watlocname)->where('name', $value->name)->value('name'))
                    {
                      $poi = ['id'=>$value->id,'name'=>$value->name,'location_name'=>$value->location_name,'country_name'=>$value->country_name,'banner_image'=>$value->banner_image];
                      array_push($array_poi, $poi);
                    }    
                  }
               $tenant_poi = PointOfInterest::where('tenant_id' , auth()->user()->tenant_id) 
                             ->where('location_name', $watlocname)
                             ->inRandomOrder()
                             ->select('id','name','banner_image','location_name','country_name')
                             ->get();  
             $status = [
                'relatedPoi' => $array_poi,
                'havingPoi'  => $tenant_poi,
            ];
            // //return $status;
            return response()->json($status);
    }

    public function getRelatedWatPoiTomTomFun(Request $request)
        {
            //$data = [];
            $watlocname = $request->locName;
            
            $tenant_poi = PointOfInterest::where('tenant_id' , auth()->user()->tenant_id) 
                             ->where('location_name', $watlocname)
                             ->inRandomOrder()
                             ->select('id','name','banner_image','location_name','country_name')
                             ->get();
            $datas = WatPointOfInterest::select('wat_point_of_interests.*')
                      ->where('location_name', $watlocname)
                      ->select('id','name','banner_image','location_name','country_name')
                      ->get();

                $array_poi = array();
                  foreach ($datas as $value) {
                    if($value->name != PointOfInterest::where('tenant_id', auth()->user()->tenant_id)->where('location_name', $watlocname)->where('name', $value->name)->value('name'))
                    {
                      $poi = ['id'=>$value->id,'name'=>$value->name,'location_name'=>$value->location_name,'country_name'=>$value->country_name,'banner_image'=>$value->banner_image];
                      array_push($array_poi, $poi);
                    }    
                  }
             $status = [
                'relatedPoi' => $array_poi,
                'havingPoi'  => $tenant_poi,
            ];
            //dd($status);
            // //return $status;
            return response()->json($status);
        } 
//Image cropper for create form
        public function cropImage(Request $request){

            $data = $request->image;
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $imageName = time() . '.png';
            $relPath = 'images/cropdumyimages/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                Image::make($data)->save( public_path($relPath . $imageName ) );
            //file_put_contents($imageName, $data);
                $urls = public_path("images/cropdumyimages/".$imageName);
                $url = url("images/cropdumyimages/".$imageName);
            //echo '<img src="'.$url.'" class="img-thumbnail" />';
            return response()->json(['img' => $url, 'url' => $urls]);

    }
//Image cropper for create form model edit
    public function editCropImage(Request $request){

            $data = $request->editimage;
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);
            $imageName = time() . '.png';
            $relPath = 'images/cropdumyimages/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                Image::make($data)->save( public_path($relPath . $imageName ) );
            //file_put_contents($imageName, $data);
                $urls = public_path("images/cropdumyimages/".$imageName);
                $url = url("images/cropdumyimages/".$imageName);
            //echo '<img src="'.$url.'" class="img-thumbnail" />';
            return response()->json(['img' => $url, 'url' => $urls]);

    }
// Temp point of interest data save in tem from multiple checkbox and api

  public function tempPointOfInterest(Request $request){
    if($request->button_id == "send_temp_form"){  
      if($request->otherPoi != '' || $request->otherPoi != null){
        foreach ($request->otherPoi as $value) {
          $watpoi = WatPointOfInterest::where('id',$value)->first();
          $poi = PoiTemp::where('place_id',$watpoi->place_id)
                    ->where(function($q) {
                        $q->where('user_id', auth()->user()->id);
                      })
                    ->where('name',$watpoi->name)
                    ->first();


          if($poi == ''){
            // $watpoiicon = PointOfInterestIcon::where('id',$watpoi->point_of_interest_icon_id)->first();
            $str = $watpoi->utc_offset;
            $str = ltrim($str, 'UTC ');
            $str = ltrim($str, '+');
            if($str[0] == '0'){
                $str = ltrim($str, '0');
            }
            $arr = explode(':', $str);
            $minuts = (($arr[0]*60)+$arr[1]);

            $poitemps       = new PoiTemp;
            $poitemps->name = $watpoi->name;
            $poitemps->country_name = $watpoi->country_name;
            $poitemps->latitude = $watpoi->latitude;
            $poitemps->longitude = $watpoi->longitude;
            $poitemps->description = $watpoi->description;
            $poitemps->location_name = $watpoi->location_name;
            $poitemps->point_of_interest_icon_id = $watpoi->point_of_interest_icon_id;
            $poitemps->address = $watpoi->address;
            $poitemps->place_id = $watpoi->place_id;
            //$poitemps->point_type = $watpoiicon->name;
            $poitemps->iso_2 = $watpoi->iso_2;
            $poitemps->utc_offset = $minuts;
            $poitemps->banner_image = $watpoi->banner_image;
            $user = auth()->user();
            $poitemps->user_id = $user->id;
            $poitemps->save();
            $last_id = $poitemps->id;
            $poitempimgs = WatPointOfInterestImage::where('wat_point_of_interest_id', $watpoi->id)
                          ->get();
                if(count($poitempimgs) > 0){                
                    foreach ($poitempimgs as $watimages) {
                         $poitempsss = new PoiTempImage;
                         $poitempsss->poi_temp_id=$last_id;
                         $poitempsss->poi_image=$watimages->poi_image;
                         $poitempsss->user_id = auth()->user()->id;
                         $poitempsss->save();
                    }   
                }
          }
        }
      }
      if($request->locationPoiSearch){
        $location_name = $request->locationPoiSearch; 
        $dest_name_ar=explode(' ',$location_name);
        $place_name =implode('+',$dest_name_ar); //dd($place_name);
        $keywords = "point+of+interest";
        $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query='.$keywords.'+'.$place_name.'&key=AIzaSyDeaIvmws05Lghj6CUUMBvM68Y2qBftMVw';
        $json=file_get_contents($url);
        $api_data=json_decode($json);

        foreach($api_data->results as $data){
          $name=$data->name;  
          $address=$data->formatted_address;
          $lat=$data->geometry->location->lat;
          $long=$data->geometry->location->lng;
          $place_id=$data->place_id;          
          //Place Api function..
          $place_details=$this->getPlaceDetails($place_id);           
          $place_details=json_decode($place_details); 

          $duplicatepoi = PoiTemp::where('place_id',$place_id)
                    ->where(function($q) {
                        $q->where('user_id', auth()->user()->id);
                      })
                    ->where('name',$name)
                    ->first();
       if($duplicatepoi == '' || $duplicatepoi == null){
          $poitemps = new PoiTemp;
          $poitemps->name=$name;
          $poitemps->location_name=$location_name;
          $poitemps->address=$data->formatted_address;
          $poitemps->place_id=$data->place_id;
          $poitemps->latitude=$data->geometry->location->lat;
          $poitemps->longitude=$data->geometry->location->lng;
          $poitemps->utc_offset=$place_details->result->utc_offset;
          $poitemps->point_of_interest_icon_id = "679";
          $user = auth()->user();
          $poitemps->user_id = $user->id;
          foreach ($place_details->result->address_components as $addressPart) {
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $poitemps->country_name = $addressPart->long_name;
            }
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $poitemps->iso_2 = $addressPart->short_name;
            }
            // if ((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))){
            //   $poitemps->location_name= $location_name;
            // }
          }
          $poitemps->save();
          }

          // DATA save in WATPOI TABLE(Open DB)
          $duplicatepoiInWatPoi = WatPointOfInterest::where('place_id',$place_id)
                    ->orWhere('name',$name)
                    ->first();
          if($duplicatepoiInWatPoi == '' || $duplicatepoiInWatPoi == null){
          $poiWatTale = new WatPointOfInterest;
          $poiWatTale->name=$name;
          $poiWatTale->location_name=$location_name;
          $poiWatTale->address=$data->formatted_address;
          $poiWatTale->place_id=$data->place_id;
          $poiWatTale->latitude=$data->geometry->location->lat;
          $poiWatTale->longitude=$data->geometry->location->lng;
          $poiWatTale->utc_offset=$place_details->result->utc_offset;
          $poiWatTale->wat_place_id = "WAT-".Str::random(25);
          $poiWatTale->point_of_interest_icon_id = "679";
          $user = auth()->user();
          foreach ($place_details->result->address_components as $addressPart) {
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $poiWatTale->country_name = $addressPart->long_name;
            }
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $poiWatTale->iso_2 = $addressPart->short_name;
            }
            // if ((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))){
            //   $poiWatTale->location_name= $location_name;
            // }
          }
          $poiWatTale->save();
          }

        }
    }
  }
    else{
      if($request->otherPoi != '' || $request->otherPoi != null){
        foreach ($request->otherPoi as $value) {
          $watpoi = WatPointOfInterest::where('id',$value)->first();
          $poi = PoiTemp::where('place_id',$watpoi->place_id)
                    ->where(function($q) {
                        $q->where('user_id', auth()->user()->id);
                      })
                    ->orWhere('name',$watpoi->name)
                    ->first();
          if($poi == '' || $poi == null){
            $str = $watpoi->utc_offset;
            if(str_contains($str, 'UTC')){
              $str = ltrim($str, 'UTC ');
              $str = ltrim($str, '+');
              if($str[0] == '0'){
                  $str = ltrim($str, '0');
              }
              $arr = explode(':', $str);
              $minuts = (($arr[0]*60)+$arr[1]);
            }
            else{
              $minuts = $str;
            }
            $poitemps       = new PoiTemp;
            $poitemps->name = $watpoi->name;
            $poitemps->country_name = $watpoi->country_name;
            $poitemps->latitude = $watpoi->latitude;
            $poitemps->longitude = $watpoi->longitude;
            $poitemps->description = $watpoi->description;
            $poitemps->location_name = $watpoi->location_name;
            $poitemps->point_of_interest_icon_id = "679";
            $poitemps->address = $watpoi->address;
            $poitemps->place_id = $watpoi->place_id;
            //$poitemps->point_type = $watpoiicon->name;
            $poitemps->iso_2 = $watpoi->iso_2;
            $poitemps->utc_offset = $minuts;
            $poitemps->banner_image = $watpoi->banner_image;
            $user = auth()->user();
            $poitemps->user_id = $user->id;
            $poitemps->save();
            $last_id = $poitemps->id;
            $poitempimgs = WatPointOfInterestImage::where('wat_point_of_interest_id', $watpoi->id)->get();
                if(count($poitempimgs) > 0){                
                    foreach ($poitempimgs as $watimages) {
                         $poitempsss = new PoiTempImage;
                         $poitempsss->poi_temp_id=$last_id;
                         $poitempsss->poi_image=$watimages->poi_image;
                         $poitempsss->user_id=Auth()->user()->id;
                         $poitempsss->save();
                    }   
                }

          }
        }  
      }
    }

    $temppois = PoiTemp::join('point_of_interest_icons','point_of_interest_icons.id','=','poi_temps.point_of_interest_icon_id')
          ->where('poi_temps.user_id', Auth()->user()->id)
          ->where('poi_temps.location_name', $request->locationPoiSearch)
          ->select('poi_temps.*','point_of_interest_icons.icon_image','point_of_interest_icons.name as iconName')
          ->get();
      foreach ($temppois as $key => $images){
        $images_row = PoiTempImage::where('poi_temp_id', $images->id)
                          ->pluck('poi_image')->toArray();
        $images->temp_images = $images_row;
      }
      return response()->json(['tempdata' => $temppois]);
  }
  function getPlaceDetails($placeid)
      {
      $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid='.$placeid.'&fields=address_component,utc_offset&key=AIzaSyDeaIvmws05Lghj6CUUMBvM68Y2qBftMVw';
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 0);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $response = curl_exec ($ch);
         $err = curl_error($ch);  //if you need
         curl_close ($ch);
         return $response;
    }
// Point of interest Update

  public function tempPointOfInterestUpdate(Request $request, $id){
           $utcOffsetInMinuts = $request->edit_utc;
            
            // $poitype = $request->edit_point_type;
            // $poitypeicon= DB::table('point_of_interest_icons')->where('id', $request->edit_point_type)->select('id', 'name')->first();

            $poitempupdate       = PoiTemp::find($id);
            $poitempupdate->name = $request->edit_poi_name;
            $poitempupdate->country_name = $request->edit_country;
            $poitempupdate->description = $request->edit_description;
            $poitempupdate->location_name = $request->edit_location;
            $poitempupdate->point_of_interest_icon_id = $request->edit_poiicon;
            $poitempupdate->address = $request->edit_address;
            //$poitempupdate->point_type = $poitypeicon->name;
            $poitempupdate->iso_2 = $request->edit_iso;
            $poitempupdate->utc_offset = $request->edit_utc;
              if($request->editcropedimage){
                $url = $request->editcropedimage;
                $basename = basename($request->editcropedimage);
                $type = pathinfo($url, PATHINFO_EXTENSION);
                $data = file_get_contents($url);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64));
                $imageName = str_random(5).time() . '.png'; 
                $path = public_path() . "/images/uploads/testing/" . $imageName;
                file_put_contents($path, $image);
//dd()
                $filepath = public_path('images/uploads/testing/'.$imageName);
                    $file_info = new finfo(FILEINFO_MIME_TYPE);
                    $mime_type = $file_info->buffer(file_get_contents($filepath));
                    $output = new \CURLFile($filepath, $mime_type, $imageName);
                    $data = array(
                            "files" => $output,
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=20');
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $result = curl_error($ch);
                    }
                    curl_close ($ch);
                    $arr_result = json_decode($result);
                    
                    $basepaths = basename($arr_result->dest);
                    $originalExtentions = explode('.', $basepaths);
                    $originalExtention = $originalExtentions[1];
                    $types = pathinfo($arr_result->dest, PATHINFO_EXTENSION);
                    $datas = file_get_contents($arr_result->dest);
                    $base64s = 'data:image/' . $types . ';base64,' . base64_encode($datas);
                    $images = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$base64s));
                    $imageNames = str_random(5).time() . '.'.$originalExtention;
                    //dd($imageNames);
                    Storage::disk('s3')->put('poi/'.$imageNames, $images, 'public');
                    $poitempupdate->banner_image = $imageNames;
                //Delete File From Local Folder
                    $image_pathss = public_path('images/uploads/testing/'.$imageName);
                    if(file_exists($image_pathss)){// check if the image indeed exists
                        unlink($image_pathss);
                    } 
                    $image_croppath = public_path('images/cropdumyimages/'.$basename);
                    if(file_exists($image_croppath)){// check if the image indeed exists
                        unlink($image_croppath);
                    } 
            }
            $poitempupdate->save();
            $last_id = $poitempupdate->id;
            $files = $request->edit_multi_img;
            if($files){
              foreach ($files as $image) {
                  $extension = $image->getClientOriginalExtension();
                  $imageName=str_random(5).time().'.'.$extension;
                  Storage::disk('s3')->put('poi/'.$imageName, file_get_contents($image), 'public');
                  $img= new PoiTempImage;
                  $img->poi_temp_id=$last_id;
                  $img->poi_image=$imageName;
                  $img->user_id=Auth()->user()->id;

                  $img->save();                
              } 
            }
            $temppoiss = PoiTemp::join('point_of_interest_icons','point_of_interest_icons.id','=','poi_temps.point_of_interest_icon_id')
                  ->where('poi_temps.id', $poitempupdate->id)
                  ->select('poi_temps.*','point_of_interest_icons.icon_image','point_of_interest_icons.name as iconName')
                  ->first();
            $mulimg = PoiTempImage::where('poi_temp_id', $last_id)
                          ->pluck('poi_image')->toArray();
            return response()->json(['stats'=>$temppoiss,'statsimg'=>$mulimg]);
    }
// Relate Poi Added If not Null
    public function finalPoiSubmitButtonPois(Request $request){
            $temptablepoi = $request->otherPoiTemp;
            
          if($request->otherPoiTemp != '' || $request->otherPoiTemp != null){
            foreach ($request->otherPoiTemp as $value) {
              $watpoi = PoiTemp::where('id',$value)->first();
              $poi = PointOfInterest::where('place_id',$watpoi->place_id)
                        ->where(function($q) {
                            $q->where('tenant_id', auth()->user()->tenant_id);
                 })->first();

              if($poi=='' || $poi==null){
                $utcOffsetInMinuts = $watpoi->utc_offset;
            //$utcOffsetInMinuts = '330';
            
                $utcOffsetInHour = floor($utcOffsetInMinuts / 60);
                $utcOffsetRemainingMinuts = $utcOffsetInMinuts - ($utcOffsetInHour * 60);
                if($utcOffsetRemainingMinuts==0.0){
                  $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts.'0';
                }
                else{
                  $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts;
                }
                if($offset >= 10){
                  $utcOffset = 'UTC'.' +'.$offset;
                  //dd($utcOffset);
                }
                elseif($offset >= 0 && $offset <= 9 ){
                  $utcOffset = 'UTC'.' +'.'0'.$offset;
                  //dd($utcOffset);
                }
                elseif($offset < 0 && $offset >= -9 ){
                  $dataoset=explode('-',$offset);
                  //dd($dataoset[1]);
                  $utcOffset = 'UTC'.' '.'-'.'0'.$dataoset[1];
                  //dd($utcOffset);
                }
                else{
                  $offset = $utcOffsetInMinuts/60;
                  $utcOffset = 'UTC'.' '.$offset;
                  //dd($utcOffset);
                }
                $watpois       = new PointOfInterest;
                $watpois->name = $watpoi->name;
                $watpois->country_name = $watpoi->country_name;
                $watpois->latitude = $watpoi->latitude;
                $watpois->longitude = $watpoi->longitude;
                $watpois->description = $watpoi->description;
                $watpois->location_name = $watpoi->location_name;
                $watpois->point_of_interest_icon_id = $watpoi->point_of_interest_icon_id;
                $watpois->address = $watpoi->address;
                $watpois->place_id = $watpoi->place_id;
                //$watpois->point_type = $watpoi->point_type;
                $watpois->iso_2 = $watpoi->iso_2;
                $watpois->utc_offset = $utcOffset;
                $watpois->banner_image = $watpoi->banner_image;
                $user = auth()->user();
                $watpois->tenant_id = $user->tenant_id;
                $watpois->user_id = $user->id;
                $watpois->save();
                $last_id = $watpois->id;
                $watpoiimgs = PoiTempImage::where('poi_temp_id', $watpoi->id)
                              ->get();
                    if(count($watpoiimgs) > 0){                
                        foreach ($watpoiimgs as $watimages) {
                             $user = auth()->user();
                             $watpoiMultipleimages = new PointOfInterestImage;
                             $watpoiMultipleimages->point_of_interest_id=$last_id;
                             $watpoiMultipleimages->poi_image=$watimages->poi_image;
                             $watpoiMultipleimages->save();
                        }   
                    }
              }
              $pointOfInterestss = WatPointOfInterest::where('place_id',$watpoi->place_id)
                                  ->orWhere('name',$watpoi->name)
                                  ->first();
                if($pointOfInterestss == '' || $pointOfInterestss == null){
                  $utcOffsetInMinuts = $watpoi->utc_offset;
                //$utcOffsetInMinuts = '330';
            
                $utcOffsetInHour = floor($utcOffsetInMinuts / 60);
                $utcOffsetRemainingMinuts = $utcOffsetInMinuts - ($utcOffsetInHour * 60);
                if($utcOffsetRemainingMinuts==0.0){
                  $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts.'0';
                }
                else{
                  $offset = $utcOffsetInHour.":".$utcOffsetRemainingMinuts;
                }
                if($offset >= 10){
                  $utcOffset = 'UTC'.' +'.$offset;
                  //dd($utcOffset);
                }
                elseif($offset >= 0 && $offset <= 9 ){
                  $utcOffset = 'UTC'.' +'.'0'.$offset;
                  //dd($utcOffset);
                }
                elseif($offset < 0 && $offset >= -9 ){
                  $dataoset=explode('-',$offset);
                  //dd($dataoset[1]);
                  $utcOffset = 'UTC'.' '.'-'.'0'.$dataoset[1];
                  //dd($utcOffset);
                }
                else{
                  $offset = $utcOffsetInMinuts/60;
                  $utcOffset = 'UTC'.' '.$offset;
                  //dd($utcOffset);
                }
                  $watpoint       = new WatPointOfInterest;
                  $watpoint->name = $watpoi->name;
                  $watpoint->country_name = $watpoi->country_name;
                  $watpoint->latitude = $watpoi->latitude;
                  $watpoint->longitude = $watpoi->longitude;
                  $watpoint->description = $watpoi->description;
                  $watpoint->location_name = $watpoi->location_name;
                  $watpoint->address = $watpoi->address;
                  $watpoint->place_id = $watpoi->place_id;
                  $watpoint->point_of_interest_icon_id = $watpoi->point_of_interest_icon_id;
                  $watpoint->wat_place_id = "WAT-".Str::random(25);
                  //$watpoint->point_type = $watpoi->point_type;
                  $watpoint->iso_2 = $watpoi->iso_2;
                  $watpoint->utc_offset = $utcOffset;
                  $watpoint->banner_image = $watpoi->banner_image;
                  $watpoint->save();
                  $wat_last_id = $watpoint->id;
                  $watpointimg = PoiTempImage::where('poi_temp_id', $watpoi->id)
                              ->get();
                    if(count($watpointimg) > 0){                
                        foreach ($watpointimg as $watimgs) {
                          $img= new WatPointOfInterestImage;
                          $img->wat_point_of_interest_id=$wat_last_id;
                          $img->poi_image=$watimgs->poi_image;
                          $img->save(); 
                          }               
                      } 
                }
            }
          }
         PoiTemp::where('user_id', Auth()->user()->id)->delete();
         PoiTempImage::where('user_id', Auth()->user()->id)->delete();
      // Relate Poi Added If not Null End
    }
    public function tempPointOfInterestDelete(Request $request, $id)
    {
      $departures  = PoiTemp::where('user_id',$id)->delete();
      return response()->json(['success'=>'Success']);
    }

    public function deletePoi(Request $request, $id)
    {
        $lpi = LocationPointOfInterest::where('point_of_interest_id', '=', $id)->first();
        if ($lpi === null) {
            PointOfInterest::find($id)->delete();
            PointOfInterestImage::where('point_of_interest_id', '=', $id)->delete();
            return response()->json([
               'success' => 'Poi deleted successfully!'
            ]);
        }
        else{
            return response()->json([
               'success' => 'This point of interest already used in departure.'
            ]);
        }
    }
}
