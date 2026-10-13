<?php
defined('BASEPATH') or exit('No direct script access allowed');
class License_api extends App_Controller
{
    public function check()
    {
        require_once dirname(__DIR__) . '/helpers/sc_module_licensing_helper.php';
        $this->output->set_header('Cache-Control: no-store');
        if (!$this->app_modules->is_active('sc_module_licensing') || !scl_issuer()
            || $this->input->method() !== 'post' || !is_https()) { show_404(); return; }
        $key = $this->input->get_request_header('X-SC-License', false);
        $raw = $this->input->raw_input_stream;
        if (!is_string($key) || !preg_match('/^SC1-[a-f0-9]{64}$/D', $key) || strlen($raw) > 2000) {
            $this->output->set_status_header(403)->set_output('{}'); return;
        }
        try {
            $p = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
            if (!is_array($p) || !is_string($p['site'] ?? null) || !is_string($p['module'] ?? null)
                || !is_string($p['installation'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $p['installation'])) {
                throw new RuntimeException('scl_invalid');
            }
            $row = $this->db->where('key_hash', hash('sha256', $key))->where('active', 1)
                ->where('expires >', time())->where('site', Sc_license_crypto::site($p['site']))
                ->where('module', $p['module'])->get(db_prefix() . 'scl_licenses')->row_array();
            if (!$row) { throw new RuntimeException('scl_invalid'); }
            $config = scl_config();
            $certificate = Sc_license_crypto::sign(['v'=>1, 'id'=>(int) $row['id'], 'company'=>$row['company'],
                'module'=>$row['module'], 'site'=>$row['site'], 'installation'=>$p['installation'],
                'issued'=>time(), 'expires'=>min(time() + 3600, (int) $row['expires']),
                'license_expires'=>(int) $row['expires']], file_get_contents($config['private_key_file']));
            $this->output->set_content_type('application/json')->set_output(json_encode(['certificate'=>$certificate]));
        } catch (Throwable $e) { $this->output->set_status_header(403)->set_output('{}'); }
    }
}
