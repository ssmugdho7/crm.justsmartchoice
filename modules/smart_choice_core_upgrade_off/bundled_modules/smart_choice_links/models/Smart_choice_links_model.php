<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_links_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null, $activeOnly = false)
    {
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }

        if ($id !== null) {
            return $this->db->where('id', (int) $id)->get(SMART_CHOICE_LINKS_TABLE)->row();
        }

        return $this->db
            ->order_by('position', 'asc')
            ->order_by('title', 'asc')
            ->get(SMART_CHOICE_LINKS_TABLE)
            ->result();
    }

    public function add($data)
    {
        $data = $this->prepare($data);
        $data['created_by']  = get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(SMART_CHOICE_LINKS_TABLE, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data = $this->prepare($data);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $id);
        return $this->db->update(SMART_CHOICE_LINKS_TABLE, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id);
        return $this->db->delete(SMART_CHOICE_LINKS_TABLE);
    }

    private function prepare($data)
    {
        $roles = isset($data['visible_to_roles']) && is_array($data['visible_to_roles']) ? $data['visible_to_roles'] : [];
        $staff = isset($data['visible_to_staff']) && is_array($data['visible_to_staff']) ? $data['visible_to_staff'] : [];

        $clean = [
            'title'            => trim((string) ($data['title'] ?? '')),
            'url'              => smart_choice_links_normalize_url($data['url'] ?? ''),
            'category'         => trim((string) ($data['category'] ?? 'General')),
            'icon'             => trim((string) ($data['icon'] ?? 'fa fa-link')),
            'target'           => in_array(($data['target'] ?? '_self'), ['_self', '_blank'], true) ? $data['target'] : '_self',
            'rel'              => trim((string) ($data['rel'] ?? 'noopener noreferrer')),
            'text_color'       => $this->clean_color($data['text_color'] ?? ''),
            'text_shadow'      => isset($data['text_shadow']) ? 1 : 0,
            'position'         => (int) ($data['position'] ?? 1),
            'is_active'        => isset($data['is_active']) ? 1 : 0,
            'is_internal'      => isset($data['is_internal']) ? 1 : 0,
            'notes'            => trim((string) ($data['notes'] ?? '')),
            'visible_to_roles' => implode(',', array_filter(array_map('trim', $roles))),
            'visible_to_staff' => implode(',', array_filter(array_map('trim', $staff))),
        ];

        if ($clean['title'] === '') {
            $clean['title'] = 'Smart Choice Link';
        }

        if ($clean['url'] === '') {
            $clean['url'] = admin_url();
        }

        return $clean;
    }

    private function clean_color($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return $value;
        }

        return '';
    }

}