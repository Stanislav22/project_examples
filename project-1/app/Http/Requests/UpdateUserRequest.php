<?php

namespace App\Http\Requests;

use App\Validation\Builder;
use App\Models\User;

class UpdateUserRequest extends CreateUserRequest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return Builder::mergeConcat([
            'name' => Builder::sometimes(),
            'password' => Builder::sometimes(),
            'roles' => Builder::sometimes(),
        ], array_merge(
            parent::rules(), [
                'email' => Builder::sometimes()->required()->email()->unique(
                    User::class,
                    setup: function($rule) {
                        $rule->ignore($this->route('user'));
                    }
                ),
            ]
        ));
    }
}
