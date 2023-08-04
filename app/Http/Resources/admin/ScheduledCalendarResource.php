<?php

namespace App\Http\Resources\admin;

use App\Http\Resources\user\UserJobTypeResource;
use App\Http\Resources\user\UserProjectResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledCalendarResource extends JsonResource
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
            'color' => $this->color,
            'label' => $this->get_label(),
            'id' => $this->id,
            'employee_id' => (int)$this->employee_id,
            'employee_name' => $this->employee_name,
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
            'is_approved' => $this->is_approved,
            'app_start' => $this->Approved_start_datetime,
            'app_end' => $this->Approved_end_datetime,
            'app_duration' => $this->app_duration,
            'app_rate' => $this->app_rate,
            'app_amount' => $this->app_amount,
            'remarks' => $this->remarks,
        ];
    }

    protected function get_label(){
        if ($this->shift_start < Carbon::now() && $this->sing_in==null) {
            $label = 'Absent';
        }elseif($this->sing_in!=null && $this->sing_out==null && $this->shift_end < Carbon::now()) {
            $label = 'Passing';
        }elseif($this->sing_in!=null && $this->sing_out==null) {
            $label = 'On-Shift';
        }elseif($this->sing_in!=null && $this->sing_out!=null && $this->is_approved==0) {
            $label = 'Pending';
        }elseif($this->is_approved==1) {
            $label = 'Approved';
        }else{
            $label = '';
        }
        return $label;
    }

    protected function get_color(){
        if ($this->roaster_type == 'Unschedueled') {
            $status = '#5DADE2';
        }elseif ($this->shift_start < Carbon::now() && $this->sing_in==null) {
            $status = '#ea5455';
        }elseif($this->sing_in!=null && $this->sing_out==null && $this->shift_end < Carbon::now()) {
            $status = '#ea5455';
        }elseif($this->sing_in!=null && $this->sing_out==null) {
            $status = '#28c76f';
        }
        // elseif($this->roaster_status->color) {
        //     $status = $this->roaster_status->color;
        // }
        else{
            $status = '#FFC69E';
        }
        return $status;
    }
}
