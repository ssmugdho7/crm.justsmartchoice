<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Env_ver extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function activate()
    {
        set_alert('success', 'BPMN Diagrams is activated for Smart Choice Contractors internal use.');
        redirect(admin_url('modules'));
    }

    public function upgrade_database()
    {
        require_once module_dir_path('diagramy') . 'install.php';
        set_alert('success', 'BPMN Diagrams database checked and repaired.');
        redirect(admin_url('modules'));
    }
}
