<?php

namespace App\Http\Resources\user;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCalendarEventResource extends JsonResource
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
            'project_name' => $this->project->pName,
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'color' => '#884EA0',
            'is_event' => true,
            'shift' => [
                'id' => $this->id,
                // 'employee_id' => $this->employee_id,
                'project' =>new UserProjectResource($this->project),
                'roaster_date' => $this->event_date,
                'shift_start' => $this->shift_start,
                'shift_end' => $this->shift_end,
                'sing_in' => null,
                'sing_out' => null,
                'duration' => $duration,
                'ratePerHour' => (double) $this->rate,
                'amount' => (double) ($this->rate * $duration),
                'job_type' => new UserJobTypeResource($this->job_type),
                'is_applied' => count($this->already_applied)? 1:0,
                'remarks' => $this->remarks,
            ]
        ];
    }
}
