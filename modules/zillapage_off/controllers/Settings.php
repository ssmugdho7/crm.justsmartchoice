<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('landingpage_model');
    }

    public function index()
    {
        
        if ($this->input->post()) {

            if (!has_permission('landingpages-settings', '', 'edit')) {
                    access_denied('landingpages-settings');
            }
            $post = $this->input->post(NULL, false);
            $preset = isset($post['design_preset']) ? $post['design_preset'] : 'default';
            $presets = zillapage_design_presets();
            if (isset($post['apply_preset']) && isset($presets[$preset])) {
                $post['blockscss'] = $presets[$preset]['css'];
            }
            if (isset($post['reset_default'])) {
                $preset = 'default';
                $post['blockscss'] = $presets['default']['css'];
            }
            $post['design_preset'] = $preset;
            $success = $this->landingpage_model->save_settings_bulk($post);
            if ($success == true) {
                set_alert('success', _l('updated_successfully', _l('client')));
            }
            redirect(admin_url('zillapage/settings'));

        }

        $data['blockscss'] = $this->landingpage_model->get_landing_page_setting('blockscss');
        $data['settings'] = $this->landingpage_model->get_settings_map();
        $data['presets'] = zillapage_design_presets();
        $title = _l('edit', _l('setting'));
        $data['title']     = $title;

        $this->load->view('zillapage/settings/index', $data);
    }
   
    
}
