<?php

namespace App\Http\Resources\user;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCalendarResource extends JsonResource
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
            'project_name' => $this->project->pName,
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'color' => $this->get_color(),
            'is_event' => false,
            'shift' => new UserTimekeeperResource($this)
        ];
    }

    protected function get_color(){
        if ($this->roaster_type == 'Unschedueled') {
            $status = '#5DADE2';
        }elseif ($this->roaster_status_id == roaster_status('Rejected')) {
            $status = '#F8C471';
        } elseif ($this->roaster_status_id == roaster_status('Published')) {
            $status = '#F93737';
        } elseif (Carbon::parse($this->shift_start) < Carbon::now() && Carbon::parse($this->shift_end) < Carbon::now()) {
            $status = $this->sing_in ? '#7367f0' : '#F8C471';
        } elseif (Carbon::parse($this->roaster_date)->toDateString() == Carbon::now()->toDateString() && Carbon::parse($this->shift_end) > Carbon::now()) {
            $status = '#82E0AA';
        } else {
            $status = '#82E0AA';
        }
        return $status;
    }
}
