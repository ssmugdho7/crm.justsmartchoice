<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_421 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'invoices';
        $columns = [
            'sc_customer_name'    => "VARCHAR(191) NULL",
            'sc_customer_email'   => "VARCHAR(191) NULL",
            'sc_customer_phone'   => "VARCHAR(64) NULL",
            'sc_project_address'  => "VARCHAR(255) NULL",
        ];

        foreach ($columns as $name => $definition) {
            $exists = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $this->db->escape($name))->num_rows() > 0;
            if (!$exists) {
                $this->db->query("ALTER TABLE `{$table}` ADD `{$name}` {$definition}");
            }
        }

        update_option('sc_crm_build_version', '4.2.1');
        update_option('smart_choice_crm_build', '4.2.1');
        update_option('smart_choice_crm_current_version', '4.2.1');
    }

    public function down()
    {
        // Non-destructive rollback. Preserve customer snapshots already saved on invoices.
    }
}
