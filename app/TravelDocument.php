<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelDocument extends Model
{
    public function document_icons()
    {
        return $this->belongsTo('App\DocumentIcon');
    }
}
