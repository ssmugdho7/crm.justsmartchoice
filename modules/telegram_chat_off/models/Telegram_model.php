<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Telegram_model extends App_Model
{
    public function add($data)
    {
        $this->db->insert(db_prefix() . 'telegram_info', $data);
        return $this->db->insert_id();
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'telegram_info', $data);
        return $this->db->affected_rows();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('user_id', $id);
            return $this->db->get(db_prefix() . 'telegram_info')->row();
        }

        return $this->db->get(db_prefix() . 'telegram_info')->result_array();
    }

    public function get_admin_id()
    {
        return $this->db->get(db_prefix() . 'telegram_info')->row();
    }

    public function log_message($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return false;
        }

        $insert = [
            'staff_id'             => isset($data['staff_id']) ? $data['staff_id'] : null,
            'chat_id'              => isset($data['chat_id']) ? $data['chat_id'] : null,
            'message_direction'    => isset($data['message_direction']) ? $data['message_direction'] : 'outgoing',
            'module'               => isset($data['module']) ? $data['module'] : null,
            'action'               => isset($data['action']) ? $data['action'] : null,
            'record_id'            => isset($data['record_id']) ? $data['record_id'] : null,
            'related_id'           => isset($data['related_id']) ? $data['related_id'] : null,
            'telegram_message_id'  => isset($data['telegram_message_id']) ? $data['telegram_message_id'] : null,
            'sender_name'          => isset($data['sender_name']) ? $data['sender_name'] : null,
            'message_text'         => isset($data['message_text']) ? $data['message_text'] : null,
            'raw_payload'          => isset($data['raw_payload']) ? $data['raw_payload'] : null,
            'is_read'              => isset($data['is_read']) ? (int) $data['is_read'] : 0,
            'created_at'           => isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'telegram_chat_messages', $insert);
        return $this->db->insert_id();
    }

    public function get_messages($filters = [], $limit = 100, $offset = 0)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return [];
        }

        if (!empty($filters['q'])) {
            $this->db->group_start();
            $this->db->like('message_text', $filters['q']);
            $this->db->or_like('module', $filters['q']);
            $this->db->or_like('action', $filters['q']);
            $this->db->or_like('sender_name', $filters['q']);
            $this->db->group_end();
        }

        if (!empty($filters['module'])) {
            $this->db->where('module', $filters['module']);
        }

        if (!empty($filters['direction'])) {
            $this->db->where('message_direction', $filters['direction']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $this->db->order_by('id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);

        return $this->db->get(db_prefix() . 'telegram_chat_messages')->result_array();
    }

    public function count_messages($filters = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return 0;
        }

        if (!empty($filters['q'])) {
            $this->db->group_start();
            $this->db->like('message_text', $filters['q']);
            $this->db->or_like('module', $filters['q']);
            $this->db->or_like('action', $filters['q']);
            $this->db->or_like('sender_name', $filters['q']);
            $this->db->group_end();
        }

        if (!empty($filters['module'])) {
            $this->db->where('module', $filters['module']);
        }

        if (!empty($filters['direction'])) {
            $this->db->where('message_direction', $filters['direction']);
        }

        return $this->db->count_all_results(db_prefix() . 'telegram_chat_messages');
    }

    public function delete_messages($ids)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return false;
        }

        $ids = array_filter(array_map('intval', (array) $ids));
        if (empty($ids)) {
            return false;
        }

        $this->db->where_in('id', $ids);
        return $this->db->delete(db_prefix() . 'telegram_chat_messages');
    }

    public function get_setting($name, $default = '')
    {
        $value = get_option($name);
        return $value === '' ? $default : $value;
    }
}
