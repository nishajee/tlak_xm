<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UpcommingTourPackage extends Model
{
    public function tour_pckages()
    {
        return $this->belongsToMany('App\TourPckage','tour_pckage_upcomming_tour_packages')->withTimestamps();
    }
   
}
