<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
     protected $fillable = [
         'name','tenant_id','user_id','utc_offset','country_name','country_name'
    ];

    public function itinearies()
     {
        return $this->belongsToMany('App\Itineary','itinerary_locations')->withTimestamps();
     }
  //    public function locations() 
  //    {
		// return $this->belongsToMany('App\Location', 'itinerary_location_point_of_interests')->withTimestamps();
	 // }
}
