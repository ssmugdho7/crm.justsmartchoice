<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_343 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'options')) {
            return;
        }

        $this->smartChoiceUpdateOption('smart_choice_enterprise_current_version', '3.4.3');
        $this->smartChoiceUpdateOption('smart_choice_crm_portal_url', 'https://crm.justsmartchoice.com');

        $companyVat = get_option('company_vat');
        if ($companyVat !== '') {
            $formattedEin = $this->smartChoiceFormatEin($companyVat);
            if ($formattedEin !== $companyVat) {
                update_option('company_vat', $formattedEin);
            }
        }
    }

    private function smartChoiceUpdateOption($name, $value)
    {
        if (get_option($name) === '') {
            add_option($name, $value);
            return;
        }

        update_option($name, $value);
    }

    private function smartChoiceFormatEin($value)
    {
        $numbers = preg_replace('/\D+/', '', (string) $value);
        $numbers = substr($numbers, 0, 9);

        if (strlen($numbers) <= 3) {
            return $numbers;
        }

        if (strlen($numbers) <= 5) {
            return substr($numbers, 0, 3) . '-' . substr($numbers, 3);
        }

        return substr($numbers, 0, 3) . '-' . substr($numbers, 3, 2) . '-' . substr($numbers, 5);
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
