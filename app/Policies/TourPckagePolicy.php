<?php

namespace App\Policies;

use App\User;
use App\TourPckage;
use Illuminate\Auth\Access\HandlesAuthorization;

class TourPckagePolicy
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

    public function departure_view()
    {
        $permission = User::getPermissions();

        if(in_array('departure-view', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function departure_create()
    {
        $permission = User::getPermissions();

        if(in_array('departure-create', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function departure_edit()
    {
        $permission = User::getPermissions();

        if(in_array('departure-edit', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }
}
