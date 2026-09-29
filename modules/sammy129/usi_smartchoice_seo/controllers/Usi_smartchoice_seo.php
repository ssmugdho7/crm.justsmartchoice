<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_model', 'usi_seo');
        $this->load->language('usi_smartchoice_seo/module', 'english');
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
        $this->load->view('usi_smartchoice_seo/camera/manage', $data);
    }

    public function save_camera_intake(): void
    {
        $this->require_create_or_edit(0);
        if (!$this->input->post()) {
            redirect(admin_url('usi_smartchoice_seo/camera_intake'));
        }
        $post = $this->input->post(null, false);
        if ($this->input->post('run_estimate')) {
            $post = $this->usi_seo->calculate_estimate_payload($post);
        }
        $savedId = $this->usi_seo->save_ai_estimate($post, 0);
        if ($this->input->post('run_estimate')) { $this->usi_seo->calculate_ai_estimate_numbers($savedId); }
        if (!empty($_FILES['photo']['name'])) {
            $this->usi_seo->save_ai_photo($savedId, $_FILES['photo'], $post);
        }
        set_alert('success', $this->input->post('run_estimate') ? 'Camera intake saved and estimate calculated.' : _l('usi_smartchoice_ai_camera_saved'));
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


    private function json_response(array $payload): void
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function require_view(): void { if (!has_permission('usi_smartchoice_seo', '', 'view_own') && !has_permission('usi_smartchoice_seo', '', 'view_global')) { access_denied('usi_smartchoice_seo'); } }
    private function require_create_or_edit(int $id): void { if ($id > 0) { $this->require_edit(); return; } if (!has_permission('usi_smartchoice_seo', '', 'create')) { access_denied('usi_smartchoice_seo'); } }
    private function require_edit(): void { if (!has_permission('usi_smartchoice_seo', '', 'edit')) { access_denied('usi_smartchoice_seo'); } }
    private function require_delete(): void { if (!has_permission('usi_smartchoice_seo', '', 'delete')) { access_denied('usi_smartchoice_seo'); } }
}
