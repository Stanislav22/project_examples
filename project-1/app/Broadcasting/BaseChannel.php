<?php
 
namespace App\Broadcasting;

use App\Models\User;
use App\Traits\Policies\InspectUser;

class BaseChannel
{
    use InspectUser;
    
    /**
     * Determine if the given user can join channel.
     *
     * @param  User $user
     * @return bool
     */
    public function join(User $user)
    {
        return $this->isBackendUser($user);
    }
}