<?php

namespace App\Http\Resources\user;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTimekeeperResource extends JsonResource
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
            'employee_id' => (int)$this->employee_id,
            'employee_name' => $this->employee_name,
            'project' =>new UserProjectResource($this->project),
            'roaster_date' => $this->roaster_date,
            'shift_start' => $this->shift_start?Carbon::parse($this->shift_start)->format('Y-m-d H:i:s'):null,
            'shift_end' => $this->shift_end?Carbon::parse($this->shift_end)->format('Y-m-d H:i:s'):null,
            'sing_in' => $this->sing_in?Carbon::parse($this->sing_in)->format('Y-m-d H:i:s'):null,
            'sing_out' => $this->sing_out?Carbon::parse($this->sing_out)->format('Y-m-d H:i:s'):null,
            'duration' => (double) $this->duration,
            'ratePerHour' => (double) $this->ratePerHour,
            'amount' => (double) $this->amount,
            'job_type' => new UserJobTypeResource($this->job_type),
            'roaster_type' => $this->roaster_type,
            'payment_status' => $this->payment_status,
            'is_approved' => $this->is_approved,
            'is_applied' => $this->apply_status(),
            'color' => $this->color,
            'remarks' => $this->remarks,
        ];
    }

    // protected function get_color(){
    //     if ($this->shift_start < Carbon::now() && $this->sing_in==null) {
    //         $status = '#ea5455';
    //     }elseif($this->sing_in!=null && $this->sing_out==null && $this->shift_end < Carbon::now()) {
    //         $status = '#ea5455';
    //     }elseif($this->sing_in!=null && $this->sing_out==null) {
    //         $status = '#28c76f';
    //     }else{
    //         $status = '#28c76f';
    //     }
    //     return $status;
    // }

    protected function apply_status(){
        if ($this->roaster_type == 'Unschedueled') {
            return 1;
        }elseif ($this->roaster_status_id == roaster_status('Rejected')) {
            return 3;
        } elseif ($this->roaster_status_id == roaster_status('Accepted')) {
            return 1;
        } else {
            return 0;
        }
    }
}
