<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_license_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__) . '/helpers/sc_module_licensing_helper.php';
        $this->load->library('encryption');
    }

    public function valid($module)
    {
        $row = $this->db->where('module', $module)->get(db_prefix() . 'scl_activations')->row_array();
        if (!$row) { return false; }
        $config = scl_config();
        try {
            Sc_license_crypto::verify($row['certificate'], $config['public_key'] ?? '', $module,
                base_url(), get_option('scl_installation'));
            return true;
        } catch (Throwable $e) {
            // Check at most once per minute on unavailable/expired leases; never extend a failed lease.
            if ((int) $row['checked_at'] > time() - 60) { return false; }
            $key = $this->encryption->decrypt($row['key_encrypted']);
            if (!$key) { return false; }
            return $this->activate($module, $key);
        }
    }

    public function activate($module, $key)
    {
        if (!preg_match('/^[a-z][a-z0-9_]{2,79}$/D', $module)
            || !is_string($key) || !preg_match('/^SC1-[a-f0-9]{64}$/D', $key)) { return false; }
        $config = scl_config();
        $this->db->where('module', $module)->update(db_prefix() . 'scl_activations', ['checked_at'=>time()]);
        try {
            $server = Sc_license_crypto::site($config['server'] ?? '');
            $data = $this->fetch($server, $key, ['module'=>$module, 'site'=>Sc_license_crypto::site(base_url()),
                'installation'=>get_option('scl_installation')]);
            Sc_license_crypto::verify($data['certificate'] ?? '', $config['public_key'] ?? '', $module,
                base_url(), get_option('scl_installation'));
            $encrypted = $this->encryption->encrypt($key);
            if (!$encrypted) { return false; }
            return $this->db->replace(db_prefix() . 'scl_activations', ['module'=>$module,
                'key_encrypted'=>$encrypted, 'certificate'=>$data['certificate'], 'checked_at'=>time()]);
        } catch (Throwable $e) { return false; }
    }

    protected function fetch($server, $key, array $payload)
    {
        $response = '';
        $curl = curl_init($server . '/sc_module_licensing/license_api/check');
        curl_setopt_array($curl, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER=>['Content-Type: application/json', 'X-SC-License: ' . $key],
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>3, CURLOPT_TIMEOUT=>8,
            CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS, CURLOPT_WRITEFUNCTION=>function ($ch, $chunk) use (&$response) {
                $response .= $chunk; return strlen($response) <= 16000 ? strlen($chunk) : 0;
            }]);
        $ok = curl_exec($curl); $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
        if ($ok === false || $code !== 200) { throw new RuntimeException('scl_unavailable'); }
        return json_decode($response, true, 16, JSON_THROW_ON_ERROR);
    }

    public function issue($company, $module, $site, $years)
    {
        if (!scl_issuer() || !scl_can('create')) { throw new RuntimeException('scl_denied'); }
        if (!is_string($company) || trim($company) === '' || strlen($company) > 191
            || !is_string($module) || !preg_match('/^[a-z][a-z0-9_]{2,79}$/D', $module)
            || !in_array((string) $years, ['1', '2', '3', '4', '5'], true)) { throw new RuntimeException('scl_invalid'); }
        $key = 'SC1-' . bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+' . (int) $years . ' years')->getTimestamp();
        $this->db->trans_start();
        $this->db->insert(db_prefix() . 'scl_licenses', ['key_hash'=>hash('sha256', $key),
            'company'=>trim($company), 'module'=>$module, 'site'=>Sc_license_crypto::site($site),
            'expires'=>$expires, 'active'=>1, 'created_by'=>get_staff_user_id(), 'created_at'=>time()]);
        $id = $this->db->insert_id();
        $this->audit($id, 'issued');
        $this->db->trans_complete();
        if (!$this->db->trans_status()) { throw new RuntimeException('scl_unavailable'); }
        return $key;
    }

    public function scoped($id = null)
    {
        if (!scl_issuer() || !(scl_can('view') || scl_can('view_own'))) {
            throw new RuntimeException('scl_denied');
        }
        $this->db->select('id,company,module,site,expires,active,created_by,created_at');
        if (!scl_can('view')) { $this->db->where('created_by', get_staff_user_id()); }
        if ($id !== null) { return $this->db->where('id', (int) $id)->get(db_prefix() . 'scl_licenses')->row_array(); }
        return $this->db->order_by('id', 'desc')->limit(100)->get(db_prefix() . 'scl_licenses')->result_array();
    }

    public function change($id, $action, $expires = null)
    {
        if (!scl_can($action === 'delete' ? 'delete' : 'edit') || !$this->scoped($id)) {
            throw new RuntimeException('scl_denied');
        }
        $data = ['active'=>$action === 'enable' ? 1 : 0];
        if ($action === 'extend') {
            if (!is_int($expires) || $expires <= time() || $expires > time() + 6 * 366 * 86400) {
                throw new RuntimeException('scl_invalid');
            }
            $data = ['expires'=>$expires];
        } elseif (!in_array($action, ['enable','revoke','delete'], true)) { throw new RuntimeException('scl_invalid'); }
        $this->db->trans_start();
        if ($action === 'delete') { $this->db->where('id', (int) $id)->delete(db_prefix() . 'scl_licenses'); }
        else { $this->db->where('id', (int) $id)->update(db_prefix() . 'scl_licenses', $data); }
        $this->audit($id, $action);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function audit($id, $action)
    {
        $this->db->insert(db_prefix() . 'scl_audit', ['license_id'=>$id, 'actor'=>get_staff_user_id(),
            'action'=>$action, 'occurred_at'=>time()]);
    }
}
