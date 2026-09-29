<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Favorite_links extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function menu_detail($menu_id = 0)
    {
        $view_data = [];
        $data = new stdClass();

        $menu_info = $this->db->select('*')->from(db_prefix() . 'perfex_menu_links')->where('id', (int) $menu_id)->get()->row();

        if (!empty($menu_info)) {
            $data = $menu_info;
        } else {
            $menu_id = 0;
            $data->pml_title = '';
            $data->pml_link = '';
            $data->pml_hotkeys = '';
            $data->pml_hotkey_numbers = '';
            $data->pml_rels = 'nofollow';
            $data->pml_target = get_option('favorite_links_default_target') ?: '_self';
            $data->pml_order = 1;
        }

        $view_data['data'] = $data;
        $view_data['menu_id'] = (int) $menu_id;

        $this->load->view('menu_detail', $view_data);
    }

    public function save($menu_id = 0)
    {
        if ($this->input->post()) {
            $post_data = $this->input->post(null, true);
            $post_data = $this->normalize_post_data($post_data);

            if (empty($menu_id)) {
                $this->db->insert(db_prefix() . 'perfex_menu_links', $post_data);
            } else {
                $this->db->where('id', (int) $menu_id)->update(db_prefix() . 'perfex_menu_links', $post_data);
            }

            set_alert('success', _l('pml_success'));
        }

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url());
    }

    public function delete($menu_id = 0)
    {
        $this->db->where('id', (int) $menu_id)->delete(db_prefix() . 'perfex_menu_links');
        set_alert('success', _l('pml_success'));
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url());
    }

    private function normalize_post_data($data)
    {
        $allowedTargets = ['_self', '_blank', '_parent', '_top'];
        $allowedRels = ['alternate', 'author', 'bookmark', 'external', 'help', 'license', 'next', 'nofollow', 'noreferrer', 'noopener', 'prev', 'search', 'tag'];

        $data['pml_title'] = trim($data['pml_title'] ?? '');
        $data['pml_link'] = $this->normalize_link($data['pml_link'] ?? '');
        $data['pml_rels'] = in_array(($data['pml_rels'] ?? ''), $allowedRels, true) ? $data['pml_rels'] : 'nofollow';
        $data['pml_target'] = in_array(($data['pml_target'] ?? ''), $allowedTargets, true) ? $data['pml_target'] : (get_option('favorite_links_default_target') ?: '_self');
        $data['pml_hotkeys'] = strtoupper(substr(trim($data['pml_hotkeys'] ?? ''), 0, 1));
        $data['pml_hotkey_numbers'] = !empty($data['pml_hotkey_numbers']) ? (int) $data['pml_hotkey_numbers'] : null;
        $data['pml_order'] = isset($data['pml_order']) ? (int) $data['pml_order'] : 1;

        return $data;
    }

    private function normalize_link($link)
    {
        $link = trim((string) $link);
        $link = preg_replace('/\s+/', '', $link);

        if ($link === '') {
            return admin_url();
        }

        if (stripos($link, 'ttps://') === 0) {
            $link = 'h' . $link;
        }

        $link = str_replace('crm.justsmartchoice.com//', 'crm.justsmartchoice.com/', $link);
        $link = str_replace('/adminmodules', '/admin/modules', $link);
        $link = str_replace('adminmodules', 'admin/modules', $link);

        return $link;
    }
}
