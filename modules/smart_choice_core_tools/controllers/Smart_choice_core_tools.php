<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_core_tools extends AdminController
{
    public function calculator()
    {
        $data['title'] = _l('scct_calculator');
        $this->load->view('calculator', $data);
    }

    public function project_profitability()
    {
        $this->load->model('projects_model');
        $data['projects'] = $this->projects_model->get();
        $data['selected'] = (int) $this->input->get('project_id');
        $data['summary'] = null;

        if ($data['selected']) {
            $p = db_prefix();
            $income = (float) $this->db->query("SELECT COALESCE(SUM(total),0) t FROM {$p}invoices WHERE project_id=? AND status NOT IN (5,6)", [$data['selected']])->row()->t;
            $expenses = (float) $this->db->query("SELECT COALESCE(SUM(amount),0) t FROM {$p}expenses WHERE project_id=?", [$data['selected']])->row()->t;
            $data['summary'] = ['income' => $income, 'expenses' => $expenses, 'profit' => $income - $expenses];
        }

        $data['title'] = _l('scct_project_profitability');
        $this->load->view('project_profitability', $data);
    }

    public function system_health()
    {
        if (!is_admin()) {
            access_denied('Smart Choice System Health');
        }

        $companyPath = rtrim(get_upload_path_by_type('company'), '/\\') . DIRECTORY_SEPARATOR;
        $staffRows = $this->db->select('staffid,firstname,lastname,profile_image')->order_by('staffid', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $staff = [];

        foreach ($staffRows as $row) {
            $base = FCPATH . 'uploads/staff_profile_images/' . (int) $row['staffid'] . '/';
            $name = (string) $row['profile_image'];
            $candidates = $name === '' ? [] : [$name, 'small_' . $name, 'thumb_' . $name];
            $found = [];
            foreach ($candidates as $candidate) {
                if (is_file($base . $candidate)) { $found[] = $candidate; }
            }
            $row['folder'] = $base;
            $row['found'] = $found;
            $staff[] = $row;
        }

        $data = [
            'title' => _l('scct_system_health'),
            'company_path' => $companyPath,
            'company_exists' => is_dir($companyPath),
            'company_writable' => is_writable($companyPath),
            'company_permissions' => is_dir($companyPath) ? substr(sprintf('%o', fileperms($companyPath)), -4) : 'N/A',
            'company_owner' => function_exists('fileowner') && is_dir($companyPath) ? @fileowner($companyPath) : null,
            'notes_table' => $this->db->table_exists(db_prefix() . 'notes'),
            'notes_count' => $this->db->table_exists(db_prefix() . 'notes') ? (int) $this->db->count_all(db_prefix() . 'notes') : 0,
            'sales_meta_table' => $this->db->table_exists(db_prefix() . 'sc_sales_meta'),
            'staff' => $staff,
        ];

        $this->load->view('system_health', $data);
    }

    public function module_compliance()
    {
        if (!is_admin()) {
            access_denied('Module Compliance');
        }

        $modulesPath = FCPATH . 'modules';
        $rows = [];
        foreach ((array) glob($modulesPath . '/*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);
            $main = $dir . '/' . $slug . '.php';
            $english = $dir . '/language/english/' . $slug . '_lang.php';
            $spanish = $dir . '/language/spanish/' . $slug . '_lang.php';
            $migrations = $dir . '/migrations';
            $invalidMigrationConfig = is_file($dir . '/config/migration.php');
            $rows[] = [
                'slug' => $slug,
                'main' => is_file($main),
                'english' => is_file($english),
                'spanish' => is_file($spanish),
                'migrations' => is_dir($migrations),
                'invalid_migration_config' => $invalidMigrationConfig,
            ];
        }

        $data['title'] = 'Module Compliance';
        $data['rows'] = $rows;
        $this->load->view('module_compliance', $data);
    }
}
