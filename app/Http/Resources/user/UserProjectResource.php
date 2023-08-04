<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProjectResource extends JsonResource
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
            'id' => (int)$this->id,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'pName' => $this->pName,
            'project_address' => $this->project_address,
            'suburb' => $this->suburb,
            'project_state' => $this->project_state,
        ];
    }
}
