<?php
 
namespace App\Policies;
 
use App\Policies\BaseCrudPolicy;
use App\Models\User; 

class UserPolicy extends BaseCrudPolicy
{
    /**
     * Determine if the given user can create tokens.
     *
     * @param  User $user
     * @return bool
     */
    public function createToken(User $user)
    {
        return $this->isSuperUser($user);
    }

    /**
     * Determine if the given user can update the given token.
     *
     * @param  User $user
     * @param  User $token
     * @return bool
     */
    public function updateToken(User $user, User $token)
    {
        return $this->isSuperUser($user);
    }

    /**
     * Determine if the given user can delete the given token.
     *
     * @param  User $user
     * @param  User $token
     * @return bool
     */
    public function deleteToken(User $user, User $token)
    {
        return $this->isSuperUser($user);
    }
}