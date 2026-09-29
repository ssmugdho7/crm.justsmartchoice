<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employees_tracker_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_google_api_key()
    {
        $keys = [
            trim((string) get_option('employees_tracker_google_api_key')),
            trim((string) get_option('fe_googlemap_api_key')),
            trim((string) get_option('google_api_key')),
            trim((string) get_option('google_api')),
        ];

        foreach ($keys as $key) {
            if ($this->is_valid_google_maps_key($key)) {
                return $key;
            }
        }

        return '';
    }

    public function is_valid_google_maps_key($key)
    {
        $key = trim((string)$key);
        if ($key === '') {
            return false;
        }

        // OAuth client IDs end with apps.googleusercontent.com and cannot load Google Maps JS.
        if (stripos($key, 'apps.googleusercontent.com') !== false) {
            return false;
        }

        // Most browser Maps keys start with AIza. Keep this flexible for older/custom keys.
        return strlen($key) >= 20;
    }

    public function get_all_staff()
    {
        $this->db->order_by('firstname', 'ASC');
        return $this->db->get(db_prefix().'staff')->result_array();
    }

    public function get_trackable_staff()
    {
        return $this->get_all_staff();
    }

    public function get_all_projects()
    {
        $this->db->order_by('name', 'ASC');
        return $this->db->get(db_prefix().'projects')->result_array();
    }

    public function get_trackable_projects()
    {
        return $this->get_all_projects();
    }

    public function get_appointments()
    {
        $possible = [db_prefix().'appointments', db_prefix().'appointly_appointments'];
        foreach ($possible as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->limit(100);
                return $this->db->get($table)->result_array();
            }
        }
        return [];
    }

    public function save_location($staff_id, $lat, $lng, $accuracy = null, $battery = null, $source = 'browser')
    {
        $table = db_prefix().'employees_tracker_locations';
        $payload = [
            'staff_id' => (int)$staff_id,
            'lat'      => (float)$lat,
            'lng'      => (float)$lng,
            'accuracy' => $accuracy !== null && $accuracy !== '' ? (float)$accuracy : null,
        ];

        if ($this->db->field_exists('battery', $table)) {
            $payload['battery'] = $battery !== null && $battery !== '' ? (int)$battery : null;
        }

        if ($this->db->field_exists('source', $table)) {
            $payload['source'] = trim((string)$source) ?: 'browser';
        }

        $this->db->insert($table, $payload);
        return $this->db->insert_id();
    }

    public function save_assignment($data)
    {
        $project_id = (int) ($data['project_id'] ?? 0);
        $staff_id   = (int) ($data['staff_id'] ?? 0);

        if ($project_id <= 0 || $staff_id <= 0) {
            return false;
        }

        $payload = [
            'project_id'       => $project_id,
            'staff_id'         => $staff_id,
            'appointment_id'   => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'service_type'     => trim((string)($data['service_type'] ?? get_option('employees_tracker_default_service_type'))),
            'job_notes'        => trim((string)($data['job_notes'] ?? '')),
            'scheduled_start'  => !empty($data['scheduled_start']) ? to_sql_date($data['scheduled_start'], true) : null,
            'enabled'          => isset($data['enabled']) ? (int)$data['enabled'] : 1,
            'notify_client'    => isset($data['notify_client']) ? (int)$data['notify_client'] : 1,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $exists = $this->db->get_where(db_prefix().'employees_tracker_assignments', [
            'project_id' => $project_id,
            'staff_id'   => $staff_id,
        ])->row();

        if ($exists) {
            $this->db->where('id', $exists->id)->update(db_prefix().'employees_tracker_assignments', $payload);
            return (int)$exists->id;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'employees_tracker_assignments', $payload);
        return $this->db->insert_id();
    }

    public function get_assignments()
    {
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get(db_prefix().'employees_tracker_assignments')->result_array();
    }


    public function get_all_latest_locations()
    {
        $latestSql = 'SELECT l.* FROM `'.db_prefix().'employees_tracker_locations` l INNER JOIN (SELECT staff_id, MAX(id) AS max_id FROM `'.db_prefix().'employees_tracker_locations` GROUP BY staff_id) x ON x.max_id = l.id';
        $rows = $this->db->query($latestSql)->result_array();
        $out = [];

        foreach ($rows as $row) {
            $staff = $this->db->get_where(db_prefix().'staff', ['staffid' => (int)$row['staff_id']])->row_array();
            if (!$staff) {
                continue;
            }

            $out[] = [
                'staff_id' => (int)$row['staff_id'],
                'staff_name' => trim(($staff['firstname'] ?? '') . ' ' . ($staff['lastname'] ?? '')),
                'staff_image' => $this->get_staff_image_url((int)$row['staff_id']),
                'lat' => $row['lat'],
                'lng' => $row['lng'],
                'accuracy' => $row['accuracy'] ?? null,
                'battery' => $row['battery'] ?? null,
                'last_time' => $row['created_at'] ?? null,
                'source' => $row['source'] ?? 'browser',
            ];
        }

        return $out;
    }

    public function get_staff_latest($staff_id)
    {
        $this->db->order_by('created_at','DESC');
        $row = $this->db->get_where(db_prefix().'employees_tracker_locations', ['staff_id'=>(int)$staff_id], 1)->row_array();
        return $row ?: null;
    }

    public function get_project_location($project_id)
    {
        $row = $this->db->get_where(db_prefix().'employees_tracker_projects', ['project_id'=>(int)$project_id])->row_array();
        return $row ?: null;
    }

    public function set_project_location($project_id, $lat, $lng, $address = null)
    {
        $project_id = (int)$project_id;
        $payload = [
            'lat'        => $lat !== null ? (float)$lat : null,
            'lng'        => $lng !== null ? (float)$lng : null,
            'address'    => $address,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $exists = $this->db->get_where(db_prefix().'employees_tracker_projects', ['project_id'=>$project_id])->row();
        if ($exists) {
            $this->db->where('id', $exists->id)->update(db_prefix().'employees_tracker_projects', $payload);
        } else {
            $payload['project_id'] = $project_id;
            $this->db->insert(db_prefix().'employees_tracker_projects', $payload);
        }
    }

    public function distance_km($lat1,$lon1,$lat2,$lon2)
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $earthRadius = 6371;
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLon = deg2rad((float)$lon2 - (float)$lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 3);
    }

    public function eta_minutes_from_distance($distance_km)
    {
        if ($distance_km === null) {
            return null;
        }
        // Practical default for mixed residential driving.
        return max(1, (int)ceil(((float)$distance_km / 45) * 60));
    }

    public function resolve_client_id($contact_or_client_id)
    {
        $id = (int)$contact_or_client_id;
        if ($id <= 0) {
            return 0;
        }

        $contact = $this->db->get_where(db_prefix().'contacts', ['id'=>$id])->row();
        if ($contact && isset($contact->userid)) {
            return (int)$contact->userid;
        }

        // When Perfex returns the company/client id instead of the contact id.
        $client = $this->db->get_where(db_prefix().'clients', ['userid'=>$id])->row();
        if ($client) {
            return $id;
        }

        return $id;
    }

    public function get_client_projects($contact_or_client_id)
    {
        $client_id = $this->resolve_client_id($contact_or_client_id);
        if ($client_id <= 0) {
            return [];
        }

        $this->db->where('clientid', $client_id);
        $this->db->order_by('name', 'ASC');
        return $this->db->get(db_prefix().'projects')->result_array();
    }

    public function client_can_view_project($contact_or_client_id, $project_id)
    {
        $client_id = $this->resolve_client_id($contact_or_client_id);
        if ($client_id <= 0) {
            return false;
        }

        $project = $this->db->get_where(db_prefix().'projects', ['id'=>(int)$project_id])->row();
        return $project && (int)$project->clientid === $client_id;
    }

    public function get_project($project_id)
    {
        return $this->db->get_where(db_prefix().'projects', ['id'=>(int)$project_id])->row_array();
    }

    public function get_staff_image_url($staff_id)
    {
        if (function_exists('staff_profile_image_url')) {
            return staff_profile_image_url($staff_id);
        }

        $base = FCPATH . 'uploads/staff_profile_images/' . (int)$staff_id . '/';
        if (is_dir($base)) {
            $files = glob($base . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            if (!empty($files)) {
                return base_url('uploads/staff_profile_images/' . (int)$staff_id . '/' . basename($files[0]));
            }
        }

        return base_url('assets/images/user-placeholder.jpg');
    }

    public function get_project_installers($project_id)
    {
        $this->db->where(['project_id'=>(int)$project_id, 'enabled'=>1]);
        $assign = $this->db->get(db_prefix().'employees_tracker_assignments')->result_array();

        $out = [];
        foreach ($assign as $a) {
            $staff = $this->db->get_where(db_prefix().'staff', ['staffid'=>(int)$a['staff_id']])->row_array();
            if ($staff) {
                $latest = $this->get_staff_latest($a['staff_id']);
                $out[] = [
                    'assignment' => $a,
                    'staff'      => $staff,
                    'latest'     => $latest,
                ];
            }
        }

        return $out;
    }

    public function get_project_status_payload($project_id)
    {
        $project = $this->get_project($project_id);
        $projLocation = $this->get_project_location($project_id);
        $installers = $this->get_project_installers($project_id);
        $payload = [];

        foreach ($installers as $i) {
            $distance = null;
            $eta = null;
            if ($projLocation && $i['latest']) {
                $distance = $this->distance_km($projLocation['lat'], $projLocation['lng'], $i['latest']['lat'], $i['latest']['lng']);
                $eta = $this->eta_minutes_from_distance($distance);
            }

            $payload[] = [
                'assignment_id' => (int)$i['assignment']['id'],
                'staff_id' => (int)$i['staff']['staffid'],
                'staff_name' => trim($i['staff']['firstname'].' '.$i['staff']['lastname']),
                'staff_image' => $this->get_staff_image_url($i['staff']['staffid']),
                'last_lat' => $i['latest']['lat'] ?? null,
                'last_lng' => $i['latest']['lng'] ?? null,
                'last_time' => $i['latest']['created_at'] ?? null,
                'accuracy' => $i['latest']['accuracy'] ?? null,
                'distance_km' => $distance,
                'distance_text' => $distance !== null ? round($distance * 0.621371, 1) . ' miles' : null,
                'eta_minutes' => $eta,
                'eta_text' => $eta !== null ? $eta . ' min' : null,
                'service_type' => $i['assignment']['service_type'] ?? '',
                'job_notes' => $i['assignment']['job_notes'] ?? '',
                'scheduled_start' => $i['assignment']['scheduled_start'] ?? null,
            ];
        }

        return [
            'project' => $project,
            'project_location' => $projLocation,
            'installers' => $payload,
            'api_key_available' => $this->get_google_api_key() !== '',
        ];
    }

    public function get_database_health()
    {
        $tables = [
            'locations' => db_prefix().'employees_tracker_locations',
            'assignments' => db_prefix().'employees_tracker_assignments',
            'projects' => db_prefix().'employees_tracker_projects',
            'notifications' => db_prefix().'employees_tracker_notifications',
        ];

        $out = [];
        foreach ($tables as $key => $table) {
            $exists = $this->db->table_exists($table);
            $out[$key] = [
                'table' => $table,
                'exists' => $exists,
                'columns' => [],
                'rows' => 0,
            ];
            if ($exists) {
                $out[$key]['columns'] = $this->db->list_fields($table);
                $out[$key]['rows'] = (int)$this->db->count_all($table);
            }
        }

        return $out;
    }

    public function process_eta_notifications()
    {
        if ((int)get_option('employees_tracker_enable_email_notifications') !== 1) {
            return;
        }

        $threshold = (int)get_option('employees_tracker_eta_notify_minutes');
        $threshold = $threshold > 0 ? $threshold : 30;

        $this->db->where('enabled', 1);
        $this->db->where('notify_client', 1);
        $assignments = $this->db->get(db_prefix().'employees_tracker_assignments')->result_array();

        foreach ($assignments as $a) {
            $status = $this->get_project_status_payload($a['project_id']);
            foreach ($status['installers'] as $installer) {
                if ((int)$installer['staff_id'] !== (int)$a['staff_id'] || $installer['eta_minutes'] === null) {
                    continue;
                }

                if ((int)$installer['eta_minutes'] > $threshold) {
                    continue;
                }

                if (!empty($a['last_notified_at']) && strtotime($a['last_notified_at']) > strtotime('-45 minutes')) {
                    continue;
                }

                $this->send_eta_email($a, $status['project'], $installer);
            }
        }
    }

    public function send_eta_email($assignment, $project, $installer)
    {
        if (!$project || empty($project['clientid'])) {
            return false;
        }

        $this->db->where('userid', (int)$project['clientid']);
        $this->db->where('active', 1);
        $contacts = $this->db->get(db_prefix().'contacts')->result_array();

        if (empty($contacts)) {
            return false;
        }

        $subject = 'Your installer is on the way';
        $message = 'Your installer ' . $installer['staff_name'] . ' is expected to arrive in approximately ' . $installer['eta_text'] . ' for ' . ($installer['service_type'] ?: 'your scheduled service') . '.';

        foreach ($contacts as $contact) {
            if (empty($contact['email'])) {
                continue;
            }

            $this->load->library('email');
            $this->email->clear(true);
            $this->email->from(get_option('smtp_email'), get_option('companyname'));
            $this->email->to($contact['email']);
            $this->email->subject($subject);
            $this->email->message(nl2br(html_escape($message)));
            @ $this->email->send();

            $this->db->insert(db_prefix().'employees_tracker_notifications', [
                'assignment_id' => (int)$assignment['id'],
                'project_id' => (int)$assignment['project_id'],
                'staff_id' => (int)$assignment['staff_id'],
                'clientid' => (int)$project['clientid'],
                'contact_id' => (int)$contact['id'],
                'eta_minutes' => (int)$installer['eta_minutes'],
                'distance_text' => $installer['distance_text'],
                'message' => $message,
                'sent_by' => 'email',
            ]);
        }

        $this->db->where('id', (int)$assignment['id'])->update(db_prefix().'employees_tracker_assignments', [
            'last_eta_minutes' => (int)$installer['eta_minutes'],
            'last_distance_text' => $installer['distance_text'],
            'last_eta_text' => $installer['eta_text'],
            'last_notified_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
