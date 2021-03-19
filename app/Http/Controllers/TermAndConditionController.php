<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\TermAndCondition;
use App\TourPckage;
use App\Tenant;

class TermAndConditionController extends Controller
{
    public function index(Request $request, $id)
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
        	$data = TermAndCondition::where('tour_package_id', $id)->first();
        	$terms = ($data)?$data->terms:'';
        	$penandcomitem = TourPckage::completedAndPendingItem($id);
        	$tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        	return view('termandconditions.index', compact('terms','penandcomitem','tenant','id'));
        }
        else{
            $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
            return view('401.401',compact('tenant'));
        }     
    }

    public function updateTerm(Request $request, $id)
    {
    	$penandcomitem = TourPckage::completedAndPendingItem($id);
    	$tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();

    	if (TermAndCondition::where('tour_package_id', '=', $id)->exists()) 
    	{
    		$termsandconditions = TermAndCondition::where('tour_package_id', $id)
				       ->update([
				           'terms' => $request->terms
				    ]);
			return redirect()->route('termandconditions_index',$id)->with('status', 'Terms & Conditions updated!');		       
					       
    	}
    	else
    	{
    		$termsandconditions = new TermAndCondition();
    		$termsandconditions->terms = $request->terms;
    		$termsandconditions->tour_package_id = $id;
    		$termsandconditions->save();
    		return redirect()->route('termandconditions_index',$id)->with('status', 'Terms & Conditions added!');
    	}
    }
}
