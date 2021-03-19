<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentIcon extends Model
{
    public function travel_documents()
    {
        return $this->belongsTo('App\TravelDocument');
        ////jgfjd
    }
}
