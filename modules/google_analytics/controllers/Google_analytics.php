<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_analytics extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('google_analytics/google_analytics');
        $this->load->model('google_analytics/google_analytics_model');
    }

    private function require_capability($capability = 'view')
    {
        if (!is_admin() && !has_permission('google_analytics', '', $capability)) {
            access_denied('Google Analytics');
        }
    }

    public function index()
    {
        $this->require_capability('view');
        $data['title'] = _l('ga_dashboard');
        $data['websites'] = $this->google_analytics_model->get_websites(true);
        $data['selected_id'] = (int) $this->input->get('website_id');
        $data['report'] = null;
        $data['error'] = null;
        $website = $data['selected_id'] ? $this->google_analytics_model->get_website($data['selected_id']) : ($data['websites'][0] ?? null);
        $data['website'] = $website;
        if ($website && !empty($website->property_id)) {
            try {
                $data['report'] = $this->google_analytics_model->dashboard_report($website, $this->input->get('start') ?: '30daysAgo', $this->input->get('end') ?: 'today', $this->input->get('refresh') === '1');
            } catch (Throwable $e) { $data['error'] = $e->getMessage(); }
        }
        $this->load->view('dashboard', $data);
    }

    public function websites()
    {
        $this->require_capability('view');
        $data['title'] = _l('ga_websites');
        $data['websites'] = $this->google_analytics_model->get_websites();
        $this->load->view('websites', $data);
    }

    public function website($id = 0)
    {
        $this->require_capability($id ? 'edit' : 'create');
        $data['title'] = $id ? _l('ga_edit_website') : _l('ga_add_website');
        $data['website'] = $id ? $this->google_analytics_model->get_website($id) : null;
        if ($this->input->post()) {
            $post = $this->input->post(null, false);
            $url = ga_normalize_url($post['website_url'] ?? '');
            if (trim((string)($post['name'] ?? '')) === '' || !$url || !ga_valid_measurement_id($post['measurement_id'] ?? '')) {
                set_alert('danger', _l('ga_invalid_website_settings'));
            } elseif (!empty($post['property_id']) && !ga_valid_property_id($post['property_id'])) {
                set_alert('danger', _l('ga_invalid_property_id'));
            } else {
                $post['website_url'] = $url;
                $savedId = $this->google_analytics_model->save_website($post, (int) $id);
                ga_audit($id ? 'website_updated' : 'website_created', ['website_id'=>$savedId]);
                set_alert('success', _l('ga_website_saved'));
                redirect(admin_url('google_analytics/websites'));
            }
        }
        $this->load->view('website_form', $data);
    }

    public function delete_website($id)
    {
        $this->require_capability('delete');
        if ($this->google_analytics_model->delete_website($id)) {
            ga_audit('website_deleted', ['website_id'=>(int)$id]);
            set_alert('success', _l('deleted', _l('ga_website')));
        }
        redirect(admin_url('google_analytics/websites'));
    }

    public function snippet($id)
    {
        $this->require_capability('view');
        $website = $this->google_analytics_model->get_website($id);
        if (!$website) show_404();
        $data['title'] = _l('ga_tracking_code');
        $data['website'] = $website;
        $data['snippet'] = ga_tracking_snippet($website->measurement_id);
        $this->load->view('snippet', $data);
    }

    public function reports()
    {
        $this->require_capability('view');
        $data['title'] = _l('ga_reports');
        $data['websites'] = $this->google_analytics_model->get_websites(true);
        $data['report'] = null;
        $data['error'] = null;
        $data['website'] = null;
        $id = (int) $this->input->get('website_id');
        if ($id) {
            $data['website'] = $this->google_analytics_model->get_website($id);
            if ($data['website']) {
                try { $data['report'] = $this->google_analytics_model->realtime_report($data['website']); }
                catch (Throwable $e) { $data['error'] = $e->getMessage(); }
            }
        }
        $this->load->view('reports', $data);
    }

    public function settings()
    {
        $this->require_capability('edit');
        $data['title'] = _l('ga_settings');
        if ($this->input->post()) {
            $enabled = $this->input->post('ga_enabled') ? '1' : '0';
            $measurement = strtoupper(trim((string) $this->input->post('ga_crm_measurement_id')));
            if ($measurement !== '' && !ga_valid_measurement_id($measurement)) {
                set_alert('danger', _l('ga_invalid_measurement_id'));
                redirect(admin_url('google_analytics/settings'));
            }
            update_option('ga_enabled', $enabled);
            update_option('ga_track_admin', $this->input->post('ga_track_admin') ? '1' : '0');
            update_option('ga_track_client', $this->input->post('ga_track_client') ? '1' : '0');
            update_option('ga_crm_measurement_id', $measurement);
            update_option('ga_default_property_id', trim((string) $this->input->post('ga_default_property_id')));
            update_option('ga_report_cache_minutes', max(5, min(1440, (int)$this->input->post('ga_report_cache_minutes'))));
            $serviceJson = trim((string) $this->input->post('ga_service_account_json', false));
            if ($serviceJson !== '') {
                $decoded = json_decode($serviceJson, true);
                if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
                    set_alert('danger', _l('ga_invalid_service_account'));
                    redirect(admin_url('google_analytics/settings'));
                }
                update_option('ga_service_account_json', ga_encrypt_secret($serviceJson));
            }
            ga_audit('settings_updated');
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('google_analytics/settings'));
        }
        $this->load->view('settings', $data);
    }

    public function health()
    {
        $this->require_capability('view');
        $data['title'] = _l('ga_health_check');
        $data['checks'] = [
            'curl' => function_exists('curl_init'),
            'openssl' => function_exists('openssl_sign'),
            'json' => function_exists('json_decode'),
            'websites_table' => $this->db->table_exists(db_prefix().'ga_websites'),
            'cache_table' => $this->db->table_exists(db_prefix().'ga_report_cache'),
            'service_account' => get_option('ga_service_account_json') !== '',
            'measurement_id' => ga_valid_measurement_id(get_option('ga_crm_measurement_id')),
        ];
        $data['api_message'] = null;
        if ($this->input->get('test_api') === '1') {
            try { $this->google_analytics_model->get_access_token(); $data['api_message'] = ['success', _l('ga_api_connection_success')]; }
            catch (Throwable $e) { $data['api_message'] = ['danger', $e->getMessage()]; }
            update_option('ga_last_health_check', date('Y-m-d H:i:s'));
            ga_audit('health_check_run');
        }
        $this->load->view('health', $data);
    }

    public function clear_cache()
    {
        $this->require_capability('edit');
        $this->google_analytics_model->clear_cache();
        ga_audit('report_cache_cleared');
        set_alert('success', _l('ga_cache_cleared'));
        redirect(admin_url('google_analytics'));
    }
}
