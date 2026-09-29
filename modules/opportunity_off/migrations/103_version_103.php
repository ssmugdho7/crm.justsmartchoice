<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $opportunity_send_email = [
            'type' => 'opportunity',
            'slug' => 'opportunity_send_email',
            'name' => 'Opportunity Send Email',
            'subject' => '{subject}',
            'message' => '{message}'
        ];
        create_email_template($opportunity_send_email['subject'], $opportunity_send_email['message'], $opportunity_send_email['type'], $opportunity_send_email['name'], $opportunity_send_email['slug']);

        $CI = &get_instance();
        // check if column exists in table
        if (!$CI->db->field_exists('rel_type', db_prefix() . 'opportunity')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "opportunity` ADD `rel_type` VARCHAR(30) NULL DEFAULT NULL AFTER `client_id`, ADD `rel_id` INT(11) NULL DEFAULT NULL AFTER `rel_type`;");
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
