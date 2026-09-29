<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_179 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $defaults = [
            'smart_choice_recruitment_portal_title' => 'Build Your Career With Smart Choice Contractors USA',
            'smart_choice_recruitment_portal_success_message' => 'Thank you for submitting your resume/application. Smart Choice Contractors USA will review your information and contact you if your experience matches the position.',
            'smart_choice_recruitment_notify_all_staff' => '1',
            'smart_choice_recruitment_return_website_url' => 'https://justsmartchoice.com',
            'smart_choice_recruitment_return_portal_url' => site_url('recruitment/recruitment_portal'),
        ];

        foreach ($defaults as $name => $value) {
            if (function_exists('add_option')) {
                add_option($name, $value);
            }
        }

        $this->create_sample_position($CI);
    }

    private function create_sample_position($CI)
    {
        $table = db_prefix() . 'rec_job_position';
        if (!$CI->db->table_exists($table)) {
            return;
        }

        $fields = $CI->db->list_fields($table);
        if (!in_array('position_name', $fields, true)) {
            return;
        }

        $exists = $CI->db->where('position_name', 'Smart Choice Construction Project Manager')->get($table)->row();
        if ($exists) {
            return;
        }

        $description = 'Sample Smart Choice role for construction project management, customer communication, field coordination, CRM documentation, jobsite standards, scheduling, and safety-focused customer service.';

        $insert = [
            'position_name' => 'Smart Choice Construction Project Manager',
        ];

        $optionalMap = [
            'position_description' => $description,
            'description' => $description,
            'job_description' => $description,
            'status' => 1,
            'active' => 1,
            'datecreated' => date('Y-m-d H:i:s'),
        ];

        foreach ($optionalMap as $column => $value) {
            if (in_array($column, $fields, true)) {
                $insert[$column] = $value;
            }
        }

        // Never force legacy/new schema-specific columns such as job_p_id.
        $insert = array_intersect_key($insert, array_flip($fields));

        if (!empty($insert)) {
            $CI->db->insert($table, $insert);
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
