<?php

namespace App\Policies;

use App\Models\User;

class ClinicPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny()
    {
        return true;
    }

    public function view()
    {
        return true;
    }

    public function create(User $user)
    {
        return match ($user->role->name) {
            'admin' => false,
            'doctor' => true,
            'owner' => false,
        };
    }

    public function update()
    {
        return auth()->user()->role->name == 'admin';
    }
}
