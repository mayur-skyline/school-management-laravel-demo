<?php

namespace App\Http\Resources\ActionPlan;

use Illuminate\Http\Resources\Json\JsonResource;

class ActionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->data,
            'meta' => $this->meta
        ];
    }
}
