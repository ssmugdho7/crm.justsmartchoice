<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_ui extends AdminController
{
    public function save_world_clocks()
    {
        if (!is_admin() && staff_cant('edit', 'settings')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['success'=>false]));
        }
        $raw = $this->input->post('clocks', false);
        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) $decoded = [];
        $clean = [];
        foreach (array_slice($decoded, 0, 20) as $row) {
            $label = trim(strip_tags((string)($row['label'] ?? '')));
            $zone = trim((string)($row['zone'] ?? ''));
            if ($label === '' || !in_array($zone, timezone_identifiers_list(), true)) continue;
            $clean[] = ['label'=>mb_substr($label,0,80), 'zone'=>$zone];
        }
        if (!$clean) {
            $clean = [['label'=>'Florida (Eastern)','zone'=>'America/New_York']];
        }
        update_option('sc_world_clocks', json_encode($clean));
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'clocks'=>$clean]));
    }
    public function log_idle()
    {
        if (!is_staff_logged_in() || !$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['success'=>false]));
        }
        $minutes = max(1, min(480, (int)$this->input->post('minutes')));
        $staffId = (int) get_staff_user_id();
        if ($this->db->table_exists(db_prefix().'sc_staff_idle_logs')) {
            $this->db->insert(db_prefix().'sc_staff_idle_logs', [
                'staff_id' => $staffId,
                'idle_minutes' => $minutes,
                'recorded_at' => date('Y-m-d H:i:s'),
                'ip_address' => $this->input->ip_address(),
                'user_agent' => mb_substr((string)$this->input->user_agent(), 0, 255),
            ]);
        }
        log_activity('Staff idle threshold reached [Staff ID: '.$staffId.', Minutes: '.$minutes.']');
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true]));
    }

    public function timezones()
    {
        if (!is_staff_logged_in()) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode([]));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode(timezone_identifiers_list()));
    }

}
