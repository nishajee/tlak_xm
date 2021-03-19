<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
//use Illuminate\Support\Facades\Storage;
use Storage;
use App\DocumentIcon;
use App\DocumentTravelDocument;
use App\Flight;
use App\Inclusion;
use App\InclusionTourPckage;
use App\Itinerary;
use App\LocationPointOfInterest;
use App\PdfItinerary;
use App\Tenant;
use App\TourPckage;
use App\TravelDocument;
use App\Hotel;
use App\Communication;
use App\DepartureManager;
use App\DepartureGuide;
use App\Placard;
use Auth;
use Image;
use PDF;
use Redirect;
use \ConvertApi\ConvertApi;
use View;

ConvertApi::setApiSecret('uA9UpeXTiQMjjysR');



class DocumentAndCreationController extends Controller
{
    public function DocumentAndCreation(Request $request, $id){

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
            $penandcomitem = TourPckage::completedAndPendingItem($route_id); 
            $user = auth()->user();
        	$traveldocs =TravelDocument::join('document_icons','document_icons.id','=','travel_documents.document_icon_id')
                ->select('travel_documents.name as tdocName','travel_documents.id','travel_documents.document_icon_id','travel_documents.tour_package_id','travel_documents.alias_name','document_icons.name as docIconName','document_icons.icon_image')
                ->where('travel_documents.tour_package_id','=',$route_id)
                ->get();
            //$traveldocsPdf = TravelDocument::get();
            $dd = count($traveldocs);
            if($dd > 0 || $dd != '' || $dd != null){
                foreach ($traveldocs as $value) {
                    $traveldocs_Pdfall[] = DocumentTravelDocument::where('travel_document_id',$value->id)->get();
                 }
                 $traveldocsPdfall = array_flatten($traveldocs_Pdfall);
                 //dd($traveldocsPdfall);
            }
        	$docicons =DocumentIcon::get();
            $pdf_itinerary_data = PdfItinerary::where('tour_package_id', $route_id)->where('tenant_id', $user->tenant_id)->where('status','1')->get();
        	//$ddicon =TravelDocument::where('id',$id)->first();
            $devDeparture  = TourPckage::where('id',$route_id)->where('tenant_id',Auth()->user()->tenant_id)->first();
            $current_dates = date('Y-m-d');
            $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first(); 
            $DocSRCPath = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'.'/traveldocuments/';
            return view('documentcreation.create_travel_document',compact('traveldocs','docicons','pdf_itinerary_data','tenant','penandcomitem','DocSRCPath','traveldocsPdfall','disableDeparture','devDeparture'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }
    }

    public function storeTravelDocument(Request $request){
    	$data = $request->all();
        $validatedData = $request->validate([
           'name' => 'required|max:255',
           //'file.*' => 'required|file|max:5000|mimes:pdf,docx,doc',
        ]); 
        $route_ids = $request->route('id'); 
        $route_id = (int)$route_ids;
        if($route_id){
	        $alias=$request->name;
	        $traveldocument  = new TravelDocument;
	        $traveldocument->name = $request->name;
	        $traveldocument->alias_name = str_slug($alias, '-');
	        $traveldocument->document_icon_id = $request->document_icon;
	        $traveldocument->tour_package_id = $route_id; 
	        $user = auth()->user();
            $traveldocument->tenant_id = $user->tenant_id;
	        $traveldocument->user_id = $user->id;
	        $traveldocument->save();
            $last_id = $traveldocument->id;
            $travel_alias=$traveldocument->alias_name;
            $file = json_decode($request->docFiles);
            //dd(gettype($file));
            if($file){
                foreach ($file as $key => $tdocs) {
                    if($tdocs != null){
                        $tdoc = $tdocs->Content;
                        $namedoc = $tdocs->FileName;
                        $part0 = substr("$namedoc",0, strrpos($namedoc,'.'));
                        $part2 = substr("$namedoc", (strrpos($namedoc,'.') + 1));
                        $arr = explode(" ",$part0); 
                        $part1 = implode("-",$arr);
                        $mime = $tdocs->MimeType;
                        $mimess = explode("/",$mime);
                        //dd($mimess[0]);
                        if($mimess[0] == 'application'){
                            $imagebase = base64_decode(preg_replace('#^data:application/\w+;base64,#i', '',$tdoc));
                        }
                        else{
                            $imagebase = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$tdoc));
                        }
                        $filename = $part1.time().'.'.$part2;
                        //dd($filename);
                        $storagePath = Storage::disk('s3')->put('traveldocuments'.'/'.$filename, $imagebase, 'public');
                        //$storagePath = Storage::disk('s3')->put('testing'.'/'.$filename, $imagebase, 'public');
                       
                        //dd($avatar_url);
                        $traveldocss= new DocumentTravelDocument;
                        $traveldocss->travel_document_id=$last_id;
                        $traveldocss->document=$filename;

                        $traveldocss->save();
                    }                
                } 
            } 
	    }

