<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'fname' => $this->fname,
            'mname' => $this->mname,
            'lname' => $this->lname,
            'role' => $this->role,
            'address' => $this->address,
            'suburb' => $this->suburb,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'email' => $this->email,
            'status' => $this->status,
            'contact_number' => $this->contact_number,
            'date_of_birth' => $this->date_of_birth,
            'license_no' => $this->license_no,
            'license_expire_date' => $this->license_expire_date,
            'first_aid_license' => $this->first_aid_license,
            'first_aid_expire_date' => $this->first_aid_expire_date,
            'image' => $this->image ? asset($this->image) : "",
        ];
    }
}
