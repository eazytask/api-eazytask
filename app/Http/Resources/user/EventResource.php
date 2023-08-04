<?php

namespace App\Http\Resources\user;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'project' =>new UserProjectResource($this->project),
            'roaster_date' => $this->event_date,
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'duration' => Carbon::parse($this->shift_start)->floatDiffInRealHours(Carbon::parse($this->shift_end)),
            'rate' => $this->rate,
            'remarks' => $this->remarks,
            'already_applied' => count($this->already_applied)? true:false,
            // "meta_data"=> [
            //     "pagination"=>[
            //        "total"=> $request->total(),
            //        "per_page"=> $request->perPage(),
            //        "last_page"=> $request->lastPage(),
            //        "current_page"=> $request->currentPage(),
            //        "from"=> $request->firstItem(),
            //        "to"=> $request->lastItem()
            //     ]
            // ]
        ];
    }
}