        $request->session()->flash('status','Created successfully.');
        return redirect()->route('document_creation',$route_id);
    }
    public function updateTravelDocument(Request $request, $id)
    {
        $alias=$request->name;
        $traveldocument = TravelDocument::findOrFail($id);
        $traveldocument->name = $request->name;
        $traveldocument->alias_name = str_slug($alias, '-');
        $traveldocument->save();
        return response()->json($traveldocument);
        
    }
    public function deleteTravelDocument(Request $request, $id)
    {
        $traveldocument = TravelDocument::find($id)->delete();
        $traveldoct = DocumentTravelDocument::where('travel_document_id',$id)->delete();
        return response()->json([
           'success' => 'Document deleted successfully!'
       ]);
    }

    //PDF Creation Functions

     public function pdfTravelDocument(Request $request, $id)
     {
        $data = $request->all();
        $file_names = $request->create_filename;
        $file_name = str_slug($file_names, '_').time();
        $route_ids = $request->route('id');
        $route_id = (int)$route_ids;
        $user = auth()->user();
        $pdf_data = array();
        foreach ($request->itinerary as $key => $value) {

            if($value=='noteFrontHeader'){
                $headerFooter = Tenant::where('tenant_id',Auth()->user()->tenant_id)->first();
                array_push($pdf_data, array('note_front_header' => $headerFooter));
            }

            if($value=='tourPackage'){
                $tourpkg = TourPckage::where('id',$route_id)->first();
                array_push($pdf_data, array('tour_package' => $tourpkg));
            }

            if($value=='itinerary'){
                $itinerary = Itinerary::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('itinerary' => $itinerary));
            }

            if($value=='locations'){
                $location_data = array();
                // $location_arr = LocationPointOfInterest::where('tour_package_id',$route_id)->get();
                $poi = DB::table("location_point_of_interests")
                        ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                        ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                        ->join('locations','locations.id','=','location_point_of_interests.location_id')
                        ->where("location_point_of_interests.tour_package_id",$route_id)
                        ->where(function($q) {
                                        $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                             })
                        ->select("point_of_interests.*","locations.name as dest_name","location_point_of_interests.id as dest_id","location_point_of_interests.status","point_of_interest_icons.icon_image")->paginate(10);

                // foreach ($location_arr as $key => $location) {
                //     array_push($location_data, $location->name);
                // }
                // $location_data = array_unique($location_data);
                
                array_push($pdf_data, array('locations' => $poi));
                
            }

            if($value=='inclusions'){
                $inclusion_data = array();
                $inclusion = InclusionTourPckage::where('tour_package_id',$route_id)->get();
                foreach ($inclusion as $key => $inc) {
                    $inclusion_name = Inclusion::where('id', $inc->inclusion_id)->value('name');
                    array_push($inclusion_data, $inclusion_name);
                }
                array_push($pdf_data, array('inclusion' => $inclusion_data));
            }

            if($value=='flights'){
                $flight = Flight::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('flight' => $flight));

            }

            if($value=='hotel'){
                $hotel = Hotel::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('hotel' => $hotel));

            }

            if($value=='operationTeam'){
                $manager = DepartureManager::where('tour_package_id',$route_id)->get();
                $guide = DepartureGuide::where('tour_package_id',$route_id)->get();
                $placard = Placard::where('tour_package_id',$route_id)->get();
                $contact = Communication::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('manager' => $manager));
                array_push($pdf_data, array('guide' => $guide));
                array_push($pdf_data, array('placard' => $placard));
                array_push($pdf_data, array('contact' => $contact));

            }

            if($value=='noteFrontFooter'){
                $headerFooter = Tenant::where('tenant_id',Auth()->user()->tenant_id)->first();
                array_push($pdf_data, array('note_front_footer' => $headerFooter));
            }

        }
        if(file_exists('images/uploads/pdf_generate/'.Auth()->user()->tenant_id.'/pdfgenerate.html')){
             unlink('images/uploads/pdf_generate/'.Auth()->user()->tenant_id.'/pdfgenerate.html');
         }
        if (!file_exists('images/uploads/pdf_generate/'.Auth()->user()->tenant_id)) {
            mkdir('images/uploads/pdf_generate/'.Auth()->user()->tenant_id, 0777, true);
        }

        $public_path = public_path('images/uploads/pdf_generate/'.Auth()->user()->tenant_id.'/pdfgenerate.html');
        $return_html_file = View('documentcreation/create_pdf_itinerary', compact('pdf_data'))->render();

        $dgsyfyu = File::put($public_path,$return_html_file);

        // dd($ashish);

        $result = ConvertApi::convert('pdf', [
                'File' => 'https://account.tlakapp.com/images/uploads/pdf_generate/'.Auth()->user()->tenant_id.'/pdfgenerate.html',
                'FileName' => 'test',
                'ConversionDelay' => '30',
                'MarginTop' => '0',
                'MarginRight' => '0',
                'MarginBottom' => '0',
                'MarginLeft' => '0',
            ], 'html'
        );
        $result->getFile()->save(storage_path("app/public/documents/pdf/itinerary/".$file_name.".pdf"));

        // $result = ConvertApi::convert('pdf', ['File' => 'file:///Users/ashishpatel/Desktop/pdffile.html']);

        // # save to file
        // $result->getFile()->save(public_path('/ashishpatel.pdf'));
        // die();

        // $pdfs = PDF::loadView('documentcreation/create_pdf_itinerary', compact('pdf_data'));

        // $content = $pdfs->output();
        // $x= storage_path("app/public/documents/pdf/itinerary/".$file_name.".pdf");
        // file_put_contents($x, $content);

        // $filename = $file_names.time().'.pdf';
        // $storagePath = Storage::disk('s3')->put('itinerary'.'/'.'pdf'.'/'.$filename, file_get_contents($content), 'public');
