<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Popup_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function get_default_settings()
    {
        return [
            'trigger_all_pages' => true,
            'triggers' => [],
            'display_trigger' => 'delay',
            'display_trigger_value' => 2,
            'display_frequency' => 'all_time',
            'display_mobile' => true,
            'display_desktop' => true,
            'display_duration' => -1,
            'display_position' => 'middle_center',
            'on_animation' => 'fadeIn',
            'off_animation' => 'fadeOut',
        ];
    }

    public function get_with_code($code = '')
    {
        $this->db->where('code', $code);
        return $this->db->get(db_prefix() . 'popups_popups')->row();
    }
    public function get_with_key($key = '')
    {
        $this->db->where('popup_key', $key);
        $this->db->where('is_enabled', true);
        return $this->db->get(db_prefix() . 'popups_popups')->row();
    }
    public function get_template_with_code($code = '')
    {
        $this->db->where('code', $code);
        return $this->db->get(db_prefix() . 'popups_templates')->row();
    }
    public function subscriber_submit_notification($popup){
        if ($popup->notify_lead_imported != 0) {
            if ($popup->notify_type == 'assigned') {
                $to_responsible = true;
            } else {
                $ids            = @unserialize($popup->notify_ids);
                $to_responsible = false;
                if ($popup->notify_type == 'specific_staff') {
                    $field = 'staffid';
                } elseif ($popup->notify_type == 'roles') {
                    $field = 'role';
                }
            }

            if ($to_responsible == false && is_array($ids) && count($ids) > 0) {
                $this->db->where('active', 1);
                $this->db->where_in($field, $ids);
                $staff = $this->db->get(db_prefix() . 'staff')->result_array();
            } else {
                $staff = [
                    [
                        'staffid' => $popup->responsible,
                    ],
                ];
            }
            $notifiedUsers = [];
            foreach ($staff as $member) {
                if ($member['staffid'] != 0) {
                    $notified = add_notification([
                        'description'     => 'new_subscriber_imported_from_popup',
                        'touserid'        => $member['staffid'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'additional_data' => serialize([
                            $popup->name,
                        ]),
                        'link' => 'perfex_popup/popups/subscribers',
                    ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }
    }

    public function get_all_popups()
    {
        $this->db->order_by('created_at', 'desc');
        $data = $this->db->get(db_prefix() . 'popups_popups')->result_array();
        return array_values($data);
    }

    public function get_all_templates()
    {
        $this->db->order_by('created_at', 'desc');
        $data = $this->db->get(db_prefix() . 'popups_templates')->result();
        return array_values($data);
    }
    public function find_template($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'popups_templates')->row();
        }
        return $this->db->get(db_prefix() . 'popups_templates')->result_array();
    }
    

    public function get_template($id)
    {
        $this->db->where('id', $id);
        $data = $this->db->get(db_prefix() . 'popups_templates')->row();
        return $data;       
    }

    public function add_template($data)
    {
        $data['active']    = isset($data['active']) ? 1 : 0;
        $thumb_uploaded  = handle_thumb_template_upload();

        if ($thumb_uploaded) {
            $data['thumbnail'] = $thumb_uploaded;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix().'popups_templates', $data);

        $id = $this->db->insert_id();
        if (!$id) {
            return false;
        }
        log_activity('New Template Popup Added [ID: ' . $id . ', Name: ' . $data['name'] . ']');
        return $id;
    }

    public function update_template($data, $item)
    {

        $data['active']    = isset($data['active']) ? 1 : 0;
        $thumb_uploaded  = handle_thumb_template_upload($item->thumbnail);

        if ($thumb_uploaded) {
            $data['thumbnail'] = $thumb_uploaded;
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $item->id);

        $this->db->update(db_prefix() . 'popups_templates', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete_template($item)
    {
        $this->db->where('id', $item->id);
        $this->db->delete(db_prefix() . 'popups_templates');

        if ($this->db->affected_rows() > 0) {
            // delete thumb
            $file_path = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/popup_thumb_templates/".$item->thumbnail;;
            handle_delete_file_perfex_popup($file_path);

            log_activity('Template Popup Deleted [ID:' . $item->id . ']');

            return true;
        }
        return false;
    }
    

    public function get_popups_setting($key)
    {
        $this->db->where('key', $key);
        $data = $this->db->get(db_prefix() . 'popups_settings')->row();
        return $data;
    }

     public function update_settings($data,$id)
    {
        if (isset($data['blockscss'])) {
            # code...
            $this->db->where('id', $id);
            $data['value'] = $data['blockscss'];
            unset($data['blockscss']);
            $this->db->update(db_prefix() . 'popups_popups_settings', $data);

        }
        return true;
    }


    public function get_settings($arr)
    {
        $this->db->where_in('key',$arr);
        $data = $this->db->get(db_prefix() . 'popups_popups_settings')->result_array();
        return $data;
    }

    

    public function delete_popup($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'popups_popups');
        if ($this->db->affected_rows() > 0) {
            log_activity('Popup Deleted [ID:' . $id . ']');

            return true;
        }
        return false;
    }
    public function get_subscriber($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'popups_subscribers')->row();
    }

    public function delete_subscriber($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'popups_subscribers');
        if ($this->db->affected_rows() > 0) {
            log_activity('Popup Subscriber Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    

    public function update_popup($data, $item)
    {
        $data                  = $this->_do_notify_responsibles($data);
        
        $data['trigger_all_pages'] = isset($data['trigger_all_pages']) ? true : false;
        $data['triggers'] = [];
        if (isset($data['trigger_type']) && isset($data['trigger_value']) && count($data['trigger_type']) == count($data['trigger_value'])) {
            foreach ($data['trigger_type'] as $key => $value) {
                $data['triggers'][$value] = $data['trigger_value'][$key];
            }
        }
        $data['display_trigger_value'] = $data['display_trigger_value'] ?? '0';
        $data['display_trigger_value'] = intval($data['display_trigger_value']);
        $data['display_mobile'] = isset($data['display_mobile']) ? true : false;
        $data['display_desktop'] = isset($data['display_desktop']) ? true : false;
        $data['display_duration'] = intval($data['display_duration']);


        $data['settings'] = json_encode([
            'trigger_all_pages' => $data['trigger_all_pages'],
            'triggers' => $data['triggers'],
            'display_trigger' => $data['display_trigger'],
            'display_trigger_value' => $data['display_trigger_value'],
            'display_frequency' => $data['display_frequency'],
            'display_mobile' => $data['display_mobile'],
            'display_desktop' => $data['display_desktop'],
            'display_duration' => $data['display_duration'],
            'display_position' => $data['display_position'],
            'on_animation' => $data['on_animation'],
            'off_animation' => $data['off_animation'],
        ]);
        
        $data['is_enabled']    = isset($data['is_enabled']) ? 1 : 0;
        
        $remove = ['trigger_all_pages', 'triggers', 'display_trigger', 'display_trigger_value', 'display_frequency', 
        'display_mobile', 'display_desktop', 'display_duration', 'display_position', 'on_animation', 'off_animation', 
        'trigger_type', 'trigger_value'];

        $data = array_diff_key($data, array_flip($remove));
        //dd($data);

        $this->db->where('code', $item->code);
        $this->db->update(db_prefix() . 'popups_popups', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function form_perfex_popup_submit_notification($lead_id, $assigned, $integration = false)
    {
        if ((!empty($assigned) && $assigned != 0)) {
            if ($integration == false) {
                if ($assigned == get_staff_user_id()) {
                    return false;
                }
            }

            $name = $this->db->select('name')->from(db_prefix() . 'leads')->where('id', $lead_id)->get()->row()->name;

            $notification_data = [
                'description'     => ($integration == false) ? 'not_assigned_lead_to_you' : 'not_lead_assigned_from_form',
                'touserid'        => $assigned,
                'link'            => '#leadid=' . $lead_id,
                'additional_data' => ($integration == false ? serialize([
                    $name,
                ]) : serialize([])),
            ];

            if ($integration != false) {
                $notification_data['fromcompany'] = 1;
            }

            if (add_notification($notification_data)) {
                pusher_trigger_notification([$assigned]);
            }

            $this->db->select('email');
            $this->db->where('staffid', $assigned);
            $email = $this->db->get(db_prefix() . 'staff')->row()->email;

            send_mail_template('lead_assigned', $lead_id, $email);

            $this->db->where('id', $lead_id);
            $this->db->update(db_prefix() . 'leads', [
                'dateassigned' => date('Y-m-d'),
            ]);

            $not_additional_data = [
                get_staff_full_name(),
                '<a href="' . admin_url('profile/' . $assigned) . '" target="_blank">' . get_staff_full_name($assigned) . '</a>',
            ];

            if ($integration == true) {
                unset($not_additional_data[0]);
                array_values(($not_additional_data));
            }

            $not_additional_data = serialize($not_additional_data);

            $not_desc = ($integration == false ? 'not_lead_activity_assigned_to' : 'not_lead_activity_assigned_from_form');
            $this->log_lead_activity($lead_id, $not_desc, $integration, $not_additional_data);
        }
    }

    
    private function _do_notify_responsibles($data)
    {
        if (isset($data['notify_lead_imported'])) {
            $data['notify_lead_imported'] = 1;
        } else {
            $data['notify_lead_imported'] = 0;
        }

        if ($data['responsible'] == '') {
            $data['responsible'] = 0;
        }
        if ($data['notify_lead_imported'] != 0) {
            if ($data['notify_type'] == 'specific_staff') {
                if (isset($data['notify_ids_staff'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_staff']);
                    unset($data['notify_ids_staff']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_staff']);
                }
                if (isset($data['notify_ids_roles'])) {
                    unset($data['notify_ids_roles']);
                }
            } else {
                if (isset($data['notify_ids_roles'])) {
                    $data['notify_ids'] = serialize($data['notify_ids_roles']);
                    unset($data['notify_ids_roles']);
                } else {
                    $data['notify_ids'] = serialize([]);
                    unset($data['notify_ids_roles']);
                }
                if (isset($data['notify_ids_staff'])) {
                    unset($data['notify_ids_staff']);
                }
            }
        } else {
            $data['notify_ids']  = serialize([]);
            $data['notify_type'] = null;
            if (isset($data['notify_ids_staff'])) {
                unset($data['notify_ids_staff']);
            }
            if (isset($data['notify_ids_roles'])) {
                unset($data['notify_ids_roles']);
            }
        }

        return $data;
    }

    public function add_convert_to_lead($data)
    {
        if (isset($data['custom_contact_date']) || isset($data['custom_contact_date'])) {
            if (isset($data['contacted_today'])) {
                $data['lastcontact'] = date('Y-m-d H:i:s');
                unset($data['contacted_today']);
            } else {
                $data['lastcontact'] = to_sql_date($data['custom_contact_date'], true);
            }
        }

        if (isset($data['is_public']) && ($data['is_public'] == 1 || $data['is_public'] === 'on')) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }

        if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {
            $data['country'] = 0;
        }

        if (isset($data['custom_contact_date'])) {
            unset($data['custom_contact_date']);
        }

        $data['description'] = nl2br($data['description']);
        $data['dateadded']   = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();

        $data = hooks()->apply_filters('before_lead_added', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $data['email'] = trim($data['email']);
        $this->db->insert(db_prefix() . 'leads', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
        

        return false;
    }

}


