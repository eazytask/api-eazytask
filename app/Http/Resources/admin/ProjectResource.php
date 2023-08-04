<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'id' => $this->id,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'pName' => $this->pName,
            'Status' => $this->Status,
            'cName' => $this->cName,
            'cNumber' => $this->cNumber,
            'clientName' => $this->clientName,
            'project_address' => $this->project_address,
            'suburb' => $this->suburb,
            'project_state' => $this->project_state,
            'postal_code' => $this->postal_code
        ];
    }
}
