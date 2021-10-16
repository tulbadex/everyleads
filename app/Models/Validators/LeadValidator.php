<?php

namespace App\Models\Validators;

use App\Models\Lead;
use Illuminate\Validation\Rule;

class LeadValidator
{
    public function validate(Lead $lead, array $attributes): array
    {
        return validator($attributes, [
            'title' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'description' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'value' => [Rule::when($lead->exists, 'sometimes'), 'required', 'integer'],
            'source' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            // 'assign_to' => [Rule::when($lead->exists, 'sometimes'), 'integer'],
            'contact_person' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_email' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_phone' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'contact_organization' => [Rule::when($lead->exists, 'sometimes'), 'required', 'string'],
            'start_date' => [Rule::when($lead->exists, 'sometimes'), 'required', 'date:Y-m-d', 'before:end_date'],
            'end_date' => [Rule::when($lead->exists, 'sometimes'), 'required', 'date:Y-m-d', 'after:start_date'],
            'status' => [Rule::in([$lead::STATUS_FOLLOW_UP, $lead::STATUS_PROSPECT, $lead::STATUS_NEGOTIATION, 
                    $lead::STATUS_WON, $lead::STATUS_LOST])],
        ])->validate();
    }
}
