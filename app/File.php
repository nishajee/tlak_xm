<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public function libraries()
    {
        return $this->belongsTo('App\Library');
    }
}
