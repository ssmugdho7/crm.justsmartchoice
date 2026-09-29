<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enterprise_audit
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public function record($action, $entityType = null, $entityId = null, array $before = [], array $after = [], array $metadata = [])
    {
        $table = db_prefix() . 'sce_audit_log';
        if (!$this->CI->db->table_exists($table)) { return false; }
        return $this->CI->db->insert($table, [
            'action' => substr((string)$action, 0, 100),
            'entity_type' => $entityType ? substr((string)$entityType, 0, 100) : null,
            'entity_id' => $entityId !== null ? substr((string)$entityId, 0, 100) : null,
            'staff_id' => get_staff_user_id() ?: null,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => substr((string)$this->CI->input->user_agent(), 0, 500),
            'before_data' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
            'after_data' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
            'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
