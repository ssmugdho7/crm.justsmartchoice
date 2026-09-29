<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_module_manager_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_modules()
    {
        $modulesPath = FCPATH . 'modules';
        $modules = [];

        if (!is_dir($modulesPath)) {
            return $modules;
        }

        foreach (scandir($modulesPath) as $folder) {
            if ($folder === '.' || $folder === '..' || !smart_module_manager_is_safe_slug($folder)) {
                continue;
            }

            $path = $modulesPath . '/' . $folder;
            if (!is_dir($path)) {
                continue;
            }

            $mainFile = $path . '/' . $folder . '.php';
            $headers = $this->parse_module_headers($mainFile);

            $modules[] = [
                'slug'       => $folder,
                'name'       => !empty($headers['Module Name']) ? $headers['Module Name'] : smart_module_manager_title_case($folder),
                'version'    => !empty($headers['Version']) ? $headers['Version'] : '-',
                'author'     => !empty($headers['Author']) ? $headers['Author'] : '-',
                'main_file'  => file_exists($mainFile) ? 'Yes' : 'No',
                'path'       => $path,
                'active'     => module_is_active($folder) ? 'Yes' : 'No',
                'health'     => $this->module_health($folder),
            ];
        }

        usort($modules, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $modules;
    }

    public function parse_module_headers($file)
    {
        $headers = [];
        if (!file_exists($file)) {
            return $headers;
        }

        $content = file_get_contents($file, false, null, 0, 8192);
        foreach (['Module Name', 'Description', 'Version', 'Author', 'Author URI', 'Requires at least'] as $field) {
            if (preg_match('/' . preg_quote($field, '/') . '\s*:\s*(.+)$/mi', $content, $matches)) {
                $headers[$field] = trim($matches[1]);
            }
        }

        return $headers;
    }

    public function module_health($moduleName)
    {
        $base = FCPATH . 'modules/' . $moduleName;
        $checks = [
            'Main File' => file_exists($base . '/' . $moduleName . '.php'),
            'English Language' => file_exists($base . '/language/english/' . $moduleName . '/' . $moduleName . '_lang.php'),
            'Spanish Language' => file_exists($base . '/language/spanish/' . $moduleName . '/' . $moduleName . '_lang.php'),
            'Migrations Folder' => is_dir($base . '/migrations'),
            'Views Folder' => is_dir($base . '/views'),
            'Controllers Folder' => is_dir($base . '/controllers'),
        ];

        return $checks;
    }

    public function cleanup_module_records($moduleName)
    {
        if (!smart_module_manager_is_safe_slug($moduleName)) {
            return false;
        }

        $like = $this->db->escape_like_str($moduleName);

        if ($this->db->table_exists(db_prefix() . 'options')) {
            $this->db->like('name', $like, 'both');
            $this->db->delete(db_prefix() . 'options');
        }

        if ($this->db->table_exists(db_prefix() . 'permissions')) {
            if ($this->db->field_exists('feature', db_prefix() . 'permissions')) {
                $this->db->where('feature', $moduleName);
                $this->db->delete(db_prefix() . 'permissions');
            }
        }

        if ($this->db->table_exists(db_prefix() . 'emailtemplates')) {
            if ($this->db->field_exists('type', db_prefix() . 'emailtemplates')) {
                $this->db->where('type', $moduleName);
                $this->db->delete(db_prefix() . 'emailtemplates');
            }
        }

        return true;
    }
}
