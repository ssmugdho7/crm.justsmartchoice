<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $articles = db_prefix() . 'wiki_articles';
        if (!$CI->db->table_exists($articles)) { return; }
        $slugs = [
            'smart-choice-service-experience',
            'smart-choice-client-portal-guide',
            'smart-choice-support-system',
            'smart-choice-document-review',
            'smart-choice-company-vision',
        ];
        foreach ($slugs as $slug) {
            $CI->db->where('slug', $slug);
            if ($CI->db->field_exists('audience', $articles)) { $CI->db->set('audience', 'customer_portal'); }
            if ($CI->db->field_exists('content_kind', $articles)) { $CI->db->set('content_kind', 'article'); }
            $CI->db->set('is_publish', 1)->update($articles);
        }
    }
    public function down() {}
}
