<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_installer_tracking_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_dashboard_stats(): array
    {
        $today = date('Y-m-d');
        return [
            'active_trips' => $this->count_trips(['status' => ['accepted', 'on_the_way']]),
            'arrived_today' => $this->count_by_date('arrived_at', $today),
            'completed_today' => $this->count_by_date('completed_at', $today),
            'notifications_today' => $this->count_notifications($today),
        ];
    }

    public function count_trips(array $filters = []): int
    {
        $this->db->from(db_prefix() . 'smart_installer_trips');
        if (isset($filters['staff_id'])) {
            $this->db->where('staff_id', (int) $filters['staff_id']);
        }
        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->db->where_in('status', $filters['status']);
            } else {
                $this->db->where('status', (string) $filters['status']);
            }
        }
        return (int) $this->db->count_all_results();
    }

    public function count_by_date(string $field, string $date): int
    {
        $this->db->from(db_prefix() . 'smart_installer_trips');
        $this->db->where('DATE(' . $field . ') =', $date);
        return (int) $this->db->count_all_results();
    }

    public function count_notifications(string $date): int
    {
        $this->db->from(db_prefix() . 'smart_installer_notifications');
        $this->db->where('DATE(created_at) =', $date);
        return (int) $this->db->count_all_results();
    }

    public function get_trips(array $filters = [], int $limit = 100): array
    {
        $this->db->select('t.*, s.firstname, s.lastname, s.profile_image, c.company, p.name as project_name');
        $this->db->from(db_prefix() . 'smart_installer_trips t');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = t.staff_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = t.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = t.project_id', 'left');
        if (!empty($filters['staff_id'])) {
            $this->db->where('t.staff_id', (int) $filters['staff_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', (string) $filters['status']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('DATE(t.created_at)', (string) $filters['date']);
        }
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_trip(int $id): ?array
    {
        $this->db->select('t.*, s.firstname, s.lastname, s.email as staff_email, s.profile_image, c.company, p.name as project_name');
        $this->db->from(db_prefix() . 'smart_installer_trips t');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = t.staff_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = t.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = t.project_id', 'left');
        $this->db->where('t.id', $id);
        $row = $this->db->get()->row_array();
        return is_array($row) ? $row : null;
    }

    public function get_trip_by_token(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }
        $this->db->select('t.*, s.firstname, s.lastname, s.profile_image, c.company, p.name as project_name');
        $this->db->from(db_prefix() . 'smart_installer_trips t');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = t.staff_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = t.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = t.project_id', 'left');
        $this->db->where('t.tracking_token', $token);
        $this->db->where('t.tracking_enabled', 1);
        $row = $this->db->get()->row_array();
        return is_array($row) ? $row : null;
    }

    public function create_trip(array $data): int
    {
        $insert = [
            'appointment_id' => $this->nullable_int($data['appointment_id'] ?? null),
            'project_id' => $this->nullable_int($data['project_id'] ?? null),
            'client_id' => $this->nullable_int($data['client_id'] ?? null),
            'staff_id' => (int) ($data['staff_id'] ?? get_staff_user_id()),
            'department_id' => $this->nullable_int($data['department_id'] ?? null),
            'status' => 'scheduled',
            'client_name' => $this->safe_text($data['client_name'] ?? ''),
            'client_phone' => $this->safe_text($data['client_phone'] ?? ''),
            'client_email' => $this->safe_text($data['client_email'] ?? ''),
            'destination_address' => $this->safe_text($data['destination_address'] ?? ''),
            'destination_lat' => $this->nullable_decimal($data['destination_lat'] ?? null),
            'destination_lng' => $this->nullable_decimal($data['destination_lng'] ?? null),
            'eta_minutes' => (int) get_option('smart_installer_tracking_default_eta_minutes'),
            'eta_text' => 'About ' . (int) get_option('smart_installer_tracking_default_eta_minutes') . ' minutes',
            'tracking_token' => bin2hex(random_bytes(24)),
            'tracking_enabled' => 1,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'smart_installer_trips', $insert);
        $id = (int) $this->db->insert_id();
        $this->log_action(get_staff_user_id(), 'Trip Created', 'Trip #' . $id . ' was created.');
        return $id;
    }

    public function update_trip_status(int $id, string $status): bool
    {
        $allowed = ['scheduled', 'accepted', 'on_the_way', 'arrived', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $fieldMap = [
            'accepted' => 'accepted_at',
            'on_the_way' => 'started_at',
            'arrived' => 'arrived_at',
            'completed' => 'completed_at',
            'cancelled' => 'cancelled_at',
        ];
        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if (isset($fieldMap[$status])) {
            $update[$fieldMap[$status]] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', $id);
        $ok = $this->db->update(db_prefix() . 'smart_installer_trips', $update);
        if ($ok) {
            $this->log_action(get_staff_user_id(), 'Trip Status Updated', 'Trip #' . $id . ' changed to ' . smart_installer_tracking_clean_text($status) . '.');
        }
        return (bool) $ok;
    }

    public function save_location(int $trip_id, int $staff_id, array $data): bool
    {
        $lat = $this->nullable_decimal($data['latitude'] ?? null);
        $lng = $this->nullable_decimal($data['longitude'] ?? null);
        if ($lat === null || $lng === null) {
            return false;
        }
        $row = [
            'trip_id' => $trip_id,
            'staff_id' => $staff_id,
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $this->nullable_decimal($data['accuracy'] ?? null),
            'speed' => $this->nullable_decimal($data['speed'] ?? null),
            'heading' => $this->nullable_decimal($data['heading'] ?? null),
            'source' => 'browser',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'smart_installer_locations', $row);
        $this->db->where('id', $trip_id);
        $this->db->update(db_prefix() . 'smart_installer_trips', [
            'last_lat' => $lat,
            'last_lng' => $lng,
            'last_accuracy' => $row['accuracy'],
            'last_speed' => $row['speed'],
            'last_heading' => $row['heading'],
            'last_location_at' => date('Y-m-d H:i:s'),
            'status' => 'on_the_way',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function get_latest_locations(): array
    {
        return $this->get_trips(['status' => 'on_the_way'], 250);
    }

    public function send_due_eta_notifications(): void
    {
        if (get_option('smart_installer_tracking_notify_by_email') !== '1') {
            return;
        }
        $minutes = (int) get_option('smart_installer_tracking_default_eta_minutes');
        $this->db->where('status', 'on_the_way');
        $this->db->where('notification_sent_at IS NULL', null, false);
        $trips = $this->db->get(db_prefix() . 'smart_installer_trips')->result_array();
        foreach ($trips as $trip) {
            $this->send_client_notification($trip, $minutes);
        }
    }

    public function send_client_notification(array $trip, int $minutes = 30): bool
    {
        $email = trim((string) ($trip['client_email'] ?? ''));
        $phone = trim((string) ($trip['client_phone'] ?? ''));
        $url = smart_installer_tracking_public_url((string) $trip['tracking_token']);
        $message = 'Your installer is on the way and is about ' . $minutes . ' minutes away. Track the visit here: ' . $url;
        $sent = false;
        if ($email !== '' && get_option('smart_installer_tracking_notify_by_email') === '1') {
            $this->load->library('email');
            $this->email->to($email);
            $this->email->subject('Your installer is on the way');
            $this->email->message($message);
            $sent = (bool) $this->email->send();
            $this->record_notification((int) $trip['id'], 'email', $email, 'Your installer is on the way', $message, $sent ? 'sent' : 'failed');
        }
        if ($phone !== '' && get_option('smart_installer_tracking_notify_by_sms') === '1') {
            // Perfex/Twilio implementations vary by installation. This logs the SMS intent safely.
            $this->record_notification((int) $trip['id'], 'sms', $phone, 'Installer ETA', $message, 'pending');
        }
        if ($sent) {
            $this->db->where('id', (int) $trip['id']);
            $this->db->update(db_prefix() . 'smart_installer_trips', ['notification_sent_at' => date('Y-m-d H:i:s')]);
        }
        return $sent;
    }

    public function record_notification(int $trip_id, string $channel, string $recipient, string $subject, string $message, string $status): void
    {
        $this->db->insert(db_prefix() . 'smart_installer_notifications', [
            'trip_id' => $trip_id,
            'staff_id' => get_staff_user_id(),
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'status' => $status,
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_reports(array $filters = []): array
    {
        $from = $filters['from'] ?? date('Y-m-01');
        $to = $filters['to'] ?? date('Y-m-d');
        $this->db->select('t.staff_id, CONCAT(s.firstname," ",s.lastname) as staff_name, COUNT(t.id) as total_trips, SUM(t.status="completed") as completed_trips, SUM(t.status="arrived") as arrived_trips, SUM(t.status="cancelled") as cancelled_trips, AVG(t.eta_minutes) as average_eta');
        $this->db->from(db_prefix() . 'smart_installer_trips t');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = t.staff_id', 'left');
        $this->db->where('DATE(t.created_at) >=', $from);
        $this->db->where('DATE(t.created_at) <=', $to);
        if (!empty($filters['staff_id'])) {
            $this->db->where('t.staff_id', (int) $filters['staff_id']);
        }
        $this->db->group_by('t.staff_id');
        return $this->db->get()->result_array();
    }

    public function health_check(): array
    {
        $tables = ['smart_installer_trips', 'smart_installer_locations', 'smart_installer_notifications', 'smart_installer_logs'];
        $result = [];
        foreach ($tables as $table) {
            $result[] = [
                'item' => smart_installer_tracking_clean_text($table),
                'status' => $this->db->table_exists(db_prefix() . $table) ? 'Pass' : 'Fail',
                'details' => $this->db->table_exists(db_prefix() . $table) ? 'Table exists.' : 'Table is missing.',
            ];
        }
        $key = trim((string) get_option('smart_installer_tracking_google_maps_api_key'));
        $result[] = ['item' => 'Google Maps API Key', 'status' => $key !== '' ? 'Pass' : 'Warning', 'details' => $key !== '' ? 'API key is saved.' : 'API key is not configured.'];
        $result[] = ['item' => 'Live Tracking', 'status' => get_option('smart_installer_tracking_enabled') === '1' ? 'Pass' : 'Warning', 'details' => get_option('smart_installer_tracking_enabled') === '1' ? 'Module is active.' : 'Module is disabled.'];
        $result[] = ['item' => 'Client Public Map', 'status' => get_option('smart_installer_tracking_allow_client_live_map') === '1' ? 'Pass' : 'Warning', 'details' => get_option('smart_installer_tracking_allow_client_live_map') === '1' ? 'Client map is enabled.' : 'Client map is disabled.'];
        return $result;
    }

    public function log_action(?int $staff_id, string $action, string $description): void
    {
        if (!$this->db->table_exists(db_prefix() . 'smart_installer_logs')) {
            return;
        }
        $this->db->insert(db_prefix() . 'smart_installer_logs', [
            'staff_id' => $staff_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_logs(int $limit = 100): array
    {
        $this->db->select('l.*, CONCAT(s.firstname," ",s.lastname) as staff_name');
        $this->db->from(db_prefix() . 'smart_installer_logs l');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = l.staff_id', 'left');
        $this->db->order_by('l.id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function repair_schema(): void
    {
        require(module_dir_path('smart_installer_tracking') . 'install.php');
        $this->log_action(get_staff_user_id(), 'Schema Repair', 'Safe database checker repair was executed.');
    }


    public function get_staff_options(): array
    {
        $this->db->select('staffid, firstname, lastname, email');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('active', 1);
        $this->db->order_by('firstname', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_client_options(): array
    {
        $this->db->select('userid, company, phonenumber, city, state, zip, address, billing_street, billing_city, billing_state, billing_zip');
        $this->db->from(db_prefix() . 'clients');
        $this->db->order_by('company', 'ASC');
        $this->db->limit(500);
        return $this->db->get()->result_array();
    }

    public function get_project_options(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'projects')) {
            return [];
        }
        $this->db->select('id, name, clientid');
        $this->db->from(db_prefix() . 'projects');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(500);
        return $this->db->get()->result_array();
    }

    public function get_appointment_options(): array
    {
        $table = db_prefix() . 'appointments';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        $fields = $this->db->list_fields($table);
        $idField = in_array('id', $fields, true) ? 'id' : (in_array('appointment_id', $fields, true) ? 'appointment_id' : 'id');
        $subjectField = in_array('subject', $fields, true) ? 'subject' : (in_array('title', $fields, true) ? 'title' : $idField);
        $startField = in_array('start', $fields, true) ? 'start' : (in_array('date', $fields, true) ? 'date' : (in_array('start_time', $fields, true) ? 'start_time' : $idField));
        $clientField = in_array('client_id', $fields, true) ? 'client_id' : (in_array('clientid', $fields, true) ? 'clientid' : null);
        $projectField = in_array('project_id', $fields, true) ? 'project_id' : (in_array('projectid', $fields, true) ? 'projectid' : null);
        $select = $idField . ' as id, ' . $subjectField . ' as subject, ' . $startField . ' as start_date';
        if ($clientField !== null) {
            $select .= ', ' . $clientField . ' as client_id';
        }
        if ($projectField !== null) {
            $select .= ', ' . $projectField . ' as project_id';
        }
        $this->db->select($select, false);
        $this->db->from($table);
        $this->db->order_by($idField, 'DESC');
        $this->db->limit(500);
        return $this->db->get()->result_array();
    }

    public function geocode_address(string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            return ['success' => false, 'message' => 'Address is required.'];
        }
        $key = smart_installer_tracking_google_key();
        if ($key === '') {
            return ['success' => false, 'message' => 'Google Maps API key is not configured in CRM Settings.'];
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address) . '&key=' . rawurlencode($key);
        $response = function_exists('file_get_contents') ? @file_get_contents($url) : false;
        if ($response === false) {
            return ['success' => false, 'message' => 'Google Geocoding request failed.'];
        }
        $json = json_decode($response, true);
        if (!is_array($json) || ($json['status'] ?? '') !== 'OK' || empty($json['results'][0]['geometry']['location'])) {
            return ['success' => false, 'message' => 'Address could not be converted to latitude and longitude.'];
        }
        $loc = $json['results'][0]['geometry']['location'];
        return [
            'success' => true,
            'lat' => (float) $loc['lat'],
            'lng' => (float) $loc['lng'],
            'formatted_address' => (string) ($json['results'][0]['formatted_address'] ?? $address),
        ];
    }

    private function nullable_int($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function nullable_decimal($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function safe_text($value): string
    {
        return trim((string) ($value ?? ''));
    }
}
