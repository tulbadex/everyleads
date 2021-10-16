<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

use App\Models\User;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            /* 'creator' => new UserResource(User::find($this->creator)),
            'assign' => new UserResource(User::find($this->assign)), */

            'creator' => UserResource::make(User::find($this->creator)),
            'assign' => UserResource::make(User::find($this->assign_to)),

            $this->merge(Arr::except(parent::toArray($request), [
                'created_at', 'updated_at'
            ]))
        ];
    }
}
