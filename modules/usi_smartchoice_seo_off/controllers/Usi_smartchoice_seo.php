<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_model', 'usi_seo');
    }

    public function index(): void { $this->ai_dashboard(); }

    public function ai_dashboard(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_dashboard');
        $data['counts'] = $this->usi_seo->dashboard_counts();
        $this->load->view('usi_smartchoice_seo/ai/dashboard', $data);
    }


    public function voice_assistant(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_voice_assistant');
        $data['recent_commands'] = $this->usi_seo->get_ai_commands(['command_source' => 'voice']);
        $this->load->view('usi_smartchoice_seo/voice/manage', $data);
    }

    public function camera_intake(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_camera_intake');
        $data['recent_leads'] = $this->usi_seo->get_recent_crm_leads();
        $data['recent_customers'] = $this->usi_seo->get_recent_crm_customers();
        $this->load->view('usi_smartchoice_seo/camera/manage', $data);
    }

    public function save_camera_intake(): void
    {
        $this->require_create_or_edit(0);
        if (!$this->input->post()) {
            redirect(admin_url('usi_smartchoice_seo/camera_intake'));
        }
        $post = $this->input->post(null, false);
        $crmResult = $this->usi_seo->resolve_camera_intake_crm_records($post);
        if (!empty($crmResult['customer_id'])) { $post['customer_id'] = (int)$crmResult['customer_id']; }
        if (!empty($crmResult['lead_id'])) { $post['lead_id'] = (int)$crmResult['lead_id']; }
        if (!empty($crmResult['message'])) {
            $post['ai_observations'] = trim((string)($post['ai_observations'] ?? '') . "
" . $crmResult['message']);
        }
        if ($this->input->post('run_estimate')) {
            $post = $this->usi_seo->calculate_estimate_payload($post);
        }
        $savedId = $this->usi_seo->save_ai_estimate($post, 0);
        if ($this->input->post('run_estimate')) { $this->usi_seo->calculate_ai_estimate_numbers($savedId); }
        if (!empty($_FILES['photos']['name'][0])) {
            $this->usi_seo->save_ai_photos($savedId, $_FILES['photos'], $post);
        } elseif (!empty($_FILES['photo']['name'])) {
            $this->usi_seo->save_ai_photo($savedId, $_FILES['photo'], $post);
        }
        set_alert('success', $this->input->post('run_estimate') ? 'Camera intake saved, CRM records checked, and estimate calculated.' : _l('usi_smartchoice_ai_camera_saved'));
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $savedId));
    }

    public function voice_preview(): void
    {
        $this->require_view();
        $commandText = trim((string)$this->input->post('command_text', false));
        $result = $this->usi_seo->preview_voice_command($commandText);
        $this->json_response($result);
    }

    public function voice_save(): void
    {
        $this->require_create_or_edit(0);
        $commandText = trim((string)$this->input->post('command_text', false));
        $result = $this->usi_seo->save_voice_command($commandText);
        $this->json_response($result);
    }

    public function voice_execute(): void
    {
        $this->require_create_or_edit(0);
        $commandText = trim((string)$this->input->post('command_text', false));
        $commandId = (int)$this->input->post('command_id', true);
        $result = $this->usi_seo->execute_voice_command($commandText, $commandId);
        $this->json_response($result);
    }


    public function voice_run_estimate(): void
    {
        $this->require_create_or_edit(0);
        $commandText = trim((string)$this->input->post('command_text', false));
        $result = $this->usi_seo->create_estimate_from_voice($commandText);
        $this->json_response($result);
    }

    public function check_api_key(): void
    {
        $this->require_edit();
        $this->json_response($this->usi_seo->check_ai_api_key());
    }

    public function ai_commands(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_commands');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['commands'] = $this->usi_seo->get_ai_commands($data['filters']);
        $this->load->view('usi_smartchoice_seo/ai_commands/manage', $data);
    }

    public function ai_command(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_command($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_ai_command/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_command') : _l('usi_smartchoice_ai_new_command');
        $data['command'] = $id > 0 ? $this->usi_seo->get_ai_command($id) : null;
        $this->load->view('usi_smartchoice_seo/ai_commands/form', $data);
    }

    public function view_ai_command(int $id): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_view_command');
        $data['command'] = $this->usi_seo->get_ai_command($id);
        $this->load->view('usi_smartchoice_seo/ai_commands/view', $data);
    }

    public function delete_ai_command(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_command($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_commands'));
    }

    public function mass_delete_ai_commands(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_ai_commands($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_commands'));
    }

    public function ai_estimates(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_estimates');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['estimates'] = $this->usi_seo->get_ai_estimates($data['filters']);
        $this->load->view('usi_smartchoice_seo/ai_estimates/manage', $data);
    }


    public function rebuild_price_index(): void
    {
        $this->require_edit();
        $result = $this->usi_seo->rebuild_price_index();
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/ai_estimates'));
    }


    public function pricing_engine(): void
    {
        $this->require_view();
        $data['title'] = 'Pricing Engine';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['price_rows'] = $this->usi_seo->get_price_index($data['filters']);
        $data['counts'] = $this->usi_seo->pricing_engine_counts();
        $this->load->view('usi_smartchoice_seo/pricing_engine/manage', $data);
    }

    public function calculate_ai_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->calculate_ai_estimate_numbers($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }

    public function create_crm_estimate_from_ai(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_crm_estimate_from_ai($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['estimate_id'])) {
            redirect(admin_url('estimates/list_estimates/' . (int)$result['estimate_id']));
        }
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }


    public function estimate_review_queue(): void
    {
        $this->require_view();
        $data['title'] = 'Estimate Review';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['estimates'] = $this->usi_seo->get_estimate_review_queue($data['filters']);
        $this->load->view('usi_smartchoice_seo/ai_estimates/review_queue', $data);
    }

    public function estimate_review(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Estimate Review';
        $data['estimate'] = $this->usi_seo->get_ai_estimate($id);
        $data['lines'] = $this->usi_seo->get_ai_estimate_lines($id);
        $data['sources'] = $this->usi_seo->get_ai_estimate_sources($id);
        $data['approval_log'] = $this->usi_seo->get_ai_estimate_approval_log($id);
        $this->load->view('usi_smartchoice_seo/ai_estimates/review', $data);
    }

    public function save_estimate_review(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->save_estimate_review($id, $this->input->post(null, false) ?: []);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/estimate_review/' . $id));
    }

    public function prepare_customer_scope(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->prepare_customer_scope($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/estimate_review/' . $id));
    }

    public function approve_ai_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->approve_ai_estimate($id, trim((string)$this->input->post('approval_notes', false)));
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/estimate_review/' . $id));
    }

    public function mark_ai_estimate_needs_revision(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_ai_estimate_needs_revision($id, trim((string)$this->input->post('revision_notes', false)));
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/estimate_review/' . $id));
    }


    public function customer_packages(): void
    {
        $this->require_view();
        $data['title'] = 'Customer Packages';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['packages'] = $this->usi_seo->get_customer_packages($data['filters']);
        $this->load->view('usi_smartchoice_seo/customer_packages/manage', $data);
    }

    public function customer_package(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Customer Package';
        $data['package'] = $this->usi_seo->get_customer_package($id);
        $this->load->view('usi_smartchoice_seo/customer_packages/view', $data);
    }

    public function build_customer_package(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->build_customer_package_from_ai_estimate($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['package_id'])) {
            redirect(admin_url('usi_smartchoice_seo/customer_package/' . (int)$result['package_id']));
        }
        redirect(admin_url('usi_smartchoice_seo/estimate_review/' . $id));
    }

    public function save_customer_package(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->update_customer_package($id, $this->input->post(null, false) ?: []);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/customer_package/' . $id));
    }

    public function mark_customer_package_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_customer_package_ready($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/customer_package/' . $id));
    }


    public function field_verifications(): void
    {
        $this->require_view();
        $data['title'] = 'Field Verification';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['verifications'] = $this->usi_seo->get_field_verifications($data['filters']);
        $this->load->view('usi_smartchoice_seo/field_verifications/manage', $data);
    }

    public function field_verification(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_field_verification($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_field_verification/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Field Verification' : 'New Field Verification';
        $data['verification'] = $id > 0 ? $this->usi_seo->get_field_verification($id) : null;
        $this->load->view('usi_smartchoice_seo/field_verifications/form', $data);
    }

    public function view_field_verification(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Field Verification Detail';
        $data['verification'] = $this->usi_seo->get_field_verification($id);
        $data['estimate'] = !empty($data['verification']['ai_estimate_id']) ? $this->usi_seo->get_ai_estimate((int)$data['verification']['ai_estimate_id']) : null;
        $this->load->view('usi_smartchoice_seo/field_verifications/view', $data);
    }

    public function create_field_verification_from_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_field_verification_from_estimate($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['verification_id'])) {
            redirect(admin_url('usi_smartchoice_seo/view_field_verification/' . (int)$result['verification_id']));
        }
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }

    public function mark_field_verification_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_field_verification_ready($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_field_verification/' . $id));
    }

    public function delete_field_verification(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_field_verification($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/field_verifications'));
    }

    public function mass_delete_field_verifications(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_field_verifications($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/field_verifications'));
    }

    public function ai_estimate(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $post = $this->input->post(null, false);
            if ($this->input->post('run_estimate')) {
                $post = $this->usi_seo->calculate_estimate_payload($post);
            }
            $savedId = $this->usi_seo->save_ai_estimate($post, $id);
            if ($this->input->post('run_estimate')) { $this->usi_seo->calculate_ai_estimate_numbers($savedId); }
            if (!empty($_FILES['photo']['name'])) {
                $this->usi_seo->save_ai_photo($savedId, $_FILES['photo'], $post);
            }
            set_alert('success', $this->input->post('run_estimate') ? 'Estimate calculated and saved.' : _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_estimate') : _l('usi_smartchoice_ai_new_estimate');
        $data['estimate'] = $id > 0 ? $this->usi_seo->get_ai_estimate($id) : null;
        $data['photos'] = $id > 0 ? $this->usi_seo->get_ai_estimate_photos($id) : [];
        $this->load->view('usi_smartchoice_seo/ai_estimates/form', $data);
    }

    public function view_ai_estimate(int $id): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_view_estimate');
        $data['estimate'] = $this->usi_seo->get_ai_estimate($id);
        $data['photos'] = $this->usi_seo->get_ai_estimate_photos($id);
        $data['lines'] = $this->usi_seo->get_ai_estimate_lines($id);
        $data['sources'] = $this->usi_seo->get_ai_estimate_sources($id);
        $this->load->view('usi_smartchoice_seo/ai_estimates/view', $data);
    }

    public function delete_ai_estimate(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_estimate($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_estimates'));
    }

    public function mass_delete_ai_estimates(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_ai_estimates($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_estimates'));
    }

    public function ai_training(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_training');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['training'] = $this->usi_seo->get_ai_training($data['filters']);
        $this->load->view('usi_smartchoice_seo/ai_training/manage', $data);
    }

    public function ai_training_item(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_training($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/ai_training_item/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_training') : _l('usi_smartchoice_ai_new_training');
        $data['item'] = $id > 0 ? $this->usi_seo->get_ai_training_item($id) : null;
        $this->load->view('usi_smartchoice_seo/ai_training/form', $data);
    }

    public function delete_ai_training(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_training($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_training'));
    }

    public function mass_delete_ai_training(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_ai_training($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/ai_training'));
    }


    public function video_studio(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_video_studio');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['videos'] = $this->usi_seo->get_ai_videos($data['filters']);
        $this->load->view('usi_smartchoice_seo/video_studio/manage', $data);
    }

    public function video_project(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_video($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_video_project/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_video') : _l('usi_smartchoice_ai_new_video');
        $data['video'] = $id > 0 ? $this->usi_seo->get_ai_video($id) : null;
        $data['voices'] = $this->usi_seo->get_ai_voices([]);
        $data['avatars'] = $this->usi_seo->get_ai_avatars([]);
        $this->load->view('usi_smartchoice_seo/video_studio/form', $data);
    }

    public function view_video_project(int $id): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_view_video');
        $data['video'] = $this->usi_seo->get_ai_video($id);
        $data['layers'] = $this->usi_seo->get_video_text_layers($id);
        $this->load->view('usi_smartchoice_seo/video_studio/view', $data);
    }

    public function delete_video_project(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_video($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/video_studio'));
    }

    public function mass_delete_videos(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_ai_videos($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/video_studio'));
    }

    public function run_video_preview(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->run_video_preview($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_video_project/' . $id));
    }

    public function video_voices(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_voices');
        $data['voices'] = $this->usi_seo->get_ai_voices($this->input->get(null, true) ?: []);
        $this->load->view('usi_smartchoice_seo/video_voices/manage', $data);
    }

    public function video_voice(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_voice($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/video_voices'));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_voice') : _l('usi_smartchoice_ai_new_voice');
        $data['voice'] = $id > 0 ? $this->usi_seo->get_ai_voice($id) : null;
        $this->load->view('usi_smartchoice_seo/video_voices/form', $data);
    }

    public function delete_video_voice(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_voice($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/video_voices'));
    }

    public function video_avatars(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_ai_avatars');
        $data['avatars'] = $this->usi_seo->get_ai_avatars($this->input->get(null, true) ?: []);
        $this->load->view('usi_smartchoice_seo/video_avatars/manage', $data);
    }

    public function video_avatar(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_avatar($this->input->post(null, false), $id);
            if (!empty($_FILES['source_photo']['name'])) {
                $this->usi_seo->save_avatar_photo($savedId, $_FILES['source_photo']);
            }
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/video_avatars'));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_ai_edit_avatar') : _l('usi_smartchoice_ai_new_avatar');
        $data['avatar'] = $id > 0 ? $this->usi_seo->get_ai_avatar($id) : null;
        $this->load->view('usi_smartchoice_seo/video_avatars/form', $data);
    }

    public function delete_video_avatar(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_avatar($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/video_avatars'));
    }

    public function pages(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_seo_pages');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['pages'] = $this->usi_seo->get_pages($data['filters']);
        $this->load->view('usi_smartchoice_seo/pages/manage', $data);
    }

    public function page(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_page($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/page/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_seo_edit_page') : _l('usi_smartchoice_seo_new_page');
        $data['page'] = $id > 0 ? $this->usi_seo->get_page($id) : null;
        $this->load->view('usi_smartchoice_seo/pages/form', $data);
    }

    public function view_page(int $id): void { $this->require_view(); $data['title'] = _l('usi_smartchoice_seo_view_page'); $data['page'] = $this->usi_seo->get_page($id); $this->load->view('usi_smartchoice_seo/pages/view', $data); }
    public function delete_page(int $id): void { $this->require_delete(); $this->usi_seo->delete_page($id); set_alert('success', _l('usi_smartchoice_seo_deleted')); redirect(admin_url('usi_smartchoice_seo/pages')); }
    public function mass_delete_pages(): void { $this->require_delete(); $deleted = $this->usi_seo->mass_delete_pages($this->input->post('ids') ?: []); set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted')); redirect(admin_url('usi_smartchoice_seo/pages')); }

    public function keywords(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_seo_keywords');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['keywords'] = $this->usi_seo->get_keywords($data['filters']);
        $this->load->view('usi_smartchoice_seo/keywords/manage', $data);
    }

    public function keyword(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_keyword($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/keyword/' . $savedId));
        }
        $data['title'] = $id > 0 ? _l('usi_smartchoice_seo_edit_keyword') : _l('usi_smartchoice_seo_new_keyword');
        $data['keyword'] = $id > 0 ? $this->usi_seo->get_keyword($id) : null;
        $this->load->view('usi_smartchoice_seo/keywords/form', $data);
    }

    public function view_keyword(int $id): void { $this->require_view(); $data['title'] = _l('usi_smartchoice_seo_view_keyword'); $data['keyword'] = $this->usi_seo->get_keyword($id); $this->load->view('usi_smartchoice_seo/keywords/view', $data); }
    public function delete_keyword(int $id): void { $this->require_delete(); $this->usi_seo->delete_keyword($id); set_alert('success', _l('usi_smartchoice_seo_deleted')); redirect(admin_url('usi_smartchoice_seo/keywords')); }
    public function mass_delete_keywords(): void { $this->require_delete(); $deleted = $this->usi_seo->mass_delete_keywords($this->input->post('ids') ?: []); set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted')); redirect(admin_url('usi_smartchoice_seo/keywords')); }

    public function reports(): void
    {
        $this->require_view();
        $data['title'] = _l('usi_smartchoice_seo_reports');
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['reports'] = $this->usi_seo->get_reports($data['filters']);
        $this->load->view('usi_smartchoice_seo/reports/manage', $data);
    }

    public function mass_delete_reports(): void { $this->require_delete(); $deleted = $this->usi_seo->mass_delete_reports($this->input->post('ids') ?: []); set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted')); redirect(admin_url('usi_smartchoice_seo/reports')); }

    public function settings(): void
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_edit();
            foreach (['usi_smartchoice_seo_primary_domain','usi_smartchoice_seo_brand_name','usi_smartchoice_ai_enabled','usi_smartchoice_ai_voice_enabled','usi_smartchoice_ai_camera_enabled','usi_smartchoice_ai_default_estimate_status','usi_smartchoice_ai_require_estimate_approval','usi_smartchoice_ai_default_tax_percent','usi_smartchoice_ai_default_overhead_profit_percent','usi_smartchoice_ai_command_mode','usi_smartchoice_ai_api_provider','usi_smartchoice_ai_api_key','usi_smartchoice_ai_voice_continuous','usi_smartchoice_ai_voice_language','usi_smartchoice_ai_listen_seconds','usi_smartchoice_ai_mobile_grid_columns','usi_smartchoice_ai_video_enabled','usi_smartchoice_ai_video_provider','usi_smartchoice_ai_video_api_key','usi_smartchoice_ai_video_default_voice','usi_smartchoice_ai_video_logo_enabled','usi_smartchoice_ai_video_logo_url'] as $option) {
                update_option($option, trim((string)$this->input->post($option, true)));
            }
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/settings'));
        }
        $data['title'] = _l('usi_smartchoice_seo_settings');
        $this->load->view('usi_smartchoice_seo/settings/manage', $data);
    }

    public function health(): void { $this->require_view(); $data['title'] = _l('usi_smartchoice_seo_health'); $data['checks'] = $this->usi_seo->health(); $this->load->view('usi_smartchoice_seo/health/manage', $data); }
    public function repair_database(): void { $this->require_edit(); require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php'); usi_smartchoice_seo_ensure_schema(); set_alert('success', _l('usi_smartchoice_seo_database_repaired')); redirect(admin_url('usi_smartchoice_seo/health')); }
    public function help(): void { $this->require_view(); $data['title'] = _l('usi_smartchoice_seo_help'); $this->load->view('usi_smartchoice_seo/help/manage', $data); }


    public function import_video_voices(): void
    {
        $this->require_create_or_edit(0);
        $imported = 0;
        if (!empty($_FILES['import_file']['tmp_name']) && is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if ($handle !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    if (!$header || count($row) === 0) { continue; }
                    $data = array_combine(array_slice($header, 0, count($row)), $row);
                    if (!empty($data['voice_name'])) {
                        $this->usi_seo->save_ai_voice($data, 0);
                        $imported++;
                    }
                }
                fclose($handle);
            }
        }
        set_alert($imported > 0 ? 'success' : 'warning', $imported > 0 ? ($imported . ' voices imported.') : 'No voices imported. Use the Sample Header format.');
        redirect(admin_url('usi_smartchoice_seo/video_voices'));
    }

    public function import_video_avatars(): void
    {
        $this->require_create_or_edit(0);
        $imported = 0;
        if (!empty($_FILES['import_file']['tmp_name']) && is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if ($handle !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    if (!$header || count($row) === 0) { continue; }
                    $data = array_combine(array_slice($header, 0, count($row)), $row);
                    if (!empty($data['avatar_name'])) {
                        $this->usi_seo->save_ai_avatar($data, 0);
                        $imported++;
                    }
                }
                fclose($handle);
            }
        }
        set_alert($imported > 0 ? 'success' : 'warning', $imported > 0 ? ($imported . ' avatars imported.') : 'No avatars imported. Use the Sample Header format.');
        redirect(admin_url('usi_smartchoice_seo/video_avatars'));
    }

    public function sample_header(string $type): void
    {
        $headers = [
            'pages' => 'title,slug,page_type,meta_title,meta_description,primary_keyword,city,status,content',
            'keywords' => 'keyword,intent,city,priority,status,notes',
            'reports' => 'report_date,summary,pages_created,keywords_targeted,issues_found',
            'ai_commands' => 'command_text,command_source,intent,target_module,target_record_type,target_record_id,action_status,review_notes',
            'ai_estimates' => 'title,customer_id,lead_id,project_id,location,scope_summary,measurement_notes,materials_summary,labor_summary,subtotal,tax_total,total,status,review_status,customer_scope,exclusions,payment_schedule,estimator_review_notes,field_verified',
            'ai_training' => 'title,category,prompt_text,expected_behavior,status',
            'videos' => 'title,script_text,language,voice_id,avatar_id,video_purpose,status,logo_enabled,logo_position,ai_notes',
            'voices' => 'voice_name,language,gender,provider_voice_id,sample_text,status',
            'avatars' => 'avatar_name,avatar_type,position_name,provider_avatar_id,status',
            'price_index' => 'source_type,source_id,service_keyword,description,quantity,unit,unit_rate,line_total,city,confidence',
            'field_verifications' => 'ai_estimate_id,customer_id,project_id,verification_title,jobsite_address,measurement_summary,inspection_checklist,photo_notes,field_status,ready_for_estimate',
            'project_handoffs' => 'ai_estimate_id,customer_package_id,field_verification_id,customer_id,project_id,handoff_title,project_name,handoff_status,assigned_staff_id,start_date,due_date,production_notes,material_notes,permit_notes,task_plan',
            'communications' => 'ai_estimate_id,customer_package_id,project_handoff_id,customer_id,lead_id,communication_type,language,subject,message_body,send_status',
            'memory_items' => 'source_type,source_id,source_title,customer_id,lead_id,project_id,city,service_category,memory_text,summary,keywords,amount_total,record_date,memory_status',
            'purchase_orders' => 'po_number,title,takeoff_id,ai_estimate_id,vendor_id,customer_id,project_id,status,delivery_fee,requested_date,delivery_address,vendor_notes,internal_notes',
        ];
        $line = $headers[$type] ?? 'name,status,notes';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_sample_header.csv"');
        echo $line . "\n";
        exit;
    }

    public function export(string $type): void
    {
        $this->require_view();
        $rows = [];
        if ($type === 'ai_commands') { $rows = $this->usi_seo->get_ai_commands([]); }
        if ($type === 'ai_estimates') { $rows = $this->usi_seo->get_ai_estimates([]); }
        if ($type === 'ai_training') { $rows = $this->usi_seo->get_ai_training([]); }
        if ($type === 'pages') { $rows = $this->usi_seo->get_pages([]); }
        if ($type === 'keywords') { $rows = $this->usi_seo->get_keywords([]); }
        if ($type === 'reports') { $rows = $this->usi_seo->get_reports([]); }
        if ($type === 'videos') { $rows = $this->usi_seo->get_ai_videos([]); }
        if ($type === 'voices') { $rows = $this->usi_seo->get_ai_voices([]); }
        if ($type === 'avatars') { $rows = $this->usi_seo->get_ai_avatars([]); }
        if ($type === 'price_index') { $rows = $this->usi_seo->get_price_index([]); }
        if ($type === 'field_verifications') { $rows = $this->usi_seo->get_field_verifications([]); }
        if ($type === 'project_handoffs') { $rows = $this->usi_seo->get_project_handoffs([]); }
        if ($type === 'communications') { $rows = $this->usi_seo->get_communications([]); }
        if ($type === 'memory_items') { $rows = $this->usi_seo->get_memory_items([]); }
        if ($type === 'purchase_orders') { $rows = $this->usi_seo->get_purchase_orders([]); }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_export.csv"');
        $out = fopen('php://output', 'w');
        if (!empty($rows)) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $row) { fputcsv($out, $row); } }
        fclose($out);
        exit;
    }


    public function project_handoffs(): void
    {
        $this->require_view();
        $data['title'] = 'Project Handoff';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['handoffs'] = $this->usi_seo->get_project_handoffs($data['filters']);
        $this->load->view('usi_smartchoice_seo/project_handoffs/manage', $data);
    }

    public function project_handoff(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_project_handoff($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Project Handoff' : 'New Project Handoff';
        $data['handoff'] = $id > 0 ? $this->usi_seo->get_project_handoff($id) : null;
        $this->load->view('usi_smartchoice_seo/project_handoffs/form', $data);
    }

    public function view_project_handoff(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Project Handoff Detail';
        $data['handoff'] = $this->usi_seo->get_project_handoff($id);
        $data['tasks'] = $this->usi_seo->get_project_handoff_tasks($id);
        $this->load->view('usi_smartchoice_seo/project_handoffs/view', $data);
    }

    public function build_project_handoff_from_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->build_project_handoff_from_estimate($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['handoff_id'])) { redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . (int)$result['handoff_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }

    public function generate_project_handoff_tasks(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_project_handoff_tasks($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . $id));
    }

    public function mark_project_handoff_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_project_handoff_ready($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . $id));
    }

    public function create_crm_tasks_from_handoff(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_crm_tasks_from_handoff($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . $id));
    }

    public function delete_project_handoff(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_project_handoff($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/project_handoffs'));
    }

    public function mass_delete_project_handoffs(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_project_handoffs($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/project_handoffs'));
    }


    public function communications(): void
    {
        $this->require_view();
        $data['title'] = 'Customer Communications';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['communications'] = $this->usi_seo->get_communications($data['filters']);
        $this->load->view('usi_smartchoice_seo/communications/manage', $data);
    }

    public function communication(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_communication($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_communication/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Customer Communication' : 'New Customer Communication';
        $data['communication'] = $id > 0 ? $this->usi_seo->get_communication($id) : null;
        $this->load->view('usi_smartchoice_seo/communications/form', $data);
    }

    public function view_communication(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Customer Communication Detail';
        $data['communication'] = $this->usi_seo->get_communication($id);
        $this->load->view('usi_smartchoice_seo/communications/view', $data);
    }

    public function build_follow_up_from_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->build_follow_up_from_estimate($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['communication_id'])) { redirect(admin_url('usi_smartchoice_seo/view_communication/' . (int)$result['communication_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }

    public function build_follow_up_from_package(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->build_follow_up_from_package($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['communication_id'])) { redirect(admin_url('usi_smartchoice_seo/view_communication/' . (int)$result['communication_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_customer_package/' . $id));
    }

    public function mark_communication_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_communication_ready($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_communication/' . $id));
    }

    public function mark_communication_sent(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_communication_sent($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_communication/' . $id));
    }

    public function delete_communication(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_communication($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/communications'));
    }

    public function mass_delete_communications(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_communications($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/communications'));
    }


    public function memory_engine(): void
    {
        $this->require_view();
        $data['title'] = 'AI Memory Engine';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['counts'] = $this->usi_seo->memory_counts();
        $data['items'] = $this->usi_seo->get_memory_items($data['filters']);
        $data['runs'] = $this->usi_seo->get_memory_runs();
        $this->load->view('usi_smartchoice_seo/memory_engine/manage', $data);
    }

    public function memory_item(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_memory_item($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_memory_item/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Memory Item' : 'New Memory Item';
        $data['item'] = $id > 0 ? $this->usi_seo->get_memory_item($id) : null;
        $this->load->view('usi_smartchoice_seo/memory_engine/form', $data);
    }

    public function view_memory_item(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Memory Item Detail';
        $data['item'] = $this->usi_seo->get_memory_item($id);
        $data['links'] = $this->usi_seo->get_memory_links($id);
        $this->load->view('usi_smartchoice_seo/memory_engine/view', $data);
    }

    public function rebuild_memory_index(): void
    {
        $this->require_edit();
        $result = $this->usi_seo->rebuild_memory_index();
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/memory_engine'));
    }

    public function memory_search(): void
    {
        $this->require_view();
        $query = trim((string)$this->input->post('query', false));
        $data['title'] = 'AI Memory Search';
        $data['query'] = $query;
        $data['counts'] = $this->usi_seo->memory_counts();
        $data['items'] = $this->usi_seo->search_memory($query);
        $data['runs'] = $this->usi_seo->get_memory_runs();
        $this->load->view('usi_smartchoice_seo/memory_engine/manage', $data);
    }

    public function delete_memory_item(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_memory_item($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/memory_engine'));
    }

    public function mass_delete_memory_items(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_memory_items($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/memory_engine'));
    }


    public function document_intelligence(): void
    {
        $this->require_view();
        $data['title'] = 'Document Intelligence';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['counts'] = $this->usi_seo->document_counts();
        $data['documents'] = $this->usi_seo->get_ai_documents($data['filters']);
        $data['runs'] = $this->usi_seo->get_document_runs();
        $this->load->view('usi_smartchoice_seo/document_intelligence/manage', $data);
    }

    public function document_item(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_ai_document($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_document_item/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit AI Document' : 'New AI Document';
        $data['document'] = $id > 0 ? $this->usi_seo->get_ai_document($id) : null;
        $this->load->view('usi_smartchoice_seo/document_intelligence/form', $data);
    }

    public function view_document_item(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Document Detail';
        $data['document'] = $this->usi_seo->get_ai_document($id);
        $data['chunks'] = $this->usi_seo->get_document_chunks($id);
        $this->load->view('usi_smartchoice_seo/document_intelligence/view', $data);
    }

    public function rebuild_document_index(): void
    {
        $this->require_edit();
        $result = $this->usi_seo->rebuild_document_index();
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/document_intelligence'));
    }

    public function document_search(): void
    {
        $this->require_view();
        $query = trim((string)$this->input->post('query', false));
        $data['title'] = 'Document Search';
        $data['query'] = $query;
        $data['counts'] = $this->usi_seo->document_counts();
        $data['documents'] = $this->usi_seo->search_ai_documents($query);
        $data['runs'] = $this->usi_seo->get_document_runs();
        $this->load->view('usi_smartchoice_seo/document_intelligence/manage', $data);
    }

    public function delete_document_item(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_ai_document($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/document_intelligence'));
    }

    public function mass_delete_documents(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_ai_documents($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/document_intelligence'));
    }


    public function material_takeoffs(): void
    {
        $this->require_view();
        $data['title'] = 'Material Takeoff';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['counts'] = $this->usi_seo->takeoff_counts();
        $data['takeoffs'] = $this->usi_seo->get_takeoffs($data['filters']);
        $this->load->view('usi_smartchoice_seo/material_takeoffs/manage', $data);
    }

    public function material_takeoff(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_takeoff($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_material_takeoff/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Material Takeoff' : 'New Material Takeoff';
        $data['takeoff'] = $id > 0 ? $this->usi_seo->get_takeoff($id) : null;
        $data['estimates'] = $this->usi_seo->get_ai_estimates([]);
        $this->load->view('usi_smartchoice_seo/material_takeoffs/form', $data);
    }

    public function view_material_takeoff(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Material Takeoff Detail';
        $data['takeoff'] = $this->usi_seo->get_takeoff($id);
        $data['lines'] = $this->usi_seo->get_takeoff_lines($id);
        $this->load->view('usi_smartchoice_seo/material_takeoffs/view', $data);
    }

    public function build_takeoff_from_estimate(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->build_takeoff_from_estimate($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['takeoff_id'])) { redirect(admin_url('usi_smartchoice_seo/view_material_takeoff/' . (int)$result['takeoff_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_ai_estimate/' . $id));
    }

    public function calculate_material_takeoff(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->calculate_takeoff($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_material_takeoff/' . $id));
    }

    public function mark_takeoff_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_takeoff_ready($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_material_takeoff/' . $id));
    }

    public function delete_material_takeoff(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_takeoff($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/material_takeoffs'));
    }

    public function mass_delete_takeoffs(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_takeoffs($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/material_takeoffs'));
    }



    public function purchasing(): void
    {
        $this->require_view();
        $data['title'] = 'AI Purchasing';
        $data['filters'] = $this->input->get(null, true) ?: [];
        $data['counts'] = $this->usi_seo->purchasing_counts();
        $data['purchase_orders'] = $this->usi_seo->get_purchase_orders($data['filters']);
        $data['vendors'] = $this->usi_seo->get_vendors([]);
        $data['takeoffs'] = $this->usi_seo->get_takeoffs(['status' => 'ready_for_purchasing']);
        $this->load->view('usi_smartchoice_seo/purchasing/manage', $data);
    }

    public function vendor(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_vendor($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/purchasing?vendor_saved=' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Vendor' : 'New Vendor';
        $data['vendor'] = $id > 0 ? $this->usi_seo->get_vendor($id) : null;
        $this->load->view('usi_smartchoice_seo/purchasing/vendor_form', $data);
    }

    public function delete_vendor(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_vendor($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/purchasing'));
    }

    public function purchase_order(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_purchase_order($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_purchase_order/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Purchase Order' : 'New Purchase Order';
        $data['purchase_order'] = $id > 0 ? $this->usi_seo->get_purchase_order($id) : null;
        $data['vendors'] = $this->usi_seo->get_vendors(['status' => 'active']);
        $data['takeoffs'] = $this->usi_seo->get_takeoffs([]);
        $this->load->view('usi_smartchoice_seo/purchasing/order_form', $data);
    }

    public function view_purchase_order(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Purchase Order Detail';
        $data['purchase_order'] = $this->usi_seo->get_purchase_order($id);
        $data['lines'] = $this->usi_seo->get_purchase_order_lines($id);
        $data['vendor'] = !empty($data['purchase_order']['vendor_id']) ? $this->usi_seo->get_vendor((int)$data['purchase_order']['vendor_id']) : null;
        $this->load->view('usi_smartchoice_seo/purchasing/order_view', $data);
    }

    public function create_purchase_order_from_takeoff(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_purchase_order_from_takeoff($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['purchase_order_id'])) { redirect(admin_url('usi_smartchoice_seo/view_purchase_order/' . (int)$result['purchase_order_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_material_takeoff/' . $id));
    }

    public function recalculate_purchase_order(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->recalculate_purchase_order($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_purchase_order/' . $id));
    }

    public function mark_purchase_order_ordered(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_purchase_order_status($id, 'ordered');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_purchase_order/' . $id));
    }

    public function mark_purchase_order_received(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_purchase_order_status($id, 'received');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_purchase_order/' . $id));
    }

    public function delete_purchase_order(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_purchase_order($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/purchasing'));
    }

    public function mass_delete_purchase_orders(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_purchase_orders($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/purchasing'));
    }


    public function scheduling(): void
    {
        $this->require_view();
        $data['title'] = 'AI Scheduling';
        $data['filters'] = [
            'status' => $this->input->get('status', true),
            'schedule_type' => $this->input->get('schedule_type', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
        ];
        $data['counts'] = $this->usi_seo->schedule_counts();
        $data['schedules'] = $this->usi_seo->get_schedules($data['filters']);
        $data['handoffs'] = $this->usi_seo->get_project_handoffs(['status' => 'ready_for_project']);
        $this->load->view('usi_smartchoice_seo/scheduling/manage', $data);
    }

    public function schedule_plan(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_schedule($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_schedule/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit AI Schedule' : 'New AI Schedule';
        $data['schedule'] = $id > 0 ? $this->usi_seo->get_schedule($id) : null;
        $data['handoffs'] = $this->usi_seo->get_project_handoffs([]);
        $this->load->view('usi_smartchoice_seo/scheduling/form', $data);
    }

    public function view_schedule(int $id): void
    {
        $this->require_view();
        $data['title'] = 'AI Schedule Detail';
        $data['schedule'] = $this->usi_seo->get_schedule($id);
        $data['items'] = $this->usi_seo->get_schedule_items($id);
        $this->load->view('usi_smartchoice_seo/scheduling/view', $data);
    }

    public function create_schedule_from_handoff(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_schedule_from_handoff($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['schedule_id'])) { redirect(admin_url('usi_smartchoice_seo/view_schedule/' . (int)$result['schedule_id'])); }
        redirect(admin_url('usi_smartchoice_seo/view_project_handoff/' . $id));
    }

    public function generate_schedule_items(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_schedule_items($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_schedule/' . $id));
    }

    public function mark_schedule_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_schedule_status($id, 'ready_for_calendar');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_schedule/' . $id));
    }

    public function delete_schedule(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_schedule($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/scheduling'));
    }

    public function mass_delete_schedules(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_schedules($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/scheduling'));
    }


    public function jobsite_assistant(): void
    {
        $this->require_view();
        $data['title'] = 'AI Jobsite Assistant';
        $data['filters'] = [
            'status' => $this->input->get('status', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
        ];
        $data['counts'] = $this->usi_seo->jobsite_counts();
        $data['logs'] = $this->usi_seo->get_jobsite_logs($data['filters']);
        $data['schedules'] = $this->usi_seo->get_schedules(['status' => 'ready_for_calendar']);
        $this->load->view('usi_smartchoice_seo/jobsite_assistant/manage', $data);
    }

    public function jobsite_log(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_jobsite_log($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_jobsite_log/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Jobsite Log' : 'New Jobsite Log';
        $data['log'] = $id > 0 ? $this->usi_seo->get_jobsite_log($id) : null;
        $data['estimates'] = $this->usi_seo->get_ai_estimates([]);
        $data['schedules'] = $this->usi_seo->get_schedules([]);
        $this->load->view('usi_smartchoice_seo/jobsite_assistant/form', $data);
    }

    public function view_jobsite_log(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Jobsite Log Detail';
        $data['log'] = $this->usi_seo->get_jobsite_log($id);
        $data['items'] = $this->usi_seo->get_jobsite_punch_items($id);
        $this->load->view('usi_smartchoice_seo/jobsite_assistant/view', $data);
    }

    public function create_jobsite_log_from_schedule(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_jobsite_log_from_schedule($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['jobsite_log_id'])) { redirect(admin_url('usi_smartchoice_seo/view_jobsite_log/' . (int)$result['jobsite_log_id'])); }
        redirect(admin_url('usi_smartchoice_seo/scheduling'));
    }

    public function generate_punch_items(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_punch_items($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_jobsite_log/' . $id));
    }

    public function mark_jobsite_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_jobsite_log_status($id, 'ready_for_closeout');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_jobsite_log/' . $id));
    }

    public function delete_jobsite_log(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_jobsite_log($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/jobsite_assistant'));
    }

    public function mass_delete_jobsite_logs(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_jobsite_logs($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/jobsite_assistant'));
    }


    public function closeout_warranty(): void
    {
        $this->require_view();
        $data['title'] = 'AI Closeout & Warranty';
        $data['filters'] = [
            'status' => $this->input->get('status', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
        ];
        $data['counts'] = $this->usi_seo->closeout_counts();
        $data['closeouts'] = $this->usi_seo->get_closeouts($data['filters']);
        $data['jobsite_logs'] = $this->usi_seo->get_jobsite_logs(['status' => 'ready_for_closeout']);
        $this->load->view('usi_smartchoice_seo/closeout_warranty/manage', $data);
    }

    public function closeout_record(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        if ($this->input->post()) {
            $savedId = $this->usi_seo->save_closeout($this->input->post(null, false), $id);
            set_alert('success', _l('usi_smartchoice_seo_saved'));
            redirect(admin_url('usi_smartchoice_seo/view_closeout/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Closeout Package' : 'New Closeout Package';
        $data['closeout'] = $id > 0 ? $this->usi_seo->get_closeout($id) : null;
        $data['jobsite_logs'] = $this->usi_seo->get_jobsite_logs([]);
        $this->load->view('usi_smartchoice_seo/closeout_warranty/form', $data);
    }

    public function view_closeout(int $id): void
    {
        $this->require_view();
        $data['title'] = 'Closeout Package Detail';
        $data['closeout'] = $this->usi_seo->get_closeout($id);
        $data['warranties'] = $this->usi_seo->get_warranties($id);
        $data['followups'] = $this->usi_seo->get_closeout_followups($id);
        $this->load->view('usi_smartchoice_seo/closeout_warranty/view', $data);
    }

    public function create_closeout_from_jobsite(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_closeout_from_jobsite($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        if (!empty($result['closeout_id'])) { redirect(admin_url('usi_smartchoice_seo/view_closeout/' . (int)$result['closeout_id'])); }
        redirect(admin_url('usi_smartchoice_seo/jobsite_assistant'));
    }

    public function generate_warranty_records(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_warranty_records($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_closeout/' . $id));
    }

    public function generate_closeout_followups(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_closeout_followups($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_closeout/' . $id));
    }

    public function mark_closeout_ready(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_closeout_status($id, 'ready_for_customer', true);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_closeout/' . $id));
    }

    public function mark_closeout_completed(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_closeout_status($id, 'completed', true);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_closeout/' . $id));
    }

    public function delete_closeout(int $id): void
    {
        $this->require_delete();
        $this->usi_seo->delete_closeout($id);
        set_alert('success', _l('usi_smartchoice_seo_deleted'));
        redirect(admin_url('usi_smartchoice_seo/closeout_warranty'));
    }

    public function mass_delete_closeouts(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_closeouts($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/closeout_warranty'));
    }



    public function executive_dashboard(): void
    {
        $this->require_view();
        $data['title'] = 'AI Executive Dashboard';
        $data['summary'] = $this->usi_seo->executive_dashboard_summary();
        $data['snapshots'] = $this->usi_seo->get_executive_snapshots();
        $data['actions'] = $this->usi_seo->get_executive_actions(['status' => $this->input->get('status', true)]);
        $this->load->view('usi_smartchoice_seo/executive_dashboard/manage', $data);
    }

    public function create_executive_snapshot(): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_executive_snapshot();
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/executive_dashboard'));
    }

    public function generate_executive_actions(int $snapshotId): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_executive_actions($snapshotId);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/executive_dashboard'));
    }

    public function mark_executive_action_done(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_executive_action_status($id, 'completed');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/executive_dashboard'));
    }

    public function mass_delete_executive_actions(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_executive_actions($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/executive_dashboard'));
    }


    public function business_intelligence(): void
    {
        $this->require_view();
        $data['title'] = 'AI Business Intelligence';
        $data['summary'] = $this->usi_seo->business_intelligence_summary();
        $data['snapshots'] = $this->usi_seo->get_business_snapshots();
        $data['recommendations'] = $this->usi_seo->get_business_recommendations(['status' => $this->input->get('status', true)]);
        $this->load->view('usi_smartchoice_seo/business_intelligence/manage', $data);
    }

    public function create_business_snapshot(): void
    {
        $this->require_edit();
        $result = $this->usi_seo->create_business_snapshot();
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/business_intelligence'));
    }

    public function generate_business_recommendations(int $snapshotId): void
    {
        $this->require_edit();
        $result = $this->usi_seo->generate_business_recommendations($snapshotId);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/business_intelligence'));
    }

    public function mark_business_recommendation_done(int $id): void
    {
        $this->require_edit();
        $result = $this->usi_seo->mark_business_recommendation_status($id, 'completed');
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/business_intelligence'));
    }

    public function mass_delete_business_recommendations(): void
    {
        $this->require_delete();
        $deleted = $this->usi_seo->mass_delete_business_recommendations($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' ' . _l('usi_smartchoice_seo_rows_deleted'));
        redirect(admin_url('usi_smartchoice_seo/business_intelligence'));
    }


    public function automation_center(): void
    {
        $this->require_view();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        $filters = [
            'status'      => $this->input->get('status', true),
            'priority'    => $this->input->get('priority', true),
            'source_area' => $this->input->get('source_area', true),
        ];
        $data['title'] = 'Sammy AI Alerts & Automation Center';
        $data['alerts'] = $this->sammy_automation->get_alerts($filters);
        $data['queue'] = $this->sammy_automation->get_queue([]);
        $data['filters'] = $filters;
        $this->load->view('automation_center', $data);
    }

    public function create_automation_alert(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        if ($this->input->post()) {
            $this->sammy_automation->create_alert($this->input->post(null, true));
            set_alert('success', 'Alert created successfully.');
        }
        redirect(admin_url('usi_smartchoice_seo/automation_center'));
    }

    public function build_default_alerts(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        $count = $this->sammy_automation->build_default_alerts();
        set_alert('success', $count . ' default alerts prepared.');
        redirect(admin_url('usi_smartchoice_seo/automation_center'));
    }

    public function complete_automation_alert(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        $this->sammy_automation->complete_alert($id);
        set_alert('success', 'Alert marked completed.');
        redirect(admin_url('usi_smartchoice_seo/automation_center'));
    }

    public function delete_automation_alert(int $id): void
    {
        $this->require_delete();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        $this->sammy_automation->delete_alerts([$id]);
        set_alert('success', 'Alert deleted.');
        redirect(admin_url('usi_smartchoice_seo/automation_center'));
    }

    public function mass_delete_automation_alerts(): void
    {
        $this->require_delete();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
        $this->sammy_automation->delete_alerts($this->input->post('ids') ?: []);
        set_alert('success', 'Selected alerts deleted.');
        redirect(admin_url('usi_smartchoice_seo/automation_center'));
    }



    public function workflow_engine(): void
    {
        $this->require_view();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $filters = [
            'is_active' => $this->input->get('is_active', true),
            'trigger_area' => $this->input->get('trigger_area', true),
            'workflow_type' => $this->input->get('workflow_type', true),
        ];
        $data['title'] = 'Sammy AI Workflow Engine';
        $data['filters'] = $filters;
        $data['workflows'] = $this->sammy_workflow->get_workflows($filters);
        $data['runs'] = $this->sammy_workflow->get_runs();
        $data['logs'] = $this->sammy_workflow->get_logs();
        $this->load->view('workflow_engine', $data);
    }

    public function workflow(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        if ($this->input->post()) {
            $savedId = $this->sammy_workflow->save_workflow($this->input->post(null, false), $id);
            set_alert('success', 'Workflow saved.');
            redirect(admin_url('usi_smartchoice_seo/view_workflow/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'Edit Workflow' : 'New Workflow';
        $data['workflow'] = $id > 0 ? $this->sammy_workflow->get_workflow($id) : null;
        $this->load->view('workflow_form', $data);
    }

    public function view_workflow(int $id): void
    {
        $this->require_view();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $data['title'] = 'View Workflow';
        $data['workflow'] = $this->sammy_workflow->get_workflow($id);
        $data['steps'] = $this->sammy_workflow->get_steps($id);
        $data['runs'] = $this->sammy_workflow->get_runs($id);
        if (empty($data['workflow'])) {
            set_alert('warning', 'Workflow not found.');
            redirect(admin_url('usi_smartchoice_seo/workflow_engine'));
        }
        $this->load->view('workflow_view', $data);
    }

    public function save_workflow_step(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $workflowId = (int)$this->input->post('workflow_id', true);
        $this->sammy_workflow->save_step($this->input->post(null, false));
        set_alert('success', 'Workflow step added.');
        redirect(admin_url('usi_smartchoice_seo/view_workflow/' . $workflowId));
    }

    public function run_workflow(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $result = $this->sammy_workflow->run_workflow($id, 'manual', 0);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/view_workflow/' . $id));
    }

    public function enable_workflow(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $result = $this->sammy_workflow->toggle_workflow($id, 1);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/workflow_engine'));
    }

    public function disable_workflow(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $result = $this->sammy_workflow->toggle_workflow($id, 0);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/workflow_engine'));
    }

    public function duplicate_workflow(int $id): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $result = $this->sammy_workflow->duplicate_workflow($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('usi_smartchoice_seo/workflow_engine'));
    }

    public function mass_delete_workflows(): void
    {
        $this->require_delete();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_workflow_model', 'sammy_workflow');
        $deleted = $this->sammy_workflow->delete_workflows($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' rows deleted.');
        redirect(admin_url('usi_smartchoice_seo/workflow_engine'));
    }



    public function chat_engine(): void
    {
        $this->require_view();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $filters = [
            'status' => $this->input->get('status', true),
            'source_area' => $this->input->get('source_area', true),
            'search' => $this->input->get('search', true),
        ];
        $data['title'] = 'Sammy AI Conversation & Chat Engine';
        $data['filters'] = $filters;
        $data['conversations'] = $this->sammy_chat->get_conversations($filters);
        $data['recent_messages'] = $this->sammy_chat->get_recent_messages(20);
        $this->load->view('chat_engine', $data);
    }

    public function conversation(int $id = 0): void
    {
        $this->require_create_or_edit($id);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        if ($this->input->post()) {
            $savedId = $this->sammy_chat->save_conversation($this->input->post(null, false), $id);
            set_alert('success', 'Conversation saved.');
            redirect(admin_url('usi_smartchoice_seo/conversation/' . $savedId));
        }
        $data['title'] = $id > 0 ? 'View Conversation' : 'New Conversation';
        $data['conversation'] = $id > 0 ? $this->sammy_chat->get_conversation($id) : null;
        $data['messages'] = $id > 0 ? $this->sammy_chat->get_messages($id) : [];
        $data['context'] = $id > 0 ? $this->sammy_chat->get_context($id) : [];
        $this->load->view('conversation_view', $data);
    }

    public function save_conversation_message(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $conversationId = (int)$this->input->post('conversation_id', true);
        $conversationId = $this->sammy_chat->save_message($this->input->post(null, false), $conversationId);
        set_alert('success', 'Message saved to conversation.');
        redirect(admin_url('usi_smartchoice_seo/conversation/' . $conversationId));
    }

    public function build_conversation_from_memory(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $id = $this->sammy_chat->build_from_memory($this->input->post(null, true));
        set_alert('success', 'Conversation created from Sammy AI memory context.');
        redirect(admin_url('usi_smartchoice_seo/conversation/' . $id));
    }

    public function close_conversation(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $this->sammy_chat->set_status($id, 'closed');
        set_alert('success', 'Conversation closed.');
        redirect(admin_url('usi_smartchoice_seo/chat_engine'));
    }

    public function reopen_conversation(int $id): void
    {
        $this->require_edit();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $this->sammy_chat->set_status($id, 'open');
        set_alert('success', 'Conversation reopened.');
        redirect(admin_url('usi_smartchoice_seo/conversation/' . $id));
    }

    public function mass_delete_conversations(): void
    {
        $this->require_delete();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_chat_model', 'sammy_chat');
        $deleted = $this->sammy_chat->delete_conversations($this->input->post('ids') ?: []);
        set_alert('success', $deleted . ' conversations deleted.');
        redirect(admin_url('usi_smartchoice_seo/chat_engine'));
    }


    public function voice_orchestrator(): void
    {
        $this->require_view();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_voice_model', 'sammy_voice');
        $filters = [
            'listening_status' => $this->input->get('listening_status', true),
            'search' => $this->input->get('search', true),
        ];
        $data['title'] = 'Sammy AI Voice Orchestrator';
        $data['filters'] = $filters;
        $data['sessions'] = $this->sammy_voice->get_sessions($filters);
        $data['routes'] = $this->sammy_voice->get_routes();
        $data['recent_transcripts'] = $this->sammy_voice->get_recent_transcripts(25);
        $this->load->view('voice_orchestrator', $data);
    }

    public function voice_orchestrator_start(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_voice_model', 'sammy_voice');
        $sessionId = $this->sammy_voice->start_session($this->input->post(null, true) ?: []);
        $this->json_response(['success' => true, 'session_id' => $sessionId, 'message' => 'Voice session started.']);
    }

    public function voice_orchestrator_stop(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_voice_model', 'sammy_voice');
        $sessionId = (int)$this->input->post('session_id', true);
        $this->json_response(['success' => $this->sammy_voice->stop_session($sessionId), 'message' => 'Voice session stopped.']);
    }

    public function voice_orchestrator_transcript(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_voice_model', 'sammy_voice');
        $sessionId = (int)$this->input->post('session_id', true);
        $text = trim((string)$this->input->post('transcript_text', false));
        $confidence = (float)$this->input->post('confidence_score', true);
        if ($text === '') {
            $this->json_response(['success' => false, 'message' => 'No transcript text received.']);
            return;
        }
        $this->json_response($this->sammy_voice->save_transcript($sessionId, $text, $confidence));
    }

    public function voice_orchestrator_execute(): void
    {
        $this->require_create_or_edit(0);
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_voice_model', 'sammy_voice');
        $transcriptId = (int)$this->input->post('transcript_id', true);
        $this->json_response($this->sammy_voice->execute_transcript($transcriptId));
    }


    public function vision(): void
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_create_or_edit(0);
            $id = $this->usi_smartchoice_seo_model->add_vision_session($this->input->post(null, true));
            if ($id) {
                set_alert('success', _l('sammy_ai_vision_saved'));
                redirect(admin_url('usi_smartchoice_seo/view_vision/' . (int)$id));
                return;
            }
            set_alert('danger', _l('sammy_ai_save_failed'));
        }
        $data['title'] = _l('sammy_ai_vision_engine');
        $data['sessions'] = $this->usi_smartchoice_seo_model->get_vision_sessions();
        $this->load->view('usi_smartchoice_seo/vision', $data);
    }

    public function analyze_vision(int $id): void
    {
        $this->require_create_or_edit((int)$id);
        $this->usi_smartchoice_seo_model->generate_vision_findings((int)$id);
        set_alert('success', _l('sammy_ai_vision_analyzed'));
        redirect(admin_url('usi_smartchoice_seo/view_vision/' . (int)$id));
    }

    public function view_vision(int $id): void
    {
        $this->require_view();
        $data['title'] = _l('sammy_ai_vision_engine');
        $data['session'] = $this->usi_smartchoice_seo_model->get_vision_session((int)$id);
        if (!$data['session']) {
            show_404();
            return;
        }
        $data['findings'] = $this->usi_smartchoice_seo_model->get_vision_findings((int)$id);
        $this->load->view('usi_smartchoice_seo/vision_view', $data);
    }



    public function learning_center(): void
    {
        $this->require_view();
        $data['title'] = 'Learning & Training Center';
        $data['courses'] = $this->usi_seo->get_learning_courses();
        $data['assignments'] = $this->usi_seo->get_learning_assignments();
        $this->load->view('usi_smartchoice_seo/learning_center/manage', $data);
    }

    public function save_learning_course(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_learning_course($this->input->post(null, false));
        set_alert('success', 'Learning course saved.');
        redirect(admin_url('usi_smartchoice_seo/learning_center'));
    }

    public function api_manager(): void
    {
        $this->require_view();
        $data['title'] = 'API Manager';
        $data['providers'] = $this->usi_seo->get_api_providers();
        $data['keys'] = $this->usi_seo->get_api_keys();
        $data['checks'] = $this->usi_seo->get_api_health_checks();
        $this->load->view('usi_smartchoice_seo/api_manager/manage', $data);
    }

    public function save_api_provider(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_api_provider($this->input->post(null, false));
        set_alert('success', 'API provider saved.');
        redirect(admin_url('usi_smartchoice_seo/api_manager'));
    }

    public function save_api_key(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_api_key($this->input->post(null, false));
        set_alert('success', 'API key saved.');
        redirect(admin_url('usi_smartchoice_seo/api_manager'));
    }

    public function check_api_provider(int $id): void
    {
        $this->require_create_or_edit((int)$id);
        $this->usi_seo->check_api_provider((int)$id);
        set_alert('success', 'API provider check logged.');
        redirect(admin_url('usi_smartchoice_seo/api_manager'));
    }

    public function security_audit(): void
    {
        $this->require_view();
        $data['title'] = 'Security & Audit Center';
        $data['policies'] = $this->usi_seo->get_security_policies();
        $data['events'] = $this->usi_seo->get_security_audit_events();
        $this->load->view('usi_smartchoice_seo/security_audit/manage', $data);
    }

    public function save_security_policy(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_security_policy($this->input->post(null, false));
        set_alert('success', 'Security policy saved.');
        redirect(admin_url('usi_smartchoice_seo/security_audit'));
    }

    public function log_security_event(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->log_security_event($this->input->post(null, false));
        set_alert('success', 'Security audit event logged.');
        redirect(admin_url('usi_smartchoice_seo/security_audit'));
    }

    public function command_center(): void
    {
        $this->require_view();
        $data['title'] = 'Sammy AI Command Center';
        $data['cards'] = $this->usi_seo->get_command_center_cards();
        $data['summary'] = $this->usi_seo->command_center_summary();
        $this->load->view('usi_smartchoice_seo/command_center/manage', $data);
    }

    public function rebuild_command_center(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->rebuild_command_center_cards();
        set_alert('success', 'Command Center rebuilt.');
        redirect(admin_url('usi_smartchoice_seo/command_center'));
    }



    public function ai_core(): void
    {
        $this->require_view();
        $data['title'] = 'Production AI Core';
        $data['summary'] = $this->usi_seo->ai_core_summary();
        $data['actions'] = $this->usi_seo->get_ai_core_actions();
        $data['timeline'] = $this->usi_seo->get_ai_core_timeline();
        $data['search_index'] = $this->usi_seo->get_ai_core_search_index();
        $data['diagnostics'] = $this->usi_seo->get_ai_core_diagnostics();
        $this->load->view('usi_smartchoice_seo/ai_core/manage', $data);
    }

    public function save_ai_core_action(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_ai_core_action($this->input->post(null, false));
        set_alert('success', 'AI Core action saved.');
        redirect(admin_url('usi_smartchoice_seo/ai_core'));
    }

    public function mark_ai_core_action(int $id, string $status): void
    {
        $this->require_edit();
        $allowed = ['pending', 'approved', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) { $status = 'pending'; }
        $this->usi_seo->mark_ai_core_action_status((int)$id, $status);
        set_alert('success', 'AI Core action updated.');
        redirect(admin_url('usi_smartchoice_seo/ai_core'));
    }

    public function rebuild_ai_core_index(): void
    {
        $this->require_create_or_edit(0);
        $count = $this->usi_seo->rebuild_ai_core_index();
        set_alert('success', 'AI Core search index rebuilt. Records indexed: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/ai_core'));
    }

    public function rebuild_ai_core_diagnostics(): void
    {
        $this->require_create_or_edit(0);
        $count = $this->usi_seo->rebuild_ai_core_diagnostics();
        set_alert('success', 'AI Core diagnostics rebuilt. Checks performed: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/ai_core'));
    }



    public function intelligence_engine(): void
    {
        $this->require_view();
        $data['title'] = 'AI Intelligence Engine';
        $data['summary'] = $this->usi_seo->intelligence_engine_summary();
        $data['prompts'] = $this->usi_seo->get_ai_prompt_templates();
        $data['requests'] = $this->usi_seo->get_ai_request_history();
        $data['contexts'] = $this->usi_seo->get_ai_context_profiles();
        $data['cache'] = $this->usi_seo->get_ai_response_cache();
        $data['provider_logs'] = $this->usi_seo->get_ai_provider_logs();
        $this->load->view('usi_smartchoice_seo/intelligence_engine/manage', $data);
    }

    public function save_ai_prompt_template(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_ai_prompt_template($this->input->post(null, false));
        set_alert('success', 'AI prompt template saved.');
        redirect(admin_url('usi_smartchoice_seo/intelligence_engine'));
    }

    public function save_ai_context_profile(): void
    {
        $this->require_create_or_edit(0);
        $this->usi_seo->save_ai_context_profile($this->input->post(null, false));
        set_alert('success', 'AI context profile saved.');
        redirect(admin_url('usi_smartchoice_seo/intelligence_engine'));
    }

    public function test_ai_intelligence_request(): void
    {
        $this->require_create_or_edit(0);
        $id = $this->usi_seo->log_ai_intelligence_test_request($this->input->post(null, false));
        set_alert($id > 0 ? 'success' : 'warning', $id > 0 ? 'AI test request logged.' : 'AI request could not be logged.');
        redirect(admin_url('usi_smartchoice_seo/intelligence_engine'));
    }

    public function rebuild_ai_context_profiles(): void
    {
        $this->require_create_or_edit(0);
        $count = $this->usi_seo->rebuild_ai_context_profiles();
        set_alert('success', 'AI context profiles rebuilt. Profiles created: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/intelligence_engine'));
    }

    public function clear_ai_response_cache(): void
    {
        $this->require_delete();
        $count = $this->usi_seo->clear_ai_response_cache();
        set_alert('success', 'AI response cache cleared. Rows removed: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/intelligence_engine'));
    }



    public function advanced_estimating(): void
    {
        $this->require_view();
        $data['title'] = 'Advanced Estimating Intelligence';
        $data['summary'] = $this->usi_seo->advanced_estimating_summary();
        $data['runs'] = $this->usi_seo->get_estimate_intelligence_runs();
        $data['patterns'] = $this->usi_seo->get_estimate_price_patterns();
        $this->load->view('usi_smartchoice_seo/advanced_estimating/manage', $data);
    }

    public function create_estimate_intelligence_run(): void
    {
        $this->require_create_or_edit(0);
        $id = $this->usi_seo->create_estimate_intelligence_run($this->input->post(null, false));
        set_alert($id > 0 ? 'success' : 'warning', $id > 0 ? 'Advanced estimate intelligence run created.' : 'Estimate intelligence run could not be created.');
        redirect(admin_url('usi_smartchoice_seo/advanced_estimating'));
    }

    public function calculate_estimate_intelligence(int $id): void
    {
        $this->require_create_or_edit((int)$id);
        $this->usi_seo->calculate_estimate_intelligence((int)$id);
        set_alert('success', 'Estimate intelligence numbers recalculated.');
        redirect(admin_url('usi_smartchoice_seo/advanced_estimating'));
    }

    public function rebuild_estimate_price_patterns(): void
    {
        $this->require_create_or_edit(0);
        $count = $this->usi_seo->rebuild_estimate_price_patterns();
        set_alert('success', 'Estimate price patterns rebuilt. Patterns created: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/advanced_estimating'));
    }



    public function advanced_vision(): void
    {
        $this->require_view();
        $data['title'] = 'Advanced Vision Intelligence';
        $data['summary'] = $this->usi_seo->advanced_vision_summary();
        $data['sessions'] = $this->usi_seo->get_advanced_vision_sessions();
        $data['detections'] = $this->usi_seo->get_advanced_vision_detections();
        $data['measurements'] = $this->usi_seo->get_advanced_vision_measurements();
        $this->load->view('usi_smartchoice_seo/advanced_vision/manage', $data);
    }

    public function create_advanced_vision_session(): void
    {
        $this->require_create_or_edit(0);
        $id = $this->usi_seo->create_advanced_vision_session($this->input->post(null, false));
        set_alert($id > 0 ? 'success' : 'warning', $id > 0 ? 'Advanced vision session created.' : 'Advanced vision session could not be created.');
        redirect(admin_url('usi_smartchoice_seo/advanced_vision'));
    }

    public function analyze_advanced_vision(int $id): void
    {
        $this->require_create_or_edit((int)$id);
        $this->usi_seo->analyze_advanced_vision((int)$id);
        set_alert('success', 'Advanced vision analysis completed.');
        redirect(admin_url('usi_smartchoice_seo/advanced_vision'));
    }

    public function rebuild_advanced_vision_index(): void
    {
        $this->require_create_or_edit(0);
        $count = $this->usi_seo->rebuild_advanced_vision_index();
        set_alert('success', 'Advanced vision index rebuilt. Sessions created: ' . $count);
        redirect(admin_url('usi_smartchoice_seo/advanced_vision'));
    }


    public function multi_agent_ai(): void
    {
        $this->require_view();
        $data['title'] = 'Multi-Agent AI';
        $data['summary'] = $this->usi_seo->multi_agent_summary();
        $data['agents'] = $this->usi_seo->get_ai_agents();
        $data['tasks'] = $this->usi_seo->get_ai_agent_tasks();
        $data['logs'] = $this->usi_seo->get_ai_agent_logs();
        $this->load->view('usi_smartchoice_seo/multi_agent/manage', $data);
    }

    public function create_ai_agent_task(): void
    {
        $this->require_create_or_edit(0);
        $id = $this->usi_seo->create_ai_agent_task($this->input->post(null, false));
        set_alert($id > 0 ? 'success' : 'warning', $id > 0 ? 'Agent task created.' : 'Agent task could not be created.');
        redirect(admin_url('usi_smartchoice_seo/multi_agent_ai'));
    }

    public function run_ai_agent_task(int $id): void
    {
        $this->require_edit();
        $ok = $this->usi_seo->run_ai_agent_task((int)$id);
        set_alert($ok ? 'success' : 'warning', $ok ? 'Agent task completed.' : 'Agent task could not be completed.');
        redirect(admin_url('usi_smartchoice_seo/multi_agent_ai'));
    }

    private function json_response(array $payload): void
    {
        if (isset($this->security)) {
            $payload['csrfName'] = $this->security->get_csrf_token_name();
            $payload['csrfHash'] = $this->security->get_csrf_hash();
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function require_view(): void { if (!has_permission('usi_smartchoice_seo', '', 'view_own') && !has_permission('usi_smartchoice_seo', '', 'view_global')) { access_denied('usi_smartchoice_seo'); } }
    private function require_create_or_edit(int $id): void { if ($id > 0) { $this->require_edit(); return; } if (!has_permission('usi_smartchoice_seo', '', 'create')) { access_denied('usi_smartchoice_seo'); } }
    private function require_edit(): void { if (!has_permission('usi_smartchoice_seo', '', 'edit')) { access_denied('usi_smartchoice_seo'); } }
    private function require_delete(): void { if (!has_permission('usi_smartchoice_seo', '', 'delete')) { access_denied('usi_smartchoice_seo'); } }
    public function footer_chat_command(): void
    {
        $this->require_view();
        $command = trim((string)$this->input->post('command', false));
        if ($command === '') {
            $this->json_response(['success' => false, 'message' => 'Enter a CRM question or command.']);
            return;
        }
        $result = $this->usi_seo->preview_voice_command($command);
        $message = (string)($result['preview'] ?? $result['message'] ?? 'Command received. Open the Message Center for the complete workflow.');
        $this->json_response([
            'success' => true,
            'message' => $message,
            'chat_url' => admin_url('usi_smartchoice_seo/chat_engine'),
            'csrfHash' => $this->security->get_csrf_hash(),
        ]);
    }


}
