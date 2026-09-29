<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexiblekit extends AdminController
{
    public function add()
    {
        if ($post = $this->input->post()) {
            $this->db->trans_begin();

            try {
                $success = false;
                $post['flexiblekit_interval_label'] = flexiblekit_get_interval_label($post['flexiblekit_interval_type'], $post['flexiblekit_interval']);
                $post['flexiblekit_userid'] = get_staff_user_id();
                $this->load->model('flexiblekit/flexiblekit_model');

                // We derive the end_datetime from the end time provided
                // and the date part for the start_datetime.
                $date_parts = explode('T', $post['flexiblekit_start_datetime']);
                $date_parts[1] = $post['flexiblekit_end_datetime'];
                $post['flexiblekit_end_datetime'] = implode(' ', $date_parts);

                if (array_key_exists('flexiblekit_id', $post)) {
                    $flexiblekit_id = $post['flexiblekit_id'];
                    unset($post['flexiblekit_id']);

                    $saved = $this->flexiblekit_model->update($flexiblekit_id, $post);
                } else {
                    // The flexiblekit id is return after adding the record
                    $saved = $this->flexiblekit_model->add($post);

                }

                if ($saved) {
                    if(array_key_exists('flexiblekit_active', $post)){
                        // Create a schedule if the flexiblekit was activated on creation or update
                        if($post['flexiblekit_active']){
                            // We add the next schedule for the active flexiblekits
                            // including the one just created/updated above
                            flexiblekit_add_next_schedules();
                        }
                    }
                    $success = true;
                    $message = _l('flexiblekit_saved_successfully');
                }

                $this->db->trans_commit();

                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);

                die;
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                throw $th;
            }
        }
    }

    public function schedules($schedule_id){
        $this->load->model('flexiblekit/flexibleschedule_model');
        if($post = $this->input->post()){
            $contact_type = $post['flexibleschedule_contact_type'];
            $from = isset($post['from']) ? $post['from'] : '';
            unset($post['flexibleschedule_contact_type']);
            if($from){
                unset($post['from']);
            }
            $saved = $this->flexibleschedule_model->update($schedule_id, $post);

            if($saved){
                set_alert('success', _l('flexiblekit_schedule_updated_successfully'));
                if($from == 'widget') {
                    return redirect(admin_url('/'));
                }
                $url = $contact_type == FLEXIBLEKIT_LEAD_CONTACT_TYPE
                    ? 'leads'
                    : 'clients';
                redirect(admin_url($url));
            }
        }

        $data = [
            'schedule' => $this->flexibleschedule_model->get([
                'id' => $schedule_id
            ])
        ];

        if(!$data['schedule']){
            show_404();
        }

        $data['title'] = _l('flexiblekit_schedule') . " - " . $data['schedule']['flexibleschedule_subject'];

        $this->load->view('schedule_form', $data);
    }

}