<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enterprise_events
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public function dispatch($eventKey, array $payload = [], $aggregateType = null, $aggregateId = null, array $context = [])
    {
        $table = db_prefix() . 'sce_events';
        if (!$this->CI->db->table_exists($table)) { return false; }
        $correlation = !empty($context['correlation_id']) ? $context['correlation_id'] : bin2hex(random_bytes(16));
        $data = [
            'event_key' => substr((string)$eventKey, 0, 191),
            'aggregate_type' => $aggregateType ? substr((string)$aggregateType, 0, 100) : null,
            'aggregate_id' => $aggregateId !== null ? substr((string)$aggregateId, 0, 100) : null,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'staff_id' => get_staff_user_id() ?: null,
            'correlation_id' => $correlation,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->CI->db->insert($table, $data);
        hooks()->do_action('smart_choice_enterprise_event', array_merge($data, ['id' => $this->CI->db->insert_id()]));
        hooks()->do_action('smart_choice_enterprise_event_' . str_replace(['.', '-', ' '], '_', $eventKey), $data);
        return $correlation;
    }
}
