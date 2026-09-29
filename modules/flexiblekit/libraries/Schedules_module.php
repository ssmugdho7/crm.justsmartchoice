<?php

use Carbon\Carbon;

class Schedules_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('flexiblekit/flexiblekit_model');
        $this->ci->load->model('flexiblekit/flexibleschedule_model');
        $this->ci->load->model('flexiblekit/flexibleevent_model');
    }

    public function add_next_schedules()
    {
        $this->ci->db->trans_begin();
        try {
            // Get all active flexiblekit configurations
            $flexiblekits = $this->ci->flexiblekit_model->all([
                'flexiblekit_active' => 1
            ]);
    
            foreach($flexiblekits as $flexiblekit){
                // Sort flexiblekit schedules by the start date in descending order
                $flexibleschedules = $this->ci->flexibleschedule_model->all([
                    'flexiblekit_id' => $flexiblekit['id']
                ], [
                    [
                        'field' => 'flexibleschedule_start_datetime',
                        'order' => 'desc'
                    ]
                ]);
    
                if(empty($flexibleschedules)){
                    $this->add($flexiblekit['id']);
                }else{
                    // Pick the most recent schedule.
                    $flexibleschedule = $flexibleschedules[0];
    
                    // Check if next schedule should be added.
                    $should_add_next = (Carbon::now() >= $flexibleschedule['flexibleschedule_end_datetime']) 
                        || ($flexibleschedule['flexibleschedule_status'] != FLEXIBLEKIT_SCHEDULED_STATUS);
    
                    if($should_add_next){
                        $increment_string = implode(' ', [
                            '+',
                            $flexiblekit['flexiblekit_interval'],
                            $flexiblekit['flexiblekit_interval_type']
                        ]);
        
                        $next_date = flexiblekit_get_next_date($flexibleschedule['flexibleschedule_start_datetime'], $increment_string);
        
                        // If skip weekends is enable;
                        // Perform recursive date check to get a non-weekend date
                        if($flexiblekit['flexiblekit_skip_weekends']){
                            $next_date = $this->recursive_date_check($next_date, $increment_string);
                        }
        
                        $this->add($flexiblekit['id'], $next_date);
                    }
                }
            }
            $this->ci->db->trans_commit();
        } catch (\Throwable $th) {
            $this->ci->db->trans_rollback();
            throw $th;
        }
    }

    public function recursive_date_check($date, $increment_string){
        if(flexiblekit_is_weekend($date)){
            $date = flexiblekit_get_next_date($date, $increment_string);

            return $this->recursive_date_check($date, $increment_string);
        }else{
            return $date;
        }
    }

    public function add($flexiblekit_id, $start_date = null)
    {
        $flexiblekit = $this->ci->flexiblekit_model->get([
            'id' => $flexiblekit_id
        ]);

        if(!$flexiblekit){
            throw new Exception('Flexiblekti with ID: ' . $flexiblekit_id . ' not found!');
        }

        // Set the end date time of of the schedule using the start and end date time
        // of the flexiblekit
        $start_date = $start_date ? $start_date : $flexiblekit['flexiblekit_start_datetime'];
        $start_date_parts = explode(' ', $start_date);
        $end_date_parts = explode(' ', $flexiblekit['flexiblekit_end_datetime']);
        $end_date_parts[0] = $start_date_parts[0];
        $end_date = implode(' ', $end_date_parts);

        $schedule_data = [
            'flexiblekit_id' => $flexiblekit['id'],
            'flexibleschedule_userid' => $flexiblekit['flexiblekit_userid'],
            'flexibleschedule_contact_id' => $flexiblekit['flexiblekit_contact_id'],
            'flexibleschedule_contact_name' => $flexiblekit['flexiblekit_contact_name'],
            'flexibleschedule_contact_type' => $flexiblekit['flexiblekit_contact_type'],
            'flexibleschedule_name' => $flexiblekit['flexiblekit_name'],
            'flexibleschedule_subject' => $flexiblekit['flexiblekit_name'] . ' - ' . $flexiblekit['flexiblekit_contact_name'],
            'flexibleschedule_type' => $flexiblekit['flexiblekit_type'],
            'flexibleschedule_interval_label' => $flexiblekit['flexiblekit_interval_label'],
            'flexibleschedule_status' => FLEXIBLEKIT_SCHEDULED_STATUS,
            'flexibleschedule_start_datetime' => $start_date,
            'flexibleschedule_end_datetime' => $end_date
        ];

        $event_data = [
            'title' => $schedule_data['flexibleschedule_subject'],
            'userid' => $schedule_data['flexibleschedule_userid'],
            'start' => $schedule_data['flexibleschedule_start_datetime'],
            'end' => $schedule_data['flexibleschedule_end_datetime'],
            'color' => get_option('flexiblekit_color'),
            'reminder_before' => true,
        ];

        $event_id = $this->ci->flexibleevent_model->add($event_data);

        if(!$event_id){
            throw new Exception(_l('flexible_event_creation_failed'));
        }

        return $this->ci->flexibleschedule_model->add($schedule_data);
    }

    /**
     * Get the next active flexible schedule for a staff
     *
     * @return null|mixed
     */
    public function get_next_schedule()
    {
        $flexibleschedules = $this->ci->flexibleschedule_model->all([
            'flexibleschedule_userid' => get_staff_user_id(),
            'flexibleschedule_status' => FLEXIBLEKIT_SCHEDULED_STATUS,
            'flexibleschedule_start_datetime >=' => date('Y-m-d H:i:s')
        ], [
            [
                'field' => 'flexibleschedule_start_datetime',
                'order' => 'asc'
            ]
        ]);

        if(empty($flexibleschedules)){
            return null;
        }

        return $flexibleschedules[0];
    }
    
    /**
     * Get the previous flexible schedule for a staff
     *
     * @return null|array
     */
    public function get_previous_schedules()
    {
        return $this->ci->flexibleschedule_model->previous(get_staff_user_id());
    }
    
    /**
     * Get the team members flexible schedule
     *
     * @return null|array
     */
    public function get_team_schedules()
    {
        return $this->ci->flexibleschedule_model->team(get_staff_user_id());
    }
}
