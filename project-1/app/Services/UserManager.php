<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserType;
use App\Traits\Models\SearchableTrait;
use App\Exceptions\CreateUserException;
use App\Exceptions\UpdateUserException;
use App\Exceptions\DeleteUserException;
use App\Events\UserWasCreated;
use App\Events\UserWasUpdated;
use App\Events\UserWasDeleted;
use App\Traits\Models\UpdateRelatedTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\AbstractUser as SocialUser;
use Illuminate\Support\Facades\Password;
use App\Exceptions\ResetPasswordException;
use Illuminate\Auth\Events\PasswordReset;
use App\Facades\Settings;
use App\Facades\MFA;
use Carbon\Carbon;

class UserManager implements \App\Contracts\UserManager{
    use SearchableTrait,
        UpdateRelatedTrait;

    /**
     * @var array
     */
    protected $filters = [
        'is_api' => 'isApi',
    ];

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        $userData = $this->createUserData($data);
        $storesData = $this->createStoresData($data);
        $rolesData = $this->createRolesData($data);

        if (empty($userData['password'])) {
            $userData['password'] = Hash::make($this->generatePassword());
        }

        $user = new User($userData);

        try {
            $this->validateUserRole($rolesData, $user->email);
        } catch (\Exception $e) {
            throw new CreateUserException($e->getMessage());
        }

        DB::transaction(function() use($rolesData, $user, $storesData) {
            $user->save();

            $user->roles()->createMany($rolesData);

            if ($storesData->count() !== 0) {
                $user->stores()->sync($storesData);
            }
        });

        event(new UserWasCreated($user));

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function search(array $params)
    {
        return $this->performSearch(User::query(), $params);
    }

    /**
     * @inheritdoc
     */
    public function update(User $user, array $data)
    {
        $userData = $this->createUserData($data);
        $rolesData = $this->createRolesData($data);

        $user->fill($userData);

        try {
            $this->validateUserRole($rolesData, $user->email);
        } catch (\Exception $e) {
            throw new UpdateUserException($e->getMessage());
        }

        DB::transaction(function() use($rolesData, $user, $data) {
            $user->save();

            if ($rolesData->count()) {
                $this->updateRelated($user, 'roles', $rolesData, 'role');
            }

            if (isset($data['stores'])) {
                $storesData = $this->createStoresData($data);
                $user->stores()->sync($storesData);
            }
        });

        event(new UserWasUpdated($user));
    }

    /**
     * @inheritdoc
     */
    public function changePassword(User $user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();
    }

    /**
     * @inheritdoc
     */
    public function delete(User $user)
    {
        $user->delete();

        event(new UserWasDeleted($user));
    }

    /**
     * @inheritdoc
     */
    public function login($email, $password, $device, $mfaCode = null)
    {
        $user = User::active()
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (Settings::get('general.features.mfa')) {
            if ($user->mfa_method->isEnabled()) {
                $identifier = $user->getMFAIdentifier();
                $method = $user->mfa_method->value();

                if ($mfaCode === null) {
                    return MFA::startChallenge($identifier, $method);
                } elseif (! MFA::verifyChallenge($identifier, $method, $mfaCode)) {
                    return null;
                }
            }
        }

        return $this->authorizeUser($user, $device);
    }

    /**
     * @inheritdoc
     */
    public function authorize(User $user, $device, $replaceToken = true)
    {
        if ($replaceToken) {
            $user->tokens()->where('name', $device)->delete();
        }

        return $this->authorizeUser($user, $device);
    }

    /**
     * @inheritdoc
     */
    public function socialLogin(SocialUser $socialUser, $device)
    {
        $email = $socialUser->getEmail();
        $user = User::where('email', $email)->first();

        if ($user === null || ! $user->is_active) {
            return null;
        }

        return $this->authorizeUser($user, $device);
    }

    /**
     * @inheritdoc
     */
    public function emailExists($email)
    {
        return User::where('email', $email)->exists();
    }

    /**
     * @inheritdoc
     */
    public function logout($allDevices = true)
    {
        if ($allDevices) {
            Auth::user()
                ->tokens()
                ->delete();
        } else {
            Auth::user()
                ->currentAccessToken()
                ->delete();
        }
    }

    /**
     * @inheritdoc
     */
    public function generatePassword()
    {
        $soup = [];

        $this->pushRandomSymbols($soup, 5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $this->pushRandomSymbols($soup, 5, 'abcdefghijklmnopqrstuvwxyz');
        $this->pushRandomSymbols($soup, 3, '0123456789');
        $this->pushRandomSymbols($soup, 3, '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{}~');

        return implode(Arr::shuffle($soup));
    }

    /**
     * @inheritdoc
     */
    public function sendPasswordResetLink($email)
    {
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new ResetPasswordException(__($status));
        }
    }

