<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function user_view()
    {
        $permission = User::getPermissions();
        if(in_array('user-view', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function user_create()
    {
        $permission = User::getPermissions();
        if(in_array('user-create', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function user_edit()
    {
        $permission = User::getPermissions();
        if(in_array('user-edit', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }

    public function user_activate_inactivate()
    {
        $permission = User::getPermissions();
        if(in_array('user-activate-inactivate', $permission)) {
            return true;
        }
        else{
            return false;
        }
    }
}
