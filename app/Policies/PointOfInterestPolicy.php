<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointOfInterestPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function poi_view()
    {
        $permission = User::getPermissions();
        if(in_array('poi-view', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function poi_create()
    {
        $permission = User::getPermissions();
        if(in_array('poi-create', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function poi_edit()
    {
        $permission = User::getPermissions();
        if(in_array('poi-edit', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function poi_delete()
    {
        $permission = User::getPermissions();
        if(in_array('poi-delete', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function location_edit()
    {
        $permission = User::getPermissions();
        if(in_array('location-edit', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }
}
