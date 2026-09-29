<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_188 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'recruitment_portal_page_bg' => '#F5F7F6',
            'recruitment_portal_card_bg' => '#FFFFFF',
            'recruitment_portal_text_color' => '#263238',
            'recruitment_portal_muted_color' => '#607D76',
            'recruitment_portal_border_color' => '#D9E2DF',
            'recruitment_portal_button_color' => '#F28C28',
            'recruitment_portal_button_text' => '#FFFFFF',
            'recruitment_portal_badge_bg' => '#EEF3F1',
            'recruitment_portal_badge_text' => '#0E6F5B',
            'recruitment_test_construction_enabled' => '1',
            'recruitment_test_behavior_enabled' => '1',
            'recruitment_test_safety_enabled' => '1',
            'recruitment_test_reliability_enabled' => '1',
            'recruitment_tests_required' => '1',
        ];
        foreach ($defaults as $key => $value) { if (get_option($key) === false) { add_option($key, $value); } }
        $table = db_prefix().'rec_candidate_assessments';
        $CI = &get_instance();
        if (!$CI->db->table_exists($table)) {
            $CI->db->query('CREATE TABLE `'.$table.'` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`candidate_id` INT NOT NULL,`assessment_type` VARCHAR(40) NOT NULL,`answers` LONGTEXT NULL,`score` DECIMAL(5,2) NOT NULL DEFAULT 0,`summary` TEXT NULL,`ip_address` VARCHAR(64) NULL,`user_agent` TEXT NULL,`submitted_at` DATETIME NULL,PRIMARY KEY (`id`),UNIQUE KEY `candidate_type` (`candidate_id`,`assessment_type`),KEY `candidate_id` (`candidate_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        }
    }
    public function down() {}
}
