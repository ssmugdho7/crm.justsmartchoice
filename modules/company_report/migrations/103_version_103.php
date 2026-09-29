<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{

    public function up()
    {


        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'company_report_module_data_orders'))
        {
            $CI->db->query("CREATE TABLE `".db_prefix()."company_report_module_data_orders` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `report_type` varchar(100) DEFAULT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;"
            );
        }


    }

}
