<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_links extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        smart_choice_links_ensure_database();
        $this->load->model('smart_choice_links/smart_choice_links_model');
        $this->load->model('roles_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        if (!smart_choice_links_user_can_manage()) {
            access_denied('Smart Choice Links');
        }

        $data['title'] = _l('smart_choice_links');
        $data['links'] = $this->smart_choice_links_model->get();
        $this->load->view('manage', $data);
    }

    public function settings()
    {
        if (!smart_choice_links_user_can_edit()) {
            access_denied('Smart Choice Links Settings');
        }
        $data['title'] = _l('smart_choice_links_settings');
        $this->load->view('settings_page', $data);
    }

    public function save_settings()
    {
        if (!smart_choice_links_user_can_edit()) {
            access_denied('Smart Choice Links Settings');
        }
        $settings = $this->input->post('settings');
        if (!is_array($settings)) {
            $settings = [];
        }

        $allowed = [
            'smart_choice_links_enabled','smart_choice_links_show_topbar','smart_choice_links_show_left_star',
            'smart_choice_links_show_right_star','smart_choice_links_open_new_tab','smart_choice_links_fix_bad_urls',
            'smart_choice_links_enable_menu_search','smart_choice_links_enable_command_palette','smart_choice_links_enable_setup_shortcut',
            'smart_choice_links_title_left','smart_choice_links_title_right','smart_choice_links_footer_note','smart_choice_links_default_open_behavior',
            'smart_choice_links_panel_width','smart_choice_links_row_height','smart_choice_links_icon_size',
            'smart_choice_links_font_size','smart_choice_links_padding','smart_choice_links_radius','smart_choice_links_animation_speed'
        ];

        foreach ($allowed as $key) {
            if ($this->input->post($key) !== null) {
                $settings[$key] = $this->input->post($key);
            }
        }

        foreach ($settings as $key => $value) {
            if (strpos($key, 'smart_choice_links_') !== 0) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            update_option($key, $value);
        }

        $yesNo = [
            'smart_choice_links_enabled','smart_choice_links_show_topbar','smart_choice_links_show_left_star',
            'smart_choice_links_show_right_star','smart_choice_links_open_new_tab','smart_choice_links_fix_bad_urls',
            'smart_choice_links_enable_menu_search','smart_choice_links_enable_command_palette','smart_choice_links_enable_setup_shortcut'
        ];
        foreach ($yesNo as $key) {
            if (!isset($settings[$key])) {
                update_option($key, '0');
            }
        }
        set_alert('success', _l('settings_updated'));
        redirect(admin_url('smart_choice_links/settings'));
    }

    public function help()
    {
        $data['title'] = _l('smart_choice_links_how_to_use');
        $this->load->view('help', $data);
    }

    public function health()
    {
        $data['title'] = _l('smart_choice_links_health_check');
        $data['checks'] = [
            ['name' => 'Database table', 'status' => $this->db->table_exists(SMART_CHOICE_LINKS_TABLE), 'note' => SMART_CHOICE_LINKS_TABLE],
            ['name' => 'CSS file', 'status' => file_exists(module_dir_path(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/css/smart_choice_links.css')), 'note' => 'assets/css/smart_choice_links.css'],
            ['name' => 'JavaScript file', 'status' => file_exists(module_dir_path(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/js/smart_choice_links.js')), 'note' => 'assets/js/smart_choice_links.js'],
            ['name' => 'English language', 'status' => file_exists(module_dir_path(SMART_CHOICE_LINKS_MODULE_NAME, 'language/english/smart_choice_links_lang.php')), 'note' => 'English labels'],
            ['name' => 'Spanish language', 'status' => file_exists(module_dir_path(SMART_CHOICE_LINKS_MODULE_NAME, 'language/spanish/smart_choice_links_lang.php')), 'note' => 'Spanish labels'],
            ['name' => _l('smart_choice_links_topbar_stars'), 'status' => get_option('smart_choice_links_enabled') === '1' && get_option('smart_choice_links_show_topbar') === '1', 'note' => 'Enabled: ' . get_option('smart_choice_links_enabled') . ' / Top bar: ' . get_option('smart_choice_links_show_topbar')],
            ['name' => _l('smart_choice_links_search_main_menu'), 'status' => get_option('smart_choice_links_enable_menu_search') === '1', 'note' => 'Menu search setting'],
        ];
        $this->load->view('health', $data);
    }

    public function modal($id = 0)
    {
        if (!smart_choice_links_user_can_edit()) {
            access_denied('Smart Choice Links');
        }
        $data['link'] = $id ? $this->smart_choice_links_model->get($id) : null;
        $data['roles'] = $this->roles_model->get();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('modal', $data);
    }

    public function save($id = 0)
    {
        if (!smart_choice_links_user_can_edit()) {
            access_denied('Smart Choice Links');
        }
        if ($this->input->post()) {
            if ((int) $id > 0) {
                $this->smart_choice_links_model->update($id, $this->input->post());
            } else {
                $this->smart_choice_links_model->add($this->input->post());
            }
            set_alert('success', _l('updated_successfully', _l('smart_choice_links')));
        }
        redirect(admin_url('smart_choice_links'));
    }

    public function reorder()
    {
        if (!smart_choice_links_user_can_edit()) {
            access_denied('Smart Choice Links');
        }
        $order = $this->input->post('order');
        if (is_string($order)) {
            $decoded = json_decode($order, true);
            if (is_array($decoded)) { $order = $decoded; }
        }
        if (!is_array($order)) {
            echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
            return;
        }
        $position = 1;
        foreach ($order as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->db->where('id', $id)->update(SMART_CHOICE_LINKS_TABLE, ['position' => $position, 'updated_at' => date('Y-m-d H:i:s')]);
                $position++;
            }
        }
        echo json_encode(['success' => true]);
    }

    public function delete($id)
    {
        if (!is_admin() && !has_permission('smart_choice_links', '', 'delete')) { access_denied('Smart Choice Links'); }
        $this->smart_choice_links_model->delete($id);
        set_alert('success', _l('deleted', _l('smart_choice_links')));
        redirect(admin_url('smart_choice_links'));
    }

    public function import_old_favorites()
    {
        if (!is_admin() && !has_permission('smart_choice_links', '', 'delete')) { access_denied('Smart Choice Links'); }
        smart_choice_links_import_old_favorite_links();
        set_alert('success', _l('smart_choice_links_imported_old_favorites'));
        redirect(admin_url('smart_choice_links'));
    }
}
