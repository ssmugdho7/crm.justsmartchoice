<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_core_manager_model extends App_Model
{
    public function write_audit($action, array $details = [])
    {
        $table = db_prefix() . 'sc_core_audit';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        return $this->db->insert($table, [
            'staff_id'  => get_staff_user_id(),
            'action'    => (string) $action,
            'details'   => json_encode($details),
            'created_at'=> date('Y-m-d H:i:s'),
        ]);
    }

    public function latest_audit_entries($limit = 10)
    {
        $table = db_prefix() . 'sc_core_audit';
        if (!$this->db->table_exists($table)) {
            return [];
        }

        return $this->db->order_by('id', 'DESC')->limit((int) $limit)->get($table)->result_array();
    }
}
