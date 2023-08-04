<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CompanyResource extends JsonResource
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
        $roles = [];
        foreach (Auth::user()->user_roles->where('company_code',$this->id) as $role) {
            if ($role->status == 1) {
                array_push($roles, $role);
            }
        }
        return [
            'id' => $this->id,
            'company_code' => $this->company_code,
            'company_name' => $this->company,
            'roles' => UserRoleResource::collection($roles),
        ];
    }
}
