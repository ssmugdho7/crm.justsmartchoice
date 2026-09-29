<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_voice_model extends App_Model
{
    public function get_sessions(array $filters = []): array
    {
        $table = db_prefix() . 'usi_ai_voice_sessions';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        if (!empty($filters['listening_status'])) {
            $this->db->where('listening_status', $filters['listening_status']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('session_title', $filters['search']);
            $this->db->or_like('last_command_text', $filters['search']);
            $this->db->group_end();
        }
        return $this->db->order_by('updated_at', 'DESC')->limit(100)->get($table)->result_array();
    }

    public function get_routes(): array
    {
        $table = db_prefix() . 'usi_ai_voice_command_routes';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->order_by('sort_order', 'ASC')->get($table)->result_array();
    }

    public function get_recent_transcripts(int $limit = 20): array
    {
        $table = db_prefix() . 'usi_ai_voice_transcripts';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->order_by('created_at', 'DESC')->limit($limit)->get($table)->result_array();
    }

    public function start_session(array $data = []): int
    {
        $now = date('Y-m-d H:i:s');
        $staffId = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $payload = [
            'session_title' => trim((string)($data['session_title'] ?? 'Voice Session - ' . date('m/d/Y h:i A'))),
            'session_mode' => trim((string)($data['session_mode'] ?? 'continuous')),
            'language_code' => trim((string)($data['language_code'] ?? 'en-US')),
            'listening_status' => 'listening',
            'source_area' => trim((string)($data['source_area'] ?? 'voice_orchestrator')),
            'source_id' => (int)($data['source_id'] ?? 0),
            'customer_id' => (int)($data['customer_id'] ?? 0),
            'lead_id' => (int)($data['lead_id'] ?? 0),
            'project_id' => (int)($data['project_id'] ?? 0),
            'estimate_id' => (int)($data['estimate_id'] ?? 0),
            'created_by' => $staffId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $this->db->insert(db_prefix() . 'usi_ai_voice_sessions', $payload);
        return (int)$this->db->insert_id();
    }

    public function stop_session(int $sessionId): bool
    {
        if ($sessionId <= 0) {
            return false;
        }
        return $this->db->where('id', $sessionId)->update(db_prefix() . 'usi_ai_voice_sessions', [
            'listening_status' => 'stopped',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_transcript(int $sessionId, string $text, float $confidence = 0.00): array
    {
        $text = trim($text);
        if ($sessionId <= 0) {
            $sessionId = $this->start_session([]);
        }
        $intent = $this->detect_intent($text);
        $route = $this->find_route($intent, $text);
        $now = date('Y-m-d H:i:s');
        $staffId = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $this->db->insert(db_prefix() . 'usi_ai_voice_transcripts', [
            'session_id' => $sessionId,
            'transcript_text' => $text,
            'clean_command_text' => $text,
            'detected_intent' => $intent,
            'confidence_score' => $confidence,
            'route_status' => $route ? 'ready' : 'unmatched',
            'route_target' => $route ? $route['target_action'] : null,
            'route_result' => $route ? 'Matched route: ' . $route['route_name'] : 'No matching route found.',
            'created_by' => $staffId,
            'created_at' => $now,
        ]);
        $transcriptId = (int)$this->db->insert_id();
        $this->db->set('command_count', 'command_count+1', false)
            ->where('id', $sessionId)
            ->update(db_prefix() . 'usi_ai_voice_sessions', [
                'last_command_text' => $text,
                'last_intent' => $intent,
                'updated_at' => $now,
            ]);
        return [
            'success' => true,
            'session_id' => $sessionId,
            'transcript_id' => $transcriptId,
            'intent' => $intent,
            'route' => $route,
            'message' => $route ? 'Voice command matched and ready.' : 'Voice command saved but no route matched yet.',
        ];
    }

    public function execute_transcript(int $transcriptId): array
    {
        $table = db_prefix() . 'usi_ai_voice_transcripts';
        $row = $this->db->where('id', $transcriptId)->get($table)->row_array();
        if (!$row) {
            return ['success' => false, 'message' => 'Transcript was not found.'];
        }
        $target = (string)($row['route_target'] ?? '');
        $text = (string)($row['clean_command_text'] ?? $row['transcript_text']);
        $url = admin_url('usi_smartchoice_seo/voice_assistant');
        if ($target === 'run_ai_estimate') {
            $url = admin_url('usi_smartchoice_seo/ai_estimates');
        } elseif ($target === 'open_lead_form') {
            $url = admin_url('leads');
        } elseif ($target === 'open_task_form') {
            $url = admin_url('tasks');
        } elseif ($target === 'memory_engine_search') {
            $url = admin_url('usi_smartchoice_seo/memory_engine?search=' . urlencode($text));
        } elseif ($target === 'crm_search_customer') {
            $url = admin_url('clients');
        }
        $this->db->where('id', $transcriptId)->update($table, [
            'route_status' => 'executed',
            'route_result' => 'Executed target: ' . $target,
        ]);
        return ['success' => true, 'message' => 'Command routed.', 'redirect_url' => $url];
    }

    private function detect_intent(string $text): string
    {
        $clean = strtolower($text);
        if (strpos($clean, 'estimate') !== false || strpos($clean, 'precio') !== false || strpos($clean, 'price') !== false) {
            return strpos($clean, 'create') !== false || strpos($clean, 'make') !== false ? 'create_estimate' : 'run_estimate';
        }
        if (strpos($clean, 'lead') !== false || strpos($clean, 'cliente potencial') !== false) {
            return 'create_lead';
        }
        if (strpos($clean, 'task') !== false || strpos($clean, 'tarea') !== false) {
            return 'create_task';
        }
        if (strpos($clean, 'memory') !== false || strpos($clean, 'historial') !== false) {
            return 'search_memory';
        }
        if (strpos($clean, 'customer') !== false || strpos($clean, 'cliente') !== false) {
            return 'open_customer';
        }
        return 'unknown';
    }

    private function find_route(string $intent, string $text): ?array
    {
        $table = db_prefix() . 'usi_ai_voice_command_routes';
        if (!$this->db->table_exists($table)) {
            return null;
        }
        $route = $this->db->where('intent_key', $intent)->where('is_active', 1)->get($table)->row_array();
        if ($route) {
            return $route;
        }
        $routes = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get($table)->result_array();
        $clean = strtolower($text);
        foreach ($routes as $candidate) {
            if ($candidate['trigger_phrase'] !== '' && strpos($clean, strtolower($candidate['trigger_phrase'])) !== false) {
                return $candidate;
            }
        }
        return null;
    }
}
