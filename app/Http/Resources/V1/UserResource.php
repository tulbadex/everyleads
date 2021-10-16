<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

use App\Models\User;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /* if (auth()->user()->isAdmin()) {
            return [
                Arr::except(parent::toArray($request))
            ];
        } else {
            return [
                $this->merge(Arr::except(parent::toArray($request), [
                    'created_at', 'updated_at', 'email_verified_at', 'email', 'is_admin'
                ]))
            ];
        } */

        return [
            $this->merge(Arr::except(parent::toArray($request), [
                'created_at', 'updated_at', 'email_verified_at', 'email', 'is_admin'
            ]))
        ];
    }
}
