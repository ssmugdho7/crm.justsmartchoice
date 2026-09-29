<?php

namespace Techy4m\XmlExports;
defined('BASEPATH') or exit('No direct script access allowed');

class Installer
{
    public function activate()
    {
        add_option('xml_export_electronic_address', '');
        add_option('xml_export_electronic_address_scheme', '0088');
        add_option('xml_export_company_id', '');
        add_option('xml_export_company_id_scheme', '0183');
        add_option('xml_export_customer_electronic_address_field');
        add_option('xml_export_customer_electronic_address_scheme_field', '0201');
        add_option('xml_export_invoice_buyer_reference_field');
        add_option('xml_export_attach_xml_to_invoice_emails', 0);
    }

    public function uninstall()
    {
        delete_option('xml_export_electronic_address');
        delete_option('xml_export_electronic_address_scheme');
        delete_option('xml_export_company_id');
        delete_option('xml_export_company_id_scheme');
        delete_option('xml_export_customer_electronic_address_field');
        delete_option('xml_export_customer_electronic_address_scheme_field');
        delete_option('xml_export_invoice_buyer_reference_field');
        delete_option('xml_export_attach_xml_to_invoice_emails');
    }
}
