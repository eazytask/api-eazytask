<?php

namespace App\Http\Resources\admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientRecource extends JsonResource
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
            'cname' => $this->cname,
            'cemail' => $this->cemail,
            'cnumber' => $this->cnumber,
            'cstate' => $this->cstate,
            'caddress' => $this->caddress,
            'suburb' => $this->suburb,
            'cperson' => $this->cperson,
            'cpostal_code' => $this->cpostal_code,
            'cimage' => $this->cimage,
            'status' => $this->status,
        ];
    }
}
