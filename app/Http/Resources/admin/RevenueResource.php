<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResource extends JsonResource
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
            'project_name' => new ProjectResource($this->project),
            'roaster_date_from' => $this->roaster_date_from,
            'roaster_date_to' => $this->roaster_date_to,
            'hours' => $this->hours,
            'rate' => $this->rate,
            'amount' => $this->cNumber,
            'remarks' => $this->remarks,
            'payment_date' => $this->payment_date,
        ];
    }
}
