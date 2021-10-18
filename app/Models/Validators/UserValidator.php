<?php

namespace App\Models\Validators;

use App\Models\User;
use Illuminate\Validation\Rule;


class UserValidator
{
    public function validate(User $user, array $attributes) : array
    {
        return validator($attributes, [
            'name' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'username' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'email' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'email'],
            'password' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:6'],
            // 'is_admin' => [Rule::when($user->exists, 'sometimes'), 'required', 'boolean'], :rfc,dns
        ])->validate();
    }
}
