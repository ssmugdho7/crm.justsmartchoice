<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_405 extends CI_Migration
{
    public function up()
    {
        $defaults = [
            'sc_proposals/manage.php_col_number_visible' => '1',
            'sc_proposals/manage.php_col_number_width' => '12',
            'sc_proposals/manage.php_col_subject_visible' => '1',
            'sc_proposals/manage.php_col_subject_width' => '18',
            'sc_proposals/manage.php_col_to_visible' => '1',
            'sc_proposals/manage.php_col_to_width' => '15',
            'sc_proposals/manage.php_col_total_visible' => '1',
            'sc_proposals/manage.php_col_total_width' => '10',
            'sc_proposals/manage.php_col_date_visible' => '1',
            'sc_proposals/manage.php_col_date_width' => '10',
            'sc_proposals/manage.php_col_open_till_visible' => '1',
            'sc_proposals/manage.php_col_open_till_width' => '10',
            'sc_proposals/manage.php_col_project_visible' => '1',
            'sc_proposals/manage.php_col_project_width' => '14',
            'sc_proposals/manage.php_col_tags_visible' => '1',
            'sc_proposals/manage.php_col_tags_width' => '10',
            'sc_proposals/manage.php_col_date_created_visible' => '1',
            'sc_proposals/manage.php_col_date_created_width' => '10',
            'sc_proposals/manage.php_col_status_visible' => '1',
            'sc_proposals/manage.php_col_status_width' => '10',
            'sc_estimates/manage.php_col_number_visible' => '1',
            'sc_estimates/manage.php_col_number_width' => '10',
            'sc_estimates/manage.php_col_amount_visible' => '1',
            'sc_estimates/manage.php_col_amount_width' => '10',
            'sc_estimates/manage.php_col_tax_visible' => '1',
            'sc_estimates/manage.php_col_tax_width' => '8',
            'sc_estimates/manage.php_col_year_visible' => '1',
            'sc_estimates/manage.php_col_year_width' => '7',
            'sc_estimates/manage.php_col_client_visible' => '1',
            'sc_estimates/manage.php_col_client_width' => '15',
            'sc_estimates/manage.php_col_project_visible' => '1',
            'sc_estimates/manage.php_col_project_width' => '14',
            'sc_estimates/manage.php_col_tags_visible' => '1',
            'sc_estimates/manage.php_col_tags_width' => '10',
            'sc_estimates/manage.php_col_date_visible' => '1',
            'sc_estimates/manage.php_col_date_width' => '10',
            'sc_estimates/manage.php_col_expiry_visible' => '1',
            'sc_estimates/manage.php_col_expiry_width' => '10',
            'sc_estimates/manage.php_col_reference_visible' => '1',
            'sc_estimates/manage.php_col_reference_width' => '10',
            'sc_estimates/manage.php_col_status_visible' => '1',
            'sc_estimates/manage.php_col_status_width' => '10',
            'sc_invoices/manage.php_col_number_visible' => '1',
            'sc_invoices/manage.php_col_number_width' => '10',
            'sc_invoices/manage.php_col_amount_visible' => '1',
            'sc_invoices/manage.php_col_amount_width' => '10',
            'sc_invoices/manage.php_col_tax_visible' => '1',
            'sc_invoices/manage.php_col_tax_width' => '8',
            'sc_invoices/manage.php_col_year_visible' => '1',
            'sc_invoices/manage.php_col_year_width' => '7',
            'sc_invoices/manage.php_col_date_visible' => '1',
            'sc_invoices/manage.php_col_date_width' => '10',
            'sc_invoices/manage.php_col_client_visible' => '1',
            'sc_invoices/manage.php_col_client_width' => '15',
            'sc_invoices/manage.php_col_project_visible' => '1',
            'sc_invoices/manage.php_col_project_width' => '14',
            'sc_invoices/manage.php_col_tags_visible' => '1',
            'sc_invoices/manage.php_col_tags_width' => '10',
            'sc_invoices/manage.php_col_due_visible' => '1',
            'sc_invoices/manage.php_col_due_width' => '10',
            'sc_invoices/manage.php_col_status_visible' => '1',
            'sc_invoices/manage.php_col_status_width' => '10',
        ];
        foreach ($defaults as $key => $value) {
            if (get_option($key) === '') {
                add_option($key, $value);
            }
        }
        update_option('smart_choice_crm_build', '4.0.5');
        update_option('smart_choice_crm_current_version', '4.0.5');
    }

    public function down()
    {
        // Upgrade-only migration. Existing CRM data and table preferences are preserved.
    }
}
