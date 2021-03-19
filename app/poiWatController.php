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
class poiWatController extends Controller
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
                $query->where('point_of_interests.name', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.location_name', 'LIKE','%'.$keywords.'%')->orWhere('point_of_interests.point_type', 'LIKE','%'.$keywords.'%');
                })
                ->where('point_of_interests.tenant_id',auth()->user()->tenant_id)
                ->select('point_of_interests.*','point_of_interest_icons.icon_image')->orderBy('id','DESC')->paginate(20);
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
    public function createWat()
    {
        $permission = User::getPermissions();
        if (Gate::allows('poi_create',$permission)) {
            $poiicon = PointOfInterestIcon::get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            $temppois = PoiTemp::where('user_id', Auth()->user()->id)->get();
            $temppoicount = count($temppois); 
            //dd($temppoicount);
            $PoiSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket/testing/';
            return view('pointofinterest.poi_wat_create',compact('poiicon','tenant', 'temppois','temppoicount','PoiSRCPath'));
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
    public function storewat(Request $request)
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
dd('hhh');
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
            $poi->point_type = $request->point_type;
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
                $path = public_path() . "/images/uploads/testing/" . $imageName;
                file_put_contents($path, $image);
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
                    Storage::disk('s3')->put('testing/'.$imageNames, $images, 'public');
                    $poi->banner_image = $imageNames;
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
              $watpoi->point_type = $request->point_type;
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
                $path = public_path() . "/images/uploads/testing/" . $imageName;
                file_put_contents($path, $image);

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
                    //Storage::disk('s3')->put('watpoi/'.$imageNames, $images, 'public');
                    Storage::disk('s3')->put('testing/'.$imageNames, $images, 'public');
                    $watpoi->banner_image = $imageNames;
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
              $watpoi->save();

              $wat_last_id = $watpoi->id;
              $someArray = json_decode($request->poiImages);
            
              if($someArray){
                  foreach ($someArray as $images) {
                    if($images != null){
                      $image = $images->Content;
                      $imagebase = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image));
                      $imageName = str_random(5).time() . '.png';
                      $storagePath = Storage::disk('s3')->put('watpoi/'.$imageName, $imagebase, 'public');
                      $img= new WatPointOfInterestImage;
                      $img->wat_point_of_interest_id=$wat_last_id;
                      $img->poi_image=$imageName;
                      $img->save(); 
                      }               
                  } 
              }
            }

// Relate Poi Added If not Null
      if($request->otherPoiTemp != '' || $request->otherPoiTemp != null){
        foreach ($request->otherPoiTemp as $value) {
          $watpoi = PoiTemp::where('id',$value)->first();
          $poi = PointOfInterest::where('place_id',$watpoi->place_id)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();
          if($poi==''){
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
            $watpois->point_type = $watpoi->point_type;
            $watpois->iso_2 = $watpoi->iso_2;
            $watpois->utc_offset = $watpoi->utc_offset;
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
            if($pointOfInterestss == ''){
              $watpoint       = new WatPointOfInterest;
              $watpoint->name = $watpoi->name;
              $watpoint->country_name = $watpoi->country_name;
              $watpoint->latitude = $watpoi->latitude;
              $watpoint->longitude = $watpoi->longitude;
              $watpoint->description = $watpoi->description;
              $watpoint->location_name = $watpoi->location_name;
              $watpoint->address = $watpoi->address;
              $watpoint->place_id = $watpoi->place_id;
              $watpoint->wat_place_id = "WAT-".Str::random(25);
              $watpoint->point_type = $watpoi->point_type;
              $watpoint->iso_2 = $watpoi->iso_2;
              $watpoint->utc_offset = $watpoi->utc_offset;
              $watpoint->banner_image = $watpoi->banner_image;
              $watpoint->save();
              $wat_last_id = $watpoint->id;
              $watpointimg = PoiTempImage::where('poi_temp_id', $watpoi->id)
                          ->get();
                if(count($watpointimg) > 0){                
                    foreach ($watpointimg as $watimgs) {
                      $img= new WatPointOfInterestImage;
                      $img->wat_point_of_interest_id=$wat_last_id;
                      $img->poi_image=$watimgs;
                      $img->save(); 
                      }               
                  } 
            }
        }
      }
// Relate Poi Added If not Null End

            $request->session()->flash('status', 'Point of interest created successfully.');
            return back();
  
    }

