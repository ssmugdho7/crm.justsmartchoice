<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_modes_model');
        $this->load->model('settings_model');
    }

    // View all settings
    public function index()
    {
        if (staff_cant('view', 'settings')) {
            access_denied('settings');
        }

        $group = $this->input->get('group');

        // Pre 3.1.6
        if ($group === 'sales') {
            $group = 'sales_general';
        }

        if ($this->input->post()) {
            if (staff_cant('edit', 'settings')) {
                access_denied('settings');
            }

            $post_data = $this->input->post();
            hooks()->do_action('before_update_system_options', $post_data);

            $logoUploadResult  = handle_company_logo_upload();
            $logo_uploaded     = !empty($logoUploadResult['uploaded']);
            $favicon_uploaded  = (handle_favicon_upload() ? true : false);
            $signatureUploaded = (handle_company_signature_upload() ? true : false);
            $scLoginBackgroundsUploaded = $this->handle_smart_choice_login_backgrounds();

            $tmpData = $this->input->post(null, false);

            if (isset($post_data['settings']['email_header'])) {
                $post_data['settings']['email_header'] = $tmpData['settings']['email_header'];
            }

            if (isset($post_data['settings']['email_footer'])) {
                $post_data['settings']['email_footer'] = $tmpData['settings']['email_footer'];
            }

            if (isset($post_data['settings']['email_signature'])) {
                $post_data['settings']['email_signature'] = $tmpData['settings']['email_signature'];
            }

            if (isset($post_data['settings']['smtp_password'])) {
                $post_data['settings']['smtp_password'] = $tmpData['settings']['smtp_password'];
            }
            foreach (['sc_custom_admin_js','sc_custom_client_js'] as $scRawSetting) {
                if (isset($post_data['settings'][$scRawSetting]) && isset($tmpData['settings'][$scRawSetting])) {
                    $post_data['settings'][$scRawSetting] = $tmpData['settings'][$scRawSetting];
                }
            }

            $success = $this->settings_model->update($post_data);

            if ($success > 0 || $group === 'payment_gateways') {
                set_alert('success', _l('settings_updated'));
            }

            if (!empty($logoUploadResult['uploaded'])) {
                set_alert('success', implode(' and ', $logoUploadResult['uploaded']) . ' uploaded successfully. The new image is displayed below.');
            }

            if (!empty($logoUploadResult['failed'])) {
                $uploadErrors = [];
                foreach ($logoUploadResult['failed'] as $logoLabel => $reason) {
                    $uploadErrors[] = $logoLabel . ': ' . $reason;
                }
                set_alert('danger', 'Logo upload failed — ' . implode(' | ', $uploadErrors));
            }

            if ($favicon_uploaded) {
                set_alert('success', 'Favicon uploaded successfully.');
            }

            if ($signatureUploaded) {
                set_alert('success', 'Signature image uploaded successfully.');
            }

            if ($logo_uploaded || $favicon_uploaded) {
                $this->load->helper('file');
                $cachePath = APPPATH . 'cache/';
                foreach (glob($cachePath . '*') ?: [] as $cacheFile) {
                    if (is_file($cacheFile) && !in_array(basename($cacheFile), ['index.html', '.htaccess'], true)) {
                        @unlink($cacheFile);
                    }
                }
            }

            // Do hard refresh on general for the logo
            if ($group == 'general') {
                redirect(admin_url('settings?group=' . $group), 'refresh');
            } elseif ($signatureUploaded) {
                redirect(admin_url('settings?group=pdf&tab=signature'));
            } else {
                $redUrl = admin_url('settings?group=' . $group);

                if ($this->input->get('active_tab')) {
                    $redUrl .= '&tab=' . $this->input->get('active_tab');
                }

                redirect($redUrl);
            }
        }

        $this->load->model('taxes_model');
        $this->load->model('tickets_model');
        $this->load->model('leads_model');
        $this->load->model('currencies_model');
        $this->load->model('staff_model');
        $data['taxes']                                   = $this->taxes_model->get();
        $data['ticket_priorities']                       = $this->tickets_model->get_priority();
        $data['ticket_priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['roles']                                   = $this->roles_model->get();
        $data['leads_sources']                           = $this->leads_model->get_source();
        $data['leads_statuses']                          = $this->leads_model->get_status();
        $data['title']                                   = _l('options');
        $data['staff']                                   = $this->staff_model->get('', ['active' => 1]);

        $data['admin_tabs'] = ['update', 'info'];

        if (! $group || (in_array($group, $data['admin_tabs']) && ! is_admin())) {
            $group = 'general';
        }

        // $data['tabs'] = $this->app_tabs->get_settings_tabs();
        $data['sections'] = $this->app->get_settings_sections();
        if (! in_array($group, $data['admin_tabs'])) {
            $data['group'] = collect($data['sections'])->pluck('children')->flatten(1)->first(function ($sectionGroup) use ($group) {
                return $sectionGroup['id'] == $group;
            });
        } else {
            // Core tabs are not registered
            $data['group']['id']       = $group;
            $data['group']['view']     = 'admin/settings/includes/' . $group;
            $data['group']['name']     = $group === 'info' ? ' System/Server Info' : _l('settings_update');
            $data['group']['children'] = [];
            if ($group === 'info') {
                $data['group']['without_submit_button'] = true;
            }
        }

        if (! $data['group']) {
            show_404();
        }

        if ($data['group']['id'] == 'update') {
            if (! extension_loaded('curl')) {
                $data['update_errors'][] = 'CURL Extension not enabled';
                $data['latest_version']  = 0;
                $data['update_info']     = json_decode('');
            } else {
                $data['update_info'] = $this->app->get_update_info();
                if (strpos($data['update_info'], 'Curl Error -') !== false) {
                    $data['update_errors'][] = $data['update_info'];
                    $data['latest_version']  = 0;
                    $data['update_info']     = json_decode('');
                } else {
                    $data['update_info']    = json_decode($data['update_info']);
                    $data['latest_version'] = $data['update_info']->latest_version;
                    $data['update_errors']  = [];
                }
            }

            if (! extension_loaded('zip')) {
                $data['update_errors'][] = 'ZIP Extension not enabled';
            }

            $data['current_version'] = $this->current_db_version;
        }

        $data['contacts_permissions'] = get_contact_permissions();
        $data['payment_gateways']     = $this->payment_modes_model->get_payment_gateways(true);

        $this->load->view('admin/settings/all', $data);
    }

    public function delete_tag($id)
    {
        if (! $id) {
            redirect(admin_url('settings?group=tags'));
        }

        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tags');
        $this->db->where('tag_id', $id);
        $this->db->delete(db_prefix() . 'taggables');

        redirect(admin_url('settings?group=tags'));
    }

    public function remove_signature_image()
    {
        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }

        $sImage = get_option('signature_image');
        if (file_exists(get_upload_path_by_type('company') . '/' . $sImage)) {
            unlink(get_upload_path_by_type('company') . '/' . $sImage);
        }

        update_option('signature_image', '');

        redirect(admin_url('settings?group=pdf&tab=signature'));
    }

    // Remove company logo from settings / ajax
    private function handle_smart_choice_login_backgrounds()
    {
        $uploaded = [];
        $map = [
            'sc_admin_login_background' => 'admin',
            'sc_client_login_background' => 'client',
        ];
        foreach ($map as $field => $suffix) {
            if (empty($_FILES[$field]['name'])) { continue; }
            $path = get_upload_path_by_type('company');
            if (!is_dir($path)) { @mkdir($path, 0755, true); }
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'], true) || (int)$_FILES[$field]['size'] > 5242880) {
                set_alert('danger', _l('sc_invalid_login_background'));
                continue;
            }
            $info = @getimagesize($_FILES[$field]['tmp_name']);
            if (!$info || $info[0] < 640 || $info[1] < 360) {
                set_alert('danger', _l('sc_invalid_login_background_dimensions'));
                continue;
            }
            $filename = 'sc_' . $suffix . '_login_background.' . $ext;
            $target = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;
            foreach (glob(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . 'sc_' . $suffix . '_login_background.*') ?: [] as $old) {
                if (is_file($old)) @unlink($old);
            }
            if (@move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                update_option($field, $filename);
                $uploaded[] = $field;
            } else {
                set_alert('danger', _l('sc_login_background_upload_failed'));
            }
        }
        return $uploaded;
    }

    public function remove_company_logo($type = '')
    {
        hooks()->do_action('before_remove_company_logo');

        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }

        $logoName = get_option('company_logo');
        if ($type == 'dark') {
            $logoName = get_option('company_logo_dark');
        }

        $path = get_upload_path_by_type('company') . '/' . $logoName;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option('company_logo' . ($type == 'dark' ? '_dark' : ''), '');
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function remove_fv()
    {
        hooks()->do_action('before_remove_favicon');
        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }
        if (file_exists(get_upload_path_by_type('company') . '/' . get_option('favicon'))) {
            unlink(get_upload_path_by_type('company') . '/' . get_option('favicon'));
        }
        update_option('favicon', '');
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function delete_option($name)
    {
        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }

        echo json_encode([
            'success' => delete_option($name),
        ]);
    }

    public function clear_sessions()
    {
        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }
        $this->db->empty_table(db_prefix() . 'sessions');

        set_alert('success', 'Sessions Cleared');
        redirect(admin_url('settings?group=info'));
    }
    public function clear_cache()
    {
        if (!is_admin()) {
            access_denied('settings');
        }

        $deleted = $this->smart_choice_clear_directory(APPPATH . 'cache');
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        clearstatcache(true);
        set_alert('success', 'CRM cache cleaned successfully. ' . $deleted . ' cached files removed.');
        redirect(admin_url('settings?group=info'));
    }

    private function smart_choice_clear_directory($directory)
    {
        $count = 0;
        if (!is_dir($directory)) {
            return $count;
        }
        $items = @scandir($directory);
        if (!is_array($items)) {
            return $count;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === 'index.html' || $item === '.htaccess') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path) && !is_link($path)) {
                $count += $this->smart_choice_clear_directory($path);
                @rmdir($path);
            } elseif (is_file($path) || is_link($path)) {
                if (@unlink($path)) {
                    $count++;
                }
            }
        }
        return $count;
    }

}
