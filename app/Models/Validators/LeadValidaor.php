<?php

namespace App\Models\Validators;

use App\Models\Lead;
use Illuminate\Validation\Rule;


class LeadValidator
{
    public function validate(Lead $lead, array $attributes) : array
    {
        return validator($attributes, [
            'title' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'description' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'value' => [Rule::when($lead->exists, 'sometimes'), 'required', 'integer'],
            'source' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_person' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_email' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_phone' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_organization' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'start_date' => [Rule::when($lead->exists, 'sometimes'), 'required', 'date', 'before:end_date'],
            'end_date' => [Rule::when($lead->exists, 'sometimes'), 'required', 'date', 'after:start_date'],

        ])->validate();
    }
}
