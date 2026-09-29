<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_chat_model extends App_Model
{
    public function get_conversations(array $filters = []): array
    {
        $table = db_prefix() . 'usi_ai_conversations';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['source_area'])) {
            $this->db->where('source_area', $filters['source_area']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('conversation_title', $filters['search']);
            $this->db->or_like('summary', $filters['search']);
            $this->db->group_end();
        }
        $this->db->order_by('updated_at', 'DESC');
        return $this->db->get($table)->result_array();
    }

    public function get_conversation(int $id): ?array
    {
        $table = db_prefix() . 'usi_ai_conversations';
        if (!$this->db->table_exists($table)) {
            return null;
        }
        $row = $this->db->where('id', $id)->get($table)->row_array();
        return $row ?: null;
    }

    public function get_messages(int $conversationId): array
    {
        $table = db_prefix() . 'usi_ai_conversation_messages';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->where('conversation_id', $conversationId)->order_by('created_at', 'ASC')->get($table)->result_array();
    }

    public function get_context(int $conversationId): array
    {
        $table = db_prefix() . 'usi_ai_conversation_context';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->where('conversation_id', $conversationId)->order_by('created_at', 'DESC')->get($table)->result_array();
    }

    public function get_recent_messages(int $limit = 20): array
    {
        $table = db_prefix() . 'usi_ai_conversation_messages';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->order_by('created_at', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function save_conversation(array $data, int $id = 0): int
    {
        $now = date('Y-m-d H:i:s');
        $staffId = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $payload = [
            'conversation_title' => trim((string)($data['conversation_title'] ?? 'Sammy AI Conversation')),
            'conversation_type' => trim((string)($data['conversation_type'] ?? 'general')),
            'source_area' => trim((string)($data['source_area'] ?? 'manual')),
            'source_id' => (int)($data['source_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'estimate_id' => (int)($data['estimate_id'] ?? 0),
            'status' => trim((string)($data['status'] ?? 'open')),
            'summary' => trim((string)($data['summary'] ?? '')),
            'updated_at' => $now,
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_conversations', $payload);
            return $id;
        }
        $payload['created_by'] = $staffId;
        $payload['created_at'] = $now;
        $this->db->insert(db_prefix() . 'usi_ai_conversations', $payload);
        return (int)$this->db->insert_id();
    }

    public function save_message(array $data, int $conversationId = 0): int
    {
        if ($conversationId <= 0) {
            $conversationId = $this->save_conversation([
                'conversation_title' => 'Voice / Chat Command - ' . date('m/d/Y h:i A'),
                'conversation_type' => 'command_chat',
                'source_area' => 'voice_assistant',
                'status' => 'open',
            ], 0);
        }
        $now = date('Y-m-d H:i:s');
        $staffId = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $messageText = trim((string)($data['message_text'] ?? ''));
        $messageRole = trim((string)($data['message_role'] ?? 'user'));
        if ($messageText !== '') {
            $this->db->insert(db_prefix() . 'usi_ai_conversation_messages', [
                'conversation_id' => $conversationId,
                'message_role' => $messageRole,
                'message_source' => trim((string)($data['message_source'] ?? 'typed')),
                'message_text' => $messageText,
                'intent' => trim((string)($data['intent'] ?? 'crm_assistant')),
                'action_status' => trim((string)($data['action_status'] ?? 'draft')),
                'created_by' => $staffId,
                'created_at' => $now,
            ]);
            $this->db->where('id', $conversationId)->update(db_prefix() . 'usi_ai_conversations', ['updated_at' => $now]);
        }
        return $conversationId;
    }

    public function build_from_memory(array $data): int
    {
        $query = trim((string)($data['memory_query'] ?? ''));
        $id = $this->save_conversation([
            'conversation_title' => $query !== '' ? 'Memory Search: ' . $query : 'Memory Search Conversation',
            'conversation_type' => 'memory_context',
            'source_area' => 'ai_memory_engine',
            'status' => 'open',
            'summary' => 'Conversation started from AI Memory Engine context.',
        ], 0);
        if ($query !== '') {
            $this->save_message([
                'conversation_id' => $id,
                'message_role' => 'user',
                'message_source' => 'memory_query',
                'message_text' => $query,
                'intent' => 'memory_search',
            ], $id);
        }
        $memoryTable = db_prefix() . 'usi_ai_memory_items';
        if ($this->db->table_exists($memoryTable) && $query !== '') {
            $rows = $this->db->like('content_text', $query)->or_like('memory_title', $query)->limit(5)->get($memoryTable)->result_array();
            foreach ($rows as $row) {
                $this->db->insert(db_prefix() . 'usi_ai_conversation_context', [
                    'conversation_id' => $id,
                    'context_type' => 'memory_item',
                    'source_area' => (string)($row['source_area'] ?? 'memory'),
                    'source_id' => (int)($row['source_id'] ?? 0),
                    'context_title' => (string)($row['memory_title'] ?? 'Memory Item'),
                    'context_summary' => substr((string)($row['content_text'] ?? ''), 0, 1000),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        return $id;
    }

    public function set_status(int $id, string $status): bool
    {
        return $this->db->where('id', $id)->update(db_prefix() . 'usi_ai_conversations', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete_conversations(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }
        $this->db->where_in('conversation_id', $ids)->delete(db_prefix() . 'usi_ai_conversation_context');
        $this->db->where_in('conversation_id', $ids)->delete(db_prefix() . 'usi_ai_conversation_messages');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'usi_ai_conversations');
        return $this->db->affected_rows();
    }
}
