<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_module_licensing extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__) . '/helpers/sc_module_licensing_helper.php';
        if (!$this->app_modules->is_active('sc_module_licensing')) { show_404(); exit; }
        $this->load->model('sc_module_licensing/sc_license_model');
        $this->output->set_header('Cache-Control: no-store');
    }
    public function index()
    {
        redirect(admin_url('sc_module_licensing/' . (scl_can('manage_settings') ? 'activation' : 'help')));
    }
    public function activation()
    {
        if (!scl_can('manage_settings')) { access_denied('sc_module_licensing'); }
        if ($this->input->method() === 'post') {
            $module = $this->input->post('module', true);
            $key = $this->input->post('activation_key', false);
            $ok = is_string($module) && $this->sc_license_model->activate($module, $key);
            set_alert($ok ? 'success' : 'danger', _l($ok ? 'scl_activated' : 'scl_activation_failed'));
            redirect(admin_url('sc_module_licensing/activation'));
        }
        $rows = $this->db->select('module,certificate')->get(db_prefix() . 'scl_activations')->result_array();
        $this->page('activation', ['activations'=>$rows]);
    }
    public function licenses()
    {
        if (!scl_issuer() || !(scl_can('view') || scl_can('view_own') || scl_can('create'))) {
            access_denied('sc_module_licensing');
        }
        $key = '';
        if ($this->input->method() === 'post') {
            try {
                $key = $this->sc_license_model->issue($this->input->post('company', true),
                    $this->input->post('module', true), $this->input->post('site', true),
                    $this->input->post('years', true));
            } catch (Throwable $e) { set_alert('danger', _l('scl_issue_failed')); }
        }
        $rows = (scl_can('view') || scl_can('view_own')) ? $this->sc_license_model->scoped(null, (int) $this->input->get('before')) : [];
        $this->page('licenses', ['licenses'=>$rows, 'issued_key'=>$key]);
    }
    public function change($id)
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        try {
            $date = $this->input->post('expires', true);
            $expires = null;
            if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
                $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));
                if ($parsed && $parsed->format('Y-m-d') === $date) { $expires = $parsed->getTimestamp() + 86399; }
            }
            $ok = $this->sc_license_model->change((int) $id, $this->input->post('action', true), $expires);
            set_alert($ok ? 'success' : 'danger', _l($ok ? 'scl_saved' : 'scl_issue_failed'));
        } catch (Throwable $e) { access_denied('sc_module_licensing'); }
        redirect(admin_url('sc_module_licensing/licenses'));
    }
    public function help()
    {
        if (!(scl_can('view') || scl_can('view_own') || scl_can('manage_settings') || scl_can('create'))) {
            access_denied('sc_module_licensing');
        }
        $this->page('help', []);
    }
    private function page($page, array $data)
    {
        $data['page'] = $page;
        $data['title'] = _l('scl_title');
        $this->load->view('sc_module_licensing/manage', $data);
    }
}
