<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
	protected $fillable = ['name','tour_package_id','tenant_id','user_id'];
    public $table = "peoples";
}
