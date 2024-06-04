<?php

namespace App\Models;

use App\Traits\Models\SanitizeArgTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends \Konekt\User\Models\User implements NotifiableContract
{
    use HasFactory,
        SanitizeArgTrait,
        HasApiTokens;

    /**
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_api' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $enums = [
        'type' => UserType::class,
        'mfa_method' => MFAMethod::class,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'type', 'is_active', 'is_api', 'mfa_method',
    ];

    /**
     * @var array
     */
    protected $with = ['roles'];

    /**
     * @inheritdoc
     */
    protected static function booted()
    {
        static::softDeleted(function ($user) {
            $user->trashEmail();
        });
    }

    /**
     * Associated roles
     */
    public function roles()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /**
     * Related oauth tokens
     */
    public function oauth_tokens()
    {
        return $this->hasMany(OAuthToken::class, 'user_id', 'id');
    }


    /**
     * Scope to filter api users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query $query
     * @param mixed $value
     */
    public function scopeIsApi($query, $value)
    {
        return $query->where($this->qualifyColumn('is_api'), $this->safeBoolean($value));
    }


    /**
     * @inheritdoc
     */
    public function sendPasswordResetNotification($token)
    {
        // Get additional query data for frontend
        $additional = \Request::get('additional', []);

        $notification = Notifications::getPasswordReset();
        if ($notification) {
            $this->notify(new ResetPassword(
                    $notification->email_content,
                    $notification->email_subject,
                    $token,
                    $notification->sms ? $notification->sms_content : null,
                    null,
                    [
                        'additional' => $additional,
                    ],
                    $notification->bcc,
                    $notification->bcc_recipients
                )
            );
        }
    }

    /**
     * Check if user has specific role
     *
     * @param $role string
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles->contains('role', $role);
    }

    /**
     * Check if user has any role from array
     *
     * @param $roles array
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        foreach ($roles as $role) {
            if ($this->roles->contains('role', $role)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Make user email re-usable for others
     */
    protected function trashEmail()
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->update([
            'email' => $this->email .'_deleted_'. time(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTimezone()
    {
        return $this->customer ? $this->customer->getTimezone() : Settings::get('general.time_zone');
    }

    /**
     * Get MFA identifier
     *
     * @return string
     */
    public function getMFAIdentifier()
    {
        if ($this->mfa_method->value() === MFAMethod::SMS) {
            $phone = $this->customer ? $this->customer->mobile : null;

            if ($phone === null) {
                throw new \Exception('User does not have a phone number');
            }

            return $phone;
        }

        return $this->email;
    }

    /**
     * Return current OAuth token of given type
     *
     * @param $type
     * @return mixed
     */
    public function currentOAuthToken($type)
    {
        return $this->oauth_tokens()->where('type', $type)->first();
    }

    /**
     * Sync new OAuth token by type
     *
     * @param $type
     * @param $data
     * @return OAuthToken
     */
    public function syncNewOAuthToken($type, $data)
    {
        $this->oauth_tokens()->where('type', $type)->delete();
        return $this->oauth_tokens()->create(array_merge($data, [
            'type' => $type,
        ]));
    }
}
