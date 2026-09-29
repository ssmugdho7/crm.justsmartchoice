<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Migration_Version_108 extends App_module_migration

{

     public function up()

     {

         $CI = &get_instance();


         if( !$CI->db->field_exists('es_otp_code', db_prefix() .'contacts') )
         {

             $CI->db->query('ALTER TABLE `'.db_prefix().'contacts`
                                ADD COLUMN `es_otp_code` varchar(20) NULL AFTER `profile_image`;');

         }

     }

}

