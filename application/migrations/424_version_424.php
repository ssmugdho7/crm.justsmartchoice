<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_424 extends CI_Migration
{
    public function up()
    {
        // UI cleanup only: preserve every custom field and its data, while
        // removing the unwanted display prefix introduced by translation work.
        $table = db_prefix() . 'customfields';
        if ($this->db->table_exists($table) && $this->db->field_exists('name', $table)) {
            $this->db->query("UPDATE `{$table}` SET `name`=TRIM(SUBSTRING(`name`, 13)) WHERE LOWER(`name`) LIKE 'cf translate %'");
            $this->db->query("UPDATE `{$table}` SET `name`=TRIM(SUBSTRING(`name`, 14)) WHERE LOWER(`name`) LIKE 'cf_translate %'");
        }
        update_option('sc_crm_build_version', '4.2.4');
        update_option('smart_choice_crm_build', '4.2.4');
        update_option('smart_choice_crm_current_version', '4.2.4');
    }

    public function down()
    {
        // Non-destructive by design.
    }
}
