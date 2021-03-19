<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
   	protected $fillable = [
        'name','tenant_id','company_name','phone','contact_person','address_street','address_city','address_zip','address_country','company_website','hear_about','tenant_code','email','company_id','referred_by'
    ];
}
