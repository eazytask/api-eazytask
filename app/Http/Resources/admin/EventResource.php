<?php

namespace App\Http\Resources\admin;

use App\Http\Resources\user\UserJobTypeResource;
use App\Http\Resources\user\UserProjectResource;
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
        $shift_start = Carbon::parse($this->shift_start);
        $shift_end = Carbon::parse($this->shift_end);
        $duration = round($shift_start->floatDiffInRealHours($shift_end), 2);
        return [
            'id' => $this->id,
            'employee_id' => (int)$this->employee_id,
            'project' =>new UserProjectResource($this->project),
            'event_date' => $this->event_date,
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'duration' => (double) $duration,
            'ratePerHour' => (double) $this->rate,
            'amount' => (double) $duration* $this->rate,
            'job_type' => new UserJobTypeResource($this->job_type),
            'remarks' => $this->remarks,
        ];
    }
}
