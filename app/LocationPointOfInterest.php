<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationPointOfInterest extends Model
{
	protected $fillable = [
        'name','poi_name','place_id','location_id','point_of_interest_id','tenant_id','user_id','tour_package_id'
    ];
  

    public function itinearies()
     {
        return $this->belongsToMany('App\Itineary','itinerary_locations')->withTimestamps();
     }
     public function locations() 
     {
        return $this->belongsToMany('App\Location', 'itinerary_locations')->withTimestamps();
     }
}
