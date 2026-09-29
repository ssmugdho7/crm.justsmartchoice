<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_393 extends App_migration
{
    public function up()
    {
        if (get_option('sc_proposal_custom_statuses') === false) {
            add_option('sc_proposal_custom_statuses', '[]');
        }
        update_option('smart_choice_crm_build', '3.9.3');
        update_option('smart_choice_crm_current_version', '3.9.3');
    }
}
