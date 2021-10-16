<?php

namespace App\Models\Validators;

use App\Models\User;
use Illuminate\Validation\Rule;


class LeadValidator
{
    public function validate(User $user, array $attributes) : array
    {
        return validator($attributes, [
            'name' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'username' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'email' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'password' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'is_admin' => [Rule::when($user->exists, 'sometimes'), 'required', 'boolean'],
        ])->validate();
    }
}
