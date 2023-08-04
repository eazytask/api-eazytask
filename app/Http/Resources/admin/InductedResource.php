<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class InductedResource extends JsonResource
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
            'employee_name' => $this->employee->fname,
            'employee_id' => $this->employee_id,
            'project_id' => $this->project_id,
            'project_name' => $this->project->pName,
            'induction_date' => $this->induction_date,
            'remarks' => $this->remarks,
        ];
    }
}
