<?php

namespace App\Contracts;

use App\Models\User;
use Laravel\Socialite\AbstractUser as SocialUser;

interface UserManager
{
    /**
     * Create a user from the given input
     * 
     * @param  array $data
     * @return  User
     */
    public function create(array $data);

    /**
     * Search users
     * 
     * @param  array $params
     * @return  \Illuminate\Pagination\Paginator
     */
    public function search(array $params);

    /**
     * Update the user within the given input
     * 
     * @param  User $user
     * @param  array $data
     */
    public function update(User $user, array $data);

    /**
     * Update use password
     * 
     * @param  User $user
     * @param  string $password
     */
    public function changePassword(User $user, $password);

    /**
     * Delete the given user
     * 
     * @param  User $user
     */
    public function delete(User $user);

    /**
     * Log user in
     * 
     * @param string $email
     * @param string $password
     * @param string $device
     * @param string $mfaCode
     * @return User|\App\Models\MFAChallenge|null
     */
    public function login($email, $password, $device, $mfaCode = null);

    /**
     * Log user in by social account
     * 
     * @param SocialUser $socialUser
     * @param string $device
     * @return User
     */
    public function socialLogin(SocialUser $socialUser, $device);

    /**
     * Authorize user
     * 
     * @param User $user
     * @param string $device
     * @param bool $replaceToken
     * @return User
     */
    public function authorize(User $user, $device, $replaceToken = true);

    /**
     * Log user out
     * 
     * @param bool $allDevices
     */
    public function logout($allDevices = true);

    /**
     * Check if email already registered
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists($email);

    /**
     * Generate random password
     * 
     * @return string
     */
    public function generatePassword();

    /**
     * Send password reset link
     * 
     * @param string $email
     */
    public function sendPasswordResetLink($email);

    /**
     * Send password reset link
     * 
     * @param string $token
     * @param string $email
     * @param string $password
     */
    public function resetPassword($token, $email, $password);

    /**
     * Search API users
     * 
     * @param array $params
     * @return \Illuminate\Pagination\Paginator
     */
    public function searchApiUsers($params);

    /**
     * Create API user
     * 
     * @param array $data
     * @return User
     */
    public function createApiUser(array $data);

    /**
     * Update API user
     * 
     * @param User $user
     * @return User
     */
    public function updateApiUser(User $user, array $data);

    /**
     * Revoke API user token and deactivate the user
     * 
     * @param User $user
     */
    public function revokeApiUser(User $user);
}