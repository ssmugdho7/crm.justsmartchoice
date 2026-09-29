<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_attach_xml_to_invoice_emails', 0);
    }
}
