<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_111 extends App_module_migration {
    public function up() {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix().'smart_square_transactions')) { smart_square_activate(); }
        update_option('smart_square_schema_version', '1.2.0');
    }
    public function down() { update_option('smart_square_schema_version', '1.1.0'); }
}
