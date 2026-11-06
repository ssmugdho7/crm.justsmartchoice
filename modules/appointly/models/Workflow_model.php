<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Workflow_model extends App_Model {
    public function reschedule(array $a,$date,$time,$version){
        if(!appointly_valid_schedule($date,$time))throw new InvalidArgumentException('Choose a valid date and time.');
        if(in_array($a['status'],['completed','cancelled','no-show'],true))throw new RuntimeException('This appointment is closed. Reopen it before rescheduling.');
        if(!empty($a['google_event_id']) && (int)($a['google_added_by_id']??0)!==(int)get_staff_user_id())throw new RuntimeException('Only the connected Google calendar owner can reschedule this event.');
        $provider=(int)$a['provider_id'];if(!$provider)throw new RuntimeException('Assign a qualified provider before rescheduling.');
        $locks=['sc_appointment_'.(int)$a['id'],'sc_provider_'.$provider.'_'.$date];$held=[];
        try{
            foreach($locks as $lock){$row=$this->db->query('SELECT GET_LOCK(?, 5) AS acquired',[$lock])->row_array();if((int)($row['acquired']??0)!==1)throw new RuntimeException('Scheduling is busy. Please retry.');$held[]=$lock;}
            $fresh=$this->db->where('id',$a['id'])->get(db_prefix().'appointly_appointments')->row_array();
            if(!$fresh || !hash_equals(appointly_schedule_version($fresh),$version))throw new RuntimeException('The appointment changed. Refresh before rescheduling.');
            $this->load->model('appointly/appointly_model');
            if(!empty($a['service_id']) && !$this->db->where('service_id',$a['service_id'])->where('staff_id',$provider)->count_all_results(db_prefix().'appointly_service_staff'))throw new RuntimeException('The assigned provider is not configured for this service.');
            $slots=$this->appointly_model->get_available_time_slots($provider,$date,$a['service_id'],$a['timezone']?:'America/New_York',$a['id']);
            $slot=null;foreach($slots as $candidate)if(($candidate['start']??$candidate['value']??'')===$time && !empty($candidate['available'])){$slot=$candidate;break;}
            if(!$slot)throw new RuntimeException('That time is unavailable for this provider and service. Choose another slot.');
            $end=$slot['end'];
            // Serialize and reject overlaps again against the current native schedule.
            $overlap=$this->db->where('provider_id',$provider)->where('date',$date)->where('id !=',$a['id'])->where_not_in('status',['cancelled','no-show'])->where('start_hour <',$end)->where('end_hour >',$time)->count_all_results(db_prefix().'appointly_appointments');
            if($overlap)throw new RuntimeException('This provider has a conflicting appointment.');
            if(!empty($a['google_event_id'])){
                if(!appointlyGoogleAuth())throw new RuntimeException('Reconnect the original Google calendar before rescheduling.');
                $sync=array_merge($a,['appointment_id'=>$a['id'],'date'=>$date,'start_hour'=>$time,'end_hour'=>$end]);
                if(!updateAppointmentToGoogleCalendar($sync))throw new RuntimeException('Google Calendar could not be updated. The appointment was not moved.');
            }
            $this->db->where('id',$a['id'])->update(db_prefix().'appointly_appointments',['date'=>$date,'start_hour'=>$time,'end_hour'=>$end]);
            if(!$this->db->affected_rows() && ($date!==$a['date'] || $time!==substr($a['start_hour'],0,5)))throw new RuntimeException('The appointment could not be saved. Refresh and retry.');
            $saved=$this->db->where('id',$a['id'])->get(db_prefix().'appointly_appointments')->row_array();
            if(!$saved)throw new RuntimeException('Unable to reload the updated appointment. Refresh the board.');
            log_activity('Appointment #'.$a['id'].' rescheduled by staff #'.get_staff_user_id());
            return ['success'=>true,'version'=>appointly_schedule_version($saved),'date'=>$saved['date'],'start'=>substr($saved['start_hour'],0,5),'end'=>substr($saved['end_hour'],0,5)];
        }finally{foreach(array_reverse($held) as $lock)$this->db->query('SELECT RELEASE_LOCK(?)',[$lock]);}
    }
}
