<?php

namespace App\Http\Controllers\API2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Tenant;

class ApiHomeController extends Controller
{   
    
    public function checkCompanyId(Request $request)
    {
        $company_id = $request->all();
        $status = '';
        if (Tenant::where('company_id', '=', $company_id)->exists()) {
           $status = 'true';
           return $status;
        }
        else{
          $status = 'false';
          return $status;
        }

    }             
}       
