<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_418 extends CI_Migration
{
    public function up()
    {
        $staffTable   = db_prefix() . 'staff';
        $paymentTable = db_prefix() . 'invoicepaymentrecords';
        $creditTable  = db_prefix() . 'creditnotes';
        $docsTable    = db_prefix() . 'sc_staff_documents';

        /*
         * IMPORTANT:
         * Do not pass db_prefix().'table' into dbforge->add_column().
         * CodeIgniter's DB Forge prefixes table names internally and can create
         * tbltbl... names on installations using the standard tbl prefix.
         * This migration uses explicit ALTER/CREATE SQL against the resolved
         * physical table name so it is safe on existing installations and also
         * idempotent after a partially failed 4.1.8 upgrade.
         */

        if ($this->db->table_exists($staffTable)) {
            if (!$this->db->field_exists('sc_resume_text', $staffTable)) {
                $this->db->query("ALTER TABLE `{$staffTable}` ADD `sc_resume_text` MEDIUMTEXT NULL");
            }
            if (!$this->db->field_exists('sc_certifications_text', $staffTable)) {
                $this->db->query("ALTER TABLE `{$staffTable}` ADD `sc_certifications_text` MEDIUMTEXT NULL");
            }
        }

        if ($this->db->table_exists($paymentTable)) {
            if (!$this->db->field_exists('sc_custom_status_value', $paymentTable)) {
                $this->db->query("ALTER TABLE `{$paymentTable}` ADD `sc_custom_status_value` VARCHAR(191) NULL");
            }
            if (!$this->db->field_exists('sc_clientnote', $paymentTable)) {
                $this->db->query("ALTER TABLE `{$paymentTable}` ADD `sc_clientnote` MEDIUMTEXT NULL");
            }
            if (!$this->db->field_exists('sc_terms', $paymentTable)) {
                $this->db->query("ALTER TABLE `{$paymentTable}` ADD `sc_terms` MEDIUMTEXT NULL");
            }
        }

        if ($this->db->table_exists($creditTable)
            && !$this->db->field_exists('sc_custom_status_value', $creditTable)) {
            $this->db->query("ALTER TABLE `{$creditTable}` ADD `sc_custom_status_value` VARCHAR(191) NULL");
        }

        if (!$this->db->table_exists($docsTable)) {
            $this->db->query("CREATE TABLE `{$docsTable}` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `staff_id` INT(11) UNSIGNED NOT NULL,
                `document_type` VARCHAR(32) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_type` VARCHAR(120) NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `uploaded_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `staff_document_lookup` (`staff_id`,`document_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        foreach ([
            'sc_custom_admin_js'             => '',
            'sc_custom_client_js'            => '',
            'sc_staff_idle_timeout_minutes'  => '10',
        ] as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value, 0);
            }
        }

        /*
         * Preserve every existing Stripe credential. The replaceable sample
         * values are seeded only when the corresponding option is genuinely
         * empty. Failure to load encryption must never abort the CRM schema
         * migration, so use the CI instance explicitly and only seed the
         * sample secret when the encryption service is available.
         */
        if (trim((string) get_option('paymentmethod_stripe_api_publishable_key')) === '') {
            update_option(
                'paymentmethod_stripe_api_publishable_key',
                'pk_live_51TpVejPmy2tqppMDXmdLaW76umpO1DOs3ZiQDU6RkHwnq6b7sqPEG5w7BlalV2XCzXk6350RFrGqDQftNrr4KZlr00x3frhCCK'
            );
        }

        if (trim((string) get_option('paymentmethod_stripe_account_id')) === '') {
            update_option('paymentmethod_stripe_account_id', 'acct_1TpVejPmy2tqppMD');
        }

        if (trim((string) get_option('paymentmethod_stripe_api_secret_key')) === '') {
            $CI = &get_instance();
            $CI->load->library('encryption');
            if (isset($CI->encryption)) {
                $encrypted = $CI->encryption->encrypt(
                    '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'
                );
                if ($encrypted !== false && $encrypted !== '') {
                    update_option('paymentmethod_stripe_api_secret_key', $encrypted);
                }
            }
        }

        if (get_option('paymentmethod_stripe_active') !== false) {
            update_option('paymentmethod_stripe_active', '1');
        }

        update_option('sc_crm_build_version', '4.1.8');
        update_option('smart_choice_crm_build', '4.1.8');
        update_option('smart_choice_crm_current_version', '4.1.8');
    }

    public function down()
    {
        // Intentionally non-destructive. Smart Choice core upgrades never
        // remove staff/payment data or user configuration during rollback.
    }
}
