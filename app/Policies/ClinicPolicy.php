<?php

namespace App\Policies;

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

    public function create()
    {
        return auth()->user()->role->name == 'admin';
    }

    public function update()
    {
        return auth()->user()->role->name == 'admin';
    }
}
