<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UpcommingTourPackagePolicy
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

    public function optional_departure_view()
    {
        $permission = User::getPermissions();
        if(in_array('optional-departure-view', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function optional_departure_create()
    {
        $permission = User::getPermissions();
        if(in_array('optional-departure-create', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function optional_departure_edit()
    {
        $permission = User::getPermissions();
        if(in_array('optional-departure-edit', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function optional_departure_delete()
    {
        $permission = User::getPermissions();
        if(in_array('optional-departure-delete', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }
}
