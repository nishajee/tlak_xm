<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointOfInterest extends Model
{

    public function locations()
     {
        return $this->belongsToMany('App\Location','itinerary_locations')->withTimestamps();
     }
    public function location_point_of_interests() 
     {
		return $this->belongsToMany('App\LocationPointOfInterest', 'itinerary_locations')->withTimestamps();
	 }
}
