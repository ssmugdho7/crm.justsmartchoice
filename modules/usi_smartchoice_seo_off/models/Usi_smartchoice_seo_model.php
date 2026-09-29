<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php');
        usi_smartchoice_seo_ensure_schema();
    }

    public function dashboard_counts(): array
    {
        return [
            'commands' => $this->safe_count('usi_ai_commands'),
            'estimates' => $this->safe_count('usi_ai_estimates'),
            'photos' => $this->safe_count('usi_ai_photos'),
            'training' => $this->safe_count('usi_ai_training'),
            'price_index' => $this->safe_count('usi_ai_price_index'),
            'estimate_approvals' => $this->safe_count('usi_ai_estimate_approvals'),
            'customer_packages' => $this->safe_count('usi_ai_customer_packages'),
            'field_verifications' => $this->safe_count('usi_ai_field_verifications'),
            'videos' => $this->safe_count('usi_ai_videos'),
            'voices' => $this->safe_count('usi_ai_voices'),
            'avatars' => $this->safe_count('usi_ai_avatars'),
            'pages' => $this->safe_count('usi_seo_pages'),
            'keywords' => $this->safe_count('usi_seo_keywords'),
            'reports' => $this->safe_count('usi_seo_reports'),
            'memory_items' => $this->safe_count('usi_ai_memory_items'),
        ];
    }

    public function get_ai_commands(array $filters = []): array
    {
        $this->apply_filters($filters, ['command_text', 'command_source', 'intent', 'target_module', 'action_status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_commands')->result_array();
    }

    public function get_ai_command(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_commands')->row_array() ?: null;
    }

    public function save_ai_command(array $data, int $id = 0): int
    {
        $payload = [
            'command_text' => trim((string)($data['command_text'] ?? '')),
            'command_source' => trim((string)($data['command_source'] ?? 'typed')),
            'intent' => trim((string)($data['intent'] ?? 'crm_assistant')),
            'target_module' => trim((string)($data['target_module'] ?? '')),
            'target_record_type' => trim((string)($data['target_record_type'] ?? '')),
            'target_record_id' => (int)($data['target_record_id'] ?? 0),
            'action_status' => trim((string)($data['action_status'] ?? 'draft')),
            'ai_response' => (string)($data['ai_response'] ?? $this->build_command_preview((string)($data['command_text'] ?? ''))),
            'review_notes' => (string)($data['review_notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_commands', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_commands', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_ai_command(int $id): bool
    {
        $this->db->where('ai_command_id', $id)->delete(db_prefix() . 'usi_ai_actions');
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_commands');
    }

    public function mass_delete_ai_commands(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids) { return 0; }
        $this->db->where_in('ai_command_id', $ids)->delete(db_prefix() . 'usi_ai_actions');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_commands');
        return $this->db->affected_rows();
    }

    public function get_ai_estimates(array $filters = []): array
    {
        $this->apply_filters($filters, ['title', 'location', 'scope_summary', 'status']);
        if (!empty($filters['customer_id'])) { $this->db->where('customer_id', (int)$filters['customer_id']); }
        if (!empty($filters['lead_id'])) { $this->db->where('lead_id', (int)$filters['lead_id']); }
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_estimates')->result_array();
    }

    public function get_ai_estimate(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_estimates')->row_array() ?: null;
    }

    public function get_ai_estimate_photos(int $estimateId): array
    {
        return $this->db->where('ai_estimate_id', $estimateId)->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_photos')->result_array();
    }


    public function get_recent_crm_leads(int $limit = 100): array
    {
        if (!usi_smartchoice_seo_table_exists('leads')) { return []; }
        $fields = ['id'];
        foreach (['name', 'phonenumber', 'email', 'address', 'dateadded'] as $field) {
            if ($this->db->field_exists($field, db_prefix() . 'leads')) { $fields[] = $field; }
        }
        return $this->db->select($fields)->order_by('id', 'DESC')->limit($limit)->get(db_prefix() . 'leads')->result_array();
    }

    public function get_recent_crm_customers(int $limit = 100): array
    {
        if (!usi_smartchoice_seo_table_exists('clients')) { return []; }
        $fields = ['userid'];
        foreach (['company', 'phonenumber', 'address', 'datecreated'] as $field) {
            if ($this->db->field_exists($field, db_prefix() . 'clients')) { $fields[] = $field; }
        }
        return $this->db->select($fields)->order_by('userid', 'DESC')->limit($limit)->get(db_prefix() . 'clients')->result_array();
    }

    public function resolve_camera_intake_crm_records(array $data): array
    {
        $customerId = (int)($data['customer_id'] ?? 0);
        $leadId = (int)($data['lead_id'] ?? 0);
        $name = trim((string)($data['customer_name'] ?? $data['title'] ?? ''));
        $phone = trim((string)($data['customer_phone'] ?? ''));
        $email = trim((string)($data['customer_email'] ?? ''));
        $address = trim((string)($data['location'] ?? ''));
        $messages = [];

        if ($customerId <= 0) {
            $existingCustomer = $this->find_customer_by_phone_or_email($phone, $email);
            if ($existingCustomer > 0) {
                $customerId = $existingCustomer;
                $messages[] = 'Existing CRM customer matched by phone/email and linked to this AI estimate.';
            }
        }
        if ($leadId <= 0) {
            $existingLead = $this->find_lead_by_phone_or_email($phone, $email);
            if ($existingLead > 0) {
                $leadId = $existingLead;
                $messages[] = 'Existing CRM lead matched by phone/email and linked to this AI estimate.';
            }
        }
        if ($leadId <= 0 && ($name !== '' || $phone !== '' || $email !== '')) {
            $leadId = $this->create_crm_lead_safe($name, $phone, $email, $address, (string)($data['scope_summary'] ?? ''));
            if ($leadId > 0) { $messages[] = 'New CRM lead created from Camera Intake.'; }
        }
        if ($customerId <= 0 && ($name !== '' || $phone !== '' || $email !== '')) {
            $customerId = $this->create_crm_customer_safe($name, $phone, $email, $address);
            if ($customerId > 0) { $messages[] = 'New CRM customer created from Camera Intake.'; }
        }
        $duplicateCount = 0;
        if ($customerId > 0 && usi_smartchoice_seo_table_exists('usi_ai_estimates')) {
            $duplicateCount = (int)$this->db->where('customer_id', $customerId)->count_all_results(db_prefix() . 'usi_ai_estimates');
        } elseif ($leadId > 0 && usi_smartchoice_seo_table_exists('usi_ai_estimates')) {
            $duplicateCount = (int)$this->db->where('lead_id', $leadId)->count_all_results(db_prefix() . 'usi_ai_estimates');
        }
        if ($duplicateCount > 0) {
            $action = trim((string)($data['duplicate_action'] ?? 'create_new'));
            $messages[] = 'Duplicate check found ' . $duplicateCount . ' existing AI estimate(s) for this CRM record. Selected action: ' . str_replace('_', ' ', $action) . '.';
        }
        return ['customer_id' => $customerId, 'lead_id' => $leadId, 'message' => implode("\n", $messages)];
    }

    private function find_lead_by_phone_or_email(string $phone, string $email): int
    {
        if (!usi_smartchoice_seo_table_exists('leads')) { return 0; }
        $this->db->select('id');
        $this->db->group_start();
        $has = false;
        if ($email !== '' && $this->db->field_exists('email', db_prefix() . 'leads')) { $this->db->where('email', $email); $has = true; }
        if ($phone !== '' && $this->db->field_exists('phonenumber', db_prefix() . 'leads')) { $has ? $this->db->or_where('phonenumber', $phone) : $this->db->where('phonenumber', $phone); $has = true; }
        $this->db->group_end();
        if (!$has) { return 0; }
        $row = $this->db->limit(1)->get(db_prefix() . 'leads')->row_array();
        return (int)($row['id'] ?? 0);
    }

    private function find_customer_by_phone_or_email(string $phone, string $email): int
    {
        if (!usi_smartchoice_seo_table_exists('clients')) { return 0; }
        if ($phone !== '' && $this->db->field_exists('phonenumber', db_prefix() . 'clients')) {
            $row = $this->db->select('userid')->where('phonenumber', $phone)->limit(1)->get(db_prefix() . 'clients')->row_array();
            if (!empty($row['userid'])) { return (int)$row['userid']; }
        }
        if ($email !== '' && usi_smartchoice_seo_table_exists('contacts') && $this->db->field_exists('email', db_prefix() . 'contacts')) {
            $row = $this->db->select('userid')->where('email', $email)->limit(1)->get(db_prefix() . 'contacts')->row_array();
            if (!empty($row['userid'])) { return (int)$row['userid']; }
        }
        return 0;
    }

    private function create_crm_lead_safe(string $name, string $phone, string $email, string $address, string $scope): int
    {
        if (!usi_smartchoice_seo_table_exists('leads')) { return 0; }
        $table = 'leads';
        $payload = [
            'name' => $name !== '' ? $name : 'AI Camera Intake Lead',
            'phonenumber' => $phone,
            'email' => $email,
            'address' => $address,
            'description' => $scope,
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => get_staff_user_id(),
            'assigned' => get_staff_user_id(),
        ];
        if ($this->db->field_exists('status', db_prefix() . $table)) { $payload['status'] = $this->first_id_from_table('leads_status') ?: 0; }
        if ($this->db->field_exists('source', db_prefix() . $table)) { $payload['source'] = $this->first_id_from_table('leads_sources') ?: 0; }
        $payload = $this->filter_existing_columns($table, $payload);
        $this->db->insert(db_prefix() . $table, $payload);
        return (int)$this->db->insert_id();
    }

    private function create_crm_customer_safe(string $name, string $phone, string $email, string $address): int
    {
        if (!usi_smartchoice_seo_table_exists('clients')) { return 0; }
        $clientPayload = [
            'company' => $name !== '' ? $name : 'AI Camera Intake Customer',
            'phonenumber' => $phone,
            'address' => $address,
            'billing_street' => $address,
            'shipping_street' => $address,
            'datecreated' => date('Y-m-d H:i:s'),
            'active' => 1,
            'addedfrom' => get_staff_user_id(),
        ];
        $clientPayload = $this->filter_existing_columns('clients', $clientPayload);
        $this->db->insert(db_prefix() . 'clients', $clientPayload);
        $customerId = (int)$this->db->insert_id();
        if ($customerId > 0 && $email !== '' && usi_smartchoice_seo_table_exists('contacts')) {
            $parts = preg_split('/\s+/', trim($name));
            $first = $parts[0] ?? 'Customer';
            $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
            $contactPayload = [
                'userid' => $customerId,
                'firstname' => $first,
                'lastname' => $last,
                'email' => $email,
                'phonenumber' => $phone,
                'datecreated' => date('Y-m-d H:i:s'),
                'active' => 1,
                'is_primary' => 1,
            ];
            $contactPayload = $this->filter_existing_columns('contacts', $contactPayload);
            $this->db->insert(db_prefix() . 'contacts', $contactPayload);
        }
        return $customerId;
    }

    private function first_id_from_table(string $table): int
    {
        if (!usi_smartchoice_seo_table_exists($table)) { return 0; }
        $field = $this->db->field_exists('id', db_prefix() . $table) ? 'id' : '';
        if ($field === '') { return 0; }
        $row = $this->db->select($field)->order_by($field, 'ASC')->limit(1)->get(db_prefix() . $table)->row_array();
        return (int)($row[$field] ?? 0);
    }

    public function save_ai_estimate(array $data, int $id = 0): int
    {
        $subtotal = (float)($data['subtotal'] ?? 0);
        $tax = (float)($data['tax_total'] ?? 0);
        $payload = [
            'title' => trim((string)($data['title'] ?? 'AI Jobsite Estimate')),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'location' => trim((string)($data['location'] ?? '')),
            'scope_summary' => (string)($data['scope_summary'] ?? ''),
            'measurement_notes' => (string)($data['measurement_notes'] ?? ''),
            'materials_summary' => (string)($data['materials_summary'] ?? ''),
            'labor_summary' => (string)($data['labor_summary'] ?? ''),
            'ai_observations' => (string)($data['ai_observations'] ?? ''),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'total' => (float)($data['total'] ?? ($subtotal + $tax)),
            'status' => trim((string)($data['status'] ?? get_option('usi_smartchoice_ai_default_estimate_status'))),
            'review_status' => trim((string)($data['review_status'] ?? 'draft')),
            'customer_scope' => (string)($data['customer_scope'] ?? ''),
            'exclusions' => (string)($data['exclusions'] ?? ''),
            'payment_schedule' => (string)($data['payment_schedule'] ?? ''),
            'estimator_review_notes' => (string)($data['estimator_review_notes'] ?? ''),
            'field_verified' => (int)($data['field_verified'] ?? 0),
            'converted_estimate_id' => (int)($data['converted_estimate_id'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_estimates', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_estimates', $payload);
        return (int)$this->db->insert_id();
    }

    public function save_ai_photo(int $estimateId, array $file, array $data): bool
    {
        $uploadDir = FCPATH . 'uploads/usi_smartchoice_ai/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
        $original = (string)($file['name'] ?? '');
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed, true)) { return false; }
        $name = 'ai_photo_' . $estimateId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $target = $uploadDir . $name;
        if (!move_uploaded_file((string)$file['tmp_name'], $target)) { return false; }
        $this->db->insert(db_prefix() . 'usi_ai_photos', [
            'ai_estimate_id' => $estimateId,
            'file_name' => $name,
            'original_name' => $original,
            'file_path' => 'uploads/usi_smartchoice_ai/' . $name,
            'mime_type' => (string)($file['type'] ?? ''),
            'file_size' => (int)($file['size'] ?? 0),
            'photo_type' => trim((string)($data['photo_type'] ?? 'jobsite')),
            'ai_caption' => (string)($data['ai_caption'] ?? ''),
            'measurement_note' => (string)($data['measurement_note'] ?? ''),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function save_ai_photos(int $estimateId, array $files, array $data): int
    {
        $saved = 0;
        if (empty($files['name']) || !is_array($files['name'])) { return 0; }
        foreach ($files['name'] as $index => $name) {
            if ($name === '') { continue; }
            $file = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? 0,
                'size' => $files['size'][$index] ?? 0,
            ];
            if ((int)$file['error'] === 0 && $this->save_ai_photo($estimateId, $file, $data)) { $saved++; }
        }
        return $saved;
    }

    public function delete_ai_estimate(int $id): bool
    {
        $this->db->where('ai_estimate_id', $id)->delete(db_prefix() . 'usi_ai_photos');
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_estimates');
    }

    public function mass_delete_ai_estimates(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids) { return 0; }
        $this->db->where_in('ai_estimate_id', $ids)->delete(db_prefix() . 'usi_ai_photos');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_estimates');
        return $this->db->affected_rows();
    }

    public function get_ai_training(array $filters = []): array
    {
        $this->apply_filters($filters, ['title', 'category', 'prompt_text', 'status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_training')->result_array();
    }

    public function get_ai_training_item(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_training')->row_array() ?: null;
    }

    public function save_ai_training(array $data, int $id = 0): int
    {
        $payload = [
            'title' => trim((string)($data['title'] ?? '')),
            'category' => trim((string)($data['category'] ?? 'crm_workflow')),
            'prompt_text' => (string)($data['prompt_text'] ?? ''),
            'expected_behavior' => (string)($data['expected_behavior'] ?? ''),
            'status' => trim((string)($data['status'] ?? 'active')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_training', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_training', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_ai_training(int $id): bool
    {
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_training');
    }

    public function mass_delete_ai_training(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids) { return 0; }
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_training');
        return $this->db->affected_rows();
    }


    public function get_ai_videos(array $filters = []): array
    {
        $this->seed_video_defaults();
        $this->apply_filters($filters, ['title', 'script_text', 'language', 'video_purpose', 'status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_videos')->result_array();
    }

    public function get_ai_video(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_videos')->row_array() ?: null;
    }

    public function save_ai_video(array $data, int $id = 0): int
    {
        $payload = [
            'title' => trim((string)($data['title'] ?? 'AI Video Project')),
            'script_text' => (string)($data['script_text'] ?? ''),
            'language' => trim((string)($data['language'] ?? 'en-US')),
            'voice_id' => (int)($data['voice_id'] ?? 0),
            'avatar_id' => (int)($data['avatar_id'] ?? 0),
            'video_purpose' => trim((string)($data['video_purpose'] ?? 'training')),
            'status' => trim((string)($data['status'] ?? 'draft')),
            'logo_enabled' => (int)($data['logo_enabled'] ?? 0),
            'logo_position' => trim((string)($data['logo_position'] ?? 'top_right')),
            'intro_thumbnail' => trim((string)($data['intro_thumbnail'] ?? '')),
            'outro_thumbnail' => trim((string)($data['outro_thumbnail'] ?? '')),
            'embed_code' => (string)($data['embed_code'] ?? ''),
            'ai_notes' => (string)($data['ai_notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_videos', $payload);
            $this->save_video_layers_from_post($id, $data);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_videos', $payload);
        $newId = (int)$this->db->insert_id();
        $this->save_video_layers_from_post($newId, $data);
        return $newId;
    }

    private function save_video_layers_from_post(int $videoId, array $data): void
    {
        $this->db->where('video_id', $videoId)->delete(db_prefix() . 'usi_ai_video_text_layers');
        for ($i = 1; $i <= 4; $i++) {
            $text = trim((string)($data['text_layer_' . $i] ?? ''));
            if ($text === '') { continue; }
            $this->db->insert(db_prefix() . 'usi_ai_video_text_layers', [
                'video_id' => $videoId,
                'layer_text' => $text,
                'start_second' => (float)($data['text_start_' . $i] ?? 0),
                'duration_second' => (float)($data['text_duration_' . $i] ?? 5),
                'animation_type' => trim((string)($data['text_animation_' . $i] ?? 'fade')),
                'position_name' => trim((string)($data['text_position_' . $i] ?? 'lower_third')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function get_video_text_layers(int $videoId): array
    {
        return $this->db->where('video_id', $videoId)->order_by('start_second', 'ASC')->get(db_prefix() . 'usi_ai_video_text_layers')->result_array();
    }

    public function delete_ai_video(int $id): bool
    {
        $this->db->where('video_id', $id)->delete(db_prefix() . 'usi_ai_video_text_layers');
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_videos');
    }

    public function mass_delete_ai_videos(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids) { return 0; }
        $this->db->where_in('video_id', $ids)->delete(db_prefix() . 'usi_ai_video_text_layers');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_videos');
        return $this->db->affected_rows();
    }

    public function run_video_preview(int $id): array
    {
        $video = $this->get_ai_video($id);
        if (!$video) { return ['success' => false, 'message' => 'Video project was not found.']; }
        $notes = 'Video Studio draft prepared. Next API connection step: send script to TTS, send selected avatar and audio to lip-sync provider, apply logo/text layers, store MP4, and generate embed code.';
        $embed = '<div class="smart-choice-video-placeholder">Smart Choice AI Video Draft #' . $id . '</div>';
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_videos', [
            'status' => 'ready_for_api',
            'embed_code' => $embed,
            'ai_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Video project prepared for AI video generation. API connection is ready to be configured in Settings.'];
    }

    public function get_ai_voices(array $filters = []): array
    {
        $this->seed_video_defaults();
        $this->apply_filters($filters, ['voice_name', 'language', 'gender', 'status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_voices')->result_array();
    }

    public function get_ai_voice(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_voices')->row_array() ?: null;
    }

    public function save_ai_voice(array $data, int $id = 0): int
    {
        $payload = [
            'voice_name' => trim((string)($data['voice_name'] ?? '')),
            'language' => trim((string)($data['language'] ?? 'en-US')),
            'gender' => trim((string)($data['gender'] ?? 'neutral')),
            'provider_voice_id' => trim((string)($data['provider_voice_id'] ?? '')),
            'sample_text' => (string)($data['sample_text'] ?? ''),
            'status' => trim((string)($data['status'] ?? 'active')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_voices', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_voices', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_ai_voice(int $id): bool { return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_voices'); }

    public function get_ai_avatars(array $filters = []): array
    {
        $this->seed_video_defaults();
        $this->apply_filters($filters, ['avatar_name', 'avatar_type', 'position_name', 'status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_ai_avatars')->result_array();
    }

    public function get_ai_avatar(int $id): ?array
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_avatars')->row_array() ?: null;
    }

    public function save_ai_avatar(array $data, int $id = 0): int
    {
        $payload = [
            'avatar_name' => trim((string)($data['avatar_name'] ?? '')),
            'avatar_type' => trim((string)($data['avatar_type'] ?? 'stock')),
            'position_name' => trim((string)($data['position_name'] ?? 'front_facing')),
            'provider_avatar_id' => trim((string)($data['provider_avatar_id'] ?? '')),
            'status' => trim((string)($data['status'] ?? 'active')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_avatars', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_avatars', $payload);
        return (int)$this->db->insert_id();
    }

    public function save_avatar_photo(int $avatarId, array $file): bool
    {
        $uploadDir = FCPATH . 'uploads/usi_smartchoice_ai/avatars/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
        $original = (string)($file['name'] ?? '');
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg','jpeg','png','webp'], true)) { return false; }
        $name = 'avatar_' . $avatarId . '_' . time() . '.' . $extension;
        if (!move_uploaded_file((string)$file['tmp_name'], $uploadDir . $name)) { return false; }
        $this->db->where('id', $avatarId)->update(db_prefix() . 'usi_ai_avatars', ['source_photo' => 'uploads/usi_smartchoice_ai/avatars/' . $name, 'avatar_type' => 'custom']);
        return true;
    }

    public function delete_ai_avatar(int $id): bool { return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_avatars'); }

    private function seed_video_defaults(): void
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_voices') && $this->safe_count('usi_ai_voices') === 0) {
            $voices = [
                ['Smart Choice Trainer','en-US','male'], ['Smart Choice Sales','en-US','female'], ['Smart Choice Spanish','es-US','female'], ['Florida Contractor','en-US','male'], ['Customer Care','en-US','female'], ['Project Manager','en-US','male'], ['Estimator Voice','en-US','male'], ['Marketing Voice','en-US','female'], ['Safety Trainer','en-US','neutral'], ['Bilingual Assistant','es-US','neutral']
            ];
            foreach ($voices as $voice) { $this->save_ai_voice(['voice_name'=>$voice[0], 'language'=>$voice[1], 'gender'=>$voice[2], 'sample_text'=>'Smart Choice Contractors USA training voice.', 'status'=>'active'], 0); }
        }
        if (usi_smartchoice_seo_table_exists('usi_ai_avatars') && $this->safe_count('usi_ai_avatars') === 0) {
            $positions = ['Front Facing','Left Angle','Right Angle','Desk Presenter','Field Presenter','Estimator','Trainer','Sales Rep','Project Manager','Customer Support'];
            foreach ($positions as $position) { $this->save_ai_avatar(['avatar_name'=>'Smart Choice ' . $position, 'avatar_type'=>'stock', 'position_name'=>strtolower(str_replace(' ', '_', $position)), 'status'=>'active'], 0); }
        }
    }

    public function get_pages(array $filters = []): array
    {
        $this->apply_filters($filters, ['title', 'slug', 'primary_keyword', 'city', 'status', 'page_type']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_seo_pages')->result_array();
    }

    public function get_page(int $id): ?array { return $this->db->where('id', $id)->get(db_prefix() . 'usi_seo_pages')->row_array() ?: null; }

    public function save_page(array $data, int $id = 0): int
    {
        $payload = $this->clean_page_payload($data);
        $payload['updated_at'] = date('Y-m-d H:i:s');
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_seo_pages', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_seo_pages', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_page(int $id): bool { return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_seo_pages'); }
    public function mass_delete_pages(array $ids): int { $ids = $this->clean_ids($ids); if (!$ids) { return 0; } $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_seo_pages'); return $this->db->affected_rows(); }

    public function get_keywords(array $filters = []): array
    {
        $this->apply_filters($filters, ['keyword', 'intent', 'city', 'priority', 'status']);
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'usi_seo_keywords')->result_array();
    }

    public function get_keyword(int $id): ?array { return $this->db->where('id', $id)->get(db_prefix() . 'usi_seo_keywords')->row_array() ?: null; }

    public function save_keyword(array $data, int $id = 0): int
    {
        $payload = $this->clean_keyword_payload($data);
        $payload['updated_at'] = date('Y-m-d H:i:s');
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_seo_keywords', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_seo_keywords', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_keyword(int $id): bool { return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_seo_keywords'); }
    public function mass_delete_keywords(array $ids): int { $ids = $this->clean_ids($ids); if (!$ids) { return 0; } $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_seo_keywords'); return $this->db->affected_rows(); }

    public function get_reports(array $filters = []): array
    {
        $this->seed_report_if_empty();
        if (!empty($filters['date_from'])) { $this->db->where('report_date >=', $filters['date_from']); }
        if (!empty($filters['date_to'])) { $this->db->where('report_date <=', $filters['date_to']); }
        if (!empty($filters['search'])) { $this->db->like('summary', trim((string)$filters['search'])); }
        $this->db->order_by('report_date', 'DESC');
        return $this->db->get(db_prefix() . 'usi_seo_reports')->result_array();
    }

    public function seed_report_if_empty(): void
    {
        if ($this->safe_count('usi_seo_reports') > 0) { return; }
        $this->db->insert(db_prefix() . 'usi_seo_reports', [
            'report_date' => date('Y-m-d'),
            'summary' => 'Automatic baseline report for justsmartchoice.com website, SEO, AI voice commands, and camera estimate operations.',
            'pages_created' => $this->safe_count('usi_seo_pages'),
            'keywords_targeted' => $this->safe_count('usi_seo_keywords'),
            'issues_found' => 0,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function mass_delete_reports(array $ids): int { $ids = $this->clean_ids($ids); if (!$ids) { return 0; } $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_seo_reports'); return $this->db->affected_rows(); }

    public function health(): array
    {
        usi_smartchoice_seo_ensure_schema();
        $required = ['usi_seo_pages', 'usi_seo_keywords', 'usi_seo_reports', 'usi_ai_commands', 'usi_ai_estimates', 'usi_ai_photos', 'usi_ai_actions', 'usi_ai_training', 'usi_ai_price_index', 'usi_ai_estimate_lines', 'usi_ai_estimate_sources', 'usi_ai_estimate_approvals', 'usi_ai_customer_packages', 'usi_ai_field_verifications', 'usi_ai_communications', 'usi_ai_voices', 'usi_ai_avatars', 'usi_ai_videos', 'usi_ai_video_text_layers', 'usi_ai_takeoffs', 'usi_ai_takeoff_lines', 'usi_ai_vendors', 'usi_ai_vendor_prices', 'usi_ai_purchase_orders', 'usi_ai_purchase_order_lines'];
        $checks = [];
        foreach ($required as $table) { $checks[] = ['label' => 'Database table ' . db_prefix() . $table, 'status' => usi_smartchoice_seo_table_exists($table)]; }
        $checks[] = ['label' => 'English language file', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'language/english/usi_smartchoice_seo_lang.php'))];
        $checks[] = ['label' => 'Spanish language file', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'language/spanish/usi_smartchoice_seo_lang.php'))];
        $checks[] = ['label' => 'CSS asset', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'assets/css/usi_smartchoice_seo.css'))];
        $checks[] = ['label' => 'JavaScript asset', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'assets/js/usi_smartchoice_seo.js'))];
        $checks[] = ['label' => 'Upload folder writable', 'status' => is_writable(FCPATH . 'uploads')];
        return $checks;
    }


    public function get_communications(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_communications')) { return []; }
        $this->apply_filters($filters, ['subject', 'message_body', 'communication_type', 'send_status', 'language']);
        if (!empty($filters['send_status'])) { $this->db->where('send_status', trim((string)$filters['send_status'])); }
        if (!empty($filters['communication_type'])) { $this->db->where('communication_type', trim((string)$filters['communication_type'])); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_communications')->result_array();
    }

    public function get_communication(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_communications')) { return null; }
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_communications')->row_array() ?: null;
    }

    public function save_communication(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $payload = [
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_package_id' => (int)($data['customer_package_id'] ?? 0),
            'project_handoff_id' => (int)($data['project_handoff_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'communication_type' => trim((string)($data['communication_type'] ?? 'follow_up')),
            'language' => trim((string)($data['language'] ?? 'english')),
            'subject' => trim((string)($data['subject'] ?? 'Smart Choice Contractors USA Follow Up')),
            'message_body' => (string)($data['message_body'] ?? ''),
            'send_status' => trim((string)($data['send_status'] ?? 'draft')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0 && $this->get_communication($id)) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_communications', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_communications', $payload);
        return (int)$this->db->insert_id();
    }

    public function build_follow_up_from_estimate(int $aiEstimateId): array
    {
        $estimate = $this->get_ai_estimate($aiEstimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        $title = trim((string)($estimate['title'] ?? 'your project estimate'));
        $total = number_format((float)($estimate['total'] ?? 0), 2);
        $body = "Hello,\n\nThank you for giving Smart Choice Contractors USA the opportunity to review your project. We prepared a draft estimate for " . $title . ".\n\nEstimated total: $" . $total . "\n\nThis estimate is pending final company review before it is sent as an official CRM estimate. Please let us know if you want us to adjust the scope, add photos, or review additional details.\n\nThank you,\nSmart Choice Contractors USA";
        $id = $this->save_communication([
            'ai_estimate_id' => $aiEstimateId,
            'customer_id' => (int)($estimate['customer_id'] ?? 0),
            'lead_id' => (int)($estimate['lead_id'] ?? 0),
            'communication_type' => 'estimate_follow_up',
            'language' => 'english',
            'subject' => 'Smart Choice Contractors USA Estimate Follow Up',
            'message_body' => $body,
            'send_status' => 'draft',
        ]);
        return ['success' => true, 'message' => 'Customer follow-up draft created.', 'communication_id' => $id];
    }

    public function build_follow_up_from_package(int $packageId): array
    {
        $package = method_exists($this, 'get_customer_package') ? $this->get_customer_package($packageId) : null;
        if (!$package) { return ['success' => false, 'message' => 'Customer package was not found.']; }
        $title = trim((string)($package['package_title'] ?? 'your project package'));
        $body = "Hello,\n\nYour Smart Choice Contractors USA customer package is ready for review: " . $title . ".\n\nPlease review the scope, notes, inclusions, exclusions, and next steps. If anything needs correction, we can revise it before final approval.\n\nThank you,\nSmart Choice Contractors USA";
        $id = $this->save_communication([
            'customer_package_id' => $packageId,
            'ai_estimate_id' => (int)($package['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($package['customer_id'] ?? 0),
            'communication_type' => 'customer_package_follow_up',
            'language' => 'english',
            'subject' => 'Smart Choice Contractors USA Customer Package Ready',
            'message_body' => $body,
            'send_status' => 'draft',
        ]);
        return ['success' => true, 'message' => 'Package follow-up draft created.', 'communication_id' => $id];
    }

    public function mark_communication_ready(int $id): array
    {
        if (!$this->get_communication($id)) { return ['success' => false, 'message' => 'Communication was not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_communications', ['send_status' => 'ready_to_send', 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Communication marked ready to send.'];
    }

    public function mark_communication_sent(int $id): array
    {
        if (!$this->get_communication($id)) { return ['success' => false, 'message' => 'Communication was not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_communications', ['send_status' => 'sent', 'sent_by' => get_staff_user_id(), 'sent_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Communication marked sent.'];
    }

    public function delete_communication(int $id): void
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_communications')) { $this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_communications'); }
    }

    public function mass_delete_communications(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids || !usi_smartchoice_seo_table_exists('usi_ai_communications')) { return 0; }
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_communications');
        return count($ids);
    }


    private function safe_count(string $table): int { return usi_smartchoice_seo_table_exists($table) ? (int)$this->db->count_all_results(db_prefix() . $table) : 0; }
    private function clean_ids(array $ids): array { return array_values(array_filter(array_map('intval', $ids))); }

    private function apply_filters(array $filters, array $fields): void
    {
        foreach ($fields as $field) { if (isset($filters[$field]) && trim((string)$filters[$field]) !== '') { $this->db->like($field, trim((string)$filters[$field])); } }
        if (isset($filters['search']) && trim((string)$filters['search']) !== '') {
            $search = trim((string)$filters['search']);
            $this->db->group_start();
            foreach ($fields as $i => $field) { $i === 0 ? $this->db->like($field, $search) : $this->db->or_like($field, $search); }
            $this->db->group_end();
        }
    }

    private function clean_page_payload(array $data): array
    {
        $domain = 'justsmartchoice.com';
        return [
            'title' => trim((string)($data['title'] ?? '')), 'slug' => trim((string)($data['slug'] ?? '')), 'page_type' => trim((string)($data['page_type'] ?? 'service')),
            'meta_title' => trim((string)($data['meta_title'] ?? '')), 'meta_description' => (string)($data['meta_description'] ?? ''), 'primary_keyword' => trim((string)($data['primary_keyword'] ?? '')),
            'city' => trim((string)($data['city'] ?? '')), 'status' => trim((string)($data['status'] ?? get_option('usi_smartchoice_seo_default_status'))), 'assigned_staff_id' => (int)($data['assigned_staff_id'] ?? 0),
            'content' => str_replace(['usismartchoice.com', 'usi smartchoice.com', 'just smart choice.com'], [$domain, $domain, $domain], (string)($data['content'] ?? '')),
        ];
    }

    private function clean_keyword_payload(array $data): array
    {
        return ['keyword' => trim((string)($data['keyword'] ?? '')), 'intent' => trim((string)($data['intent'] ?? 'local_service')), 'city' => trim((string)($data['city'] ?? '')), 'priority' => trim((string)($data['priority'] ?? 'medium')), 'status' => trim((string)($data['status'] ?? 'planned')), 'assigned_staff_id' => (int)($data['assigned_staff_id'] ?? 0), 'notes' => (string)($data['notes'] ?? '')];
    }

    public function preview_voice_command(string $command): array
    {
        $command = $this->normalize_voice_command($command);
        if ($command === '') {
            return ['success' => false, 'message' => 'No command was captured.', 'preview' => ''];
        }
        $intent = $this->detect_voice_intent($command);
        return [
            'success' => true,
            'message' => 'Command captured. Review the preview before running it.',
            'intent' => $intent,
            'preview' => $this->build_command_preview($command),
        ];
    }

    public function save_voice_command(string $command): array
    {
        $command = $this->normalize_voice_command($command);
        if ($command === '') {
            return ['success' => false, 'message' => 'No command was captured.'];
        }
        $intent = $this->detect_voice_intent($command);
        $id = $this->save_ai_command([
            'command_text' => $command,
            'command_source' => 'voice',
            'intent' => $intent,
            'target_module' => $this->detect_target_module($command),
            'target_record_type' => $this->detect_target_record_type($command),
            'action_status' => 'pending_review',
            'ai_response' => $this->build_command_preview($command),
            'review_notes' => 'Captured from Voice Assistant and waiting for user confirmation.',
        ], 0);
        return ['success' => true, 'message' => 'Voice command saved for review.', 'command_id' => $id];
    }

    public function execute_voice_command(string $command, int $commandId = 0): array
    {
        $command = $this->normalize_voice_command($command);
        if ($commandId > 0) {
            $record = $this->get_ai_command($commandId);
            if ($record && trim((string)$record['command_text']) !== '') {
                $command = $this->normalize_voice_command((string)$record['command_text']);
            }
        }
        if ($command === '') {
            return ['success' => false, 'message' => 'No command was captured.'];
        }
        if ($commandId <= 0) {
            $saved = $this->save_voice_command($command);
            $commandId = (int)($saved['command_id'] ?? 0);
        }

        $intent = $this->detect_voice_intent($command);
        if ($intent === 'create_lead') {
            $lead = $this->create_lead_from_voice($command);
            $this->log_ai_action($commandId, 'Create Lead', 'leads', $command, $lead['success'] ? 'completed' : 'failed', $lead['message']);
            $this->db->where('id', $commandId)->update(db_prefix() . 'usi_ai_commands', [
                'intent' => 'create_lead',
                'target_module' => 'leads',
                'target_record_type' => 'lead',
                'target_record_id' => (int)($lead['lead_id'] ?? 0),
                'action_status' => $lead['success'] ? 'completed' : 'failed',
                'ai_response' => $lead['message'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return $lead + ['command_id' => $commandId];
        }

        $message = 'Command saved in review mode. This command type is not mapped to an automatic CRM action yet.';
        $this->log_ai_action($commandId, 'Review Command', 'crm', $command, 'pending_review', $message);
        $this->db->where('id', $commandId)->update(db_prefix() . 'usi_ai_commands', [
            'action_status' => 'pending_review',
            'ai_response' => $message,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => $message, 'command_id' => $commandId];
    }

    public function create_estimate_from_voice(string $command): array
    {
        $command = $this->normalize_voice_command($command);
        if ($command === '') {
            return ['success' => false, 'message' => 'No voice estimate command was captured.'];
        }
        $payload = $this->calculate_estimate_payload([
            'title' => 'Voice Estimate - ' . date('Y-m-d H:i'),
            'scope_summary' => $command,
            'measurement_notes' => 'Created from Voice Assistant command.',
            'location' => '',
            'status' => 'calculated',
        ]);
        $estimateId = $this->save_ai_estimate($payload, 0);
        $this->calculate_ai_estimate_numbers($estimateId);
        return [
            'success' => true,
            'message' => 'Voice estimate calculated and saved. Review the draft before sending anything to a customer.',
            'estimate_id' => $estimateId,
            'redirect_url' => admin_url('usi_smartchoice_seo/view_ai_estimate/' . $estimateId),
        ];
    }

    public function calculate_estimate_payload(array $data): array
    {
        $rawScope = (string)($data['scope_summary'] ?? '') . ' ' . (string)($data['measurement_notes'] ?? '') . ' ' . (string)($data['ai_caption'] ?? '');
        $scope = strtolower($rawScope);
        $quantity = $this->detect_estimate_quantity($scope);
        $service = $this->detect_estimate_service($scope);
        $matches = $this->find_price_matches($scope, $service, 8);
        $sourceNote = '';

        if (!empty($matches)) {
            $materialRate = 0.00;
            $laborRate = 0.00;
            $lineAverage = 0.00;
            $weighted = 0.00;
            $weightTotal = 0.00;
            foreach ($matches as $row) {
                $weight = max(1.00, (float)($row['confidence'] ?? 50));
                $rate = (float)($row['unit_rate'] ?? 0);
                $line = (float)($row['line_total'] ?? 0);
                if ($rate > 0) { $weighted += $rate * $weight; }
                if ($line > 0) { $lineAverage += $line; }
                $weightTotal += $weight;
            }
            $averageRate = $weightTotal > 0 ? round($weighted / $weightTotal, 2) : 0.00;
            $averageLine = count($matches) > 0 ? round($lineAverage / count($matches), 2) : 0.00;
            if ($averageRate <= 0 && $averageLine > 0) { $averageRate = $averageLine; }
            $materials = round(max(125.00, ($averageRate * $quantity) * 0.48), 2);
            $labor = round(max(185.00, ($averageRate * $quantity) * 0.52), 2);
            $sourceIds = array_unique(array_map(static function($row){ return (string)($row['source_id'] ?? 0); }, $matches));
            $sourceNote = ' Historical CRM pricing used from ' . count($matches) . ' similar line item(s). Source estimate IDs: ' . implode(', ', array_slice($sourceIds, 0, 5)) . '.';
        } else {
            $base = 350.00;
            $materialRate = 175.00;
            $laborRate = 225.00;
            if (strpos($scope, 'drywall') !== false) { $materialRate = 2.25; $laborRate = 3.50; $base = 275.00; }
            if (strpos($scope, 'paint') !== false || strpos($scope, 'painting') !== false) { $materialRate = 1.10; $laborRate = 2.25; $base = 250.00; }
            if (strpos($scope, 'floor') !== false || strpos($scope, 'tile') !== false) { $materialRate = 4.75; $laborRate = 6.50; $base = 400.00; }
            if (strpos($scope, 'window') !== false) { $materialRate = 450.00; $laborRate = 275.00; $base = 225.00; }
            if (strpos($scope, 'door') !== false) { $materialRate = 325.00; $laborRate = 225.00; $base = 200.00; }
            if (strpos($scope, 'roof') !== false) { $materialRate = 3.75; $laborRate = 4.25; $base = 650.00; }
            if (strpos($scope, 'cabinet') !== false || strpos($scope, 'kitchen') !== false) { $materialRate = 300.00; $laborRate = 250.00; $base = 750.00; }
            if (strpos($scope, 'bath') !== false || strpos($scope, 'shower') !== false) { $materialRate = 375.00; $laborRate = 325.00; $base = 850.00; }
            if (strpos($scope, 'electrical') !== false || strpos($scope, 'outlet') !== false) { $materialRate = 125.00; $laborRate = 185.00; $base = 275.00; }
            if (strpos($scope, 'plumbing') !== false || strpos($scope, 'pipe') !== false) { $materialRate = 150.00; $laborRate = 195.00; $base = 300.00; }
            if (strpos($scope, 'hvac') !== false || strpos($scope, 'duct') !== false || strpos($scope, 'air') !== false) { $materialRate = 225.00; $laborRate = 245.00; $base = 450.00; }
            $materials = round($base + ($quantity * $materialRate), 2);
            $labor = round($base + ($quantity * $laborRate), 2);
            $sourceNote = ' No matching CRM price index row was found. Draft used module fallback rates.';
        }

        $overheadProfit = round(($materials + $labor) * 0.18, 2);
        $subtotal = round($materials + $labor + $overheadProfit, 2);
        $taxPercent = (float)get_option('usi_smartchoice_ai_default_tax_percent');
        $tax = $taxPercent > 0 ? round($subtotal * ($taxPercent / 100), 2) : 0.00;
        $total = round($subtotal + $tax, 2);

        $data['materials_summary'] = trim((string)($data['materials_summary'] ?? '')) ?: 'Estimated materials for ' . $service . '. Quantity basis detected: ' . $quantity . '.';
        $data['labor_summary'] = trim((string)($data['labor_summary'] ?? '')) ?: 'Estimated labor for ' . $service . '. Field verification required before customer delivery.';
        $data['ai_observations'] = 'Run Estimate generated a draft only. Service detected: ' . $service . '.' . $sourceNote . ' Pricing must be reviewed by a Smart Choice estimator before sending.';
        $data['subtotal'] = $subtotal;
        $data['tax_total'] = $tax;
        $data['total'] = $total;
        $data['status'] = 'calculated';
        return $data;
    }

    public function rebuild_price_index(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_price_index')) {
            return ['success' => false, 'message' => 'Price index table does not exist. Run Upgrade Database first.'];
        }
        $this->db->where('source_type', 'crm_estimate')->delete(db_prefix() . 'usi_ai_price_index');
        $inserted = 0;
        if (usi_smartchoice_seo_table_exists('itemable')) {
            $rows = $this->db->select('rel_id, description, long_description, qty, unit, rate')
                ->where('rel_type', 'estimate')
                ->limit(2000)
                ->get(db_prefix() . 'itemable')->result_array();
            foreach ($rows as $row) {
                $description = trim((string)($row['description'] ?? '') . ' ' . (string)($row['long_description'] ?? ''));
                $qty = (float)($row['qty'] ?? 0);
                $rate = (float)($row['rate'] ?? 0);
                $lineTotal = round(max(0, $qty) * max(0, $rate), 2);
                if ($description === '' || ($rate <= 0 && $lineTotal <= 0)) { continue; }
                $this->db->insert(db_prefix() . 'usi_ai_price_index', [
                    'source_type' => 'crm_estimate',
                    'source_id' => (int)($row['rel_id'] ?? 0),
                    'service_keyword' => $this->detect_estimate_service(strtolower($description)),
                    'description' => $description,
                    'quantity' => $qty,
                    'unit' => trim((string)($row['unit'] ?? '')),
                    'unit_rate' => $rate,
                    'line_total' => $lineTotal,
                    'confidence' => 85.00,
                    'created_by' => get_staff_user_id(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }
        return ['success' => true, 'message' => 'Estimate Learning Engine rebuilt. Indexed ' . $inserted . ' historical CRM estimate line item(s).', 'inserted' => $inserted];
    }

    public function find_price_matches(string $scope, string $service, int $limit = 8): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_price_index')) { return []; }
        $words = array_values(array_unique(array_filter(preg_split('/[^a-z0-9]+/i', strtolower($scope)))));
        $this->db->group_start();
        $this->db->like('service_keyword', $service);
        foreach (array_slice($words, 0, 8) as $word) {
            if (strlen($word) >= 4) { $this->db->or_like('description', $word); }
        }
        $this->db->group_end();
        $this->db->order_by('confidence', 'DESC');
        $this->db->limit($limit);
        return $this->db->get(db_prefix() . 'usi_ai_price_index')->result_array();
    }

    private function detect_estimate_quantity(string $scope): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(sqft|sq ft|square feet|sf)/i', $scope, $m)) { return max(1, (float)$m[1]); }
        if (preg_match('/(\d+(?:\.\d+)?)\s*(linear feet|lf|feet|ft)/i', $scope, $m)) { return max(1, (float)$m[1]); }
        if (preg_match('/(\d+(?:\.\d+)?)\s*(windows|window|doors|door|outlets|lights|cabinets)/i', $scope, $m)) { return max(1, (float)$m[1]); }
        return 1.00;
    }

    private function detect_estimate_service(string $scope): string
    {
        $scope = strtolower($scope);
        if (strpos($scope, 'drywall') !== false) { return 'Drywall repair or installation'; }
        if (strpos($scope, 'paint') !== false || strpos($scope, 'painting') !== false) { return 'Interior or exterior painting'; }
        if (strpos($scope, 'floor') !== false || strpos($scope, 'tile') !== false) { return 'Flooring or tile installation'; }
        if (strpos($scope, 'window') !== false) { return 'Window service or replacement'; }
        if (strpos($scope, 'door') !== false) { return 'Door service or replacement'; }
        if (strpos($scope, 'roof') !== false) { return 'Roof repair or roofing service'; }
        if (strpos($scope, 'cabinet') !== false || strpos($scope, 'kitchen') !== false) { return 'Cabinet or kitchen scope'; }
        if (strpos($scope, 'bath') !== false || strpos($scope, 'shower') !== false) { return 'Bathroom or shower scope'; }
        if (strpos($scope, 'electrical') !== false || strpos($scope, 'outlet') !== false) { return 'Electrical service scope'; }
        if (strpos($scope, 'plumbing') !== false || strpos($scope, 'pipe') !== false) { return 'Plumbing service scope'; }
        if (strpos($scope, 'hvac') !== false || strpos($scope, 'duct') !== false || strpos($scope, 'air') !== false) { return 'HVAC or ductwork service scope'; }
        return 'General construction service';
    }


    public function get_price_index(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_price_index')) { return []; }
        $this->apply_filters($filters, ['service_keyword', 'description', 'unit', 'city']);
        if (!empty($filters['source_id'])) { $this->db->where('source_id', (int)$filters['source_id']); }
        $this->db->order_by('id', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_price_index')->result_array();
    }

    public function pricing_engine_counts(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_price_index')) {
            return ['rows' => 0, 'crm_estimates' => 0, 'services' => 0];
        }
        $rows = (int)$this->db->count_all_results(db_prefix() . 'usi_ai_price_index');
        $crm = (int)$this->db->select('COUNT(DISTINCT source_id) AS total', false)
            ->where('source_type', 'crm_estimate')
            ->get(db_prefix() . 'usi_ai_price_index')->row()->total;
        $services = (int)$this->db->select('COUNT(DISTINCT service_keyword) AS total', false)
            ->get(db_prefix() . 'usi_ai_price_index')->row()->total;
        return ['rows' => $rows, 'crm_estimates' => $crm, 'services' => $services];
    }

    public function get_ai_estimate_lines(int $estimateId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimate_lines')) { return []; }
        return $this->db->where('ai_estimate_id', $estimateId)->order_by('line_order', 'ASC')->get(db_prefix() . 'usi_ai_estimate_lines')->result_array();
    }

    public function get_ai_estimate_sources(int $estimateId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimate_sources')) { return []; }
        return $this->db->where('ai_estimate_id', $estimateId)->order_by('confidence', 'DESC')->get(db_prefix() . 'usi_ai_estimate_sources')->result_array();
    }

    public function calculate_ai_estimate_numbers(int $estimateId): array
    {
        usi_smartchoice_seo_ensure_schema();
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) {
            return ['success' => false, 'message' => 'AI estimate was not found.'];
        }
        $scope = trim((string)($estimate['scope_summary'] ?? '') . "\n" . (string)($estimate['measurement_notes'] ?? ''));
        if ($scope === '') {
            return ['success' => false, 'message' => 'Add scope summary or measurement notes before calculating numbers.'];
        }
        $lines = $this->build_estimate_line_breakdown($scope);
        if (empty($lines)) {
            return ['success' => false, 'message' => 'No estimate line items could be calculated from the scope.'];
        }
        $this->db->where('ai_estimate_id', $estimateId)->delete(db_prefix() . 'usi_ai_estimate_lines');
        $this->db->where('ai_estimate_id', $estimateId)->delete(db_prefix() . 'usi_ai_estimate_sources');
        $subtotal = 0.00;
        $confidenceTotal = 0.00;
        $confidenceCount = 0;
        $sourceRows = [];
        $order = 1;
        foreach ($lines as $line) {
            $lineTotal = (float)$line['line_total'];
            $subtotal += $lineTotal;
            $confidenceTotal += (float)$line['confidence'];
            $confidenceCount++;
            $insert = $line;
            unset($insert['sources']);
            $insert['ai_estimate_id'] = $estimateId;
            $insert['line_order'] = $order++;
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'usi_ai_estimate_lines', $insert);
            foreach ($line['sources'] as $source) {
                $sourceRows[] = $source;
                $this->db->insert(db_prefix() . 'usi_ai_estimate_sources', [
                    'ai_estimate_id' => $estimateId,
                    'price_index_id' => (int)($source['id'] ?? 0),
                    'source_estimate_id' => (int)($source['source_id'] ?? 0),
                    'service_keyword' => (string)($source['service_keyword'] ?? ''),
                    'source_description' => (string)($source['description'] ?? ''),
                    'source_rate' => (float)($source['unit_rate'] ?? 0),
                    'source_total' => (float)($source['line_total'] ?? 0),
                    'confidence' => (float)($source['confidence'] ?? 0),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $taxPercent = (float)get_option('usi_smartchoice_ai_default_tax_percent');
        $tax = $taxPercent > 0 ? round($subtotal * ($taxPercent / 100), 2) : 0.00;
        $total = round($subtotal + $tax, 2);
        $confidence = $confidenceCount > 0 ? round($confidenceTotal / $confidenceCount, 2) : 0.00;
        $basis = !empty($sourceRows) ? 'Historical CRM estimates and fallback rates' : 'Fallback rates only';
        $payload = [
            'subtotal' => round($subtotal, 2),
            'tax_total' => $tax,
            'total' => $total,
            'status' => 'calculated',
            'pricing_confidence' => $confidence,
            'pricing_basis' => $basis,
            'line_items_json' => json_encode($lines),
            'source_matches_json' => json_encode(array_slice($sourceRows, 0, 20)),
            'materials_summary' => $this->build_materials_summary_from_lines($lines),
            'labor_summary' => $this->build_labor_summary_from_lines($lines),
            'ai_observations' => 'Calculate Numbers created ' . count($lines) . ' line item(s). Confidence: ' . $confidence . '%. Pricing basis: ' . $basis . '. Review all numbers before sending to a customer.',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', $payload);
        return ['success' => true, 'message' => 'Numbers calculated. ' . count($lines) . ' line item(s) created. Total: $' . number_format($total, 2) . '.'];
    }

    private function build_estimate_line_breakdown(string $scope): array
    {
        $parts = preg_split('/\r\n|\r|\n|;|\./', $scope);
        $clean = [];
        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part !== '' && strlen($part) >= 4) { $clean[] = $part; }
        }
        if (empty($clean)) { $clean[] = trim($scope); }
        $lines = [];
        foreach (array_slice($clean, 0, 20) as $part) {
            $lower = strtolower($part);
            $quantity = $this->detect_estimate_quantity($lower);
            $service = $this->detect_estimate_service($lower);
            $unit = $this->detect_estimate_unit($lower, $service);
            $matches = $this->find_price_matches($lower, $service, 8);
            $confidence = 45.00;
            $unitRate = 0.00;
            $sourceId = 0;
            if (!empty($matches)) {
                $weighted = 0.00;
                $weightTotal = 0.00;
                foreach ($matches as $match) {
                    $rate = (float)($match['unit_rate'] ?? 0);
                    $qty = max(1.00, (float)($match['quantity'] ?? 1));
                    $total = (float)($match['line_total'] ?? 0);
                    if ($rate <= 0 && $total > 0) { $rate = $total / $qty; }
                    if ($rate <= 0) { continue; }
                    $weight = max(1.00, (float)($match['confidence'] ?? 75));
                    $weighted += $rate * $weight;
                    $weightTotal += $weight;
                }
                $unitRate = $weightTotal > 0 ? round($weighted / $weightTotal, 2) : 0.00;
                $confidence = 82.00;
                $sourceId = (int)($matches[0]['source_id'] ?? 0);
            }
            if ($unitRate <= 0) { $unitRate = $this->fallback_unit_rate($service, $unit); }
            $baseTotal = round($quantity * $unitRate, 2);
            $materialTotal = round($baseTotal * 0.45, 2);
            $laborTotal = round($baseTotal * 0.45, 2);
            $overheadProfit = round($baseTotal * 0.10, 2);
            $lineTotal = round($materialTotal + $laborTotal + $overheadProfit, 2);
            $lines[] = [
                'description' => $service,
                'long_description' => $part,
                'quantity' => $quantity,
                'unit' => $unit,
                'unit_rate' => $unitRate,
                'material_total' => $materialTotal,
                'labor_total' => $laborTotal,
                'overhead_profit_total' => $overheadProfit,
                'line_total' => $lineTotal,
                'source_type' => !empty($matches) ? 'crm_history' : 'fallback_rate',
                'source_id' => $sourceId,
                'confidence' => $confidence,
                'sources' => $matches,
            ];
        }
        return $lines;
    }

    private function detect_estimate_unit(string $scope, string $service): string
    {
        if (preg_match('/sqft|sq ft|square feet|sf/i', $scope)) { return 'sq ft'; }
        if (preg_match('/linear feet|lf|feet|ft/i', $scope)) { return 'lf'; }
        if (preg_match('/windows|window|doors|door|outlets|lights|cabinets/i', $scope)) { return 'each'; }
        if (stripos($service, 'Window') !== false || stripos($service, 'Door') !== false) { return 'each'; }
        return 'scope';
    }

    private function fallback_unit_rate(string $service, string $unit): float
    {
        $serviceLower = strtolower($service);
        if (strpos($serviceLower, 'drywall') !== false) { return $unit === 'sq ft' ? 5.75 : 450.00; }
        if (strpos($serviceLower, 'painting') !== false) { return $unit === 'sq ft' ? 3.35 : 350.00; }
        if (strpos($serviceLower, 'flooring') !== false || strpos($serviceLower, 'tile') !== false) { return $unit === 'sq ft' ? 11.25 : 650.00; }
        if (strpos($serviceLower, 'window') !== false) { return 725.00; }
        if (strpos($serviceLower, 'door') !== false) { return 550.00; }
        if (strpos($serviceLower, 'roof') !== false) { return $unit === 'sq ft' ? 8.75 : 1250.00; }
        if (strpos($serviceLower, 'kitchen') !== false || strpos($serviceLower, 'cabinet') !== false) { return 1250.00; }
        if (strpos($serviceLower, 'bathroom') !== false || strpos($serviceLower, 'shower') !== false) { return 1450.00; }
        if (strpos($serviceLower, 'electrical') !== false) { return 385.00; }
        if (strpos($serviceLower, 'plumbing') !== false) { return 420.00; }
        if (strpos($serviceLower, 'hvac') !== false || strpos($serviceLower, 'ductwork') !== false) { return 695.00; }
        return 575.00;
    }

    private function build_materials_summary_from_lines(array $lines): string
    {
        $total = 0.00;
        foreach ($lines as $line) { $total += (float)$line['material_total']; }
        return 'Calculated material allowance from line items: $' . number_format($total, 2) . '. Verify actual vendor pricing before customer delivery.';
    }

    private function build_labor_summary_from_lines(array $lines): string
    {
        $total = 0.00;
        foreach ($lines as $line) { $total += (float)$line['labor_total']; }
        return 'Calculated labor allowance from line items: $' . number_format($total, 2) . '. Verify crew rates, site conditions, access, and schedule before customer delivery.';
    }

    public function create_crm_estimate_from_ai(int $estimateId): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        if ((int)($estimate['customer_id'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Add a valid Customer ID before creating a CRM estimate draft.'];
        }
        if ((string)get_option('usi_smartchoice_ai_require_estimate_approval') === '1' && (string)($estimate['review_status'] ?? '') !== 'approved') {
            return ['success' => false, 'message' => 'Approve this AI estimate in Estimate Review before creating a CRM estimate draft.'];
        }
        $lines = $this->get_ai_estimate_lines($estimateId);
        if (empty($lines)) {
            $calc = $this->calculate_ai_estimate_numbers($estimateId);
            if (!$calc['success']) { return $calc; }
            $lines = $this->get_ai_estimate_lines($estimateId);
        }
        if (empty($lines)) { return ['success' => false, 'message' => 'No line items exist for this AI estimate.']; }
        try {
            $CI = &get_instance();
            $CI->load->model('estimates_model');
            $currency = get_base_currency();
            $newItems = [];
            foreach ($lines as $line) {
                $newItems[] = [
                    'description' => (string)$line['description'],
                    'long_description' => (string)$line['long_description'],
                    'qty' => (float)$line['quantity'],
                    'unit' => (string)$line['unit'],
                    'rate' => (float)$line['unit_rate'],
                    'order' => (int)$line['line_order'],
                ];
            }
            $data = [
                'clientid' => (int)$estimate['customer_id'],
                'number' => get_option('next_estimate_number'),
                'date' => date('Y-m-d'),
                'expirydate' => date('Y-m-d', strtotime('+30 days')),
                'currency' => is_object($currency) && isset($currency->id) ? (int)$currency->id : 0,
                'newitems' => $newItems,
                'subtotal' => (float)$estimate['subtotal'],
                'total' => (float)$estimate['total'],
                'adminnote' => 'Created from Sammy AI estimate #' . $estimateId . '. Review before sending.',
                'clientnote' => 'Thank you for considering Smart Choice Contractors USA.',
                'terms' => 'This draft estimate is subject to field verification, material availability, final measurements, and management approval.',
            ];
            $crmId = $CI->estimates_model->add($data);
            if ($crmId) {
                $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', [
                    'converted_estimate_id' => (int)$crmId,
                    'status' => 'converted',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return ['success' => true, 'message' => 'CRM estimate draft created successfully.', 'estimate_id' => (int)$crmId];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'CRM estimate draft could not be created: ' . $e->getMessage()];
        }
        return ['success' => false, 'message' => 'CRM estimate draft could not be created. Review customer ID, estimate settings, and Perfex estimate permissions.'];
    }


    public function get_estimate_review_queue(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimates')) { return []; }
        $this->apply_filters($filters, ['title', 'location', 'scope_summary', 'review_status', 'status']);
        if (!empty($filters['review_status'])) { $this->db->where('review_status', trim((string)$filters['review_status'])); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_estimates')->result_array();
    }

    public function get_ai_estimate_approval_log(int $estimateId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimate_approvals')) { return []; }
        return $this->db->where('ai_estimate_id', $estimateId)->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_estimate_approvals')->result_array();
    }

    public function save_estimate_review(int $estimateId, array $data): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        $payload = [
            'review_status' => trim((string)($data['review_status'] ?? 'in_review')),
            'customer_scope' => (string)($data['customer_scope'] ?? ''),
            'exclusions' => (string)($data['exclusions'] ?? ''),
            'payment_schedule' => (string)($data['payment_schedule'] ?? ''),
            'estimator_review_notes' => (string)($data['estimator_review_notes'] ?? ''),
            'field_verified' => (int)($data['field_verified'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', $payload);
        $this->log_estimate_review_action($estimateId, 'review_saved', (string)($data['estimator_review_notes'] ?? ''));
        return ['success' => true, 'message' => 'Estimate review saved.'];
    }

    public function approve_ai_estimate(int $estimateId, string $notes = ''): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        if ((float)($estimate['total'] ?? 0) <= 0) { return ['success' => false, 'message' => 'Calculate numbers before approval.']; }
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', [
            'review_status' => 'approved',
            'status' => 'approved',
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s'),
            'estimator_review_notes' => $notes !== '' ? $notes : (string)($estimate['estimator_review_notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log_estimate_review_action($estimateId, 'approved', $notes);
        return ['success' => true, 'message' => 'AI estimate approved. You can now create the CRM estimate draft.'];
    }

    public function mark_ai_estimate_needs_revision(int $estimateId, string $notes = ''): array
    {
        if (!$this->get_ai_estimate($estimateId)) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', [
            'review_status' => 'needs_revision',
            'status' => 'needs_revision',
            'estimator_review_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log_estimate_review_action($estimateId, 'needs_revision', $notes);
        return ['success' => true, 'message' => 'AI estimate marked as Needs Revision.'];
    }

    public function prepare_customer_scope(int $estimateId): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        $lines = $this->get_ai_estimate_lines($estimateId);
        if (empty($lines)) {
            $calc = $this->calculate_ai_estimate_numbers($estimateId);
            if (!$calc['success']) { return $calc; }
            $lines = $this->get_ai_estimate_lines($estimateId);
        }
        $scope = "Scope of Work for " . (string)$estimate['title'] . "\n\n";
        foreach ($lines as $line) {
            $scope .= '- ' . (string)$line['description'] . ': ' . (string)$line['long_description'] . ' Qty ' . (string)$line['quantity'] . ' ' . (string)$line['unit'] . "\n";
        }
        $scope .= "\nEstimated total: $" . number_format((float)$estimate['total'], 2) . "\n";
        $scope .= "\nCustomer-facing scope must be reviewed before sending. Prices are subject to field verification, final measurements, product availability, permit requirements, and management approval.";
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', [
            'customer_scope' => $scope,
            'exclusions' => (string)($estimate['exclusions'] ?? '') ?: 'Excludes hidden damage, engineering, permit fees, material upgrades, code-required changes not visible during initial review, and work not listed in this scope.',
            'payment_schedule' => (string)($estimate['payment_schedule'] ?? '') ?: 'Payment schedule to be confirmed by management before customer delivery.',
            'review_status' => 'in_review',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log_estimate_review_action($estimateId, 'customer_scope_prepared', 'Customer-facing scope prepared from calculated lines.');
        return ['success' => true, 'message' => 'Customer-facing scope prepared.'];
    }

    private function log_estimate_review_action(int $estimateId, string $action, string $notes = ''): void
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimate_approvals')) { return; }
        $this->db->insert(db_prefix() . 'usi_ai_estimate_approvals', [
            'ai_estimate_id' => $estimateId,
            'action' => $action,
            'notes' => $notes,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }


    public function get_customer_packages(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_customer_packages')) { return []; }
        $this->apply_filters($filters, ['package_title', 'package_status', 'customer_scope', 'internal_handoff']);
        if (!empty($filters['package_status'])) { $this->db->where('package_status', trim((string)$filters['package_status'])); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_customer_packages')->result_array();
    }

    public function get_customer_package(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_customer_packages')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_customer_packages')->row_array();
        return $row ?: null;
    }

    public function build_customer_package_from_ai_estimate(int $estimateId): array
    {
        usi_smartchoice_seo_ensure_schema();
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        if ((string)get_option('usi_smartchoice_ai_require_estimate_approval') === '1' && (string)($estimate['review_status'] ?? '') !== 'approved') {
            return ['success' => false, 'message' => 'Approve the AI estimate before building the customer package.'];
        }
        $lines = $this->get_ai_estimate_lines($estimateId);
        if (empty($lines)) {
            $calc = $this->calculate_ai_estimate_numbers($estimateId);
            if (!$calc['success']) { return $calc; }
            $lines = $this->get_ai_estimate_lines($estimateId);
            $estimate = $this->get_ai_estimate($estimateId) ?: $estimate;
        }
        $customerScope = trim((string)($estimate['customer_scope'] ?? ''));
        if ($customerScope === '') {
            $scopeResult = $this->prepare_customer_scope($estimateId);
            if (!$scopeResult['success']) { return $scopeResult; }
            $estimate = $this->get_ai_estimate($estimateId) ?: $estimate;
            $customerScope = trim((string)($estimate['customer_scope'] ?? ''));
        }
        $internal = $this->build_internal_handoff_from_estimate($estimate, $lines);
        $payload = [
            'ai_estimate_id' => $estimateId,
            'package_title' => 'Customer Package - ' . (string)$estimate['title'],
            'customer_id' => (int)($estimate['customer_id'] ?? 0),
            'crm_estimate_id' => (int)($estimate['converted_estimate_id'] ?? 0),
            'package_status' => 'ready_for_review',
            'customer_scope' => $customerScope,
            'exclusions' => (string)($estimate['exclusions'] ?? ''),
            'payment_schedule' => (string)($estimate['payment_schedule'] ?? ''),
            'internal_handoff' => $internal,
            'embed_notes' => 'Use this package for customer review, CRM estimate finalization, proposal preparation, and project handoff. Do not send without final management review.',
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $existingId = (int)($estimate['customer_package_id'] ?? 0);
        if ($existingId > 0 && $this->get_customer_package($existingId)) {
            unset($payload['created_by'], $payload['created_at']);
            $this->db->where('id', $existingId)->update(db_prefix() . 'usi_ai_customer_packages', $payload);
            $packageId = $existingId;
        } else {
            $this->db->insert(db_prefix() . 'usi_ai_customer_packages', $payload);
            $packageId = (int)$this->db->insert_id();
        }
        $this->db->where('id', $estimateId)->update(db_prefix() . 'usi_ai_estimates', [
            'customer_package_id' => $packageId,
            'last_package_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log_estimate_review_action($estimateId, 'customer_package_built', 'Customer package generated from approved AI estimate.');
        return ['success' => true, 'message' => 'Customer package generated.', 'package_id' => $packageId];
    }

    public function update_customer_package(int $id, array $data): array
    {
        if (!$this->get_customer_package($id)) { return ['success' => false, 'message' => 'Customer package was not found.']; }
        $payload = [
            'package_title' => trim((string)($data['package_title'] ?? 'Customer Package')),
            'package_status' => trim((string)($data['package_status'] ?? 'ready_for_review')),
            'customer_scope' => (string)($data['customer_scope'] ?? ''),
            'exclusions' => (string)($data['exclusions'] ?? ''),
            'payment_schedule' => (string)($data['payment_schedule'] ?? ''),
            'internal_handoff' => (string)($data['internal_handoff'] ?? ''),
            'embed_notes' => (string)($data['embed_notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_customer_packages', $payload);
        return ['success' => true, 'message' => 'Customer package saved.'];
    }

    public function mark_customer_package_ready(int $id): array
    {
        if (!$this->get_customer_package($id)) { return ['success' => false, 'message' => 'Customer package was not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_customer_packages', [
            'package_status' => 'ready_to_send',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Customer package marked Ready To Send.'];
    }

    private function build_internal_handoff_from_estimate(array $estimate, array $lines): string
    {
        $text = "Internal Project Handoff\n\n";
        $text .= "AI Estimate ID: " . (int)$estimate['id'] . "\n";
        $text .= "Customer ID: " . (int)($estimate['customer_id'] ?? 0) . "\n";
        $text .= "Location: " . (string)($estimate['location'] ?? '') . "\n";
        $text .= "Total: $" . number_format((float)($estimate['total'] ?? 0), 2) . "\n";
        $text .= "Confidence: " . (string)($estimate['pricing_confidence'] ?? '0') . "%\n";
        $text .= "Field Verified: " . (!empty($estimate['field_verified']) ? 'Yes' : 'No') . "\n\n";
        $text .= "Production Checklist:\n";
        $text .= "- Verify measurements before ordering materials.\n";
        $text .= "- Confirm material selections and colors with customer.\n";
        $text .= "- Confirm permit, HOA, engineering, or inspection requirements.\n";
        $text .= "- Confirm access, parking, staging, demolition, hauling, and cleanup.\n";
        $text .= "- Review exclusions before customer signature.\n\n";
        $text .= "Line Items:\n";
        foreach ($lines as $line) {
            $text .= '- ' . (string)$line['description'] . ' | Qty ' . (string)$line['quantity'] . ' ' . (string)$line['unit'] . ' | Rate $' . number_format((float)$line['unit_rate'], 2) . ' | Total $' . number_format((float)$line['line_total'], 2) . "\n";
        }
        return $text;
    }

    public function get_field_verifications(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_field_verifications')) { return []; }
        $this->apply_filters($filters, ['verification_title', 'jobsite_address', 'field_status', 'measurement_summary', 'photo_notes']);
        if (!empty($filters['field_status'])) { $this->db->where('field_status', trim((string)$filters['field_status'])); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_field_verifications')->result_array();
    }

    public function get_field_verification(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_field_verifications')) { return null; }
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_field_verifications')->row_array() ?: null;
    }

    public function save_field_verification(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $payload = [
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'verification_title' => trim((string)($data['verification_title'] ?? 'Field Verification')),
            'jobsite_address' => trim((string)($data['jobsite_address'] ?? '')),
            'measurement_summary' => (string)($data['measurement_summary'] ?? ''),
            'inspection_checklist' => (string)($data['inspection_checklist'] ?? ''),
            'photo_notes' => (string)($data['photo_notes'] ?? ''),
            'field_status' => trim((string)($data['field_status'] ?? 'pending')),
            'ready_for_estimate' => !empty($data['ready_for_estimate']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['ready_for_estimate'] === 1) { $payload['verified_by'] = get_staff_user_id(); $payload['verified_at'] = date('Y-m-d H:i:s'); }
        if ($id > 0 && $this->get_field_verification($id)) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_field_verifications', $payload);
            $this->sync_field_verification_to_estimate($id);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_field_verifications', $payload);
        $newId = (int)$this->db->insert_id();
        $this->sync_field_verification_to_estimate($newId);
        return $newId;
    }

    public function create_field_verification_from_estimate(int $estimateId): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        $id = $this->save_field_verification([
            'ai_estimate_id' => $estimateId,
            'customer_id' => (int)($estimate['customer_id'] ?? 0),
            'project_id' => (int)($estimate['project_id'] ?? 0),
            'verification_title' => 'Field Verification - ' . (string)$estimate['title'],
            'jobsite_address' => (string)($estimate['location'] ?? ''),
            'measurement_summary' => (string)($estimate['measurement_notes'] ?? ''),
            'inspection_checklist' => "Confirm measurements\nConfirm material selections\nConfirm access and staging\nConfirm permit/HOA needs\nConfirm exclusions",
            'photo_notes' => 'Review all photos attached to this AI estimate before approval.',
            'field_status' => 'pending',
        ]);
        return ['success' => true, 'message' => 'Field verification created.', 'verification_id' => $id];
    }

    public function mark_field_verification_ready(int $id): array
    {
        $verification = $this->get_field_verification($id);
        if (!$verification) { return ['success' => false, 'message' => 'Field verification was not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_field_verifications', [
            'field_status' => 'ready_for_estimate',
            'ready_for_estimate' => 1,
            'verified_by' => get_staff_user_id(),
            'verified_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->sync_field_verification_to_estimate($id);
        return ['success' => true, 'message' => 'Field verification marked ready for estimate.'];
    }

    public function delete_field_verification(int $id): void
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_field_verifications')) { $this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_field_verifications'); }
    }

    public function mass_delete_field_verifications(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids) || !usi_smartchoice_seo_table_exists('usi_ai_field_verifications')) { return 0; }
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_field_verifications');
        return $this->db->affected_rows();
    }

    private function sync_field_verification_to_estimate(int $verificationId): void
    {
        $verification = $this->get_field_verification($verificationId);
        if (!$verification || empty($verification['ai_estimate_id'])) { return; }
        $this->db->where('id', (int)$verification['ai_estimate_id'])->update(db_prefix() . 'usi_ai_estimates', [
            'field_verified' => !empty($verification['ready_for_estimate']) ? 1 : 0,
            'measurement_notes' => (string)($verification['measurement_summary'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_project_handoffs(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')) { return []; }
        $this->apply_filters($filters, ['handoff_title', 'project_name', 'handoff_status', 'production_notes', 'material_notes', 'permit_notes']);
        if (!empty($filters['handoff_status'])) { $this->db->where('handoff_status', trim((string)$filters['handoff_status'])); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_project_handoffs')->result_array();
    }

    public function get_project_handoff(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')) { return null; }
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_project_handoffs')->row_array() ?: null;
    }

    public function get_project_handoff_tasks(int $handoffId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_project_tasks')) { return []; }
        return $this->db->where('handoff_id', $handoffId)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_project_tasks')->result_array();
    }

    public function save_project_handoff(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $payload = [
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_package_id' => (int)($data['customer_package_id'] ?? 0),
            'field_verification_id' => (int)($data['field_verification_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'handoff_title' => trim((string)($data['handoff_title'] ?? 'Project Handoff')),
            'project_name' => trim((string)($data['project_name'] ?? '')),
            'handoff_status' => trim((string)($data['handoff_status'] ?? 'draft')),
            'assigned_staff_id' => (int)($data['assigned_staff_id'] ?? 0),
            'start_date' => trim((string)($data['start_date'] ?? '')) ?: null,
            'due_date' => trim((string)($data['due_date'] ?? '')) ?: null,
            'production_notes' => (string)($data['production_notes'] ?? ''),
            'material_notes' => (string)($data['material_notes'] ?? ''),
            'permit_notes' => (string)($data['permit_notes'] ?? ''),
            'task_plan' => (string)($data['task_plan'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0 && $this->get_project_handoff($id)) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_project_handoffs', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_project_handoffs', $payload);
        return (int)$this->db->insert_id();
    }

    public function build_project_handoff_from_estimate(int $estimateId): array
    {
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate was not found.']; }
        if ((string)($estimate['review_status'] ?? '') !== 'approved') {
            return ['success' => false, 'message' => 'Approve the AI estimate before project handoff.'];
        }
        $lines = $this->get_ai_estimate_lines($estimateId);
        $taskPlan = $this->default_project_task_plan($estimate, $lines);
        $id = $this->save_project_handoff([
            'ai_estimate_id' => $estimateId,
            'customer_package_id' => (int)($estimate['customer_package_id'] ?? 0),
            'customer_id' => (int)($estimate['customer_id'] ?? 0),
            'project_id' => (int)($estimate['project_id'] ?? 0),
            'handoff_title' => 'Project Handoff - ' . (string)$estimate['title'],
            'project_name' => (string)$estimate['title'],
            'handoff_status' => 'draft',
            'assigned_staff_id' => get_staff_user_id(),
            'production_notes' => (string)($estimate['customer_scope'] ?? $estimate['scope_summary'] ?? ''),
            'material_notes' => (string)($estimate['materials_summary'] ?? ''),
            'permit_notes' => 'Verify permit, HOA, engineering, utility, and inspection requirements before production start.',
            'task_plan' => $taskPlan,
        ]);
        $this->generate_project_handoff_tasks($id);
        return ['success' => true, 'message' => 'Project handoff generated.', 'handoff_id' => $id];
    }

    public function generate_project_handoff_tasks(int $handoffId): array
    {
        $handoff = $this->get_project_handoff($handoffId);
        if (!$handoff) { return ['success' => false, 'message' => 'Project handoff was not found.']; }
        if (!usi_smartchoice_seo_table_exists('usi_ai_project_tasks')) { usi_smartchoice_seo_ensure_schema(); }
        $this->db->where('handoff_id', $handoffId)->delete(db_prefix() . 'usi_ai_project_tasks');
        $tasks = $this->parse_task_plan((string)($handoff['task_plan'] ?? ''));
        if (empty($tasks)) { $tasks = $this->default_task_rows(); }
        foreach ($tasks as $task) {
            $this->db->insert(db_prefix() . 'usi_ai_project_tasks', [
                'handoff_id' => $handoffId,
                'task_name' => $task['name'],
                'task_type' => $task['type'],
                'task_status' => 'pending',
                'assigned_staff_id' => (int)($handoff['assigned_staff_id'] ?? 0),
                'due_date' => $handoff['due_date'] ?: null,
                'task_notes' => $task['notes'],
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Project handoff tasks generated.'];
    }

    public function mark_project_handoff_ready(int $id): array
    {
        if (!$this->get_project_handoff($id)) { return ['success' => false, 'message' => 'Project handoff was not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_project_handoffs', [
            'handoff_status' => 'ready_for_project',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Project handoff marked Ready For Project.'];
    }

    public function create_crm_tasks_from_handoff(int $id): array
    {
        $handoff = $this->get_project_handoff($id);
        if (!$handoff) { return ['success' => false, 'message' => 'Project handoff was not found.']; }
        if (!usi_smartchoice_seo_table_exists('tasks')) { return ['success' => false, 'message' => 'CRM tasks table was not found.']; }
        $tasks = $this->get_project_handoff_tasks($id);
        if (empty($tasks)) { $this->generate_project_handoff_tasks($id); $tasks = $this->get_project_handoff_tasks($id); }
        $created = 0;
        foreach ($tasks as $task) {
            if (!empty($task['crm_task_id'])) { continue; }
            $payload = [
                'name' => (string)$task['task_name'],
                'description' => (string)$task['task_notes'],
                'priority' => 2,
                'dateadded' => date('Y-m-d H:i:s'),
                'startdate' => date('Y-m-d'),
                'duedate' => $task['due_date'] ?: null,
                'addedfrom' => get_staff_user_id(),
                'status' => 1,
                'rel_id' => (int)($handoff['project_id'] ?? 0),
                'rel_type' => !empty($handoff['project_id']) ? 'project' : '',
            ];
            $payload = $this->filter_existing_columns('tasks', $payload);
            $this->db->insert(db_prefix() . 'tasks', $payload);
            $taskId = (int)$this->db->insert_id();
            if ($taskId > 0) {
                $created++;
                $this->db->where('id', (int)$task['id'])->update(db_prefix() . 'usi_ai_project_tasks', [
                    'crm_task_id' => $taskId,
                    'task_status' => 'created_in_crm',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                if (!empty($task['assigned_staff_id']) && usi_smartchoice_seo_table_exists('task_assigned')) {
                    $this->db->insert(db_prefix() . 'task_assigned', ['staffid' => (int)$task['assigned_staff_id'], 'taskid' => $taskId, 'assigned_from' => get_staff_user_id()]);
                }
            }
        }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_project_handoffs', ['handoff_status' => 'tasks_created', 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => $created . ' CRM task(s) created from the handoff.'];
    }

    public function delete_project_handoff(int $id): void
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_project_tasks')) { $this->db->where('handoff_id', $id)->delete(db_prefix() . 'usi_ai_project_tasks'); }
        if (usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')) { $this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_project_handoffs'); }
    }

    public function mass_delete_project_handoffs(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids) || !usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')) { return 0; }
        if (usi_smartchoice_seo_table_exists('usi_ai_project_tasks')) { $this->db->where_in('handoff_id', $ids)->delete(db_prefix() . 'usi_ai_project_tasks'); }
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_project_handoffs');
        return $this->db->affected_rows();
    }

    private function default_project_task_plan(array $estimate, array $lines): string
    {
        $plan = "Confirm final scope and customer selections\nVerify measurements and field conditions\nPrepare material order list\nConfirm permit, HOA, engineering, and inspection requirements\nSchedule production crew and start date\nConfirm customer access, parking, protection, and cleanup\nFinal manager review before production";
        foreach ($lines as $line) { $plan .= "\nProduction item: " . (string)$line['description']; }
        return $plan;
    }

    private function parse_task_plan(string $plan): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $plan) as $line) {
            $line = trim($line);
            if ($line === '') { continue; }
            $rows[] = ['name' => mb_substr($line, 0, 180), 'type' => 'production', 'notes' => $line];
        }
        return $rows;
    }

    private function default_task_rows(): array
    {
        return [
            ['name' => 'Confirm final scope', 'type' => 'production', 'notes' => 'Review approved AI estimate, customer package, exclusions, and field verification.'],
            ['name' => 'Verify measurements', 'type' => 'field', 'notes' => 'Confirm all dimensions before ordering material.'],
            ['name' => 'Prepare materials', 'type' => 'materials', 'notes' => 'Build order list and confirm availability.'],
            ['name' => 'Schedule production', 'type' => 'scheduling', 'notes' => 'Assign crew, start date, access, parking, and cleanup plan.'],
        ];
    }


    public function check_ai_api_key(): array
    {
        $key = trim((string)get_option('usi_smartchoice_ai_api_key'));
        if ($key === '') {
            return ['success' => false, 'message' => 'No API key is saved in Settings.'];
        }
        if (strlen($key) < 20) {
            return ['success' => false, 'message' => 'The saved API key is too short. Re-enter the key in Settings.'];
        }
        if (!function_exists('curl_init')) {
            return ['success' => true, 'message' => 'API key is saved. Server cURL is not available, so live billing/key validation cannot run from this Bluehost environment.'];
        }
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $key]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'message' => 'API key connected successfully.'];
        }
        if ($code === 401) { return ['success' => false, 'message' => 'API key rejected. Check the key value.']; }
        if ($code === 429) { return ['success' => false, 'message' => 'API key reached rate limit or billing/quota issue. Check provider billing.']; }
        return ['success' => false, 'message' => 'API key check did not pass. HTTP ' . $code . ($error ? ' - ' . $error : '')];
    }

    private function create_lead_from_voice(string $command): array
    {
        if (!usi_smartchoice_seo_table_exists('leads')) {
            return ['success' => false, 'message' => 'CRM leads table was not found.'];
        }
        $name = $this->extract_voice_lead_name($command);
        $email = $this->extract_voice_email($command);
        $phone = $this->extract_voice_phone($command);
        $status = $this->first_table_id('leads_status', 'id', ['isdefault' => 1]);
        if ($status <= 0) { $status = $this->first_table_id('leads_status', 'id'); }
        $source = $this->first_table_id('leads_sources', 'id');

        $payload = [
            'name' => $name,
            'description' => 'Created by Smart Choice AI Voice Assistant from command: ' . $command,
            'status' => $status,
            'source' => $source,
            'assigned' => get_staff_user_id(),
            'addedfrom' => get_staff_user_id(),
            'dateadded' => date('Y-m-d H:i:s'),
            'lastcontact' => null,
            'hash' => function_exists('app_generate_hash') ? app_generate_hash() : md5(uniqid((string)mt_rand(), true)),
        ];
        if ($email !== '') { $payload['email'] = $email; }
        if ($phone !== '') { $payload['phonenumber'] = $phone; }
        $payload = $this->filter_existing_columns('leads', $payload);
        if (empty($payload['name'])) {
            return ['success' => false, 'message' => 'Lead name could not be created from the voice command.'];
        }
        $this->db->insert(db_prefix() . 'leads', $payload);
        $leadId = (int)$this->db->insert_id();
        if ($leadId <= 0) {
            return ['success' => false, 'message' => 'CRM did not create the lead. Review required fields in the Leads module.'];
        }
        return [
            'success' => true,
            'message' => 'Lead created from voice command.',
            'lead_id' => $leadId,
            'redirect_url' => admin_url('leads/index/' . $leadId),
        ];
    }


    public function memory_counts(): array
    {
        return [
            'memory_items' => $this->safe_count('usi_ai_memory_items'),
            'memory_runs' => $this->safe_count('usi_ai_memory_runs'),
            'customers_indexed' => $this->count_distinct_memory('customer_id'),
            'projects_indexed' => $this->count_distinct_memory('project_id'),
            'estimates_indexed' => $this->count_memory_by_source('crm_estimate') + $this->count_memory_by_source('ai_estimate'),
        ];
    }

    public function get_memory_items(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return []; }
        if (!empty($filters['search'])) {
            $search = trim((string)$filters['search']);
            $this->db->group_start();
            $this->db->like('source_title', $search);
            $this->db->or_like('memory_text', $search);
            $this->db->or_like('summary', $search);
            $this->db->or_like('keywords', $search);
            $this->db->group_end();
        }
        foreach (['source_type','service_category','memory_status','city'] as $field) {
            if (!empty($filters[$field])) { $this->db->where($field, trim((string)$filters[$field])); }
        }
        if (!empty($filters['customer_id'])) { $this->db->where('customer_id', (int)$filters['customer_id']); }
        if (!empty($filters['project_id'])) { $this->db->where('project_id', (int)$filters['project_id']); }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(500);
        return $this->db->get(db_prefix() . 'usi_ai_memory_items')->result_array();
    }

    public function search_memory(string $query): array
    {
        $query = trim($query);
        if ($query === '') { return $this->get_memory_items([]); }
        return $this->get_memory_items(['search' => $query]);
    }

    public function get_memory_item(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return null; }
        return $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_memory_items')->row_array() ?: null;
    }

    public function get_memory_links(int $memoryItemId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_links')) { return []; }
        return $this->db->where('memory_item_id', $memoryItemId)->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_memory_links')->result_array();
    }

    public function get_memory_runs(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_runs')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix() . 'usi_ai_memory_runs')->result_array();
    }

    public function save_memory_item(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $payload = [
            'source_type' => trim((string)($data['source_type'] ?? 'manual')),
            'source_id' => (int)($data['source_id'] ?? 0),
            'source_number' => trim((string)($data['source_number'] ?? '')),
            'source_title' => trim((string)($data['source_title'] ?? 'Manual Memory Item')),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'related_module' => trim((string)($data['related_module'] ?? 'sammy_ai')),
            'city' => trim((string)($data['city'] ?? '')),
            'service_category' => trim((string)($data['service_category'] ?? 'general')),
            'memory_text' => (string)($data['memory_text'] ?? ''),
            'summary' => (string)($data['summary'] ?? ''),
            'keywords' => (string)($data['keywords'] ?? ''),
            'amount_total' => (float)($data['amount_total'] ?? 0),
            'record_date' => !empty($data['record_date']) ? $data['record_date'] : null,
            'memory_status' => trim((string)($data['memory_status'] ?? 'active')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['memory_text'] === '') { $payload['memory_text'] = $payload['source_title']; }
        if ($payload['summary'] === '') { $payload['summary'] = $this->memory_short_summary($payload['memory_text']); }
        if ($id > 0 && $this->get_memory_item($id)) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_memory_items', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_memory_items', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_memory_item(int $id): bool
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return false; }
        $this->db->where('memory_item_id', $id)->delete(db_prefix() . 'usi_ai_memory_links');
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_memory_items');
    }

    public function mass_delete_memory_items(array $ids): int
    {
        $ids = $this->clean_ids($ids);
        if (!$ids) { return 0; }
        $this->db->where_in('memory_item_id', $ids)->delete(db_prefix() . 'usi_ai_memory_links');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_memory_items');
        return $this->db->affected_rows();
    }

    public function rebuild_memory_index(): array
    {
        usi_smartchoice_seo_ensure_schema();
        $started = date('Y-m-d H:i:s');
        $recordsScanned = 0;
        $itemsIndexed = 0;
        $sources = [];

        $itemsIndexed += $this->index_ai_estimates_memory($recordsScanned); $sources[] = 'Sammy AI estimates';
        $itemsIndexed += $this->index_price_memory($recordsScanned); $sources[] = 'Pricing engine';
        $itemsIndexed += $this->index_customer_packages_memory($recordsScanned); $sources[] = 'Customer packages';
        $itemsIndexed += $this->index_project_handoffs_memory($recordsScanned); $sources[] = 'Project handoffs';
        $itemsIndexed += $this->index_communications_memory($recordsScanned); $sources[] = 'Customer communications';
        $itemsIndexed += $this->index_crm_estimates_memory($recordsScanned); $sources[] = 'CRM estimates';

        $this->db->insert(db_prefix() . 'usi_ai_memory_runs', [
            'run_type' => 'manual_rebuild',
            'run_status' => 'completed',
            'source_summary' => implode(', ', $sources),
            'items_indexed' => $itemsIndexed,
            'records_scanned' => $recordsScanned,
            'started_at' => $started,
            'finished_at' => date('Y-m-d H:i:s'),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'AI Memory Engine rebuilt. Records scanned: ' . $recordsScanned . '. Memory items indexed or refreshed: ' . $itemsIndexed . '.'];
    }

    private function upsert_memory_item(array $payload): bool
    {
        $sourceType = trim((string)($payload['source_type'] ?? 'manual'));
        $sourceId = (int)($payload['source_id'] ?? 0);
        if ($sourceType === '' || $sourceId <= 0) { return false; }
        $existing = $this->db->select('id')->where('source_type', $sourceType)->where('source_id', $sourceId)->get(db_prefix() . 'usi_ai_memory_items')->row_array();
        $payload['updated_at'] = date('Y-m-d H:i:s');
        if (empty($payload['summary'])) { $payload['summary'] = $this->memory_short_summary((string)($payload['memory_text'] ?? '')); }
        if ($existing) {
            $this->db->where('id', (int)$existing['id'])->update(db_prefix() . 'usi_ai_memory_items', $payload);
            return true;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_memory_items', $payload);
        return true;
    }

    private function index_ai_estimates_memory(int &$recordsScanned): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimates')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'usi_ai_estimates')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = trim(($row['scope_summary'] ?? '') . "
" . ($row['measurement_notes'] ?? '') . "
" . ($row['materials_summary'] ?? '') . "
" . ($row['labor_summary'] ?? '') . "
" . ($row['customer_scope'] ?? ''));
            if ($text === '') { $text = (string)($row['title'] ?? 'AI Estimate'); }
            if ($this->upsert_memory_item([
                'source_type' => 'ai_estimate', 'source_id' => (int)$row['id'], 'source_number' => 'AI-' . (int)$row['id'], 'source_title' => (string)($row['title'] ?? 'AI Estimate'),
                'customer_id' => (int)($row['customer_id'] ?? 0), 'lead_id' => (int)($row['lead_id'] ?? 0), 'project_id' => (int)($row['project_id'] ?? 0),
                'related_module' => 'ai_estimates', 'city' => (string)($row['location'] ?? ''), 'service_category' => $this->guess_service_category($text), 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'amount_total' => (float)($row['total'] ?? 0), 'record_date' => !empty($row['created_at']) ? substr($row['created_at'],0,10) : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function index_price_memory(int &$recordsScanned): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_price_index')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'usi_ai_price_index')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = trim((string)($row['service_keyword'] ?? '') . "
" . (string)($row['description'] ?? '') . "
Unit: " . (string)($row['unit'] ?? '') . "
Rate: " . (string)($row['unit_rate'] ?? '') . "
Total: " . (string)($row['line_total'] ?? ''));
            if ($this->upsert_memory_item([
                'source_type' => 'price_index', 'source_id' => (int)$row['id'], 'source_number' => 'PRICE-' . (int)$row['id'], 'source_title' => (string)($row['service_keyword'] ?? 'Pricing Item'),
                'related_module' => 'pricing_engine', 'city' => (string)($row['city'] ?? ''), 'service_category' => $this->guess_service_category($text), 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'amount_total' => (float)($row['line_total'] ?? 0), 'record_date' => !empty($row['created_at']) ? substr($row['created_at'],0,10) : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function index_customer_packages_memory(int &$recordsScanned): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_customer_packages')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'usi_ai_customer_packages')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = trim(($row['customer_scope'] ?? '') . "
" . ($row['exclusions'] ?? '') . "
" . ($row['payment_schedule'] ?? '') . "
" . ($row['internal_handoff'] ?? ''));
            if ($text === '') { $text = (string)($row['package_title'] ?? 'Customer Package'); }
            if ($this->upsert_memory_item([
                'source_type' => 'customer_package', 'source_id' => (int)$row['id'], 'source_number' => 'PKG-' . (int)$row['id'], 'source_title' => (string)($row['package_title'] ?? 'Customer Package'),
                'customer_id' => (int)($row['customer_id'] ?? 0), 'related_module' => 'customer_packages', 'service_category' => $this->guess_service_category($text), 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'amount_total' => 0, 'record_date' => !empty($row['created_at']) ? substr($row['created_at'],0,10) : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function index_project_handoffs_memory(int &$recordsScanned): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'usi_ai_project_handoffs')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = trim(($row['production_notes'] ?? '') . "
" . ($row['material_notes'] ?? '') . "
" . ($row['permit_notes'] ?? '') . "
" . ($row['task_plan'] ?? ''));
            if ($text === '') { $text = (string)($row['handoff_title'] ?? 'Project Handoff'); }
            if ($this->upsert_memory_item([
                'source_type' => 'project_handoff', 'source_id' => (int)$row['id'], 'source_number' => 'HANDOFF-' . (int)$row['id'], 'source_title' => (string)($row['handoff_title'] ?? 'Project Handoff'),
                'customer_id' => (int)($row['customer_id'] ?? 0), 'project_id' => (int)($row['project_id'] ?? 0), 'related_module' => 'project_handoffs', 'service_category' => $this->guess_service_category($text), 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'record_date' => !empty($row['created_at']) ? substr($row['created_at'],0,10) : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function index_communications_memory(int &$recordsScanned): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_communications')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'usi_ai_communications')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = trim((string)($row['subject'] ?? '') . "
" . (string)($row['message_body'] ?? ''));
            if ($text === '') { continue; }
            if ($this->upsert_memory_item([
                'source_type' => 'customer_communication', 'source_id' => (int)$row['id'], 'source_number' => 'COM-' . (int)$row['id'], 'source_title' => (string)($row['subject'] ?? 'Customer Communication'),
                'customer_id' => (int)($row['customer_id'] ?? 0), 'lead_id' => (int)($row['lead_id'] ?? 0), 'related_module' => 'communications', 'service_category' => 'communication', 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'record_date' => !empty($row['created_at']) ? substr($row['created_at'],0,10) : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function index_crm_estimates_memory(int &$recordsScanned): int
    {
        if (!$this->db->table_exists(db_prefix() . 'estimates')) { return 0; }
        $rows = $this->db->order_by('id','DESC')->limit(1000)->get(db_prefix() . 'estimates')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $recordsScanned++;
            $text = 'CRM estimate ' . (string)($row['number'] ?? $row['id']) . '. Total: ' . (string)($row['total'] ?? '0') . '. Admin note: ' . (string)($row['adminnote'] ?? '') . '. Client note: ' . (string)($row['clientnote'] ?? '') . '. Terms: ' . (string)($row['terms'] ?? '');
            if ($this->upsert_memory_item([
                'source_type' => 'crm_estimate', 'source_id' => (int)$row['id'], 'source_number' => (string)($row['number'] ?? $row['id']), 'source_title' => 'CRM Estimate #' . (string)($row['number'] ?? $row['id']),
                'customer_id' => (int)($row['clientid'] ?? 0), 'related_module' => 'estimates', 'service_category' => $this->guess_service_category($text), 'memory_text' => $text,
                'keywords' => $this->extract_memory_keywords($text), 'amount_total' => (float)($row['total'] ?? 0), 'record_date' => !empty($row['date']) ? $row['date'] : null, 'memory_status' => 'active'
            ])) { $count++; }
        }
        return $count;
    }

    private function memory_short_summary(string $text): string
    {
        $text = trim(strip_tags($text));
        $text = preg_replace('/\s+/', ' ', $text) ?: '';
        return strlen($text) > 240 ? substr($text, 0, 237) . '...' : $text;
    }

    private function extract_memory_keywords(string $text): string
    {
        $lower = strtolower($text);
        $keywords = [];
        foreach (['window','door','roof','kitchen','bathroom','tile','floor','drywall','paint','electrical','plumbing','hvac','concrete','permit','inspection','estimate','proposal','invoice','project','customer'] as $word) {
            if (strpos($lower, $word) !== false) { $keywords[] = $word; }
        }
        return implode(', ', array_unique($keywords));
    }

    private function guess_service_category(string $text): string
    {
        $lower = strtolower($text);
        $map = [
            'windows' => ['window','impact'], 'doors' => ['door'], 'roofing' => ['roof','shingle'], 'kitchen' => ['kitchen','cabinet'], 'bathroom' => ['bathroom','shower','toilet'],
            'flooring' => ['floor','tile','vinyl'], 'drywall_paint' => ['drywall','paint'], 'electrical' => ['electrical','panel','outlet'], 'plumbing' => ['plumbing','pipe','drain'],
            'hvac' => ['hvac','duct','air handler'], 'concrete' => ['concrete','driveway','slab'], 'permitting' => ['permit','inspection']
        ];
        foreach ($map as $category => $needles) { foreach ($needles as $needle) { if (strpos($lower, $needle) !== false) { return $category; } } }
        return 'general';
    }

    private function count_distinct_memory(string $field): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return 0; }
        $row = $this->db->select('COUNT(DISTINCT `' . $this->db->escape_str($field) . '`) AS total', false)->where($field . ' >', 0)->get(db_prefix() . 'usi_ai_memory_items')->row_array();
        return (int)($row['total'] ?? 0);
    }

    private function count_memory_by_source(string $sourceType): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return 0; }
        return (int)$this->db->where('source_type', $sourceType)->count_all_results(db_prefix() . 'usi_ai_memory_items');
    }


    private function log_ai_action(int $commandId, string $actionName, string $crmModule, string $payload, string $status, string $message): void
    {
        $this->db->insert(db_prefix() . 'usi_ai_actions', [
            'ai_command_id' => $commandId,
            'action_name' => $actionName,
            'crm_module' => $crmModule,
            'payload_json' => json_encode(['command' => $payload]),
            'execution_status' => $status,
            'result_message' => $message,
            'executed_by' => get_staff_user_id(),
            'executed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function normalize_voice_command(string $command): string
    {
        $command = trim(strip_tags($command));
        $command = str_replace(['usismartchoice.com', 'usi smartchoice.com', 'just smart choice.com', 'justsmart choice.com'], 'justsmartchoice.com', $command);
        return preg_replace('/\s+/', ' ', $command) ?: '';
    }

    private function detect_voice_intent(string $command): string
    {
        $lower = strtolower($command);
        if (strpos($lower, 'create') !== false && strpos($lower, 'lead') !== false) { return 'create_lead'; }
        if (strpos($lower, 'task') !== false) { return 'create_task'; }
        if (strpos($lower, 'note') !== false) { return 'add_note'; }
        if (strpos($lower, 'estimate') !== false) { return 'estimate_assistant'; }
        if (strpos($lower, 'open') !== false) { return 'open_crm_page'; }
        return 'crm_assistant';
    }

    private function detect_target_module(string $command): string
    {
        $lower = strtolower($command);
        foreach (['leads', 'customers', 'projects', 'tasks', 'estimates', 'invoices', 'proposals', 'reports'] as $module) {
            if (strpos($lower, rtrim($module, 's')) !== false || strpos($lower, $module) !== false) { return $module; }
        }
        return 'crm';
    }

    private function detect_target_record_type(string $command): string
    {
        $module = $this->detect_target_module($command);
        return rtrim($module, 's');
    }


    public function document_counts(): array
    {
        return [
            'documents' => usi_smartchoice_seo_table_exists('usi_ai_documents') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_documents') : 0,
            'chunks' => usi_smartchoice_seo_table_exists('usi_ai_document_chunks') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_document_chunks') : 0,
            'manual' => $this->count_documents_by_type('manual'),
            'crm' => $this->count_documents_by_type('crm_record'),
            'indexed' => $this->count_documents_by_status('indexed'),
        ];
    }

    public function get_ai_documents(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_documents')) { return []; }
        if (!empty($filters['document_type'])) { $this->db->where('document_type', $filters['document_type']); }
        if (!empty($filters['document_status'])) { $this->db->where('document_status', $filters['document_status']); }
        if (!empty($filters['source_module'])) { $this->db->where('source_module', $filters['source_module']); }
        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $this->db->group_start();
            $this->db->like('document_title', $q);
            $this->db->or_like('document_text', $q);
            $this->db->or_like('ai_summary', $q);
            $this->db->or_like('ai_keywords', $q);
            $this->db->group_end();
        }
        return $this->db->order_by('id', 'DESC')->limit(200)->get(db_prefix() . 'usi_ai_documents')->result_array();
    }

    public function search_ai_documents(string $query): array
    {
        $query = trim($query);
        if ($query === '') { return $this->get_ai_documents(); }
        return $this->get_ai_documents(['q' => $query]);
    }

    public function get_ai_document(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_documents')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_documents')->row_array();
        return $row ?: null;
    }

    public function get_document_chunks(int $documentId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_document_chunks')) { return []; }
        return $this->db->where('document_id', $documentId)->order_by('chunk_order', 'ASC')->get(db_prefix() . 'usi_ai_document_chunks')->result_array();
    }

    public function get_document_runs(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_document_runs')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(20)->get(db_prefix() . 'usi_ai_document_runs')->result_array();
    }

    public function save_ai_document(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $now = date('Y-m-d H:i:s');
        $text = trim((string)($data['document_text'] ?? ''));
        $payload = [
            'document_title' => trim((string)($data['document_title'] ?? 'Untitled Document')),
            'document_type' => trim((string)($data['document_type'] ?? 'manual')),
            'source_module' => trim((string)($data['source_module'] ?? 'manual')),
            'source_id' => (int)($data['source_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'file_name' => trim((string)($data['file_name'] ?? '')),
            'file_path' => trim((string)($data['file_path'] ?? '')),
            'mime_type' => trim((string)($data['mime_type'] ?? '')),
            'document_text' => $text,
            'ai_summary' => trim((string)($data['ai_summary'] ?? $this->document_short_summary($text))),
            'ai_keywords' => trim((string)($data['ai_keywords'] ?? $this->extract_document_keywords($text))),
            'document_status' => trim((string)($data['document_status'] ?? 'indexed')),
            'updated_at' => $now,
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_documents', $payload);
            $this->refresh_document_chunks($id, $text);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = $now;
        $this->db->insert(db_prefix() . 'usi_ai_documents', $payload);
        $newId = (int)$this->db->insert_id();
        $this->refresh_document_chunks($newId, $text);
        return $newId;
    }

    public function delete_ai_document(int $id): bool
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_documents')) { return false; }
        if (usi_smartchoice_seo_table_exists('usi_ai_document_chunks')) { $this->db->where('document_id', $id)->delete(db_prefix() . 'usi_ai_document_chunks'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_documents');
    }

    public function mass_delete_ai_documents(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_ai_document((int)$id)) { $deleted++; } }
        return $deleted;
    }

    public function rebuild_document_index(): array
    {
        usi_smartchoice_seo_ensure_schema();
        $started = date('Y-m-d H:i:s');
        $documentsIndexed = 0;
        $chunksCreated = 0;
        $documentsIndexed += $this->index_memory_documents($chunksCreated);
        $documentsIndexed += $this->index_estimate_documents($chunksCreated);
        $run = [
            'run_type' => 'manual_rebuild',
            'run_status' => 'completed',
            'source_summary' => 'Indexed AI memory items and AI estimate records into Document Intelligence.',
            'documents_indexed' => $documentsIndexed,
            'chunks_created' => $chunksCreated,
            'started_at' => $started,
            'finished_at' => date('Y-m-d H:i:s'),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_document_runs', $run);
        return ['success' => true, 'message' => 'Document index rebuilt. Documents indexed: ' . $documentsIndexed . '. Chunks created: ' . $chunksCreated . '.'];
    }

    private function refresh_document_chunks(int $documentId, string $text): int
    {
        if ($documentId <= 0 || !usi_smartchoice_seo_table_exists('usi_ai_document_chunks')) { return 0; }
        $this->db->where('document_id', $documentId)->delete(db_prefix() . 'usi_ai_document_chunks');
        $clean = trim(strip_tags($text));
        if ($clean === '') { return 0; }
        $chunks = str_split($clean, 1800);
        $count = 0;
        foreach ($chunks as $idx => $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') { continue; }
            $this->db->insert(db_prefix() . 'usi_ai_document_chunks', [
                'document_id' => $documentId,
                'chunk_order' => $idx + 1,
                'chunk_title' => 'Chunk ' . ($idx + 1),
                'chunk_text' => $chunk,
                'ai_summary' => $this->document_short_summary($chunk),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }
        return $count;
    }

    private function index_memory_documents(int &$chunksCreated): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_memory_items')) { return 0; }
        $rows = $this->db->order_by('id', 'DESC')->limit(250)->get(db_prefix() . 'usi_ai_memory_items')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $title = 'Memory: ' . (string)($row['source_title'] ?? 'CRM Memory Item');
            $text = (string)($row['memory_text'] ?? '');
            $docId = $this->upsert_ai_document([
                'document_title' => $title,
                'document_type' => 'crm_record',
                'source_module' => 'usi_ai_memory_items',
                'source_id' => (int)($row['id'] ?? 0),
                'customer_id' => (int)($row['customer_id'] ?? 0),
                'lead_id' => (int)($row['lead_id'] ?? 0),
                'project_id' => (int)($row['project_id'] ?? 0),
                'document_text' => $text,
                'ai_summary' => (string)($row['summary'] ?? $this->document_short_summary($text)),
                'ai_keywords' => (string)($row['keywords'] ?? $this->extract_document_keywords($text)),
                'document_status' => 'indexed',
            ]);
            $chunksCreated += $this->refresh_document_chunks($docId, $text);
            $count++;
        }
        return $count;
    }

    private function index_estimate_documents(int &$chunksCreated): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_estimates')) { return 0; }
        $rows = $this->db->order_by('id', 'DESC')->limit(250)->get(db_prefix() . 'usi_ai_estimates')->result_array();
        $count = 0;
        foreach ($rows as $row) {
            $parts = [
                (string)($row['title'] ?? ''),
                (string)($row['scope_summary'] ?? ''),
                (string)($row['measurement_notes'] ?? ''),
                (string)($row['materials_summary'] ?? ''),
                (string)($row['labor_summary'] ?? ''),
                (string)($row['ai_observations'] ?? ''),
                (string)($row['customer_scope'] ?? ''),
            ];
            $text = trim(implode("\n\n", array_filter($parts)));
            $docId = $this->upsert_ai_document([
                'document_title' => 'AI Estimate: ' . (string)($row['title'] ?? 'Estimate ' . (int)$row['id']),
                'document_type' => 'estimate',
                'source_module' => 'usi_ai_estimates',
                'source_id' => (int)($row['id'] ?? 0),
                'customer_id' => (int)($row['customer_id'] ?? 0),
                'lead_id' => (int)($row['lead_id'] ?? 0),
                'project_id' => (int)($row['project_id'] ?? 0),
                'document_text' => $text,
                'ai_summary' => $this->document_short_summary($text),
                'ai_keywords' => $this->extract_document_keywords($text),
                'document_status' => 'indexed',
            ]);
            $chunksCreated += $this->refresh_document_chunks($docId, $text);
            $count++;
        }
        return $count;
    }

    private function upsert_ai_document(array $payload): int
    {
        $sourceModule = (string)($payload['source_module'] ?? 'manual');
        $sourceId = (int)($payload['source_id'] ?? 0);
        $existing = null;
        if ($sourceId > 0) {
            $existing = $this->db->where('source_module', $sourceModule)->where('source_id', $sourceId)->get(db_prefix() . 'usi_ai_documents')->row_array();
        }
        if ($existing) { return $this->save_ai_document($payload, (int)$existing['id']); }
        return $this->save_ai_document($payload, 0);
    }

    private function document_short_summary(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?: '');
        if ($text === '') { return 'No readable document text has been indexed yet.'; }
        return strlen($text) > 350 ? substr($text, 0, 350) . '...' : $text;
    }

    private function extract_document_keywords(string $text): string
    {
        $text = strtolower(strip_tags($text));
        $words = preg_split('/[^a-z0-9]+/', $text) ?: [];
        $stop = ['the','and','for','with','that','this','from','into','will','shall','are','was','were','you','your','our','has','have','not','job','work'];
        $counts = [];
        foreach ($words as $word) {
            if (strlen($word) < 4 || in_array($word, $stop, true)) { continue; }
            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }
        arsort($counts);
        return implode(', ', array_slice(array_keys($counts), 0, 20));
    }

    private function count_documents_by_type(string $type): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_documents')) { return 0; }
        return (int)$this->db->where('document_type', $type)->count_all_results(db_prefix() . 'usi_ai_documents');
    }

    private function count_documents_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_documents')) { return 0; }
        return (int)$this->db->where('document_status', $status)->count_all_results(db_prefix() . 'usi_ai_documents');
    }

    private function extract_voice_lead_name(string $command): string
    {
        $patterns = [
            '/lead\s+(?:called|named|for)\s+([^,\.]+?)(?:\s+with|\s+phone|\s+email|,|\.|$)/i',
            '/customer\s+(?:called|named|for)\s+([^,\.]+?)(?:\s+with|\s+phone|\s+email|,|\.|$)/i',
            '/name\s+(?:is\s+)?([^,\.]+?)(?:\s+with|\s+phone|\s+email|,|\.|$)/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $command, $match)) {
                $name = trim($match[1]);
                if ($name !== '') { return ucwords(strtolower($name)); }
            }
        }
        return 'Voice Lead ' . date('Y-m-d H:i');
    }

    private function extract_voice_email(string $command): string
    {
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $command, $match)) { return strtolower($match[0]); }
        return '';
    }

    private function extract_voice_phone(string $command): string
    {
        if (preg_match('/(?:phone|number|call)?\s*(\+?1?[\s\-\.]?\(?\d{3}\)?[\s\-\.]?\d{3}[\s\-\.]?\d{4})/i', $command, $match)) { return trim($match[1]); }
        return '';
    }

    private function first_table_id(string $table, string $column, array $where = []): int
    {
        if (!usi_smartchoice_seo_table_exists($table)) { return 0; }
        foreach ($where as $field => $value) { if ($this->db->field_exists($field, db_prefix() . $table)) { $this->db->where($field, $value); } }
        $row = $this->db->select($column)->order_by($column, 'ASC')->limit(1)->get(db_prefix() . $table)->row_array();
        return (int)($row[$column] ?? 0);
    }

    private function filter_existing_columns(string $table, array $payload): array
    {
        $filtered = [];
        foreach ($payload as $column => $value) {
            if ($this->db->field_exists($column, db_prefix() . $table)) { $filtered[$column] = $value; }
        }
        return $filtered;
    }

    private function build_command_preview(string $command): string
    {
        $command = trim($command);
        if ($command === '') { return ''; }
        $intent = $this->detect_voice_intent($command);
        if ($intent === 'create_lead') {
            return 'Ready to create a CRM lead after confirmation. Name: ' . $this->extract_voice_lead_name($command) . '. Email: ' . ($this->extract_voice_email($command) ?: 'Not detected') . '. Phone: ' . ($this->extract_voice_phone($command) ?: 'Not detected') . '.';
        }
        return 'Command captured for review. This action will stay in review-first mode before any CRM record is changed.';
    }

    public function takeoff_counts(): array
    {
        return [
            'takeoffs' => usi_smartchoice_seo_table_exists('usi_ai_takeoffs') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_takeoffs') : 0,
            'lines' => usi_smartchoice_seo_table_exists('usi_ai_takeoff_lines') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_takeoff_lines') : 0,
            'draft' => $this->count_takeoffs_by_status('draft'),
            'calculated' => $this->count_takeoffs_by_status('calculated'),
            'ready' => $this->count_takeoffs_by_status('ready_for_purchasing'),
        ];
    }

    public function get_takeoffs(array $filters = []): array
    {
        usi_smartchoice_seo_ensure_schema();
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['service_category'])) { $this->db->where('service_category', $filters['service_category']); }
        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $this->db->group_start();
            $this->db->like('title', $q);
            $this->db->or_like('measurement_notes', $q);
            $this->db->or_like('takeoff_summary', $q);
            $this->db->group_end();
        }
        return $this->db->order_by('id', 'DESC')->limit(200)->get(db_prefix() . 'usi_ai_takeoffs')->result_array();
    }

    public function get_takeoff(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_takeoffs')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_takeoffs')->row_array();
        return $row ?: null;
    }

    public function get_takeoff_lines(int $takeoffId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_takeoff_lines')) { return []; }
        return $this->db->where('takeoff_id', $takeoffId)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_takeoff_lines')->result_array();
    }

    public function save_takeoff(array $data, int $id = 0): int
    {
        usi_smartchoice_seo_ensure_schema();
        $now = date('Y-m-d H:i:s');
        $payload = [
            'title' => trim((string)($data['title'] ?? 'Material Takeoff ' . date('Y-m-d H:i'))),
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'service_category' => trim((string)($data['service_category'] ?? 'general')),
            'room_area' => trim((string)($data['room_area'] ?? '')),
            'measurement_notes' => trim((string)($data['measurement_notes'] ?? '')),
            'takeoff_summary' => trim((string)($data['takeoff_summary'] ?? '')),
            'waste_percent' => (float)($data['waste_percent'] ?? 10),
            'status' => trim((string)($data['status'] ?? 'draft')),
            'updated_at' => $now,
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_takeoffs', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = $now;
        $this->db->insert(db_prefix() . 'usi_ai_takeoffs', $payload);
        return (int)$this->db->insert_id();
    }

    public function build_takeoff_from_estimate(int $estimateId): array
    {
        usi_smartchoice_seo_ensure_schema();
        $estimate = $this->get_ai_estimate($estimateId);
        if (!$estimate) { return ['success' => false, 'message' => 'AI estimate not found.']; }
        $payload = [
            'title' => 'Takeoff - ' . ($estimate['title'] ?? ('AI Estimate #' . $estimateId)),
            'ai_estimate_id' => $estimateId,
            'customer_id' => (int)($estimate['customer_id'] ?? 0),
            'project_id' => (int)($estimate['project_id'] ?? 0),
            'service_category' => $this->detect_takeoff_category((string)($estimate['scope_summary'] ?? '')),
            'room_area' => (string)($estimate['location'] ?? ''),
            'measurement_notes' => (string)($estimate['measurement_notes'] ?? ''),
            'takeoff_summary' => (string)($estimate['scope_summary'] ?? ''),
            'waste_percent' => 10,
            'status' => 'draft',
        ];
        $id = $this->save_takeoff($payload, 0);
        $this->calculate_takeoff($id);
        return ['success' => true, 'message' => 'Material takeoff created from AI estimate.', 'takeoff_id' => $id];
    }

    public function calculate_takeoff(int $id): array
    {
        usi_smartchoice_seo_ensure_schema();
        $takeoff = $this->get_takeoff($id);
        if (!$takeoff) { return ['success' => false, 'message' => 'Material takeoff not found.']; }
        $this->db->where('takeoff_id', $id)->delete(db_prefix() . 'usi_ai_takeoff_lines');
        $text = strtolower(($takeoff['measurement_notes'] ?? '') . ' ' . ($takeoff['takeoff_summary'] ?? '') . ' ' . ($takeoff['service_category'] ?? ''));
        $waste = max(0, (float)($takeoff['waste_percent'] ?? 10));
        $lines = $this->build_takeoff_lines_from_text($text, $waste);
        $total = 0.0;
        foreach ($lines as $line) {
            $line['takeoff_id'] = $id;
            $line['created_at'] = date('Y-m-d H:i:s');
            $total += (float)$line['line_total'];
            $this->db->insert(db_prefix() . 'usi_ai_takeoff_lines', $line);
        }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_takeoffs', [
            'material_total' => $total,
            'status' => 'calculated',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Material takeoff calculated with ' . count($lines) . ' line items.'];
    }

    public function mark_takeoff_ready(int $id): array
    {
        if (!$this->get_takeoff($id)) { return ['success' => false, 'message' => 'Material takeoff not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_takeoffs', ['status' => 'ready_for_purchasing', 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Material takeoff marked ready for purchasing.'];
    }

    public function delete_takeoff(int $id): bool
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_takeoff_lines')) { $this->db->where('takeoff_id', $id)->delete(db_prefix() . 'usi_ai_takeoff_lines'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_takeoffs');
    }

    public function mass_delete_takeoffs(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_takeoff((int)$id)) { $deleted++; } }
        return $deleted;
    }

    private function count_takeoffs_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_takeoffs')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_takeoffs');
    }

    private function detect_takeoff_category(string $text): string
    {
        $lower = strtolower($text);
        foreach (['drywall','flooring','paint','roofing','windows','doors','concrete','electrical','plumbing','hvac','cabinet'] as $category) {
            if (strpos($lower, $category) !== false) { return $category; }
        }
        return 'general';
    }

    private function build_takeoff_lines_from_text(string $text, float $waste): array
    {
        $templates = [];
        if (strpos($text, 'drywall') !== false || strpos($text, 'sheetrock') !== false) {
            $templates[] = ['Drywall Sheets 4x12', 'drywall', 'sheet', 10, 16.00, 'Generated from drywall scope. Adjust quantity after field verification.'];
            $templates[] = ['Joint Compound', 'drywall', 'bucket', 2, 24.00, 'Default drywall finishing material.'];
            $templates[] = ['Drywall Tape', 'drywall', 'roll', 2, 8.50, 'Default drywall tape allowance.'];
        }
        if (strpos($text, 'floor') !== false || strpos($text, 'tile') !== false || strpos($text, 'vinyl') !== false) {
            $templates[] = ['Flooring Material', 'flooring', 'sq ft', 250, 3.50, 'Generated from flooring scope. Replace with exact measured area.'];
            $templates[] = ['Flooring Underlayment / Setting Material', 'flooring', 'allowance', 1, 185.00, 'Default flooring installation allowance.'];
        }
        if (strpos($text, 'paint') !== false) {
            $templates[] = ['Interior Paint', 'paint', 'gallon', 5, 38.00, 'Generated from painting scope.'];
            $templates[] = ['Paint Supplies', 'paint', 'allowance', 1, 75.00, 'Tape, plastic, rollers, brushes, and prep supplies.'];
        }
        if (strpos($text, 'window') !== false) { $templates[] = ['Window Unit Allowance', 'windows', 'each', 1, 425.00, 'Window allowance from scope. Replace with vendor quote.']; }
        if (strpos($text, 'door') !== false) { $templates[] = ['Door Unit Allowance', 'doors', 'each', 1, 275.00, 'Door allowance from scope. Replace with exact product quote.']; }
        if (strpos($text, 'concrete') !== false || strpos($text, 'slab') !== false) { $templates[] = ['Concrete Material Allowance', 'concrete', 'yard', 3, 165.00, 'Generated from concrete scope. Verify cubic yards.']; }
        if (empty($templates)) { $templates[] = ['General Material Allowance', 'general', 'allowance', 1, 250.00, 'Default starter allowance. Add measured line items before ordering.']; }
        $lines = [];
        foreach ($templates as $t) {
            [$name, $trade, $unit, $qty, $cost, $note] = $t;
            $wasteQty = round($qty * ($waste / 100), 2);
            $totalQty = round($qty + $wasteQty, 2);
            $lines[] = [
                'item_name' => $name,
                'trade' => $trade,
                'unit' => $unit,
                'quantity' => $qty,
                'waste_quantity' => $wasteQty,
                'total_quantity' => $totalQty,
                'unit_cost' => $cost,
                'line_total' => round($totalQty * $cost, 2),
                'source_note' => $note,
            ];
        }
        return $lines;
    }


    public function purchasing_counts(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')) { return ['purchase_orders'=>0,'vendors'=>0,'draft'=>0,'ordered'=>0,'received'=>0]; }
        return [
            'purchase_orders' => (int)$this->db->count_all_results(db_prefix() . 'usi_ai_purchase_orders'),
            'vendors' => usi_smartchoice_seo_table_exists('usi_ai_vendors') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_vendors') : 0,
            'draft' => $this->count_purchase_orders_by_status('draft'),
            'ready' => $this->count_purchase_orders_by_status('ready_to_order'),
            'ordered' => $this->count_purchase_orders_by_status('ordered'),
            'received' => $this->count_purchase_orders_by_status('received'),
        ];
    }

    public function get_vendors(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_vendors')) { return []; }
        $table = db_prefix() . 'usi_ai_vendors';
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['category'])) { $this->db->like('category', $filters['category']); }
        if (!empty($filters['q'])) { $this->db->group_start()->like('vendor_name', $filters['q'])->or_like('contact_name', $filters['q'])->or_like('notes', $filters['q'])->group_end(); }
        $this->db->order_by('preferred', 'DESC')->order_by('vendor_name', 'ASC');
        return $this->db->get($table)->result_array();
    }

    public function get_vendor(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_vendors')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_vendors')->row_array();
        return $row ?: null;
    }

    public function save_vendor(array $data, int $id = 0): int
    {
        $payload = [
            'vendor_name' => trim((string)($data['vendor_name'] ?? '')),
            'contact_name' => trim((string)($data['contact_name'] ?? '')),
            'email' => trim((string)($data['email'] ?? '')),
            'phone' => trim((string)($data['phone'] ?? '')),
            'website' => trim((string)($data['website'] ?? '')),
            'category' => trim((string)($data['category'] ?? 'general')) ?: 'general',
            'rating' => (float)($data['rating'] ?? 0),
            'preferred' => !empty($data['preferred']) ? 1 : 0,
            'status' => trim((string)($data['status'] ?? 'active')) ?: 'active',
            'notes' => trim((string)($data['notes'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['vendor_name'] === '') { $payload['vendor_name'] = 'Unnamed Vendor'; }
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_vendors', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_vendors', $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_vendor(int $id): bool
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_vendors')) { return false; }
        $this->db->where('vendor_id', $id)->update(db_prefix() . 'usi_ai_purchase_orders', ['vendor_id' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        if (usi_smartchoice_seo_table_exists('usi_ai_vendor_prices')) { $this->db->where('vendor_id', $id)->delete(db_prefix() . 'usi_ai_vendor_prices'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_vendors');
    }

    public function get_purchase_orders(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')) { return []; }
        $table = db_prefix() . 'usi_ai_purchase_orders';
        $vendorTable = db_prefix() . 'usi_ai_vendors';
        $this->db->select($table . '.*, ' . $vendorTable . '.vendor_name');
        $this->db->from($table);
        $this->db->join($vendorTable, $vendorTable . '.id = ' . $table . '.vendor_id', 'left');
        if (!empty($filters['status'])) { $this->db->where($table . '.status', $filters['status']); }
        if (!empty($filters['vendor_id'])) { $this->db->where($table . '.vendor_id', (int)$filters['vendor_id']); }
        if (!empty($filters['q'])) { $this->db->group_start()->like($table . '.title', $filters['q'])->or_like($table . '.po_number', $filters['q'])->or_like($table . '.vendor_notes', $filters['q'])->or_like($table . '.internal_notes', $filters['q'])->group_end(); }
        $this->db->order_by($table . '.updated_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_purchase_order(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_purchase_orders')->row_array();
        return $row ?: null;
    }

    public function get_purchase_order_lines(int $id): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_purchase_order_lines')) { return []; }
        return $this->db->where('purchase_order_id', $id)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_purchase_order_lines')->result_array();
    }

    public function save_purchase_order(array $data, int $id = 0): int
    {
        $payload = [
            'po_number' => trim((string)($data['po_number'] ?? '')),
            'title' => trim((string)($data['title'] ?? '')),
            'takeoff_id' => (int)($data['takeoff_id'] ?? 0),
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'vendor_id' => (int)($data['vendor_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'status' => trim((string)($data['status'] ?? 'draft')) ?: 'draft',
            'requested_date' => trim((string)($data['requested_date'] ?? '')) ?: null,
            'delivery_address' => trim((string)($data['delivery_address'] ?? '')),
            'vendor_notes' => trim((string)($data['vendor_notes'] ?? '')),
            'internal_notes' => trim((string)($data['internal_notes'] ?? '')),
            'delivery_fee' => (float)($data['delivery_fee'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['po_number'] === '') { $payload['po_number'] = $this->next_po_number(); }
        if ($payload['title'] === '') { $payload['title'] = 'Purchase Order ' . $payload['po_number']; }
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_purchase_orders', $payload); $this->recalculate_purchase_order($id); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_purchase_orders', $payload);
        $newId = (int)$this->db->insert_id();
        $this->recalculate_purchase_order($newId);
        return $newId;
    }

    public function create_purchase_order_from_takeoff(int $takeoffId): array
    {
        $takeoff = $this->get_takeoff($takeoffId);
        if (!$takeoff) { return ['success' => false, 'message' => 'Material takeoff not found.']; }
        $lines = $this->get_takeoff_lines($takeoffId);
        if (empty($lines)) { return ['success' => false, 'message' => 'Calculate material lines before creating a purchase order.']; }
        $po = [
            'po_number' => $this->next_po_number(),
            'title' => 'PO for ' . (string)$takeoff['title'],
            'takeoff_id' => $takeoffId,
            'ai_estimate_id' => (int)$takeoff['ai_estimate_id'],
            'customer_id' => (int)$takeoff['customer_id'],
            'project_id' => (int)$takeoff['project_id'],
            'status' => 'ready_to_order',
            'vendor_notes' => 'Generated from material takeoff. Review vendor, quantities, delivery address, and prices before ordering.',
            'internal_notes' => (string)$takeoff['takeoff_summary'],
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_purchase_orders', $po);
        $poId = (int)$this->db->insert_id();
        foreach ($lines as $line) {
            $this->db->insert(db_prefix() . 'usi_ai_purchase_order_lines', [
                'purchase_order_id' => $poId,
                'takeoff_line_id' => (int)$line['id'],
                'item_name' => (string)$line['item_name'],
                'category' => (string)$line['trade'],
                'unit' => (string)$line['unit'],
                'quantity' => (float)$line['total_quantity'],
                'unit_cost' => (float)$line['unit_cost'],
                'line_total' => round((float)$line['total_quantity'] * (float)$line['unit_cost'], 2),
                'vendor_source_note' => 'Copied from material takeoff line #' . (int)$line['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->recalculate_purchase_order($poId);
        return ['success' => true, 'message' => 'Purchase order created from material takeoff.', 'purchase_order_id' => $poId];
    }

    public function recalculate_purchase_order(int $id): array
    {
        $po = $this->get_purchase_order($id);
        if (!$po) { return ['success' => false, 'message' => 'Purchase order not found.']; }
        $lines = $this->get_purchase_order_lines($id);
        $subtotal = 0.0;
        foreach ($lines as $line) { $subtotal += (float)$line['line_total']; }
        $taxRate = (float)get_option('usi_smartchoice_ai_tax_percent');
        if ($taxRate <= 0) { $taxRate = 0.00; }
        $tax = round($subtotal * ($taxRate / 100), 2);
        $delivery = (float)($po['delivery_fee'] ?? 0);
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_purchase_orders', [
            'subtotal' => round($subtotal, 2),
            'tax_total' => $tax,
            'total' => round($subtotal + $tax + $delivery, 2),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Purchase order totals recalculated.'];
    }

    public function mark_purchase_order_status(int $id, string $status): array
    {
        if (!$this->get_purchase_order($id)) { return ['success' => false, 'message' => 'Purchase order not found.']; }
        $payload = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($status === 'ordered') { $payload['ordered_date'] = date('Y-m-d'); }
        if ($status === 'received') { $payload['received_date'] = date('Y-m-d'); }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_purchase_orders', $payload);
        return ['success' => true, 'message' => 'Purchase order marked ' . str_replace('_', ' ', $status) . '.'];
    }

    public function delete_purchase_order(int $id): bool
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_purchase_order_lines')) { $this->db->where('purchase_order_id', $id)->delete(db_prefix() . 'usi_ai_purchase_order_lines'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_purchase_orders');
    }

    public function mass_delete_purchase_orders(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_purchase_order((int)$id)) { $deleted++; } }
        return $deleted;
    }

    private function count_purchase_orders_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_purchase_orders');
    }

    private function next_po_number(): string
    {
        $prefix = 'SCAI-PO-' . date('Ymd') . '-';
        $count = 1;
        if (usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')) {
            $count = (int)$this->db->like('po_number', $prefix, 'after')->count_all_results(db_prefix() . 'usi_ai_purchase_orders') + 1;
        }
        return $prefix . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
    }



    public function schedule_counts(): array
    {
        return [
            'draft' => $this->count_schedules_by_status('draft'),
            'generated' => $this->count_schedules_by_status('generated'),
            'ready_for_calendar' => $this->count_schedules_by_status('ready_for_calendar'),
            'active' => $this->count_schedules_by_status('active'),
            'completed' => $this->count_schedules_by_status('completed'),
        ];
    }

    public function get_schedules(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_schedules')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_schedules');
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['schedule_type'])) { $this->db->where('schedule_type', $filters['schedule_type']); }
        if (!empty($filters['date_from'])) { $this->db->where('start_date >=', $filters['date_from']); }
        if (!empty($filters['date_to'])) { $this->db->where('start_date <=', $filters['date_to']); }
        return $this->db->order_by('id', 'DESC')->get()->result_array();
    }

    public function get_schedule(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_schedules')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_schedules')->row_array();
        return $row ?: null;
    }

    public function get_schedule_items(int $scheduleId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_schedule_items')) { return []; }
        return $this->db->where('schedule_id', $scheduleId)->order_by('item_order', 'ASC')->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_schedule_items')->result_array();
    }

    public function save_schedule(array $data, int $id = 0): int
    {
        $payload = [
            'title' => trim((string)($data['title'] ?? '')),
            'project_handoff_id' => (int)($data['project_handoff_id'] ?? 0),
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'schedule_type' => trim((string)($data['schedule_type'] ?? 'production')) ?: 'production',
            'status' => trim((string)($data['status'] ?? 'draft')) ?: 'draft',
            'start_date' => trim((string)($data['start_date'] ?? '')) ?: null,
            'end_date' => trim((string)($data['end_date'] ?? '')) ?: null,
            'crew_lead_id' => (int)($data['crew_lead_id'] ?? 0),
            'department_id' => (int)($data['department_id'] ?? 0),
            'duration_days' => (int)($data['duration_days'] ?? 0),
            'conflict_notes' => trim((string)($data['conflict_notes'] ?? '')),
            'schedule_summary' => trim((string)($data['schedule_summary'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($payload['title'] === '') { $payload['title'] = 'AI Schedule ' . date('Y-m-d H:i'); }
        if ($id > 0) { $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_schedules', $payload); return $id; }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_schedules', $payload);
        return (int)$this->db->insert_id();
    }

    public function create_schedule_from_handoff(int $handoffId): array
    {
        $handoff = $this->get_project_handoff($handoffId);
        if (!$handoff) { return ['success' => false, 'message' => 'Project handoff not found.']; }
        $scheduleId = $this->save_schedule([
            'title' => 'Schedule for ' . (string)$handoff['title'],
            'project_handoff_id' => $handoffId,
            'ai_estimate_id' => (int)($handoff['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($handoff['customer_id'] ?? 0),
            'project_id' => (int)($handoff['project_id'] ?? 0),
            'schedule_type' => 'production',
            'status' => 'draft',
            'duration_days' => 5,
            'schedule_summary' => 'Generated from project handoff. Review crew, dates, inspections, vendor lead times, and weather before moving to calendar.',
            'conflict_notes' => 'Check existing CRM calendar events and staff availability before final scheduling.',
        ]);
        $this->generate_schedule_items($scheduleId);
        return ['success' => true, 'message' => 'AI schedule created from project handoff.', 'schedule_id' => $scheduleId];
    }

    public function generate_schedule_items(int $scheduleId): array
    {
        $schedule = $this->get_schedule($scheduleId);
        if (!$schedule) { return ['success' => false, 'message' => 'Schedule not found.']; }
        if (usi_smartchoice_seo_table_exists('usi_ai_schedule_items')) { $this->db->where('schedule_id', $scheduleId)->delete(db_prefix() . 'usi_ai_schedule_items'); }
        $start = !empty($schedule['start_date']) ? strtotime((string)$schedule['start_date']) : strtotime(date('Y-m-d'));
        $items = [
            ['Pre Job Confirmation', 'admin', 1, 'Confirm customer, scope, access, materials, and crew readiness.'],
            ['Material Delivery Check', 'material', 1, 'Confirm purchase orders, vendor delivery, and missing items before crew arrival.'],
            ['Site Protection And Setup', 'production', 1, 'Protect property, verify work areas, and document starting conditions.'],
            ['Main Production Work', 'production', 2, 'Perform the approved scope of work according to the estimate and customer package.'],
            ['Inspection And Quality Check', 'inspection', 1, 'Verify code, quality, photos, punch list, and customer concerns.'],
            ['Final Cleanup And Closeout', 'closeout', 1, 'Clean jobsite, collect final photos, prepare customer closeout notes.'],
        ];
        $order = 1;
        $cursor = $start;
        foreach ($items as $item) {
            $duration = (int)$item[2];
            $end = strtotime('+' . max(0, $duration - 1) . ' day', $cursor);
            $this->db->insert(db_prefix() . 'usi_ai_schedule_items', [
                'schedule_id' => $scheduleId,
                'item_order' => $order,
                'item_title' => $item[0],
                'item_type' => $item[1],
                'planned_start' => date('Y-m-d', $cursor),
                'planned_end' => date('Y-m-d', $end),
                'duration_days' => $duration,
                'dependency_note' => $order === 1 ? 'Start item' : 'Complete previous item first',
                'status' => 'pending',
                'notes' => $item[3],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $cursor = strtotime('+1 day', $end);
            $order++;
        }
        $this->db->where('id', $scheduleId)->update(db_prefix() . 'usi_ai_schedules', [
            'status' => 'generated',
            'duration_days' => max(1, (int)ceil(($cursor - $start) / 86400)),
            'end_date' => date('Y-m-d', strtotime('-1 day', $cursor)),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Schedule items generated.'];
    }

    public function mark_schedule_status(int $id, string $status): array
    {
        if (!$this->get_schedule($id)) { return ['success' => false, 'message' => 'Schedule not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_schedules', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Schedule marked ' . str_replace('_', ' ', $status) . '.'];
    }

    public function delete_schedule(int $id): bool
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_schedule_items')) { $this->db->where('schedule_id', $id)->delete(db_prefix() . 'usi_ai_schedule_items'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_schedules');
    }

    public function mass_delete_schedules(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_schedule((int)$id)) { $deleted++; } }
        return $deleted;
    }

    private function count_schedules_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_schedules')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_schedules');
    }


    public function jobsite_counts(): array
    {
        return [
            'draft' => $this->count_jobsite_logs_by_status('draft'),
            'in_progress' => $this->count_jobsite_logs_by_status('in_progress'),
            'needs_attention' => $this->count_jobsite_logs_by_status('needs_attention'),
            'ready_for_closeout' => $this->count_jobsite_logs_by_status('ready_for_closeout'),
            'completed' => $this->count_jobsite_logs_by_status('completed'),
        ];
    }

    public function get_jobsite_logs(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_jobsite_logs')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_jobsite_logs');
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['date_from'])) { $this->db->where('log_date >=', $filters['date_from']); }
        if (!empty($filters['date_to'])) { $this->db->where('log_date <=', $filters['date_to']); }
        return $this->db->order_by('id', 'DESC')->get()->result_array();
    }

    public function get_jobsite_log(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_jobsite_logs')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_jobsite_logs')->row_array();
        return $row ?: null;
    }

    public function get_jobsite_punch_items(int $jobsiteLogId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_jobsite_punch_items')) { return []; }
        return $this->db->where('jobsite_log_id', $jobsiteLogId)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_jobsite_punch_items')->result_array();
    }

    public function save_jobsite_log(array $data, int $id = 0): int
    {
        $payload = [
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'schedule_id' => (int)($data['schedule_id'] ?? 0),
            'log_date' => !empty($data['log_date']) ? $data['log_date'] : date('Y-m-d'),
            'title' => trim((string)($data['title'] ?? 'Jobsite Log')) ?: 'Jobsite Log',
            'weather_note' => (string)($data['weather_note'] ?? ''),
            'crew_note' => (string)($data['crew_note'] ?? ''),
            'work_completed' => (string)($data['work_completed'] ?? ''),
            'materials_used' => (string)($data['materials_used'] ?? ''),
            'safety_checklist' => (string)($data['safety_checklist'] ?? ''),
            'customer_note' => (string)($data['customer_note'] ?? ''),
            'photo_summary' => (string)($data['photo_summary'] ?? ''),
            'status' => (string)($data['status'] ?? 'draft'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_jobsite_logs', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_jobsite_logs', $payload);
        return (int)$this->db->insert_id();
    }

    public function create_jobsite_log_from_schedule(int $scheduleId): array
    {
        $schedule = $this->get_schedule($scheduleId);
        if (!$schedule) { return ['success' => false, 'message' => 'Schedule not found.']; }
        $id = $this->save_jobsite_log([
            'schedule_id' => $scheduleId,
            'project_id' => (int)($schedule['project_id'] ?? 0),
            'ai_estimate_id' => (int)($schedule['ai_estimate_id'] ?? 0),
            'log_date' => date('Y-m-d'),
            'title' => 'Jobsite Log - ' . ($schedule['title'] ?? 'Schedule'),
            'status' => 'in_progress',
            'safety_checklist' => "PPE verified\nJobsite access confirmed\nWork area reviewed\nCustomer/property protection reviewed",
            'work_completed' => 'Created from AI schedule. Add daily completed work before closeout.',
        ]);
        return ['success' => true, 'message' => 'Jobsite log created from schedule.', 'jobsite_log_id' => $id];
    }

    public function generate_punch_items(int $jobsiteLogId): array
    {
        $log = $this->get_jobsite_log($jobsiteLogId);
        if (!$log) { return ['success' => false, 'message' => 'Jobsite log not found.']; }
        if (!usi_smartchoice_seo_table_exists('usi_ai_jobsite_punch_items')) { return ['success' => false, 'message' => 'Punch item table missing. Run Upgrade Database.']; }
        if (count($this->get_jobsite_punch_items($jobsiteLogId)) > 0) { return ['success' => true, 'message' => 'Punch items already exist.']; }
        $items = [
            ['Final Photo Set', 'photo', 'normal', 'Capture wide photos and close-up photos for every completed work area.'],
            ['Safety Closeout', 'safety', 'high', 'Confirm work area is safe, clean, and free of hazards before leaving.'],
            ['Customer Walkthrough Notes', 'customer', 'normal', 'Record any customer questions, requested changes, or concerns.'],
            ['Open Deficiency Review', 'deficiency', 'high', 'List any unfinished, damaged, missing, or incorrect items before project closeout.'],
        ];
        foreach ($items as $item) {
            $this->db->insert(db_prefix() . 'usi_ai_jobsite_punch_items', [
                'jobsite_log_id' => $jobsiteLogId,
                'item_title' => $item[0],
                'item_type' => $item[1],
                'priority' => $item[2],
                'description' => $item[3],
                'status' => 'open',
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Punch list generated.'];
    }

    public function mark_jobsite_log_status(int $id, string $status): array
    {
        if (!$this->get_jobsite_log($id)) { return ['success' => false, 'message' => 'Jobsite log not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_jobsite_logs', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Jobsite log marked ' . str_replace('_', ' ', $status) . '.'];
    }

    public function delete_jobsite_log(int $id): bool
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_jobsite_punch_items')) { $this->db->where('jobsite_log_id', $id)->delete(db_prefix() . 'usi_ai_jobsite_punch_items'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_jobsite_logs');
    }

    public function mass_delete_jobsite_logs(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_jobsite_log((int)$id)) { $deleted++; } }
        return $deleted;
    }


    public function closeout_counts(): array
    {
        return [
            'draft' => $this->count_closeouts_by_status('draft'),
            'in_review' => $this->count_closeouts_by_status('in_review'),
            'ready_for_customer' => $this->count_closeouts_by_status('ready_for_customer'),
            'completed' => $this->count_closeouts_by_status('completed'),
            'warranty_active' => $this->count_warranties_by_status('active'),
        ];
    }

    public function get_closeouts(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_closeouts')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_closeouts');
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['date_from'])) { $this->db->where('closeout_date >=', $filters['date_from']); }
        if (!empty($filters['date_to'])) { $this->db->where('closeout_date <=', $filters['date_to']); }
        return $this->db->order_by('id', 'DESC')->get()->result_array();
    }

    public function get_closeout(int $id): ?array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_closeouts')) { return null; }
        $row = $this->db->where('id', $id)->get(db_prefix() . 'usi_ai_closeouts')->row_array();
        return $row ?: null;
    }

    public function save_closeout(array $data, int $id = 0): int
    {
        $payload = [
            'jobsite_log_id' => (int)($data['jobsite_log_id'] ?? 0),
            'ai_estimate_id' => (int)($data['ai_estimate_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'title' => trim((string)($data['title'] ?? 'Closeout Package')) ?: 'Closeout Package',
            'closeout_date' => !empty($data['closeout_date']) ? $data['closeout_date'] : date('Y-m-d'),
            'status' => (string)($data['status'] ?? 'draft'),
            'final_photo_checklist' => (string)($data['final_photo_checklist'] ?? ''),
            'punch_summary' => (string)($data['punch_summary'] ?? ''),
            'customer_walkthrough_notes' => (string)($data['customer_walkthrough_notes'] ?? ''),
            'warranty_summary' => (string)($data['warranty_summary'] ?? ''),
            'closeout_package_notes' => (string)($data['closeout_package_notes'] ?? ''),
            'ready_for_customer' => !empty($data['ready_for_customer']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_closeouts', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_closeouts', $payload);
        return (int)$this->db->insert_id();
    }

    public function create_closeout_from_jobsite(int $jobsiteLogId): array
    {
        $log = $this->get_jobsite_log($jobsiteLogId);
        if (!$log) { return ['success' => false, 'message' => 'Jobsite log not found.']; }
        $existing = $this->db->where('jobsite_log_id', $jobsiteLogId)->get(db_prefix() . 'usi_ai_closeouts')->row_array();
        if ($existing) { return ['success' => true, 'message' => 'Closeout package already exists.', 'closeout_id' => (int)$existing['id']]; }
        $punchItems = $this->get_jobsite_punch_items($jobsiteLogId);
        $punchSummary = '';
        foreach ($punchItems as $item) { $punchSummary .= '- ' . ($item['item_title'] ?? '') . ': ' . ($item['status'] ?? '') . "\n"; }
        $id = $this->save_closeout([
            'jobsite_log_id' => $jobsiteLogId,
            'ai_estimate_id' => (int)($log['ai_estimate_id'] ?? 0),
            'project_id' => (int)($log['project_id'] ?? 0),
            'title' => 'Closeout Package - ' . ($log['title'] ?? 'Jobsite'),
            'status' => 'in_review',
            'final_photo_checklist' => "Front/Before/After photos verified\nClose-up detail photos verified\nCompletion photos organized\nCustomer walkthrough photos ready",
            'punch_summary' => $punchSummary ?: 'No punch items were generated yet. Generate punch items before final completion if needed.',
            'customer_walkthrough_notes' => (string)($log['customer_note'] ?? ''),
            'warranty_summary' => 'Generate warranty records and review coverage before marking ready for customer.',
            'closeout_package_notes' => 'Created from Jobsite Assistant log. Review all items before sending to customer.',
        ]);
        return ['success' => true, 'message' => 'Closeout package created from jobsite log.', 'closeout_id' => $id];
    }

    public function get_warranties(int $closeoutId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_warranties')) { return []; }
        return $this->db->where('closeout_id', $closeoutId)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_warranties')->result_array();
    }

    public function generate_warranty_records(int $closeoutId): array
    {
        $closeout = $this->get_closeout($closeoutId);
        if (!$closeout) { return ['success' => false, 'message' => 'Closeout package not found.']; }
        if (count($this->get_warranties($closeoutId)) > 0) { return ['success' => true, 'message' => 'Warranty records already exist.']; }
        $start = !empty($closeout['closeout_date']) ? $closeout['closeout_date'] : date('Y-m-d');
        $end = date('Y-m-d', strtotime($start . ' +1 year'));
        $records = [
            ['Workmanship Warranty', 'general', 'workmanship', 'Smart Choice workmanship warranty for completed project scope.'],
            ['Material Warranty Tracking', 'materials', 'manufacturer', 'Manufacturer warranty information should be attached or referenced when available.'],
            ['Customer Service Follow Up', 'service', 'support', 'Track post-project support questions and warranty service requests.'],
        ];
        foreach ($records as $record) {
            $this->db->insert(db_prefix() . 'usi_ai_warranties', [
                'closeout_id' => $closeoutId,
                'project_id' => (int)($closeout['project_id'] ?? 0),
                'customer_id' => (int)($closeout['customer_id'] ?? 0),
                'warranty_title' => $record[0],
                'trade' => $record[1],
                'warranty_type' => $record[2],
                'start_date' => $start,
                'end_date' => $end,
                'coverage_notes' => $record[3],
                'exclusions' => 'Excludes misuse, abuse, unrelated damage, owner-supplied material defects, and work outside the approved scope unless management approves otherwise.',
                'status' => 'active',
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Warranty records generated.'];
    }

    public function get_closeout_followups(int $closeoutId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_closeout_followups')) { return []; }
        return $this->db->where('closeout_id', $closeoutId)->order_by('due_date', 'ASC')->get(db_prefix() . 'usi_ai_closeout_followups')->result_array();
    }

    public function generate_closeout_followups(int $closeoutId): array
    {
        $closeout = $this->get_closeout($closeoutId);
        if (!$closeout) { return ['success' => false, 'message' => 'Closeout package not found.']; }
        if (count($this->get_closeout_followups($closeoutId)) > 0) { return ['success' => true, 'message' => 'Closeout follow ups already exist.']; }
        $base = !empty($closeout['closeout_date']) ? $closeout['closeout_date'] : date('Y-m-d');
        $items = [
            ['customer_check_in', '+7 days', '7-Day Customer Check In', 'Confirm the customer is satisfied and document any open warranty or service questions.'],
            ['review_request', '+14 days', 'Review Request', 'Ask for a review only after the customer confirms satisfaction.'],
            ['warranty_check', '+90 days', '90-Day Warranty Check', 'Check if customer has any warranty-related concern.'],
        ];
        foreach ($items as $item) {
            $this->db->insert(db_prefix() . 'usi_ai_closeout_followups', [
                'closeout_id' => $closeoutId,
                'followup_type' => $item[0],
                'due_date' => date('Y-m-d', strtotime($base . ' ' . $item[1])),
                'assigned_staff_id' => get_staff_user_id(),
                'message_subject' => $item[2],
                'message_body' => $item[3],
                'status' => 'pending',
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Closeout follow ups generated.'];
    }

    public function mark_closeout_status(int $id, string $status, bool $ready = false): array
    {
        if (!$this->get_closeout($id)) { return ['success' => false, 'message' => 'Closeout package not found.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_closeouts', ['status' => $status, 'ready_for_customer' => $ready ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Closeout package marked ' . str_replace('_', ' ', $status) . '.'];
    }

    public function delete_closeout(int $id): bool
    {
        if (usi_smartchoice_seo_table_exists('usi_ai_warranties')) { $this->db->where('closeout_id', $id)->delete(db_prefix() . 'usi_ai_warranties'); }
        if (usi_smartchoice_seo_table_exists('usi_ai_closeout_followups')) { $this->db->where('closeout_id', $id)->delete(db_prefix() . 'usi_ai_closeout_followups'); }
        return (bool)$this->db->where('id', $id)->delete(db_prefix() . 'usi_ai_closeouts');
    }

    public function mass_delete_closeouts(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) { if ($this->delete_closeout((int)$id)) { $deleted++; } }
        return $deleted;
    }



    public function executive_dashboard_summary(): array
    {
        return [
            'ai_estimates_total' => $this->safe_count('usi_ai_estimates'),
            'ai_estimates_draft' => $this->safe_count_where('usi_ai_estimates', 'status', 'draft'),
            'ai_estimates_approved' => $this->safe_count_where('usi_ai_estimates', 'status', 'approved'),
            'field_ready' => $this->safe_count_where('usi_ai_field_verifications', 'status', 'ready_for_estimate'),
            'purchase_orders_open' => $this->safe_count_where_not('usi_ai_purchase_orders', 'status', 'received'),
            'schedules_open' => $this->safe_count_where_not('usi_ai_schedules', 'status', 'completed'),
            'jobsite_logs_open' => $this->safe_count_where_not('usi_ai_jobsite_logs', 'status', 'ready_for_closeout'),
            'closeouts_open' => $this->safe_count_where_not('usi_ai_closeouts', 'status', 'completed'),
            'warranties_active' => $this->safe_count_where('usi_ai_warranties', 'status', 'active'),
            'communications_ready' => $this->safe_count_where('usi_ai_communications', 'send_status', 'ready_to_send'),
            'pipeline_value' => $this->safe_sum('usi_ai_estimates', 'total'),
            'approved_value' => $this->safe_sum_where('usi_ai_estimates', 'total', 'status', 'approved'),
            'purchase_open_value' => $this->safe_sum_where_not('usi_ai_purchase_orders', 'grand_total', 'status', 'received'),
        ];
    }

    public function get_executive_snapshots(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_snapshots')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix() . 'usi_ai_executive_snapshots')->result_array();
    }

    public function create_executive_snapshot(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_snapshots')) { return ['success' => false, 'message' => 'Executive dashboard tables are missing. Run Upgrade Database.']; }
        $summary = $this->executive_dashboard_summary();
        $risk = [];
        if ((int)$summary['communications_ready'] > 0) { $risk[] = 'Customer messages are ready to send and need management review.'; }
        if ((int)$summary['purchase_orders_open'] > 0) { $risk[] = 'Open purchase orders need ordering or receiving updates.'; }
        if ((int)$summary['closeouts_open'] > 0) { $risk[] = 'Open closeouts need customer-ready review or completion.'; }
        if ((int)$summary['ai_estimates_draft'] > 0) { $risk[] = 'Draft AI estimates need review, pricing, and approval.'; }
        $actions = [
            'Review draft AI estimates and move qualified records into approval.',
            'Review open purchase orders and mark ordered or received.',
            'Review open schedules and confirm ready for calendar status.',
            'Review closeout and warranty records for completed jobs.',
        ];
        $this->db->insert(db_prefix() . 'usi_ai_executive_snapshots', [
            'snapshot_date' => date('Y-m-d'),
            'pipeline_value' => (float)$summary['pipeline_value'],
            'approved_estimate_value' => (float)$summary['approved_value'],
            'open_project_count' => (int)$summary['field_ready'],
            'open_purchase_order_value' => (float)$summary['purchase_open_value'],
            'open_schedule_count' => (int)$summary['schedules_open'],
            'open_jobsite_log_count' => (int)$summary['jobsite_logs_open'],
            'open_closeout_count' => (int)$summary['closeouts_open'],
            'active_warranty_count' => (int)$summary['warranties_active'],
            'risk_summary' => implode("
", $risk),
            'recommended_actions' => implode("
", $actions),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Executive dashboard snapshot created.'];
    }

    public function get_executive_actions(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_actions')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_executive_actions');
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        return $this->db->order_by('id', 'DESC')->limit(100)->get()->result_array();
    }

    public function generate_executive_actions(int $snapshotId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_snapshots') || !usi_smartchoice_seo_table_exists('usi_ai_executive_actions')) { return ['success' => false, 'message' => 'Executive dashboard tables are missing.']; }
        $snapshot = $this->db->where('id', $snapshotId)->get(db_prefix() . 'usi_ai_executive_snapshots')->row_array();
        if (!$snapshot) { return ['success' => false, 'message' => 'Executive snapshot not found.']; }
        $existing = $this->db->where('snapshot_id', $snapshotId)->count_all_results(db_prefix() . 'usi_ai_executive_actions');
        if ($existing > 0) { return ['success' => true, 'message' => 'Executive actions already exist for this snapshot.']; }
        $rows = [
            ['Review AI estimate pipeline', 'estimate_review', 'high', 'AI Estimates', 'Review draft and approved estimate value from the executive snapshot.'],
            ['Review purchasing exposure', 'purchasing_review', 'normal', 'AI Purchasing', 'Confirm open purchase orders, received materials, and cost risk.'],
            ['Review schedule readiness', 'schedule_review', 'normal', 'AI Scheduling', 'Confirm open schedules and calendar-ready workflow.'],
            ['Review closeout and warranty status', 'closeout_review', 'normal', 'Closeout & Warranty', 'Confirm open closeouts, active warranties, and customer follow ups.'],
        ];
        foreach ($rows as $row) {
            $this->db->insert(db_prefix() . 'usi_ai_executive_actions', [
                'snapshot_id' => $snapshotId,
                'action_title' => $row[0],
                'action_type' => $row[1],
                'priority' => $row[2],
                'related_area' => $row[3],
                'assigned_staff_id' => get_staff_user_id(),
                'due_date' => date('Y-m-d', strtotime('+1 day')),
                'status' => 'open',
                'notes' => $row[4],
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Executive action plan generated.'];
    }

    public function mark_executive_action_status(int $id, string $status): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_actions')) { return ['success' => false, 'message' => 'Executive action table is missing.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_executive_actions', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Executive action updated.'];
    }

    public function mass_delete_executive_actions(array $ids): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_executive_actions')) { return 0; }
        $deleted = 0;
        foreach ($ids as $id) {
            $this->db->where('id', (int)$id)->delete(db_prefix() . 'usi_ai_executive_actions');
            $deleted++;
        }
        return $deleted;
    }


    public function business_intelligence_summary(): array
    {
        $summary = $this->executive_dashboard_summary();
        $riskScore = 0;
        if ((int)$summary['ai_estimates_draft'] > 0) { $riskScore++; }
        if ((int)$summary['communications_ready'] > 0) { $riskScore++; }
        if ((int)$summary['purchase_orders_open'] > 0) { $riskScore++; }
        if ((int)$summary['schedules_open'] > 0) { $riskScore++; }
        if ((int)$summary['closeouts_open'] > 0) { $riskScore++; }
        $riskLevel = $riskScore >= 4 ? 'high' : ($riskScore >= 2 ? 'normal' : 'low');
        $summary['risk_score'] = $riskScore;
        $summary['risk_level'] = $riskLevel;
        $summary['recommendation_count'] = $this->safe_count('usi_ai_business_recommendations');
        $summary['open_recommendation_count'] = $this->safe_count_where('usi_ai_business_recommendations', 'status', 'open');
        return $summary;
    }

    public function get_business_snapshots(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_snapshots')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix() . 'usi_ai_business_snapshots')->result_array();
    }

    public function create_business_snapshot(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_snapshots')) { return ['success' => false, 'message' => 'Business intelligence tables are missing. Run Upgrade Database.']; }
        $summary = $this->business_intelligence_summary();
        $businessSummary = [];
        $businessSummary[] = 'Pipeline value: ' . app_format_money((float)$summary['pipeline_value'], get_base_currency());
        $businessSummary[] = 'Approved value: ' . app_format_money((float)$summary['approved_value'], get_base_currency());
        $businessSummary[] = 'Open purchasing value: ' . app_format_money((float)$summary['purchase_open_value'], get_base_currency());
        $businessSummary[] = 'Risk level: ' . ucfirst((string)$summary['risk_level']);
        $this->db->insert(db_prefix() . 'usi_ai_business_snapshots', [
            'snapshot_date' => date('Y-m-d'),
            'gross_pipeline_value' => (float)$summary['pipeline_value'],
            'approved_pipeline_value' => (float)$summary['approved_value'],
            'open_purchase_value' => (float)$summary['purchase_open_value'],
            'ready_message_count' => (int)$summary['communications_ready'],
            'draft_estimate_count' => (int)$summary['ai_estimates_draft'],
            'open_schedule_count' => (int)$summary['schedules_open'],
            'open_closeout_count' => (int)$summary['closeouts_open'],
            'active_warranty_count' => (int)$summary['warranties_active'],
            'risk_level' => (string)$summary['risk_level'],
            'business_summary' => implode("
", $businessSummary),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Business intelligence snapshot created.'];
    }

    public function get_business_recommendations(array $filters = []): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_recommendations')) { return []; }
        $this->db->from(db_prefix() . 'usi_ai_business_recommendations');
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        return $this->db->order_by('id', 'DESC')->limit(100)->get()->result_array();
    }

    public function generate_business_recommendations(int $snapshotId): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_snapshots') || !usi_smartchoice_seo_table_exists('usi_ai_business_recommendations')) { return ['success' => false, 'message' => 'Business intelligence tables are missing.']; }
        $snapshot = $this->db->where('id', $snapshotId)->get(db_prefix() . 'usi_ai_business_snapshots')->row_array();
        if (!$snapshot) { return ['success' => false, 'message' => 'Business snapshot not found.']; }
        $existing = $this->db->where('snapshot_id', $snapshotId)->count_all_results(db_prefix() . 'usi_ai_business_recommendations');
        if ($existing > 0) { return ['success' => true, 'message' => 'Business recommendations already exist for this snapshot.']; }
        $rows = [];
        if ((int)$snapshot['draft_estimate_count'] > 0) {
            $rows[] = ['Convert draft AI estimates', 'sales_pipeline', 'high', 'Estimating', 'Review draft estimates, calculate numbers, and move qualified jobs into approval.', 'Faster quote turnaround and cleaner pipeline value.'];
        }
        if ((float)$snapshot['open_purchase_value'] > 0) {
            $rows[] = ['Review open purchase exposure', 'cost_control', 'normal', 'Purchasing', 'Confirm ordered and received materials, then compare open purchase value against approved estimate totals.', 'Reduces material cost overruns and missing delivery updates.'];
        }
        if ((int)$snapshot['open_schedule_count'] > 0) {
            $rows[] = ['Confirm open schedules', 'operations', 'normal', 'Scheduling', 'Move ready schedules into calendar and check assignment conflicts.', 'Improves crew coordination and inspection readiness.'];
        }
        if ((int)$snapshot['open_closeout_count'] > 0) {
            $rows[] = ['Close out completed projects', 'customer_success', 'normal', 'Closeout', 'Finish closeout packages, warranties, customer follow ups, and final photos.', 'Improves customer handoff and warranty tracking.'];
        }
        if ((int)$snapshot['ready_message_count'] > 0) {
            $rows[] = ['Send ready customer messages', 'communication', 'high', 'Customer Communications', 'Review ready-to-send messages and mark sent after final approval.', 'Improves response time and keeps customer communication moving.'];
        }
        if (!$rows) {
            $rows[] = ['Maintain current operating rhythm', 'management_review', 'low', 'Operations', 'Review dashboard weekly and keep all AI workflows current.', 'Keeps Sammy AI data reliable for management decisions.'];
        }
        foreach ($rows as $row) {
            $this->db->insert(db_prefix() . 'usi_ai_business_recommendations', [
                'snapshot_id' => $snapshotId,
                'recommendation_title' => $row[0],
                'recommendation_type' => $row[1],
                'priority' => $row[2],
                'impact_area' => $row[3],
                'recommended_action' => $row[4],
                'expected_impact' => $row[5],
                'assigned_staff_id' => get_staff_user_id(),
                'due_date' => date('Y-m-d', strtotime('+1 day')),
                'status' => 'open',
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['success' => true, 'message' => 'Business recommendations generated.'];
    }

    public function mark_business_recommendation_status(int $id, string $status): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_recommendations')) { return ['success' => false, 'message' => 'Business recommendation table is missing.']; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_business_recommendations', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['success' => true, 'message' => 'Business recommendation updated.'];
    }

    public function mass_delete_business_recommendations(array $ids): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_business_recommendations')) { return 0; }
        $deleted = 0;
        foreach ($ids as $id) {
            $this->db->where('id', (int)$id)->delete(db_prefix() . 'usi_ai_business_recommendations');
            $deleted++;
        }
        return $deleted;
    }



    public function ai_core_summary(): array
    {
        return [
            'pending_actions' => $this->safe_count_where('usi_ai_core_actions', 'status', 'pending'),
            'active_contexts' => $this->safe_count_where('usi_ai_core_context', 'status', 'active'),
            'indexed_records' => usi_smartchoice_seo_table_exists('usi_ai_core_search_index') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_core_search_index') : 0,
            'timeline_events' => usi_smartchoice_seo_table_exists('usi_ai_core_timeline') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_core_timeline') : 0,
            'diagnostics' => usi_smartchoice_seo_table_exists('usi_ai_core_diagnostics') ? (int)$this->db->count_all_results(db_prefix() . 'usi_ai_core_diagnostics') : 0,
        ];
    }

    public function get_ai_core_actions(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_actions')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_core_actions')->result_array();
    }

    public function get_ai_core_timeline(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_timeline')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_core_timeline')->result_array();
    }

    public function get_ai_core_search_index(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_search_index')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_core_search_index')->result_array();
    }

    public function get_ai_core_diagnostics(): array
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_diagnostics')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_core_diagnostics')->result_array();
    }

    public function save_ai_core_action(array $data): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_actions')) { return 0; }
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $payload = [
            'action_title' => trim((string)($data['action_title'] ?? 'AI Action')),
            'action_type' => trim((string)($data['action_type'] ?? 'review')),
            'source_area' => trim((string)($data['source_area'] ?? 'core')),
            'related_type' => trim((string)($data['related_type'] ?? '')),
            'related_id' => isset($data['related_id']) ? (int)$data['related_id'] : 0,
            'requested_command' => (string)($data['requested_command'] ?? ''),
            'recommended_action' => (string)($data['recommended_action'] ?? ''),
            'approval_required' => isset($data['approval_required']) ? 1 : 0,
            'assigned_staff_id' => isset($data['assigned_staff_id']) ? (int)$data['assigned_staff_id'] : get_staff_user_id(),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'priority' => trim((string)($data['priority'] ?? 'normal')),
            'status' => trim((string)($data['status'] ?? 'pending')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_core_actions', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'usi_ai_core_actions', $payload);
        return (int)$this->db->insert_id();
    }

    public function mark_ai_core_action_status(int $id, string $status): bool
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_actions')) { return false; }
        $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_core_actions', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->log_ai_core_timeline('AI Action ' . ucfirst($status), 'action', 'core', 'usi_ai_core_actions', $id, 'Action status changed to ' . $status . '.');
        return true;
    }

    public function log_ai_core_timeline(string $title, string $type, string $sourceArea, string $relatedType = '', int $relatedId = 0, string $summary = ''): void
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_timeline')) { return; }
        $this->db->insert(db_prefix() . 'usi_ai_core_timeline', [
            'event_title' => $title,
            'event_type' => $type,
            'source_area' => $sourceArea,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'event_summary' => $summary,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function rebuild_ai_core_index(): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_search_index')) { return 0; }
        $this->db->truncate(db_prefix() . 'usi_ai_core_search_index');
        $sources = [
            ['usi_ai_estimates', 'AI Estimates', 'estimate_title', 'project_notes', 'ai_estimates'],
            ['usi_ai_memory_items', 'AI Memory', 'memory_title', 'memory_summary', 'memory_engine'],
            ['usi_ai_documents', 'Documents', 'document_title', 'document_summary', 'document_intelligence'],
            ['usi_ai_takeoffs', 'Material Takeoff', 'takeoff_title', 'notes', 'material_takeoffs'],
            ['usi_ai_purchase_orders', 'Purchasing', 'po_title', 'notes', 'purchasing'],
            ['usi_ai_schedules', 'Scheduling', 'schedule_title', 'notes', 'scheduling'],
            ['usi_ai_jobsite_logs', 'Jobsite', 'log_title', 'work_summary', 'jobsite_assistant'],
            ['usi_ai_closeouts', 'Closeout', 'closeout_title', 'closeout_notes', 'closeout_warranty'],
            ['usi_ai_conversations', 'Conversations', 'conversation_title', 'conversation_summary', 'chat_engine'],
        ];
        $count = 0;
        foreach ($sources as $source) {
            [$table, $area, $titleCol, $summaryCol, $route] = $source;
            if (!usi_smartchoice_seo_table_exists($table) || !$this->safe_column_exists($table, $titleCol)) { continue; }
            $rows = $this->db->limit(200)->order_by('id', 'DESC')->get(db_prefix() . $table)->result_array();
            foreach ($rows as $row) {
                $title = (string)($row[$titleCol] ?? ($area . ' #' . (int)$row['id']));
                $summary = isset($row[$summaryCol]) ? (string)$row[$summaryCol] : '';
                $this->db->insert(db_prefix() . 'usi_ai_core_search_index', [
                    'source_table' => $table,
                    'source_id' => (int)$row['id'],
                    'source_area' => $area,
                    'record_title' => $title,
                    'record_summary' => $summary,
                    'search_text' => trim($title . ' ' . $summary),
                    'record_url' => admin_url('usi_smartchoice_seo/' . $route),
                    'status' => 'active',
                    'last_indexed_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
        }
        $this->log_ai_core_timeline('AI Core Index Rebuilt', 'index', 'core', '', 0, 'Indexed ' . $count . ' records across Sammy AI.');
        return $count;
    }

    public function rebuild_ai_core_diagnostics(): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_core_diagnostics')) { return 0; }
        $this->db->truncate(db_prefix() . 'usi_ai_core_diagnostics');
        $required = [
            'usi_ai_core_context', 'usi_ai_core_actions', 'usi_ai_core_timeline', 'usi_ai_core_search_index', 'usi_ai_core_diagnostics',
            'usi_ai_estimates', 'usi_ai_memory_items', 'usi_ai_documents', 'usi_ai_takeoffs', 'usi_ai_purchase_orders', 'usi_ai_schedules', 'usi_ai_jobsite_logs', 'usi_ai_command_center_cards'
        ];
        $count = 0;
        foreach ($required as $table) {
            $ok = usi_smartchoice_seo_table_exists($table);
            $this->db->insert(db_prefix() . 'usi_ai_core_diagnostics', [
                'diagnostic_area' => 'database',
                'diagnostic_title' => 'Database table ' . db_prefix() . $table,
                'diagnostic_status' => $ok ? 'ok' : 'fail',
                'diagnostic_message' => $ok ? 'Table found.' : 'Table is missing or migration did not run.',
                'checked_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }
        return $count;
    }

    private function safe_column_exists(string $table, string $column): bool
    {
        if (!usi_smartchoice_seo_table_exists($table)) { return false; }
        if (function_exists('usi_smartchoice_seo_column_exists')) {
            return usi_smartchoice_seo_column_exists($table, $column);
        }
        $query = $this->db->query('SHOW COLUMNS FROM `' . db_prefix() . $table . '` LIKE ' . $this->db->escape($column));
        return $query !== false && $query->num_rows() > 0;
    }

    private function safe_count_where(string $table, string $column, string $value): int
    {
        if (!$this->safe_column_exists($table, $column)) { return 0; }
        return (int)$this->db->where($column, $value)->count_all_results(db_prefix() . $table);
    }

    private function safe_count_where_not(string $table, string $column, string $value): int
    {
        if (!$this->safe_column_exists($table, $column)) { return 0; }
        return (int)$this->db->where($column . ' !=', $value)->count_all_results(db_prefix() . $table);
    }

    private function safe_sum(string $table, string $column): float
    {
        if (!$this->safe_column_exists($table, $column)) { return 0.0; }
        $row = $this->db->select_sum($column, 'total')->get(db_prefix() . $table)->row_array();
        return (float)($row['total'] ?? 0);
    }

    private function safe_sum_where(string $table, string $sumColumn, string $whereColumn, string $value): float
    {
        if (!$this->safe_column_exists($table, $sumColumn) || !$this->safe_column_exists($table, $whereColumn)) { return 0.0; }
        $row = $this->db->select_sum($sumColumn, 'total')->where($whereColumn, $value)->get(db_prefix() . $table)->row_array();
        return (float)($row['total'] ?? 0);
    }

    private function safe_sum_where_not(string $table, string $sumColumn, string $whereColumn, string $value): float
    {
        if (!$this->safe_column_exists($table, $sumColumn) || !$this->safe_column_exists($table, $whereColumn)) { return 0.0; }
        $row = $this->db->select_sum($sumColumn, 'total')->where($whereColumn . ' !=', $value)->get(db_prefix() . $table)->row_array();
        return (float)($row['total'] ?? 0);
    }

    private function count_closeouts_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_closeouts')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_closeouts');
    }

    private function count_warranties_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_warranties')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_warranties');
    }

    private function count_jobsite_logs_by_status(string $status): int
    {
        if (!usi_smartchoice_seo_table_exists('usi_ai_jobsite_logs')) { return 0; }
        return (int)$this->db->where('status', $status)->count_all_results(db_prefix() . 'usi_ai_jobsite_logs');
    }


    public function get_vision_sessions(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_vision_sessions')) {
            return [];
        }
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_vision_sessions')->result_array();
    }

    public function get_vision_session(int $id): ?array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_vision_sessions')) {
            return null;
        }
        $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'usi_ai_vision_sessions')->row_array();
        return $row ?: null;
    }

    public function add_vision_session(array $data)
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_vision_sessions')) {
            return false;
        }
        $insert = [
            'title'             => trim((string)($data['title'] ?? 'Vision Analysis')),
            'related_type'      => trim((string)($data['related_type'] ?? '')),
            'related_id'        => (int)($data['related_id'] ?? 0),
            'service_type'      => trim((string)($data['service_type'] ?? '')),
            'photo_notes'       => trim((string)($data['photo_notes'] ?? '')),
            'measurement_notes' => trim((string)($data['measurement_notes'] ?? '')),
            'status'            => 'draft',
            'confidence'        => 0,
            'created_by'        => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_vision_sessions', $insert);
        return $this->db->insert_id();
    }

    public function generate_vision_findings(int $sessionId): bool
    {
        $session = $this->get_vision_session((int)$sessionId);
        if (!$session || !$this->db->table_exists(db_prefix() . 'usi_ai_vision_findings')) {
            return false;
        }
        $notes = strtolower((string)($session['photo_notes'] . ' ' . $session['measurement_notes'] . ' ' . $session['service_type']));
        $findings = [];
        if (strpos($notes, 'window') !== false || strpos($notes, 'impact') !== false) {
            $findings[] = ['finding_type' => 'Windows', 'description' => 'Potential window or impact window work detected from notes.', 'quantity' => 1, 'unit' => 'allowance', 'estimate_impact' => 0];
        }
        if (strpos($notes, 'door') !== false) {
            $findings[] = ['finding_type' => 'Doors', 'description' => 'Potential door scope detected from notes.', 'quantity' => 1, 'unit' => 'allowance', 'estimate_impact' => 0];
        }
        if (strpos($notes, 'drywall') !== false || strpos($notes, 'sheetrock') !== false) {
            $findings[] = ['finding_type' => 'Drywall', 'description' => 'Potential drywall repair or installation scope detected.', 'quantity' => 1, 'unit' => 'allowance', 'estimate_impact' => 0];
        }
        if (strpos($notes, 'paint') !== false || strpos($notes, 'painting') !== false) {
            $findings[] = ['finding_type' => 'Paint', 'description' => 'Potential painting scope detected.', 'quantity' => 1, 'unit' => 'allowance', 'estimate_impact' => 0];
        }
        if (strpos($notes, 'floor') !== false || strpos($notes, 'tile') !== false || strpos($notes, 'vinyl') !== false) {
            $findings[] = ['finding_type' => 'Flooring', 'description' => 'Potential flooring or tile scope detected.', 'quantity' => 1, 'unit' => 'allowance', 'estimate_impact' => 0];
        }
        if (!$findings) {
            $findings[] = ['finding_type' => 'General Scope', 'description' => 'General jobsite review created. Add more photos, notes, and measurements for a stronger estimate.', 'quantity' => 1, 'unit' => 'review', 'estimate_impact' => 0];
        }
        $this->db->where('vision_session_id', (int)$sessionId)->delete(db_prefix() . 'usi_ai_vision_findings');
        foreach ($findings as $finding) {
            $finding['vision_session_id'] = (int)$sessionId;
            $finding['status'] = 'new';
            $finding['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'usi_ai_vision_findings', $finding);
        }
        $confidence = min(95, 45 + (count($findings) * 10));
        $this->db->where('id', (int)$sessionId)->update(db_prefix() . 'usi_ai_vision_sessions', [
            'status' => 'analyzed',
            'confidence' => $confidence,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function get_vision_findings(int $sessionId): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_vision_findings')) {
            return [];
        }
        return $this->db->where('vision_session_id', (int)$sessionId)->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_vision_findings')->result_array();
    }


    public function get_learning_courses(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_learning_courses')) { return []; }
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_learning_courses')->result_array();
    }

    public function get_learning_assignments(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_learning_assignments')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(50)->get(db_prefix() . 'usi_ai_learning_assignments')->result_array();
    }

    public function save_learning_course(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_learning_courses')) { return 0; }
        $insert = [
            'course_title' => trim((string)($data['course_title'] ?? 'Untitled Course')),
            'course_type' => trim((string)($data['course_type'] ?? 'crm_training')),
            'description' => (string)($data['description'] ?? ''),
            'target_role' => trim((string)($data['target_role'] ?? '')),
            'status' => trim((string)($data['status'] ?? 'draft')),
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_learning_courses', $insert);
        return (int)$this->db->insert_id();
    }

    public function get_api_providers(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_providers')) { return []; }
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_api_providers')->result_array();
    }

    public function get_api_keys(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_keys')) { return []; }
        $rows = $this->db->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_api_keys')->result_array();
        foreach ($rows as &$row) {
            $key = (string)($row['api_key_encrypted'] ?? '');
            $row['api_key_preview'] = $key === '' ? '' : substr($key, 0, 6) . '••••' . substr($key, -4);
        }
        return $rows;
    }

    public function get_api_health_checks(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_health_checks')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix() . 'usi_ai_api_health_checks')->result_array();
    }

    public function save_api_provider(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_providers')) { return 0; }
        $insert = [
            'provider_name' => trim((string)($data['provider_name'] ?? 'AI Provider')),
            'provider_type' => trim((string)($data['provider_type'] ?? 'ai')),
            'base_url' => trim((string)($data['base_url'] ?? '')),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_api_providers', $insert);
        return (int)$this->db->insert_id();
    }

    public function save_api_key(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_keys')) { return 0; }
        $insert = [
            'provider_id' => (int)($data['provider_id'] ?? 0),
            'key_label' => trim((string)($data['key_label'] ?? 'Default Key')),
            'api_key_encrypted' => trim((string)($data['api_key'] ?? '')),
            'key_status' => 'untested',
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_api_keys', $insert);
        return (int)$this->db->insert_id();
    }

    public function check_api_provider(int $providerId): bool
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_api_health_checks')) { return false; }
        $this->db->insert(db_prefix() . 'usi_ai_api_health_checks', [
            'provider_id' => (int)$providerId,
            'check_status' => 'manual_check_logged',
            'message' => 'Provider check button was executed. External live billing/API verification requires provider API integration.',
            'checked_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function get_security_policies(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_security_policies')) { return []; }
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'usi_ai_security_policies')->result_array();
    }

    public function get_security_audit_events(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_security_audit_events')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get(db_prefix() . 'usi_ai_security_audit_events')->result_array();
    }

    public function save_security_policy(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_security_policies')) { return 0; }
        $insert = [
            'policy_name' => trim((string)($data['policy_name'] ?? 'New Policy')),
            'policy_area' => trim((string)($data['policy_area'] ?? 'general')),
            'policy_description' => (string)($data['policy_description'] ?? ''),
            'is_required' => isset($data['is_required']) ? 1 : 0,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_security_policies', $insert);
        return (int)$this->db->insert_id();
    }

    public function log_security_event(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_security_audit_events')) { return 0; }
        $insert = [
            'event_type' => trim((string)($data['event_type'] ?? 'manual')),
            'event_area' => trim((string)($data['event_area'] ?? 'sammy_ai')),
            'event_summary' => (string)($data['event_summary'] ?? ''),
            'severity' => trim((string)($data['severity'] ?? 'info')),
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_security_audit_events', $insert);
        return (int)$this->db->insert_id();
    }

    public function get_command_center_cards(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_command_center_cards')) { return []; }
        return $this->db->order_by('sort_order', 'ASC')->order_by('id', 'ASC')->get(db_prefix() . 'usi_ai_command_center_cards')->result_array();
    }

    public function rebuild_command_center_cards(): bool
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_command_center_cards')) { return false; }
        $cards = [
            ['Executive Dashboard', 'executive', 'Review KPIs, action plan, and company status.', 10],
            ['Estimating', 'estimating', 'Review AI estimates, pricing, takeoffs, and approval queue.', 20],
            ['Projects', 'projects', 'Review project handoffs, schedules, jobsite logs, and closeout.', 30],
            ['Automation', 'automation', 'Review alerts, workflows, conversations, and voice routing.', 40],
            ['System', 'system', 'Review API manager, security audit, health, and settings.', 50],
        ];
        foreach ($cards as $card) {
            $exists = (int)$this->db->where('card_title', $card[0])->count_all_results(db_prefix() . 'usi_ai_command_center_cards');
            if ($exists === 0) {
                $this->db->insert(db_prefix() . 'usi_ai_command_center_cards', [
                    'card_title' => $card[0], 'card_area' => $card[1], 'card_description' => $card[2], 'card_status' => 'active', 'sort_order' => $card[3], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        return true;
    }

    public function command_center_summary(): array
    {
        $tables = ['usi_ai_estimates','usi_ai_workflows','usi_ai_conversations','usi_ai_vision_sessions','usi_ai_api_providers','usi_ai_security_audit_events'];
        $summary = [];
        foreach ($tables as $table) {
            $summary[$table] = $this->db->table_exists(db_prefix() . $table) ? (int)$this->db->count_all_results(db_prefix() . $table) : 0;
        }
        return $summary;
    }


    public function intelligence_engine_summary(): array
    {
        $tables = [
            'usi_ai_prompt_templates' => 'Prompt Templates',
            'usi_ai_requests' => 'AI Requests',
            'usi_ai_context_profiles' => 'Context Profiles',
            'usi_ai_response_cache' => 'Cached Responses',
            'usi_ai_provider_logs' => 'Provider Logs',
        ];
        $summary = [];
        foreach ($tables as $table => $label) {
            $summary[$label] = $this->db->table_exists(db_prefix() . $table) ? (int)$this->db->count_all_results(db_prefix() . $table) : 0;
        }
        return $summary;
    }

    public function get_ai_prompt_templates(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_prompt_templates')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(50)->get(db_prefix() . 'usi_ai_prompt_templates')->result_array();
    }

    public function get_ai_request_history(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_requests')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(75)->get(db_prefix() . 'usi_ai_requests')->result_array();
    }

    public function get_ai_context_profiles(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_context_profiles')) { return []; }
        return $this->db->order_by('updated_at', 'DESC')->limit(75)->get(db_prefix() . 'usi_ai_context_profiles')->result_array();
    }

    public function get_ai_response_cache(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_response_cache')) { return []; }
        return $this->db->order_by('last_used_at', 'DESC')->limit(50)->get(db_prefix() . 'usi_ai_response_cache')->result_array();
    }

    public function get_ai_provider_logs(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_provider_logs')) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(50)->get(db_prefix() . 'usi_ai_provider_logs')->result_array();
    }

    public function save_ai_prompt_template(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_prompt_templates')) { return 0; }
        $insert = [
            'template_name' => trim((string)($data['template_name'] ?? 'New Prompt Template')),
            'template_area' => trim((string)($data['template_area'] ?? 'general')),
            'system_prompt' => (string)($data['system_prompt'] ?? ''),
            'user_prompt' => (string)($data['user_prompt'] ?? ''),
            'default_model' => trim((string)($data['default_model'] ?? '')),
            'temperature' => (float)($data['temperature'] ?? 0.2),
            'max_tokens' => (int)($data['max_tokens'] ?? 1200),
            'status' => trim((string)($data['status'] ?? 'active')),
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_prompt_templates', $insert);
        return (int)$this->db->insert_id();
    }

    public function save_ai_context_profile(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_context_profiles')) { return 0; }
        $insert = [
            'profile_name' => trim((string)($data['profile_name'] ?? 'Manual Context Profile')),
            'related_type' => trim((string)($data['related_type'] ?? 'general')),
            'related_id' => (int)($data['related_id'] ?? 0),
            'context_summary' => (string)($data['context_summary'] ?? ''),
            'context_payload' => (string)($data['context_payload'] ?? ''),
            'priority' => trim((string)($data['priority'] ?? 'normal')),
            'status' => trim((string)($data['status'] ?? 'active')),
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_context_profiles', $insert);
        return (int)$this->db->insert_id();
    }

    public function log_ai_intelligence_test_request(array $data): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_requests')) { return 0; }
        $prompt = trim((string)($data['test_prompt'] ?? ''));
        $area = trim((string)($data['request_area'] ?? 'manual_test'));
        $hash = sha1($area . '|' . $prompt . '|' . date('YmdHi'));
        $insert = [
            'request_area' => $area,
            'related_type' => trim((string)($data['related_type'] ?? 'general')),
            'related_id' => (int)($data['related_id'] ?? 0),
            'prompt_hash' => $hash,
            'prompt_text' => $prompt,
            'response_text' => 'Manual test request logged. Live AI execution requires an active provider/key in API Manager.',
            'model_name' => trim((string)($data['model_name'] ?? '')),
            'provider_name' => trim((string)($data['provider_name'] ?? '')),
            'request_status' => 'logged',
            'token_input' => strlen($prompt),
            'token_output' => 0,
            'cost_estimate' => 0,
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'usi_ai_requests', $insert);
        $requestId = (int)$this->db->insert_id();
        if ($this->db->table_exists(db_prefix() . 'usi_ai_provider_logs')) {
            $this->db->insert(db_prefix() . 'usi_ai_provider_logs', [
                'provider_name' => $insert['provider_name'] ?: 'Manual Provider',
                'request_id' => $requestId,
                'log_status' => 'logged',
                'message' => 'AI Intelligence Engine test request captured successfully.',
                'latency_ms' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $requestId;
    }

    public function rebuild_ai_context_profiles(): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_context_profiles')) { return 0; }
        $created = 0;
        $sources = [
            ['table' => 'usi_ai_estimates', 'type' => 'ai_estimate', 'title' => 'AI Estimate Context'],
            ['table' => 'usi_ai_memory_items', 'type' => 'memory', 'title' => 'Memory Context'],
            ['table' => 'usi_ai_conversations', 'type' => 'conversation', 'title' => 'Conversation Context'],
            ['table' => 'usi_ai_documents', 'type' => 'document', 'title' => 'Document Context'],
        ];
        foreach ($sources as $source) {
            $table = db_prefix() . $source['table'];
            if (!$this->db->table_exists($table)) { continue; }
            $rows = $this->db->limit(25)->order_by('id', 'DESC')->get($table)->result_array();
            foreach ($rows as $row) {
                $relatedId = (int)($row['id'] ?? 0);
                $exists = (int)$this->db->where('related_type', $source['type'])->where('related_id', $relatedId)->count_all_results(db_prefix() . 'usi_ai_context_profiles');
                if ($exists > 0) { continue; }
                $summaryParts = [];
                foreach (['title','estimate_title','conversation_title','document_title','customer_name','status'] as $field) {
                    if (isset($row[$field]) && $row[$field] !== '') { $summaryParts[] = $field . ': ' . $row[$field]; }
                }
                $this->db->insert(db_prefix() . 'usi_ai_context_profiles', [
                    'profile_name' => $source['title'] . ' #' . $relatedId,
                    'related_type' => $source['type'],
                    'related_id' => $relatedId,
                    'context_summary' => implode(' | ', $summaryParts),
                    'context_payload' => json_encode($row),
                    'priority' => 'normal',
                    'status' => 'active',
                    'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $created++;
            }
        }
        return $created;
    }

    public function clear_ai_response_cache(): int
    {
        if (!$this->db->table_exists(db_prefix() . 'usi_ai_response_cache')) { return 0; }
        $count = (int)$this->db->count_all_results(db_prefix() . 'usi_ai_response_cache');
        $this->db->empty_table(db_prefix() . 'usi_ai_response_cache');
        return $count;
    }




    public function advanced_estimating_summary(): array
    {
        $tables = [
            'Runs' => 'usi_ai_estimate_intelligence_runs',
            'Lines' => 'usi_ai_estimate_intelligence_lines',
            'Price Patterns' => 'usi_ai_estimate_price_patterns',
            'AI Estimates' => 'usi_ai_estimates',
            'CRM Estimates' => 'estimates',
            'Pricing Rows' => 'usi_ai_price_index',
        ];
        $summary = [];
        foreach ($tables as $label => $table) {
            $full = db_prefix() . $table;
            $summary[$label] = $this->db->table_exists($full) ? (int)$this->db->count_all_results($full) : 0;
        }
        return $summary;
    }

    public function get_estimate_intelligence_runs(int $limit = 50): array
    {
        $table = db_prefix() . 'usi_ai_estimate_intelligence_runs';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function get_estimate_price_patterns(int $limit = 50): array
    {
        $table = db_prefix() . 'usi_ai_estimate_price_patterns';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function create_estimate_intelligence_run(array $data): int
    {
        $table = db_prefix() . 'usi_ai_estimate_intelligence_runs';
        if (!$this->db->table_exists($table)) { return 0; }
        $subtotal = (float)($data['subtotal'] ?? 0);
        $tax = (float)($data['tax_total'] ?? 0);
        $overhead = (float)($data['overhead_total'] ?? 0);
        $profit = (float)($data['profit_total'] ?? 0);
        $insert = [
            'run_title' => trim((string)($data['run_title'] ?? 'Advanced Estimate Run')),
            'related_estimate_id' => (int)($data['related_estimate_id'] ?? 0),
            'related_customer_id' => (int)($data['related_customer_id'] ?? 0),
            'service_type' => trim((string)($data['service_type'] ?? 'general')),
            'city' => trim((string)($data['city'] ?? '')),
            'county' => trim((string)($data['county'] ?? '')),
            'scope_summary' => (string)($data['scope_summary'] ?? ''),
            'historical_match_count' => 0,
            'confidence_score' => 0,
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'overhead_total' => $overhead,
            'profit_total' => $profit,
            'grand_total' => $subtotal + $tax + $overhead + $profit,
            'status' => trim((string)($data['status'] ?? 'draft')),
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($table, $insert);
        return (int)$this->db->insert_id();
    }

    public function calculate_estimate_intelligence(int $id): bool
    {
        $runs = db_prefix() . 'usi_ai_estimate_intelligence_runs';
        $lines = db_prefix() . 'usi_ai_estimate_intelligence_lines';
        if (!$this->db->table_exists($runs) || !$this->db->table_exists($lines)) { return false; }
        $run = $this->db->where('id', $id)->get($runs)->row_array();
        if (!$run) { return false; }
        $this->db->where('run_id', $id)->delete($lines);
        $patterns = $this->get_estimate_price_patterns(5);
        $subtotal = 0.0;
        $matches = 0;
        foreach ($patterns as $pattern) {
            if ($run['service_type'] !== 'general' && $pattern['service_type'] !== $run['service_type']) { continue; }
            $qty = 1.0;
            $unitPrice = (float)$pattern['average_total'];
            if ($unitPrice <= 0) { $unitPrice = (float)$pattern['average_unit_price']; }
            if ($unitPrice <= 0) { continue; }
            $this->db->insert($lines, [
                'run_id' => $id,
                'item_name' => $pattern['pattern_name'],
                'item_description' => (string)($pattern['notes'] ?? 'Historical price pattern generated from CRM data.'),
                'qty' => $qty,
                'unit' => 'allowance',
                'unit_cost' => round($unitPrice * 0.70, 2),
                'unit_price' => round($unitPrice, 2),
                'line_total' => round($unitPrice * $qty, 2),
                'source_type' => 'price_pattern',
                'source_id' => (int)$pattern['id'],
                'confidence_score' => min(95, max(30, (int)$pattern['sample_count'] * 10)),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $subtotal += $unitPrice * $qty;
            $matches++;
        }
        if ($matches === 0) {
            $subtotal = (float)$run['subtotal'];
        }
        $taxRate = (float)get_option('usi_smartchoice_ai_default_tax_percent');
        if ($taxRate <= 0) { $taxRate = 0; }
        $overheadRate = (float)get_option('usi_smartchoice_ai_default_overhead_percent');
        if ($overheadRate <= 0) { $overheadRate = 10; }
        $profitRate = (float)get_option('usi_smartchoice_ai_default_profit_percent');
        if ($profitRate <= 0) { $profitRate = 20; }
        $tax = round($subtotal * ($taxRate / 100), 2);
        $overhead = round($subtotal * ($overheadRate / 100), 2);
        $profit = round($subtotal * ($profitRate / 100), 2);
        $this->db->where('id', $id)->update($runs, [
            'historical_match_count' => $matches,
            'confidence_score' => $matches > 0 ? min(95, 50 + ($matches * 5)) : 20,
            'subtotal' => round($subtotal, 2),
            'tax_total' => $tax,
            'overhead_total' => $overhead,
            'profit_total' => $profit,
            'grand_total' => round($subtotal + $tax + $overhead + $profit, 2),
            'status' => $matches > 0 ? 'calculated' : 'needs_review',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function rebuild_estimate_price_patterns(): int
    {
        $patterns = db_prefix() . 'usi_ai_estimate_price_patterns';
        if (!$this->db->table_exists($patterns)) { return 0; }
        $this->db->empty_table($patterns);
        $created = 0;
        $sources = [
            ['table' => 'usi_ai_price_index', 'service' => 'pricing_index'],
            ['table' => 'usi_ai_estimates', 'service' => 'ai_estimate'],
            ['table' => 'estimate_items', 'service' => 'crm_estimate_item'],
            ['table' => 'itemable', 'service' => 'crm_itemable'],
        ];
        foreach ($sources as $source) {
            $table = db_prefix() . $source['table'];
            if (!$this->db->table_exists($table)) { continue; }
            $rows = $this->db->limit(100)->get($table)->result_array();
            $amounts = [];
            foreach ($rows as $row) {
                foreach (['rate','unit_price','price','line_total','total','grand_total','amount'] as $field) {
                    if (isset($row[$field]) && (float)$row[$field] > 0) { $amounts[] = (float)$row[$field]; break; }
                }
            }
            if (empty($amounts)) { continue; }
            $avg = array_sum($amounts) / count($amounts);
            $this->db->insert($patterns, [
                'pattern_name' => 'Historical ' . ucwords(str_replace('_', ' ', $source['service'])),
                'service_type' => $source['service'],
                'city' => '',
                'county' => '',
                'sample_count' => count($amounts),
                'average_unit_price' => round($avg, 2),
                'average_total' => round($avg, 2),
                'low_total' => round(min($amounts), 2),
                'high_total' => round(max($amounts), 2),
                'notes' => 'Generated from ' . $source['table'] . ' during Advanced Estimating Intelligence rebuild.',
                'last_rebuilt_at' => date('Y-m-d H:i:s'),
            ]);
            $created++;
        }
        return $created;
    }


    public function advanced_vision_summary(): array
    {
        $tables = [
            'Advanced Vision Sessions' => 'usi_ai_advanced_vision_sessions',
            'Detected Objects' => 'usi_ai_advanced_vision_detections',
            'Measurement Records' => 'usi_ai_advanced_vision_measurements',
            'Vision Sessions' => 'usi_ai_vision_sessions',
            'AI Photos' => 'usi_ai_photos',
        ];
        $summary = [];
        foreach ($tables as $label => $table) {
            $full = db_prefix() . $table;
            $summary[$label] = $this->db->table_exists($full) ? (int)$this->db->count_all_results($full) : 0;
        }
        return $summary;
    }

    public function get_advanced_vision_sessions(int $limit = 50): array
    {
        $table = db_prefix() . 'usi_ai_advanced_vision_sessions';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function get_advanced_vision_detections(int $limit = 100): array
    {
        $table = db_prefix() . 'usi_ai_advanced_vision_detections';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function get_advanced_vision_measurements(int $limit = 100): array
    {
        $table = db_prefix() . 'usi_ai_advanced_vision_measurements';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function create_advanced_vision_session(array $data): int
    {
        $table = db_prefix() . 'usi_ai_advanced_vision_sessions';
        if (!$this->db->table_exists($table)) { return 0; }
        $insert = [
            'session_title' => trim((string)($data['session_title'] ?? 'Advanced Vision Session')),
            'related_type' => trim((string)($data['related_type'] ?? 'project')),
            'related_id' => (int)($data['related_id'] ?? 0),
            'service_type' => trim((string)($data['service_type'] ?? 'general')),
            'room_area' => trim((string)($data['room_area'] ?? '')),
            'photo_reference' => trim((string)($data['photo_reference'] ?? '')),
            'analysis_prompt' => (string)($data['analysis_prompt'] ?? ''),
            'measurement_notes' => (string)($data['measurement_notes'] ?? ''),
            'detected_summary' => '',
            'confidence_score' => 0,
            'status' => trim((string)($data['status'] ?? 'draft')),
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($table, $insert);
        return (int)$this->db->insert_id();
    }

    public function analyze_advanced_vision(int $id): bool
    {
        $sessions = db_prefix() . 'usi_ai_advanced_vision_sessions';
        $detections = db_prefix() . 'usi_ai_advanced_vision_detections';
        $measurements = db_prefix() . 'usi_ai_advanced_vision_measurements';
        if (!$this->db->table_exists($sessions) || !$this->db->table_exists($detections) || !$this->db->table_exists($measurements)) { return false; }
        $session = $this->db->where('id', $id)->get($sessions)->row_array();
        if (!$session) { return false; }
        $this->db->where('session_id', $id)->delete($detections);
        $this->db->where('session_id', $id)->delete($measurements);
        $service = strtolower((string)($session['service_type'] ?? 'general'));
        $objects = ['work area', 'existing condition', 'material surface'];
        if (strpos($service, 'window') !== false) { $objects = ['window opening', 'trim', 'sill', 'exterior wall']; }
        if (strpos($service, 'door') !== false) { $objects = ['door opening', 'jamb', 'threshold', 'hardware']; }
        if (strpos($service, 'drywall') !== false) { $objects = ['drywall surface', 'joint area', 'damage zone', 'texture']; }
        if (strpos($service, 'floor') !== false || strpos($service, 'tile') !== false) { $objects = ['floor area', 'transition', 'baseboard', 'material pattern']; }
        $count = 0;
        foreach ($objects as $object) {
            $this->db->insert($detections, [
                'session_id' => $id,
                'object_name' => $object,
                'object_type' => $service ?: 'general',
                'confidence_score' => 75 + ($count * 3),
                'notes' => 'Generated field-ready detection placeholder from service type and notes. Final AI vision provider can replace this with live image analysis.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }
        $measurementNotes = trim((string)($session['measurement_notes'] ?? ''));
        $measurementText = $measurementNotes !== '' ? $measurementNotes : 'Measurement notes not provided. Field verification required.';
        $this->db->insert($measurements, [
            'session_id' => $id,
            'measurement_label' => 'Field Measurement Review',
            'measurement_type' => 'manual_or_ai_assisted',
            'measurement_value' => 0,
            'unit' => 'field verify',
            'confidence_score' => $measurementNotes !== '' ? 70 : 35,
            'notes' => $measurementText,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $id)->update($sessions, [
            'detected_summary' => 'Detected ' . $count . ' likely jobsite elements for ' . ($session['service_type'] ?: 'general') . '. Measurement confidence depends on field notes and photo quality.',
            'confidence_score' => $measurementNotes !== '' ? 78 : 55,
            'status' => 'analyzed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function rebuild_advanced_vision_index(): int
    {
        $target = db_prefix() . 'usi_ai_advanced_vision_sessions';
        if (!$this->db->table_exists($target)) { return 0; }
        $created = 0;
        $sources = [
            ['table' => 'usi_ai_vision_sessions', 'title' => 'Vision Session'],
            ['table' => 'usi_ai_photos', 'title' => 'AI Photo'],
            ['table' => 'usi_ai_field_verifications', 'title' => 'Field Verification'],
        ];
        foreach ($sources as $source) {
            $table = db_prefix() . $source['table'];
            if (!$this->db->table_exists($table)) { continue; }
            $rows = $this->db->limit(25)->order_by('id', 'DESC')->get($table)->result_array();
            foreach ($rows as $row) {
                $relatedId = (int)($row['id'] ?? 0);
                $exists = (int)$this->db->where('related_type', $source['table'])->where('related_id', $relatedId)->count_all_results($target);
                if ($exists > 0) { continue; }
                $title = (string)($row['title'] ?? $row['session_title'] ?? $row['photo_title'] ?? $source['title'] . ' #' . $relatedId);
                $this->db->insert($target, [
                    'session_title' => $title,
                    'related_type' => $source['table'],
                    'related_id' => $relatedId,
                    'service_type' => (string)($row['service_type'] ?? 'general'),
                    'room_area' => (string)($row['room_area'] ?? ''),
                    'photo_reference' => (string)($row['file_name'] ?? $row['photo_reference'] ?? ''),
                    'analysis_prompt' => 'Imported into Advanced Vision Intelligence from ' . $source['table'] . '.',
                    'measurement_notes' => (string)($row['measurement_notes'] ?? $row['notes'] ?? ''),
                    'detected_summary' => '',
                    'confidence_score' => 0,
                    'status' => 'indexed',
                    'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $created++;
            }
        }
        return $created;
    }


    public function multi_agent_summary(): array
    {
        $agents = db_prefix() . 'usi_ai_agents';
        $tasks = db_prefix() . 'usi_ai_agent_tasks';
        $logs = db_prefix() . 'usi_ai_agent_logs';
        return [
            'Active Agents' => $this->db->table_exists($agents) ? (int)$this->db->where('status', 'active')->count_all_results($agents) : 0,
            'Queued Tasks' => $this->db->table_exists($tasks) ? (int)$this->db->where('status', 'queued')->count_all_results($tasks) : 0,
            'Completed Tasks' => $this->db->table_exists($tasks) ? (int)$this->db->where('status', 'completed')->count_all_results($tasks) : 0,
            'Agent Logs' => $this->db->table_exists($logs) ? (int)$this->db->count_all_results($logs) : 0,
        ];
    }

    public function get_ai_agents(): array
    {
        $table = db_prefix() . 'usi_ai_agents';
        if (!$this->db->table_exists($table)) { return []; }
        return $this->db->order_by('agent_role', 'ASC')->order_by('agent_name', 'ASC')->get($table)->result_array();
    }

    public function get_ai_agent_tasks(): array
    {
        $tasks = db_prefix() . 'usi_ai_agent_tasks';
        if (!$this->db->table_exists($tasks)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get($tasks)->result_array();
    }

    public function get_ai_agent_logs(): array
    {
        $logs = db_prefix() . 'usi_ai_agent_logs';
        if (!$this->db->table_exists($logs)) { return []; }
        return $this->db->order_by('id', 'DESC')->limit(100)->get($logs)->result_array();
    }

    public function create_ai_agent_task(array $data): int
    {
        $table = db_prefix() . 'usi_ai_agent_tasks';
        if (!$this->db->table_exists($table)) { return 0; }
        $insert = [
            'agent_id' => (int)($data['agent_id'] ?? 0),
            'task_title' => trim((string)($data['task_title'] ?? 'AI Agent Task')),
            'related_type' => trim((string)($data['related_type'] ?? 'general')),
            'related_id' => (int)($data['related_id'] ?? 0),
            'task_prompt' => trim((string)($data['task_prompt'] ?? '')),
            'task_result' => '',
            'confidence_score' => 0,
            'status' => 'queued',
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($table, $insert);
        return (int)$this->db->insert_id();
    }

    public function run_ai_agent_task(int $id): bool
    {
        $tasks = db_prefix() . 'usi_ai_agent_tasks';
        $agents = db_prefix() . 'usi_ai_agents';
        $logs = db_prefix() . 'usi_ai_agent_logs';
        if (!$this->db->table_exists($tasks)) { return false; }
        $task = $this->db->where('id', $id)->get($tasks)->row_array();
        if (!$task) { return false; }
        $agent = [];
        if ($this->db->table_exists($agents)) {
            $agent = $this->db->where('id', (int)$task['agent_id'])->get($agents)->row_array() ?: [];
        }
        $role = (string)($agent['agent_role'] ?? 'general');
        $result = 'Sammy AI ' . ucfirst(str_replace('_', ' ', $role)) . ' reviewed the request and prepared a draft action plan. Human approval is required before creating customer-facing, financial, or destructive CRM actions.';
        $this->db->where('id', $id)->update($tasks, [
            'task_result' => $result . "\n\nPrompt: " . (string)$task['task_prompt'],
            'confidence_score' => 82,
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($this->db->table_exists($logs)) {
            $this->db->insert($logs, [
                'agent_id' => (int)$task['agent_id'],
                'task_id' => $id,
                'log_type' => 'completed',
                'message' => 'Agent task completed through Multi-Agent AI workflow.',
                'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

}
