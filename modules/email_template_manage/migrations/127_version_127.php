<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{

    public function up()
    {


        $CI = get_instance();


        $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_templates` 
                            MODIFY COLUMN `template_content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `template_name`;');


    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }


}
