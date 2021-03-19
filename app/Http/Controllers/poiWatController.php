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
class poiWatController extends Controller
{

 
    public function createWat()
    {
        $permission = User::getPermissions();
        if (Gate::allows('poi_create',$permission)) {
            $poiicon = PointOfInterestIcon::get();
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('pointofinterest.poi_wat_create',compact('poiicon','tenant'));
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
    public function storeWat(Request $request)
    {

        $validatedData = $request->validate([
           'name' => 'required',
           'country' => 'required',
           'lat' => 'required',
           'long' => 'required',
           'destination' =>'required',
        ]); 
        
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
            //$poi->point_type = $request->point_type;
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

            $desti = Location::where('name',$request->destination)
                    ->where(function($q) {
                        $q->where('tenant_id', auth()->user()->tenant_id);
             })->first();
              if($desti == null || $desti == '')//if doesn't exist: create
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
              }
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
              $watpoi->wat_place_id = "WAT-".Str::random(22);
              $watpoi->point_type = $request->point_type;
              $watpoi->point_of_interest_icon_id = $request->poiicon;
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
                      $storagePath = Storage::disk('s3')->put('poi/'.$imageName, $imagebase, 'public');
                      $img= new WatPointOfInterestImage;
                      $img->wat_point_of_interest_id=$wat_last_id;
                      $img->poi_image=$imageName;
                      $img->save(); 
                      }               
                  } 
              }
            }
            $request->session()->flash('status', 'Point of interest created successfully.');
            return back();
  
    }

//Image cropper for create form
        public function cropOldPoiImage(Request $request){

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
  
}
