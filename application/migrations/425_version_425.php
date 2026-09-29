<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_425 extends CI_Migration
{
    public function up()
    {
        $cf = db_prefix() . 'customfields';
        if ($this->db->table_exists($cf) && $this->db->field_exists('name', $cf)) {
            // MySQL 5.7 compatible prefix cleanup. Stored field IDs/values/slugs stay intact.
            $this->db->query("UPDATE `{$cf}` SET `name`=TRIM(SUBSTRING(`name`,14)) WHERE LOWER(`name`) LIKE 'cf translate %'");
            $this->db->query("UPDATE `{$cf}` SET `name`=TRIM(SUBSTRING(`name`,14)) WHERE LOWER(`name`) LIKE 'cf_translate %'");
            $this->db->query("UPDATE `{$cf}` SET `name`=TRIM(SUBSTRING(`name`,11)) WHERE LOWER(`name`) LIKE 'translate %'");
        }

        $estimates = db_prefix() . 'estimates';
        if ($this->db->table_exists($estimates)) {
            $columns = [
                'sc_customer_name'   => 'VARCHAR(191) NULL',
                'sc_customer_email'  => 'VARCHAR(191) NULL',
                'sc_customer_phone'  => 'VARCHAR(80) NULL',
                'sc_project_address' => 'VARCHAR(255) NULL',
            ];
            foreach ($columns as $name => $sqlType) {
                if (!$this->db->field_exists($name, $estimates)) {
                    // Use explicit physical table SQL to avoid CI DB Forge double-prefixing.
                    $this->db->query("ALTER TABLE `{$estimates}` ADD `{$name}` {$sqlType}");
                }
            }
        }

        update_option('sc_crm_build_version', '4.2.5');
        update_option('smart_choice_crm_build', '4.2.5');
        update_option('smart_choice_crm_current_version', '4.2.5');
    }

    public function down()
    {
        // Non-destructive by policy: do not remove user data or restored field names.
    }
}
