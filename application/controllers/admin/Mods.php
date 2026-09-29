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
        $modules  = $this->app_modules->get();
        $upgraded = [];
        $errors   = [];
        $skipped  = [];

        foreach ($modules as $module) {
            $system_name = $module['system_name'];

            try {
                if ($this->app_modules->is_database_upgrade_required($system_name)) {
                    $result = $this->app_modules->upgrade_database($system_name);
                    if (is_string($result)) {
                        $errors[] = $module['headers']['module_name'] . ': ' . $result;
                    } else {
                        $upgraded[] = $module['headers']['module_name'];
                    }
                } else {
                    $skipped[] = $module['headers']['module_name'] . ': no database upgrade required';
                }
            } catch (Throwable $e) {
                $errors[] = $module['headers']['module_name'] . ': ' . $e->getMessage();
            } catch (Exception $e) {
                $errors[] = $module['headers']['module_name'] . ': ' . $e->getMessage();
            }
        }

        if (!empty($upgraded)) {
            set_alert('success', 'Upgraded modules: ' . implode(', ', $upgraded));
        }

        if (!empty($errors)) {
            set_alert('danger', 'Some modules could not be upgraded: ' . implode(' | ', $errors));
        }

        $report = ['upgraded' => $upgraded, 'skipped' => $skipped, 'errors' => $errors, 'created_at' => date('c')];
        @file_put_contents(APPPATH . 'logs/module_upgrade_' . date('Ymd_His') . '.json', json_encode($report, JSON_PRETTY_PRINT));
        if (empty($upgraded) && empty($errors)) {
            set_alert('info', 'No module database upgrades are pending. Checked ' . count($skipped) . ' modules.');
        } elseif (!empty($skipped)) {
            set_alert('info', 'Skipped modules: ' . implode(' | ', $skipped));
        }

        $this->to_modules();
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
