<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sc_ai_video_studio extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sc_ai_video_studio_model');
        $this->load->language('sc_ai_video_studio/sc_ai_video_studio');
        require_once module_dir_path('sc_ai_video_studio', 'helpers/sc_ai_video_studio_helper.php');
        sc_ai_video_studio_ensure_schema();
        sc_ai_video_studio_seed_defaults();
    }

    public function index(): void
    {
        $this->require_view();
        $data['title'] = _l('sc_video_module_name');
        $data['counts'] = $this->sc_ai_video_studio_model->counts();
        $this->load->view('dashboard', $data);
    }

    public function videos(): void
    {
        $this->require_view();
        $data['title'] = _l('sc_video_menu_videos');
        $data['videos'] = $this->sc_ai_video_studio_model->get_videos();
        $this->load->view('videos/manage', $data);
    }

    public function video($id = 0): void
    {
        if ($id > 0) {
            $this->require_view();
        } else {
            $this->require_create();
        }
        if ($this->input->post()) {
            $saveId = $this->sc_ai_video_studio_model->save_video($this->input->post(null, false), (int) $id);
            set_alert('success', _l('updated_successfully', _l('sc_video_video')));
            redirect(admin_url('sc_ai_video_studio/view_video/' . $saveId));
        }
        $data['title'] = $id > 0 ? _l('sc_video_edit_video') : _l('sc_video_new_video');
        $data['video'] = $id > 0 ? $this->sc_ai_video_studio_model->get_video((int) $id) : null;
        $data['layers'] = $id > 0 ? $this->sc_ai_video_studio_model->get_layers((int) $id) : [];
        $data['voices'] = $this->sc_ai_video_studio_model->get_voices();
        $data['avatars'] = $this->sc_ai_video_studio_model->get_avatars();
        $this->load->view('videos/form', $data);
    }

    public function view_video($id): void
    {
        $this->require_view();
        $data['video'] = $this->sc_ai_video_studio_model->get_video((int) $id);
        if (!$data['video']) {
            show_404();
        }
        $data['title'] = _l('sc_video_view_video');
        $data['layers'] = $this->sc_ai_video_studio_model->get_layers((int) $id);
        $this->load->view('videos/view', $data);
    }

    public function generate_video($id): void
    {
        $this->require_edit();
        $this->db->where('id', (int) $id)->update(db_prefix() . 'sc_video_jobs', [
            'status' => 'Ready for API',
            'provider_response' => 'Video generation payload prepared. Connect provider API in Settings to send this job.',
            'dateupdated' => date('Y-m-d H:i:s'),
        ]);
        set_alert('success', 'Video generation payload prepared.');
        redirect(admin_url('sc_ai_video_studio/view_video/' . (int) $id));
    }

    public function delete_video($id): void
    {
        $this->require_delete();
        $this->sc_ai_video_studio_model->delete_video((int) $id);
        set_alert('success', _l('deleted', _l('sc_video_video')));
        redirect(admin_url('sc_ai_video_studio/videos'));
    }

    public function voices(): void
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_create();
            $this->sc_ai_video_studio_model->save_voice($this->input->post(null, false));
            set_alert('success', 'Voice saved.');
            redirect(admin_url('sc_ai_video_studio/voices'));
        }
        $data['title'] = _l('sc_video_menu_voices');
        $data['voices'] = $this->sc_ai_video_studio_model->get_voices();
        $this->load->view('voices/manage', $data);
    }

    public function avatars(): void
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_create();
            $this->sc_ai_video_studio_model->save_avatar($this->input->post(null, false));
            set_alert('success', 'Avatar saved.');
            redirect(admin_url('sc_ai_video_studio/avatars'));
        }
        $data['title'] = _l('sc_video_menu_avatars');
        $data['avatars'] = $this->sc_ai_video_studio_model->get_avatars();
        $this->load->view('avatars/manage', $data);
    }

    public function reports(): void
    {
        $this->require_view();
        $data['title'] = _l('sc_video_menu_reports');
        $data['videos'] = $this->sc_ai_video_studio_model->get_videos();
        $data['counts'] = $this->sc_ai_video_studio_model->counts();
        $this->load->view('reports/manage', $data);
    }

    public function settings(): void
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_edit();
            $fields = ['provider','api_key','logo_enabled','logo_url','default_language','default_resolution'];
            foreach ($fields as $field) {
                update_option('sc_ai_video_studio_' . $field, (string) $this->input->post($field, false));
            }
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('sc_ai_video_studio/settings'));
        }
        $data['title'] = _l('sc_video_menu_settings');
        $this->load->view('settings/manage', $data);
    }

    public function check_api_key(): void
    {
        $this->require_view();
        $key = get_option('sc_ai_video_studio_api_key');
        if (trim((string) $key) === '') {
            set_alert('warning', 'API key is empty. Add the provider key before generating videos.');
        } else {
            set_alert('success', 'API key is saved. Provider billing/live validation requires the selected provider endpoint.');
        }
        redirect(admin_url('sc_ai_video_studio/settings'));
    }

    public function health(): void
    {
        $this->require_view();
        $data['title'] = _l('sc_video_menu_health');
        $data['checks'] = [
            ['Database table ' . db_prefix() . 'sc_video_jobs', sc_ai_video_studio_table_exists('sc_video_jobs')],
            ['Database table ' . db_prefix() . 'sc_video_voices', sc_ai_video_studio_table_exists('sc_video_voices')],
            ['Database table ' . db_prefix() . 'sc_video_avatars', sc_ai_video_studio_table_exists('sc_video_avatars')],
            ['Database table ' . db_prefix() . 'sc_video_text_layers', sc_ai_video_studio_table_exists('sc_video_text_layers')],
            ['English language file', file_exists(module_dir_path('sc_ai_video_studio', 'language/english/sc_ai_video_studio_lang.php'))],
            ['Spanish language file', file_exists(module_dir_path('sc_ai_video_studio', 'language/spanish/sc_ai_video_studio_lang.php'))],
            ['CSS asset', file_exists(module_dir_path('sc_ai_video_studio', 'assets/css/sc_ai_video_studio.css'))],
            ['JavaScript asset', file_exists(module_dir_path('sc_ai_video_studio', 'assets/js/sc_ai_video_studio.js'))],
        ];
        $this->load->view('health/manage', $data);
    }

    public function repair_database(): void
    {
        $this->require_edit();
        sc_ai_video_studio_ensure_schema();
        sc_ai_video_studio_seed_defaults();
        set_alert('success', 'Video Studio database repaired.');
        redirect(admin_url('sc_ai_video_studio/health'));
    }

    public function help(): void
    {
        $this->require_view();
        $data['title'] = _l('sc_video_menu_help');
        $this->load->view('help/manage', $data);
    }

    private function require_view(): void
    {
        if (!has_permission('sc_ai_video_studio', '', 'view_own') && !has_permission('sc_ai_video_studio', '', 'view_global')) {
            access_denied('Smart Choice AI Video Studio');
        }
    }

    private function require_create(): void
    {
        if (!has_permission('sc_ai_video_studio', '', 'create')) {
            access_denied('Smart Choice AI Video Studio');
        }
    }

    private function require_edit(): void
    {
        if (!has_permission('sc_ai_video_studio', '', 'edit')) {
            access_denied('Smart Choice AI Video Studio');
        }
    }

    private function require_delete(): void
    {
        if (!has_permission('sc_ai_video_studio', '', 'delete')) {
            access_denied('Smart Choice AI Video Studio');
        }
    }
}
