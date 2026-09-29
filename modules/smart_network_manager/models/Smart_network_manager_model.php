<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_network_manager_model extends App_Model
{
    public function devices()
    {
        return $this->db->order_by('last_seen', 'DESC')->get(db_prefix() . 'snm_devices')->result_array();
    }

    public function logs($limit = 100)
    {
        return $this->db->order_by('id', 'DESC')->limit((int)$limit)->get(db_prefix() . 'snm_logs')->result_array();
    }

    public function schedules()
    {
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'snm_schedules')->result_array();
    }

    public function log($level, $type, $message, $data = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'snm_logs')) { return false; }
        return $this->db->insert(db_prefix() . 'snm_logs', [
            'level' => (string)$level,
            'event_type' => (string)$type,
            'message' => (string)$message,
            'data' => json_encode($data),
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
        ]);
    }

    public function upsert_device($device)
    {
        $mac = isset($device['mac_address']) ? trim((string)$device['mac_address']) : '';
        if ($mac === '') { return false; }
        $row = $this->db->where('mac_address', $mac)->get(db_prefix() . 'snm_devices')->row_array();
        $data = [
            'owner_name' => $device['owner_name'] ?? '',
            'device_name' => $device['device_name'] ?? ($device['hostname'] ?? 'Unknown Device'),
            'device_type' => $device['device_type'] ?? 'unknown',
            'ip_address' => $device['ip_address'] ?? '',
            'mac_address' => $mac,
            'manufacturer' => $device['manufacturer'] ?? '',
            'group_name' => $device['group_name'] ?? '',
            'status' => $device['status'] ?? 'online',
            'last_seen' => date('Y-m-d H:i:s'),
        ];
        if ($row) {
            $this->db->where('id', $row['id'])->update(db_prefix() . 'snm_devices', $data);
            return $row['id'];
        }
        $this->db->insert(db_prefix() . 'snm_devices', $data);
        return $this->db->insert_id();
    }

    public function health_report()
    {
        $tables = ['snm_devices', 'snm_schedules', 'snm_logs'];
        $report = [];
        foreach ($tables as $table) {
            $report[] = ['name' => db_prefix() . $table, 'status' => $this->db->table_exists(db_prefix() . $table) ? 'OK' : 'Missing'];
        }
        $report[] = ['name' => 'Agent URL configured', 'status' => get_option('smart_network_manager_agent_url') ? 'OK' : 'Not configured'];
        $report[] = ['name' => 'Agent token configured', 'status' => strlen((string)get_option('smart_network_manager_agent_token')) >= 24 ? 'OK' : 'Weak/Missing'];
        $report[] = ['name' => 'Control mode', 'status' => get_option('smart_network_manager_enable_controls') === '1' ? 'Enabled' : 'Read-only'];
        $report[] = ['name' => 'Router type', 'status' => get_option('smart_network_manager_router_type') ?: 'manual'];
        return $report;
    }

    public function database_report()
    {
        $report = [];
        $required = [
            'snm_devices' => ['id','owner_name','device_name','ip_address','mac_address','status','is_blocked','last_seen'],
            'snm_schedules' => ['id','device_id','group_name','schedule_name','days','start_time','end_time','action','active'],
            'snm_logs' => ['id','level','event_type','message','data','staff_id','datecreated'],
        ];
        foreach ($required as $table => $fields) {
            $full = db_prefix() . $table;
            if (!$this->db->table_exists($full)) {
                $report[] = ['item' => $full, 'status' => 'Missing table'];
                continue;
            }
            foreach ($fields as $field) {
                $report[] = ['item' => $full . '.' . $field, 'status' => $this->db->field_exists($field, $full) ? 'OK' : 'Missing column'];
            }
        }
        return $report;
    }

    public function recent_error_report($limit = 50)
    {
        $files = glob(APPPATH . 'logs/log-*.php');
        rsort($files);
        $errors = [];
        foreach (array_slice($files, 0, 5) as $file) {
            $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) { continue; }
            foreach (array_reverse($lines) as $line) {
                if (stripos($line, 'ERROR') !== false || stripos($line, 'Severity:') !== false || stripos($line, 'Exception') !== false) {
                    $errors[] = ['file' => basename($file), 'line' => $line];
                    if (count($errors) >= $limit) { return $errors; }
                }
            }
        }
        return $errors;
    }
}
