<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sc_ai_repair extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !has_permission('sc_ai_repair', '', 'view')) {
            access_denied('sc_ai_repair');
        }
        $this->load->model('sc_ai_repair_model');
        $this->load->library('sc_ai_repair/Repair_scanner');
        $this->load->library('sc_ai_repair/Repair_engine');
    }

    public function index()
    {
        $data['title'] = _l('sc_ai_repair');
        $data['runs']  = $this->sc_ai_repair_model->recent_runs();
        $data['latest_applied_run'] = $this->sc_ai_repair_model->latest_applied_run();
        $this->load->view('dashboard', $data);
    }

    public function checkup()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('create');
        try {
            $run      = $this->sc_ai_repair_model->start_run('checkup', '');
            $findings = $this->repair_scanner->run($run);
            $this->sc_ai_repair_model->finish_run($run, 'completed', sprintf('%d findings', count($findings)));
            $this->json(['success' => true, 'run_id' => $run, 'findings' => $findings]);
        } catch (Throwable $e) {
            log_activity('AI Repair checkup failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function analyze_error()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('create');
        $error = trim((string) $this->input->post('error_text'));
        if ($error === '') {
            $this->json(['success' => false, 'message' => _l('sc_ai_repair_error_required')]);
        }
        try {
            $run    = $this->sc_ai_repair_model->start_run('error', $error);
            $result = $this->repair_engine->analyze($run, $error);
            $this->sc_ai_repair_model->finish_run($run, 'planned', $result['summary'] ?? '');
            $this->json(['success' => true, 'run_id' => $run, 'result' => $result]);
        } catch (Throwable $e) {
            log_activity('AI Repair analysis failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function improve_prompt()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('create');
        $text = trim((string) $this->input->post('error_text'));
        if ($text === '') {
            $this->json(['success' => false, 'message' => _l('sc_ai_repair_error_required')]);
        }
        try {
            $this->load->library('sc_ai_repair/Openai_repair_client');
            $improved = $this->openai_repair_client->rewriteRepairPrompt($text);
            $this->json(['success' => true, 'text' => $improved]);
        } catch (Throwable $e) {
            log_activity('AI Repair prompt improvement failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function connection_test()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('view');
        try {
            $this->load->library('sc_ai_repair/Openai_repair_client');
            $this->json(['success' => true, 'connection' => $this->openai_repair_client->connectionInfo()]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function clear_cache()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('edit');
        try {
            $result = $this->repair_engine->clear_cache();
            $this->json([
                'success' => true,
                'message' => _l('sc_ai_repair_cache_cleared'),
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            log_activity('AI Repair cache clear failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rollback_last()
    {
        $this->require_action('edit');
        $run = $this->sc_ai_repair_model->latest_applied_run();
        if (!$run) {
            set_alert('warning', _l('sc_ai_repair_no_rollback_available'));
            redirect(admin_url('sc_ai_repair'));
        }
        $this->rollback((int) $run['id']);
    }

    public function apply()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('edit');
        $run     = (int) $this->input->post('run_id');
        try {
            $result = $this->repair_engine->apply_plan($run);
            $this->json(['success' => true, 'result' => $result]);
        } catch (Throwable $e) {
            log_activity('AI Repair apply failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rollback($run = 0)
    {
        $this->require_action('edit');
        try {
            $this->repair_engine->rollback((int) $run);
            set_alert('success', _l('sc_ai_repair_rollback_complete'));
        } catch (Throwable $e) {
            set_alert('danger', $e->getMessage());
        }
        redirect(admin_url('sc_ai_repair'));
    }

    public function rename_asset()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('edit');
        try {
            $result = $this->repair_engine->rename_asset(
                trim((string) $this->input->post('old_path')),
                trim((string) $this->input->post('new_path'))
            );
            $this->json(['success' => true, 'result' => $result]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function report($run = 0)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('view');
        $report = $this->sc_ai_repair_model->get_report((int) $run);
        if (!$report) {
            $this->json(['success' => false, 'message' => _l('sc_ai_repair_report_not_found')]);
        }
        $this->json(['success' => true, 'report' => $report]);
    }

    public function delete_run($run = 0)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->require_action('delete');
        try {
            $this->sc_ai_repair_model->delete_run((int) $run);
            $this->json(['success' => true, 'message' => _l('sc_ai_repair_history_deleted')]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('sc_ai_repair');
        }

        if ($this->input->post()) {
            $root = $this->normalize_root((string) $this->input->post('crm_root'));
            if (!$this->valid_crm_root($root)) {
                set_alert('danger', _l('sc_ai_repair_invalid_crm_root'));
                redirect(admin_url('sc_ai_repair/settings'));
            }

            $url = rtrim(trim((string) $this->input->post('crm_url')), '/');
            if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
                set_alert('danger', _l('sc_ai_repair_invalid_crm_url'));
                redirect(admin_url('sc_ai_repair/settings'));
            }

            $maxBytes = (int) $this->input->post('max_file_bytes');
            $maxBytes = max(65536, min(2097152, $maxBytes));

            update_option('sc_ai_repair_crm_root', $root);
            update_option('sc_ai_repair_crm_url', $url);
            update_option('sc_ai_repair_max_file_bytes', (string) $maxBytes);

            foreach (['provider', 'model', 'base_url', 'organization'] as $key) {
                if ($this->input->post($key) !== null) {
                    $value = trim((string) $this->input->post($key));
                    if ($key === 'base_url' && $value === '') { $value = 'https://api.openai.com/v1'; }
                    if ($key === 'model' && $value === '') { $value = 'gpt-5-mini'; }
                    update_option('sc_ai_repair_' . $key, $value);
                }
            }
            foreach (['auto_backup', 'require_approval', 'scan_vendor'] as $key) {
                $postedName = 'sc_ai_repair_' . $key;
                if ($this->input->post($postedName) !== null) {
                    update_option($postedName, $this->input->post($postedName));
                }
            }

            if ($this->input->post('api_key') !== null && trim((string) $this->input->post('api_key')) !== '') {
                update_option('sc_ai_repair_api_key', trim((string) $this->input->post('api_key')));
            }

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('sc_ai_repair/settings'));
        }

        $data['title'] = _l('sc_ai_repair_settings');
        $this->load->view('settings', $data);
    }

    private function normalize_root($root)
    {
        $root = trim($root);
        if ($root === '') {
            return rtrim(FCPATH, DIRECTORY_SEPARATOR);
        }
        $real = realpath($root);
        return rtrim($real !== false ? $real : $root, DIRECTORY_SEPARATOR);
    }

    private function valid_crm_root($root)
    {
        return is_dir($root)
            && is_dir($root . DIRECTORY_SEPARATOR . 'application')
            && is_dir($root . DIRECTORY_SEPARATOR . 'system')
            && is_dir($root . DIRECTORY_SEPARATOR . 'modules')
            && is_file($root . DIRECTORY_SEPARATOR . 'index.php');
    }

    private function require_action($capability)
    {
        if (!is_admin() && !has_permission('sc_ai_repair', '', $capability)) {
            access_denied('sc_ai_repair');
        }
    }

    private function json(array $payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->output->_display();
        exit;
    }
}
