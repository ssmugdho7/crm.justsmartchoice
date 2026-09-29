<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        update_option('document_management_default_language', get_option('document_management_default_language') ?: 'english');
        update_option('document_management_allow_customer_portal', get_option('document_management_allow_customer_portal') !== '' ? get_option('document_management_allow_customer_portal') : '1');
        update_option('document_management_enable_watermark', get_option('document_management_enable_watermark') !== '' ? get_option('document_management_enable_watermark') : '0');
        update_option('document_management_enable_audit_log', get_option('document_management_enable_audit_log') !== '' ? get_option('document_management_enable_audit_log') : '1');
        update_option('document_management_default_storage_folder', get_option('document_management_default_storage_folder') ?: 'modules/document_management/uploads/files');
        update_option('document_management_primary_color', get_option('document_management_primary_color') ?: '#169179');
        update_option('document_management_accent_color', get_option('document_management_accent_color') ?: '#f47c20');
        update_option('document_management_version', '2.0.0');
    }
}
