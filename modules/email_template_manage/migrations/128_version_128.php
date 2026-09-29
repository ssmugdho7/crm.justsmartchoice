<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_128 extends App_module_migration
{

    public function up()
    {


        $CI = get_instance();


        if ( !$CI->db->field_exists('client_tags',db_prefix().'email_template_manage_timer') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_timer` 
                                ADD COLUMN `client_tags` varchar(150) NULL AFTER `not_clients`,
                                ADD COLUMN `lead_tags` varchar(150) NULL AFTER `client_tags`;');

        }



        if ( !$CI->db->field_exists('send_mail_period',db_prefix().'email_template_manage_timer') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_timer` 
                                ADD COLUMN `send_mail_period` int NULL AFTER `lead_tags`,
                                ADD COLUMN `send_mail_interval` int NULL AFTER `send_mail_period`;');

        }

        if ( !$CI->db->field_exists('period_sent_count',db_prefix().'email_template_manage_timer') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_timer` 
                                ADD COLUMN `period_sent_count` int NULL DEFAULT 0 AFTER `send_mail_interval`,
                                ADD COLUMN `period_batch_end_time` datetime NULL AFTER `period_sent_count`;');

        }

    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }


}
