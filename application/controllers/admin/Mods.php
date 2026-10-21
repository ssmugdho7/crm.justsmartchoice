<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mods extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            redirect(admin_url());
        }

        @ini_set('max_execution_time', '120');
        @ini_set('memory_limit', '512M');
    }

    public function index()
    {
        $data['modules'] = $this->app_modules->get();
        foreach ($data['modules'] as &$module) {
            $module['maintenance'] = $this->module_status($module);
        }
        unset($module);
        $data['title']   = _l('modules');
        $this->load->view('admin/modules/list', $data);
    }

    public function activate($name)
    {
        $this->app_modules->activate($name);
        $this->to_modules();
    }

    public function deactivate($name)
    {
        $this->app_modules->deactivate($name);
        $this->to_modules();
    }

    public function uninstall($name)
    {
        $this->app_modules->uninstall($name);
        $this->to_modules();
    }


    public function download($name)
    {
        $name = basename((string) $name);
        $source = FCPATH . 'modules/' . $name;
        if ($name === '' || !is_dir($source)) {
            show_404();
        }
        if (!class_exists('ZipArchive')) {
            show_error('ZipArchive is not available on this server.', 500);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'sc_mod_');
        if ($tmp === false) {
            show_error('Unable to create a temporary module archive.', 500);
        }
        @unlink($tmp);
        $zipPath = $tmp . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            show_error('Unable to create the module archive.', 500);
        }
        $rootLen = strlen(dirname($source)) + 1;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $local = substr($path, $rootLen);
            if ($file->isDir()) {
                $zip->addEmptyDir(str_replace('\\', '/', $local));
            } elseif ($file->isFile()) {
                $zip->addFile($path, str_replace('\\', '/', $local));
            }
        }
        $zip->close();
        if (!is_file($zipPath)) {
            show_error('The module archive was not generated.', 500);
        }
        $this->load->helper('download');
        $data = file_get_contents($zipPath);
        @unlink($zipPath);
        force_download($name . '_' . date('Ymd_His') . '.zip', $data);
    }

    public function upload()
    {
        $this->load->library('app_module_installer');
        $data = $this->app_module_installer->from_upload();

        if ($data['error']) {
            set_alert('danger', $data['error']);
        } else {
            set_alert('success', 'Module uploaded successfully');
        }

        $this->smart_choice_clear_cache();
        $this->to_modules();
    }

    public function upgrade_database($name)
    {
        $result = $this->app_modules->upgrade_database($name);

        if (is_string($result)) {
            set_alert('danger', $result);
        } else {
            set_alert('success', 'Database Upgraded Successfully');
        }

        $this->smart_choice_clear_cache();
        $this->to_modules();
    }

    public function upgrade_all_database()
    {
        // GET is a read-only review. Run each migration in a separate POST request:
        // module migrations can reuse PHP class names, and a fatal error or hosting
        // timeout in one module must not hide the results for the entire batch.
        if ($this->input->method() !== 'post') {
            $data = ['modules' => $this->app_modules->get(), 'title' => 'Module Database Upgrades'];
            foreach ($data['modules'] as &$module) {
                $module['maintenance'] = $this->module_status($module);
            }
            unset($module);
            $this->load->view('admin/modules/upgrades', $data);
            return;
        }

        $name = $this->input->post('module');
        $module = is_string($name) ? $this->app_modules->get($name) : null;
        if (!$module) {
            return $this->upgrade_response('error', 'Unknown module.', 404);
        }
        if ($this->input->post('version') !== (string) $module['headers']['version']
            || $this->input->post('installed_version') !== (string) $module['installed_version']) {
            return $this->upgrade_response('error', 'Module versions changed. Reload the review before continuing.', 409);
        }

        $status = $this->module_status($module);
        if (!$status['can_upgrade']) {
            return $this->upgrade_response($status['errors'] ? 'error' : 'skipped',
                $status['errors'] ? implode(' ', $status['errors']) : $status['note']);
        }

        try {
            $result = $this->app_modules->upgrade_database($name);
            if ($result !== true) {
                // Never treat false/null/empty error strings as a successful upgrade.
                $message = is_string($result) && trim($result) !== ''
                    ? 'Database upgrade failed: ' . mb_substr(strip_tags($result), 0, 500)
                    : 'Database upgrade failed. Review this module\'s migration and CRM error log.';
                return $this->record_upgrade_result($module, 'error', $message);
            }
            return $this->record_upgrade_result($module, 'upgraded', 'Database upgraded successfully.');
        } catch (Throwable $e) {
            $this->log_module_check_error($name, 'migration', $e);
            return $this->record_upgrade_result($module, 'error', 'Database upgrade failed. Review the CRM error log before retrying.');
        }
    }

    private function module_status($module)
    {
        $name = $module['system_name'];
        $status = ['upgrade_required' => false, 'compatible' => false, 'can_upgrade' => false,
            'errors' => [], 'note' => 'No pending database upgrade.'];
        try {
            $status['compatible'] = $this->app_modules->is_minimum_version_requirement_met($name);
            if (!$status['compatible']) {
                $status['errors'][] = 'Requires CRM version ' . ($module['headers']['requires_at_least'] ?? 'newer than the installed version') . '.';
            }
        } catch (Throwable $e) {
            $status['errors'][] = 'CRM compatibility check failed. Review the CRM error log.';
            $this->log_module_check_error($name, 'compatibility', $e);
        }
        try {
            $status['upgrade_required'] = $this->app_modules->is_database_upgrade_required($name);
        } catch (Throwable $e) {
            $status['errors'][] = 'Database upgrade check failed. Review the CRM error log.';
            $this->log_module_check_error($name, 'database version', $e);
        }
        if ($status['upgrade_required'] && version_compare((string) $module['installed_version'], (string) $module['headers']['version'], '>')) {
            $status['errors'][] = 'Installed database version is newer than the module files. Automatic downgrade is blocked.';
        }
        if ($module['installed_version'] === false) {
            $status['note'] = 'Not installed; activate the module separately if needed.';
        }
        $status['can_upgrade'] = $status['upgrade_required'] && !$status['errors'];
        if ($status['can_upgrade']) {
            $status['note'] = 'Ready for database upgrade.';
        }
        $failures = $this->session->userdata('module_upgrade_failures');
        $last = is_array($failures) ? ($failures[$name] ?? null) : null;
        if ($last && $last['version'] === $module['headers']['version']
            && $last['installed_version'] === $module['installed_version'] && $status['upgrade_required']) {
            $status['errors'][] = $last['message'];
        }
        return $status;
    }

    private function log_module_check_error($name, $check, Throwable $e)
    {
        // Exception messages can contain SQL or credentials. Log the location/type,
        // not the private payload, and keep the diagnostic visible to the admin.
        log_message('error', 'Module ' . $name . ' ' . $check . ' failed: ' . get_class($e)
            . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    private function record_upgrade_result($module, $result, $message)
    {
        $failures = $this->session->userdata('module_upgrade_failures');
        $failures = is_array($failures) ? $failures : [];
        if ($result === 'error') {
            $failures[$module['system_name']] = ['version' => $module['headers']['version'],
                'installed_version' => $module['installed_version'], 'message' => $message];
        } else {
            unset($failures[$module['system_name']]);
        }
        $this->session->set_userdata('module_upgrade_failures', $failures);
        log_message($result === 'error' ? 'error' : 'info', 'Module database upgrade ' . $result . ': ' . $module['system_name']);
        return $this->upgrade_response($result, $message);
    }

    private function upgrade_response($status, $message, $http_status = 200)
    {
        return $this->output->set_status_header($http_status)->set_content_type('application/json')
            ->set_output(json_encode(['status' => $status, 'message' => $message]));
    }

    public function update_version($name)
    {
        if ($this->app_modules->new_version_available($name)) {
            $this->app_modules->update_to_new_version($name);
        }

        $this->to_modules();
    }

    public function clear_cache()
    {
        $count = $this->smart_choice_clear_cache();
        set_alert('success', 'CRM cache cleaned successfully. ' . $count . ' cached files removed.');
        $this->to_modules();
    }

    private function smart_choice_clear_cache()
    {
        $directory = APPPATH . 'cache';
        $count = 0;
        if (!is_dir($directory)) {
            return $count;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $name = $file->getFilename();
            if ($name === 'index.html' || $name === '.htaccess') {
                continue;
            }
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } elseif (@unlink($file->getPathname())) {
                $count++;
            }
        }
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        clearstatcache(true);
        return $count;
    }

    private function to_modules()
    {
        redirect(admin_url('modules'));
    }
}
