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

    public function upload()
    {
        $this->load->library('app_module_installer');
        $data = $this->app_module_installer->from_upload();

        if ($data['error']) {
            set_alert('danger', $data['error']);
        } else {
            set_alert('success', 'Module uploaded successfully');
        }

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

        $this->to_modules();
    }

    public function upgrade_all_database()
    {
        $modules  = $this->app_modules->get();
        $upgraded = [];
        $errors   = [];

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

        if (empty($upgraded) && empty($errors)) {
            set_alert('info', 'No module database upgrades are pending.');
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

    private function to_modules()
    {
        redirect(admin_url('modules'));
    }
}
