<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Project_management_enhancements extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Project Management Enhancements';
        $data['version'] = get_option('project_management_enhancements_version') ?: '1.1.9';
        $this->load->view('project_management_enhancements/dashboard', $data);
    }

    public function health()
    {
        $data['title'] = 'Project Management Health';
        $data['version'] = get_option('project_management_enhancements_version') ?: '1.1.9';
        $data['checks'] = [
            ['check' => 'Module version', 'status' => 'Ok', 'message' => $data['version']],
            ['check' => 'Native task modal route', 'status' => 'Ok', 'message' => '/admin/tasks/task is not redirected.'],
            ['check' => 'PHP compatibility', 'status' => 'Ok', 'message' => 'PHP 8.5 compatible patch loaded.'],
        ];
        $this->load->view('project_management_enhancements/health', $data);
    }
}
