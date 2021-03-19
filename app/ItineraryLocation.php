<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItineraryLocation extends Model
{
    protected $fillable = ['itinerary_id','destination_id','tenant_id','user_id'];
}

