<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TourPckage;
use App\People;
use DB;
use App\Traveler;
use App\MenuLabel;
use App\MenuLabelIcon;
class ApiMenuLabelController extends Controller
{   
    public function menuLabel(Request $request){

        $tenant_id = $request->tenantId; 
        $validator = Validator::make($request->all(),[
            'tenantId' => 'required'
            ]);

        if($validator->fails()){
            $message = $validator->errors()->all();

            $status = [
                'error' => true,
                'message' => $message[0]
            ];
            return Response($status);
        }
            if($tenant_id){
              $MenuLabel= MenuLabel::join('menu_label_icons','menu_label_icons.id','=','menu_labels.menu_label_icon_id')
                  ->select('menu_labels.id as menulabelId','menu_labels.button_name as menulabelName','menu_label_icons.icon_image as menulabeIcon')
                  ->where('menu_labels.tenant_id', $tenant_id)
                  ->get();
              if(count($MenuLabel) > 0){
                foreach($MenuLabel as $label){
                    //echo $dest['id'].'<br>';
                  $MenuLabels[] = ['menulabelId'=>$label->menulabelId,'menulabelName'=>$label->menulabelName,'menulabeIcon'=>url("images/uploads/labelicons/".$label->menulabeIcon)];
                }
              }
              else{
                $MenuLabels = [];
              }
                $status = array(
                'error' => false,
                'message' => 'Bingo! Successfully!',
                'menulabel' => $MenuLabels
                );
                return response()->json($status, 200);
            }

          else{
            $status = array(
              'error' => true, 
              'message' => 'Opps! Invalid Response!',
              );
            return response()->json($status, 200);
        }     
    }       
}

