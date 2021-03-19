<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuLabel extends Model
{
    protected $fillable = [
         'id','label','button_name','menu_label_icon_id','tenant_id','user_id'
    ];
}
