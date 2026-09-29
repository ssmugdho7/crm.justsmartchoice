<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enterprise_features
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public function enabled($featureKey, $staffId = null, $roleId = null)
    {
        $table = db_prefix() . 'sce_feature_flags';
        if (!$this->CI->db->table_exists($table)) { return false; }
        if ($staffId) {
            $row = $this->CI->db->where(['feature_key'=>$featureKey,'scope_type'=>'staff','scope_id'=>(string)$staffId])->get($table)->row_array();
            if ($row) { return (bool)$row['is_enabled']; }
        }
        if ($roleId) {
            $row = $this->CI->db->where(['feature_key'=>$featureKey,'scope_type'=>'role','scope_id'=>(string)$roleId])->get($table)->row_array();
            if ($row) { return (bool)$row['is_enabled']; }
        }
        $row = $this->CI->db->where('feature_key',$featureKey)->where('scope_type','global')->where('scope_id IS NULL', null, false)->get($table)->row_array();
        return $row ? (bool)$row['is_enabled'] : false;
    }
}
