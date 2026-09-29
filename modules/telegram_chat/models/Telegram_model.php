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
        $this->db->where('id', $id)->update(db_prefix() . 'telegram_info', $data);
        return $this->db->affected_rows();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('user_id', $id)->get(db_prefix() . 'telegram_info')->row();
        }
        return $this->db->get(db_prefix() . 'telegram_info')->result_array();
    }

    public function get_admin_id()
    {
        return $this->db->order_by('id', 'ASC')->get(db_prefix() . 'telegram_info')->row();
    }

    public function log_message($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return false;
        }

        $insert = [
            'staff_id' => $data['staff_id'] ?? null,
            'chat_id' => $data['chat_id'] ?? null,
            'message_direction' => $data['message_direction'] ?? 'outgoing',
            'module' => $data['module'] ?? null,
            'action' => $data['action'] ?? null,
            'record_id' => $data['record_id'] ?? null,
            'related_id' => $data['related_id'] ?? null,
            'telegram_message_id' => $data['telegram_message_id'] ?? null,
            'sender_name' => $data['sender_name'] ?? null,
            'message_text' => $data['message_text'] ?? null,
            'raw_payload' => $data['raw_payload'] ?? null,
            'is_read' => isset($data['is_read']) ? (int) $data['is_read'] : 0,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'telegram_chat_messages', $insert);
        return $this->db->insert_id();
    }

    public function get_messages($filters = [], $limit = 200, $offset = 0)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return [];
        }

        if (!empty($filters['q'])) {
            $this->db->group_start()
                ->like('message_text', $filters['q'])
                ->or_like('module', $filters['q'])
                ->or_like('action', $filters['q'])
                ->or_like('sender_name', $filters['q'])
                ->group_end();
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

        return $this->db->order_by('id', 'DESC')->limit((int) $limit, (int) $offset)
            ->get(db_prefix() . 'telegram_chat_messages')->result_array();
    }

    public function count_messages($filters = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return 0;
        }
        if (!empty($filters['q'])) {
            $this->db->group_start()
                ->like('message_text', $filters['q'])
                ->or_like('module', $filters['q'])
                ->or_like('action', $filters['q'])
                ->or_like('sender_name', $filters['q'])
                ->group_end();
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
        $ids = array_values(array_filter(array_map('intval', (array) $ids)));
        if (!$ids || !$this->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
            return false;
        }
        return $this->db->where_in('id', $ids)->delete(db_prefix() . 'telegram_chat_messages');
    }

    public function get_appointment_tracking($appointmentId)
    {
        if (!$this->db->table_exists(db_prefix() . 'telegram_appointment_tracking')) {
            return null;
        }
        return $this->db->where('appointment_id', (int) $appointmentId)
            ->get(db_prefix() . 'telegram_appointment_tracking')->row();
    }

    public function save_appointment_tracking($appointmentId, $hash, $status)
    {
        $table = db_prefix() . 'telegram_appointment_tracking';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        $data = [
            'appointment_id' => (int) $appointmentId,
            'payload_hash' => $hash,
            'last_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $existing = $this->db->where('appointment_id', (int) $appointmentId)->get($table)->row();
        if ($existing) {
            return $this->db->where('id', $existing->id)->update($table, $data);
        }
        return $this->db->insert($table, $data);
    }

    public function get_due_scheduled_messages()
    {
        $table = db_prefix() . 'telegram_scheduled_messages';
        if (!$this->db->table_exists($table)) {
            return [];
        }

        return $this->db->where('status', 'pending')
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->order_by('scheduled_at', 'ASC')
            ->limit(50)->get($table)->result_array();
    }

    public function mark_scheduled_message($id, $sent)
    {
        return $this->db->where('id', (int) $id)->update(db_prefix() . 'telegram_scheduled_messages', [
            'status' => $sent ? 'sent' : 'failed',
            'sent_at' => $sent ? date('Y-m-d H:i:s') : null,
        ]);
    }
}
