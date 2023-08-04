<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Resources\Json\JsonResource;

class UserComplianceResource extends JsonResource
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
            'compliance_id' => $this->compliance_id,
            'compliance_name' => $this->compliance->name,
            'certificate_no' => $this->certificate_no,
            'expire_date' => $this->expire_date,
            'comment' => $this->comment,
        ];
    }
}
