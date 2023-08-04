<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ViewScheduleResource extends JsonResource
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
            'employee_id' => (int)$this->employee_id,
            'image' => $this->image ? asset($this->image) : "",
            'fname' => $this->fname,
            'mname' => $this->mname,
            'lname' => $this->lname,
            'total_hours' => $this->total_hours,
            'total_amount' => $this->total_amount,
            'record' => $this->record,
        ];
    }
}
