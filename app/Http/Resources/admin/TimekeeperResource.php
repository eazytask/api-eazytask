<?php

namespace App\Http\Resources\admin;

use App\Http\Resources\user\UserJobTypeResource;
use App\Http\Resources\user\UserProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TimekeeperResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->employee),
            'project' =>new UserProjectResource($this->project),
            'roaster_date' => $this->roaster_date,
            'shift_start' => $this->shift_start,
            'shift_end' => $this->shift_end,
            'sing_in' => $this->sing_in,
            'sing_out' => $this->sing_out,
            'duration' => (double) $this->duration,
            'ratePerHour' => (double) $this->ratePerHour,
            'amount' => (double) $this->amount,
            'job_type' => new UserJobTypeResource($this->job_type),
            'roaster_type' => $this->roaster_type,
            'payment_status' => $this->payment_status,
            'app_start' => $this->Approved_start_datetime,
            'app_end' => $this->Approved_end_datetime,
            'app_duration' => $this->app_duration,
            'app_rate' => $this->app_rate,
            'app_amount' => $this->app_amount,
            'is_approved' => $this->is_approved,
            'is_applied' => $this->apply_status(),
            // 'color' => $this->get_color(),
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
            return 1;
        } elseif ($this->roaster_status_id == roaster_status('Accepted')) {
            return 1;
        } else {
            return 0;
        }
    }
}
