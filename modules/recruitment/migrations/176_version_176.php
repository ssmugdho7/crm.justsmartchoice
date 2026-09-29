<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_176 extends App_module_migration
{
    public function up()
    {
        // Safe rescue migration.
        // Older Recruitment installations do not include job_p_id in rec_job_position.
        // This migration intentionally avoids inserting job position rows so upgrade cannot fail.
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
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