// $src = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
//                  $avatar_url = $src.'itinerary/pdf/'.$filename;
// dd($avatar_url);


        $pdf_itinerary= new PdfItinerary;
        $pdf_itinerary->name=$file_names;
        $pdf_itinerary->pdf_name=$file_name.".pdf";
        $pdf_itinerary->tour_package_id=$route_id;
        $pdf_itinerary->tenant_id=$user->tenant_id;
        $pdf_itinerary->user_id=$user->id;
        $pdf_itinerary->save();
        //$updatestatus = TourPckage::completeStatus($route_id); // Closed 19-May-2020
        return redirect()->back();

        // return $pdfs->download($file_name.'.pdf');
    }

    public function get_file($path)
    {
        return response()->download($path);
    }

    public function delete_itinerary_pdf(Request $request)
    {
        $id = $request->id;
        $pdf_name = PdfItinerary::where('id', $id)->value('pdf_name');
        $path = 'tlak/public/documents/pdf/itinerary/'.$pdf_name;
        Storage::delete($path);
        PdfItinerary::where('id', $id)->delete();
    }

    public function pdfPreview(Request $request, $id)
    {
        $data = $request->all();
        $file_names = $request->create_filename;
        $file_name = str_slug($file_names, '_').time();
        $route_ids = $request->route('id');
        $route_id = (int)$route_ids;
        $user = auth()->user();
        $pdf_data = array();
        foreach ($request->itinerary as $key => $value) {

            if($value=='noteFrontHeader'){
                $headerFooter = Tenant::where('tenant_id',Auth()->user()->tenant_id)->first();
                array_push($pdf_data, array('note_front_header' => $headerFooter));
            }

            if($value=='tourPackage'){
                $tourpkg = TourPckage::where('id',$route_id)->first();
                array_push($pdf_data, array('tour_package' => $tourpkg));
            }

            if($value=='itinerary'){
                $itinerary = Itinerary::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('itinerary' => $itinerary));
            }

            if($value=='locations'){
                $location_data = array();
                // $location_arr = LocationPointOfInterest::where('tour_package_id',$route_id)->get();
                $poi = DB::table("location_point_of_interests")
                        ->join('point_of_interests','point_of_interests.id','=','location_point_of_interests.point_of_interest_id')
                        ->join('point_of_interest_icons','point_of_interest_icons.id','=','point_of_interests.point_of_interest_icon_id')
                        ->join('locations','locations.id','=','location_point_of_interests.location_id')
                        ->where("location_point_of_interests.tour_package_id",$route_id)
                        ->where(function($q) {
                                        $q->where('location_point_of_interests.tenant_id', auth()->user()->tenant_id);
                             })
                        ->select("point_of_interests.*","locations.name as dest_name","location_point_of_interests.id as dest_id","location_point_of_interests.status","point_of_interest_icons.icon_image")->paginate(10);

                // foreach ($location_arr as $key => $location) {
                //     array_push($location_data, $location->name);
                // }
                // $location_data = array_unique($location_data);
                
                array_push($pdf_data, array('locations' => $poi));
                
            }
            
            if($value=='inclusions'){
                $inclusion_data = array();
                $inclusion = InclusionTourPckage::where('tour_package_id',$route_id)->get();
                foreach ($inclusion as $key => $inc) {
                    $inclusion_name = Inclusion::where('id', $inc->inclusion_id)->value('name');
                    array_push($inclusion_data, $inclusion_name);
                }
                array_push($pdf_data, array('inclusion' => $inclusion_data));
            }

            if($value=='flights'){
                $flight = Flight::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('flight' => $flight));

            }

            if($value=='hotel'){
                $hotel = Hotel::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('hotel' => $hotel));

            }

            if($value=='operationTeam'){
                $manager = DepartureManager::where('tour_package_id',$route_id)->get();
                $guide = DepartureGuide::where('tour_package_id',$route_id)->get();
                $placard = Placard::where('tour_package_id',$route_id)->get();
                $contact = Communication::where('tour_package_id',$route_id)->get();
                array_push($pdf_data, array('manager' => $manager));
                array_push($pdf_data, array('guide' => $guide));
                array_push($pdf_data, array('placard' => $placard));
                array_push($pdf_data, array('contact' => $contact));

            }

            if($value=='noteFrontFooter'){
                $headerFooter = Tenant::where('tenant_id',Auth()->user()->tenant_id)->first();
                array_push($pdf_data, array('note_front_footer' => $headerFooter));
            }

        }

        $current_dates = date('Y-m-d');
        $disableDeparture  = TourPckage::where('id',$route_id)->where('end_date', '<', $current_dates)->where('tenant_id',Auth()->user()->tenant_id)->first();

        return view('documentcreation/create_pdf_itinerary', compact('pdf_data','disableDeparture'));

       // $pdfs = PDF::loadView('documentcreation/create_pdf_itinerary', compact('pdf_data'));



       //  return $pdfs->download($file_name.'.pdf');
    }


}