    /**
     * @inheritdoc
     */
    public function resetPassword($token, $email, $password)
    {
        $status = Password::reset([
            'email' => $email,
            'password' => $password,
            'token' => $token,
        ], function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
            $user->tokens()->delete();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw new ResetPasswordException(__($status));
        }
    }

    /**
     * @inheritdoc
     */
    public function searchApiUsers($params)
    {
        return $this->performSearch(User::isApi(true)->with(['tokens', 'stores']), $params);
    }

    /**
     * @inheritdoc
     */
    public function createApiUser(array $data)
    {
        $data = array_merge([
            'is_api' => true,
            'is_active' => true,
            'name' => __('API'),
            'email' => sprintf('api-%s@mail.com', Str::random(10)),
            'roles' => [UserType::API],
        ], $data);

        $expiresAt = Arr::get($data, 'expires_at', null);
        $expiresAt = $expiresAt !== null ? Carbon::parse($expiresAt) : null;
        unset($data['expires_at']);

        return DB::transaction(function() use($data, $expiresAt) {
            // Create a user
            $user = $this->create($data);

            // Create a token
            $user->withAccessToken($user->createToken('api', ['*'], $expiresAt));

            return $user;
        });
    }

    /**
     * @inheritdoc
     */
    public function updateApiUser(User $user, array $data)
    {
        $refreshToken = Arr::get($data, 'refresh_token', false);
        $expiresAt = $refreshToken ? Arr::get($data, 'expires_at', null) : null;
        $expiresAt = $expiresAt !== null ? Carbon::parse($expiresAt) : null;
        $data = Arr::except($data, ['refresh_token', 'expires_at']);

        return DB::transaction(function() use($user, $data, $refreshToken, $expiresAt) {
            $this->update($user, $data);

            if ($refreshToken) {
                $user->tokens()->delete();
                $user->withAccessToken($user->createToken('api', ['*'], $expiresAt));
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function revokeApiUser(User $user)
    {
        if (! $user->is_api) {
            throw new DeleteUserException('User is not an API user');
        }

        DB::transaction(function() use($user) {
            $user->tokens()->delete();
            $user->delete();
        });
    }

    /**
     * Pick $num random symbols from $alphabet and push them into $array
     *
     * @param array $array
     * @param int $num
     * @param string $alphabet
     */
    protected function pushRandomSymbols(array & $array, $num, $alphabet)
    {
        $alphabetLen = strlen($alphabet);

        for ($i = 0; $i < $num; $i++) {
            $array[] = substr($alphabet, rand(0, $alphabetLen - 1), 1);
        }
    }

    /**
     * Make user data
     *
     * @param array $data
     * @return array
     */
    protected function createUserData(array & $data)
    {
        $userData = Arr::except($data, ['password', 'roles', 'stores']);
        $password = Arr::get($data, 'password', '');

        if ($password !== '') {
            $userData['password'] = Hash::make($password);
        }

        $userData['is_active'] = Arr::get($data, 'is_active', true);

        return $userData;
    }

    /**
     * Make stores data
     *
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    protected function createStoresData(array & $data)
    {
        return collect(Arr::get($data, 'stores', []));
    }

    /**
     * Make roles data
     *
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    protected function createRolesData(array & $data)
    {
        $roles = collect(Arr::get($data, 'roles', []));
        $map = $roles->mapWithKeys(function($role) {
            return [$role => [
                'role' => $role
            ]];
        });
        return $map;
    }

    /**
     * Authorize the given user
     *
     * @param User $user
     * @param string $device
     * @param int $ttlDays
     * @return User
     */
    protected function authorizeUser($user, $device, $ttlDays = 30)
    {
        $expiresAt = $ttlDays > 0
            ? Carbon::now()->addDays($ttlDays)
            : null;

        return $user->withAccessToken($user->createToken($device, ['*'], $expiresAt));
    }

    /**
     * Check if type/email combination is allowed
     *
     * @param string $type
     * @param string $email
     * @return bool
     */
    protected function validateUserRole($roles, $email)
    {
        if ($roles->has(UserType::SUPERADMIN)) {
            $domain = Settings::get('general.superadmin_domain');

            if (! empty($domain) &&
                ! Str::endsWith(strtoupper($email), '@'. strtoupper($domain)))
            {
                throw new \Exception('Superadmins are only allowed with email at '. $domain);
            }
        }
    }
}
