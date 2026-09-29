<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI=&get_instance();$CI->load->helper('solar_pro/solar_pro');
        if(!$CI->db->table_exists(db_prefix().'solar_proposal_templates')){
            $CI->db->query("CREATE TABLE `".db_prefix()."solar_proposal_templates` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(191) NOT NULL,`hero_title` varchar(255) NOT NULL,`intro_html` longtext DEFAULT NULL,`closing_html` longtext DEFAULT NULL,`primary_color` varchar(20) NOT NULL DEFAULT '#0E6F5B',`secondary_color` varchar(20) NOT NULL DEFAULT '#3598DB',`accent_color` varchar(20) NOT NULL DEFAULT '#F28C28',`active` tinyint(1) NOT NULL DEFAULT 1,`is_default` tinyint(1) NOT NULL DEFAULT 0,`created_by` int(11) NOT NULL DEFAULT 0,`created_at` datetime NOT NULL,`updated_at` datetime DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        $adds=[db_prefix().'solar_proposals'=>['template_id'=>'INT(11) NULL AFTER `analysis_id`'],db_prefix().'solar_equipment'=>['spec_file'=>'VARCHAR(255) NULL AFTER `specs_json`','spec_original_name'=>'VARCHAR(255) NULL AFTER `spec_file`','spec_mime'=>'VARCHAR(120) NULL AFTER `spec_original_name`','spec_preview_json'=>'LONGTEXT NULL AFTER `spec_mime`']];
        foreach($adds as $table=>$cols){foreach($cols as $col=>$def){if(!$CI->db->field_exists($col,$table)){$CI->db->query("ALTER TABLE `{$table}` ADD `{$col}` {$def}");}}}
        solar_pro_seed_proposal_templates($CI);
        solar_pro_install_proposal_assets();
        $default=$CI->db->where('active',1)->where('is_default',1)->get(db_prefix().'solar_proposal_templates')->row();if($default){$CI->db->where('template_id IS NULL',null,false)->update(db_prefix().'solar_proposals',['template_id'=>$default->id]);}
    }
}