// poi search from database
    public function poiSearchOpenDb(Request $request){


        $search = $request->text;
            $poi_db = WatPointOfInterest::select('*')
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
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
                    if($value->name != PointOfInterest::where('tenant_id' , auth()->user()->tenant_id) 
                                    ->where('name', $value->name)
                                    ->value('name'))
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
            //dd($pids);
            $datas = WatPointOfInterest::select('*')
                      ->where('location_name', $watlocname)
                      ->inRandomOrder()
                      ->get();

                $array_poi = array();
                  foreach ($datas as $value) {
                    if($value->name != PointOfInterest::where('tenant_id' , auth()->user()->tenant_id) 
                                    ->where('name', $value->name)
                                    ->value('name'))
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
                    ->first();
          if($poi == ''){
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
            //$poitemps->hour_status = $watpoi->hour_status;
            $poitemps->point_type = $watpoi->point_type;
            $poitemps->iso_2 = $watpoi->iso_2;
            $poitemps->utc_offset = $watpoi->utc_offset;
            $poitemps->banner_image = $watpoi->banner_image;
            $user = auth()->user();
            $poitemps->user_id = $user->id;
            $poitemps->save();
            $last_id = $poitemps->id;
            $poitempimgs = WatPointOfInterestImage::where('wat_point_of_interest_id', $watpoi->id)
                          ->get();
                if(count($poitempimgs) > 0){                
                    foreach ($poitempimgs as $watimages) {
                         $watpoiMultipleimages = new PoiTempImage;
                         $watpoiMultipleimages->poi_temp_id=$last_id;
                         $watpoiMultipleimages->poi_image=$watimages->poi_image;
                         $watpoiMultipleimages->save();
                    }   
                }
          }
        }
      }
      if($request->locationPoiSearch){
        $location_name = $request->locationPoiSearch; 
        $dest_name_ar=explode(' ',$location_name);
        $place_name =implode('+',$dest_name_ar);
        $keywords = "point+of+interest+Barnet";
        echo $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query='.$keywords.'+'.$place_name.'&key=AIzaSyBpny7f4norxynsPJphb9x5miXPCMETyN8';
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
          $temp = new PoiTemp;
          $temp->name=$name;
          $temp->location_name=$location_name;
          $temp->address=$data->formatted_address;
          $temp->place_id=$data->place_id;
          $temp->latitude=$data->geometry->location->lat;
          $temp->longitude=$data->geometry->location->lng;
          $temp->utc_offset=$place_details->result->utc_offset;
          $user = auth()->user();
          $temp->user_id = $user->id;
          foreach ($place_details->result->address_components as $addressPart) {
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $temp->country_name = $addressPart->long_name;
            }
            if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $temp->iso_2 = $addressPart->short_name;
            }
            if ((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))){
              $temp->location_name= $location_name;
            }
          }
          $temp->save();
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
                    ->first();
          if($poi == ''){
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
            $poitemps->point_type = $watpoi->point_type;
            $poitemps->iso_2 = $watpoi->iso_2;
            $poitemps->utc_offset = $watpoi->utc_offset;
            $poitemps->banner_image = $watpoi->banner_image;
            $user = auth()->user();
            $poitemps->user_id = $user->id;
            $poitemps->save();
            $last_id = $poitemps->id;
            $poitempimgs = WatPointOfInterestImage::where('wat_point_of_interest_id', $watpoi->id)
                          ->get();
                if(count($poitempimgs) > 0){                
                    foreach ($poitempimgs as $watimages) {
                         $watpoiMultipleimages = new PoiTempImage;
                         $watpoiMultipleimages->poi_temp_id=$last_id;
                         $watpoiMultipleimages->poi_image=$watimages->poi_image;
                         $watpoiMultipleimages->save();
                    }   
                }
          }
        }
      }
    }
  }
  function getPlaceDetails($placeid)
      {
      $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid='.$placeid.'&fields=address_component,utc_offset&key=AIzaSyBpny7f4norxynsPJphb9x5miXPCMETyN8';
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
            $poitype = $request->edit_point_type;
            $poitypeicon= DB::table('point_of_interest_icons')->where('id', $request->edit_point_type)->select('id', 'name')->first();

            $poitempupdate       = PoiTemp::find($id);
            $poitempupdate->name = $request->edit_poi_name;
            $poitempupdate->country_name = $request->edit_country;
            $poitempupdate->description = $request->edit_description;
            $poitempupdate->location_name = $request->edit_location;
            $poitempupdate->point_of_interest_icon_id = $request->edit_poiicon;
            $poitempupdate->address = $request->edit_address;
            $poitempupdate->point_type = $poitypeicon->name;
            $poitempupdate->iso_2 = $request->edit_iso;
            $poitempupdate->utc_offset = $utcOffset;
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
                    Storage::disk('s3')->put('testing/'.$imageNames, $images, 'public');
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
                  Storage::disk('s3')->put('testing/'.$imageName, file_get_contents($image), 'public');
                  $img= new PoiTempImage;
                  $img->poi_temp_id=$last_id;
                  $img->poi_image=$imageName;

                  $img->save();                
              } 
            }
            return response()->json($poitempupdate);
    }
}
