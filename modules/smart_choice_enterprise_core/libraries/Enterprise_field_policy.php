<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enterprise_field_policy
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public function allows($resourceType, $fieldKey, $action = 'view', $staffId = null, $roleId = null)
    {
        $staffId = $staffId ?: get_staff_user_id();
        if (!$roleId && $staffId) {
            $staff = $this->CI->db->select('role')->where('staffid',$staffId)->get(db_prefix().'staff')->row_array();
            $roleId = $staff ? (int)$staff['role'] : 0;
        }
        $table = db_prefix().'sce_field_permissions';
        if (!$this->CI->db->table_exists($table)) { return true; }
        $row = $this->CI->db->where(['resource_type'=>$resourceType,'field_key'=>$fieldKey,'staff_id'=>(int)$staffId])->get($table)->row_array();
        if (!$row) { $row = $this->CI->db->where(['resource_type'=>$resourceType,'field_key'=>$fieldKey,'staff_id'=>0,'role_id'=>(int)$roleId])->get($table)->row_array(); }
        if (!$row) { return true; }
        $column = $action === 'edit' ? 'can_edit' : ($action === 'export' ? 'can_export' : 'can_view');
        return (bool)$row[$column];
    }
}
