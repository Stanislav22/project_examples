<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Validation\Builder;
use App\Models\UserType;
use App\Models\User;
use App\Models\MFAMethod;

class CreateUserRequest extends FormRequest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return Builder::get([
            'name' => Builder::required()->string(),
            'email' => Builder::required()->email()->unique(User::class),
            'password' => Builder::required()->password()->confirmed(),
            'roles' =>  Builder::required()->array(),
            'roles.*' => Builder::requiredWith('roles')->distinct()->in(UserType::values()),
        ]);
    }
}
