<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use App\Traveler;
use App\DocumentIcon;
use App\TravelDocument;
use App\DocumentTravelDocument;

class ApiDocumentController extends Controller
{   
    public function travelDocument(Request $request){

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
                      ->select('id as pkgId','after_day as dayAfter')
                      ->first();
           if($pkg){
            $tour_package_id=$traveler->pkgId;
            $travelDocument = TravelDocument::where('tour_package_id', $tour_package_id)
            ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName')
            ->get();
                      
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'travelDocuments' => $travelDocument
            ); 
          return response()->json($status, 200);
        }
        else{
          $status = array(
           'status' => true,
           'message' => 'Opps! No records match!!'
           );
          return response()->json($status, 200);
        }
      }
      else{
          $status = array(
           'error' => false,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
         }      
    }       

    public function travelDocumentDetails(Request $request, $id){

        $token = $request->token; 
        if($token){ 
            $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
            $tour_package_id=$traveler->pkgId;
            $travelDocument = TravelDocument::where('id', $id)
            ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName')
            ->first();
            $documents = DocumentTravelDocument::join('travel_documents','travel_documents.id','=','document_travel_documents.travel_document_id')
                          ->join('document_icons','document_icons.id','=','travel_documents.document_icon_id')
                          ->where('document_travel_documents.travel_document_id','=',$travelDocument->travelDocId)
                          ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName','document_travel_documents.document as documrntPath','document_icons.name as docType','document_icons.icon_image as typeIcon')
                          ->get();
             
            if(count($documents)>=1){
                foreach($documents as $doc){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'. '/';
                      $avatar_url = $src.'traveldocuments/';           
                      $document[] = ['travelDocId'=>$doc->travelDocId,'travelDocName'=>$doc->documrntPath,'documrntPath'=>$avatar_url.$doc->documrntPath,'docType'=>$doc->docType,'typeIcon'=>url("images/uploads/documenticon/".$doc->typeIcon)];
                    }
                }
                else{
                  $document = [];
                }
            
            $status = array(
            'status' => true,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'travelDocuments' => $document
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

    public function travelTEST(Request $request){

        $token = "iAmSnSUtcLvM0kYiaGsSH7P9hPZFjHdnrUeTskjVV3aHIIF6gWogjZr82RNFdWQX"; 
         
        
          $traveler = Traveler::where('token',$token)->select('travelers.tour_package_id as pkgId','travelers.tenant_id as tenantId','travelers.token')->first();
          if($traveler){
          $pkg = TourPckage::where('id', $traveler->pkgId)
                            ->where(function($q) {
                                $q->where('status', 2);
                            })
                      ->select('id as pkgId','after_day as dayAfter')
                      ->first();
           if($pkg){
            $tour_package_id=$traveler->pkgId;
            $travelDocument = TravelDocument::join('document_icons','document_icons.id','=','travel_documents.document_icon_id')
            ->where('tour_package_id', $tour_package_id)
            ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName')
            ->get();
                      
            $status = array(
            'error' => false,
            'message' => 'Bingo! Success!!', 
            'traveler' => $traveler,
            'travelDocuments' => $travelDocument
            ); 
          return response()->json($status, 200);
        }
        else{
          $status = array(
           'error' => true,
           'message' => 'Opps! No records match!!'
           );
          return response()->json($status, 200);
        }
      }
      else{
          $status = array(
           'error' => true,
           'message' => 'Opps! Invalid response!!'
           );
          return response()->json($status, 200);
         }      
    }

    public function allDocument(Request $request)
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
        if($traveler){
          $pkg = TourPckage::where('id', $traveler->pkgId)
                              ->where(function($q) {
                                  $q->where('status', 2);
                              })
                        ->select('id as pkgId','after_day as dayAfter')
                        ->first();
          if($pkg){
              $tour_package_id=$traveler->pkgId;
              $travelDocument = TravelDocument::where('tour_package_id', $tour_package_id)
              ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName')
              ->get();
            $docList = [];
            foreach ($travelDocument as $value) {
            
              $travelDocument = TravelDocument::where('id', $value->travelDocId)
                                ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName')
                                ->first();
                             
              $documents = DocumentTravelDocument::join('travel_documents','travel_documents.id','=','document_travel_documents.travel_document_id')
                          ->join('document_icons','document_icons.id','=','travel_documents.document_icon_id')
                          ->where('document_travel_documents.travel_document_id','=',$travelDocument->travelDocId)
                          ->select('travel_documents.id as travelDocId','travel_documents.name as travelDocName','document_travel_documents.document as documrntPath','document_icons.name as docType','document_icons.icon_image as typeIcon')
                          ->get();
              $document = [];
                if(count($documents)>=1){
                  foreach($documents as $doc){
                      $src = 'https://s3.us-west-2.amazonaws.com/s3-tlak-bucket'. '/';
                      $avatar_url = $src.'traveldocuments/';           
                      $documentList = ['travelDocId'=>$doc->travelDocId,'travelDocName'=>$doc->documrntPath,'documentPath'=>$avatar_url.$doc->documrntPath,'docType'=>$doc->docType,'typeIcon'=>url("images/uploads/documenticon/".$doc->typeIcon)];
                      array_push($document, $documentList);
                  }
                }
                else{
                  $document = [];
                }
                $travelDocument['filename'] = $document;
                array_push($docList, $travelDocument) ;
              }

                        
              $status = array(
              'status' => true,
              'message' => 'Bingo! Success!!', 
              'traveler' => $traveler,
              'travelDocuments' => $docList
              ); 
            return response()->json($status, 200);
          }
          else{
            $status = array(
             'status' => false,
             'message' => 'Opps! No records match!!'
             );
            return response()->json($status, 200);
          }
        }
        else{
          $status = array(
           'status' => false,
           'message' => 'Opps! Invalid token!!'
           );
          return response()->json($status, 200);
        }      
    }              
}       
