<?php 

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Users extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Contracts\UserManager::class;
    }
}