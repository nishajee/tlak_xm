<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = ['tour_package_id','day_number','inclusions','description','tenant_id','user_id','banner_image','name','screen_view'];
    
    public function tour_pckages()
    {
        return $this->belongsTo('App\TourPckage');
    }
 
   
    public function locations()
     {
        return $this->belongsToMany('App\Location','itinerary_locations')->withTimestamps();
     }
    // public function hotels() 
    //  {
    //     return $this->belongsToMany('App\Hotel', 'hotel_itineraries')->withTimestamps();
    //  }
    
}
