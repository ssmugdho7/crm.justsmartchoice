<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_174 extends App_module_migration
{
    public function up()
    {
        add_option('recruitment_portal_logo_url', 'https://crm.justsmartchoice.com/media/Logos%20and%20Banners/Smart_Choice_Logo.png?_t=1760693505', 1);
        add_option('recruitment_portal_footer_text', '© ' . date('Y') . ' Smart Choice Contractors USA. Done Right Through Professional Service.', 1);

        return true;
    }

    public function down()
    {
        return true;
    }
}
