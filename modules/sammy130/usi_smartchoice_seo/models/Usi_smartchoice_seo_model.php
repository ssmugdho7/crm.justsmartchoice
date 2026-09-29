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
        $required = ['usi_seo_pages', 'usi_seo_keywords', 'usi_seo_reports', 'usi_ai_commands', 'usi_ai_estimates', 'usi_ai_photos', 'usi_ai_actions', 'usi_ai_training', 'usi_ai_price_index', 'usi_ai_estimate_lines', 'usi_ai_estimate_sources', 'usi_ai_estimate_approvals', 'usi_ai_customer_packages', 'usi_ai_field_verifications', 'usi_ai_communications', 'usi_ai_voices', 'usi_ai_avatars', 'usi_ai_videos', 'usi_ai_video_text_layers'];
        $checks = [];
        foreach ($required as $table) { $checks[] = ['label' => 'Database table ' . db_prefix() . $table, 'status' => usi_smartchoice_seo_table_exists($table)]; }
        $checks[] = ['label' => 'English language file', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'language/english/module_lang.php'))];
        $checks[] = ['label' => 'Spanish language file', 'status' => file_exists(module_dir_path('usi_smartchoice_seo', 'language/spanish/module_lang.php'))];
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
}
