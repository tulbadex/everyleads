<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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
            /* 'creator' => UserResource::make($this->creator),
            'assign' => UserResource::make($this->assign), */

            $this->merge(Arr::except(parent::toArray($request), [
                'created_at', 'updated_at'
            ]))
        ];
    }
}
