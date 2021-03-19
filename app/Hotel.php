<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name','latitude','place_id','longitude','address','tenant_id','user_id'
    ];

    public function itineraries() 
     {
		return $this->belongsToMany('App\Itinerary', 'hotel_itineraries')->withTimestamps();
	 }
}
