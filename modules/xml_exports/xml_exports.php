<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Einvoicing\Writers\UblWriter;
use Techy4m\XmlExports\Installer;
use Techy4m\XmlExports\InvoiceXML;
use Techy4m\XmlExports\XmlOutputWriter;

/*
Module Name: XML Export module
Description: Export Financial data in XML format
Author: Techy4m
Author URI: https://codecanyon.net/user/techy4m/portfolio
Version: 1.0.1
Requires at least: 3.0.6
*/

require(__DIR__ . '/vendor/autoload.php');

const XML_EXPORT_MODULE_NAME = 'xml_exports';

register_language_files(XML_EXPORT_MODULE_NAME, ['xml_exports']);
register_activation_hook(XML_EXPORT_MODULE_NAME, 'xml_export_activation_hook');
register_uninstall_hook(XML_EXPORT_MODULE_NAME, 'xml_export_uninstall_hook');

hooks()->add_filter('module_xml_exports_action_links', 'module_xml_export_action_links');
hooks()->add_action('admin_init', 'module_xml_exports_menus');
hooks()->add_action('admin_init', 'module_xml_exports_register_permissions');
hooks()->add_action('after_invoice_preview_template_rendered', 'module_xml_exports_invoice_download_button');
hooks()->add_action('before_parse_email_template_message', 'module_xml_exports_attach_xml_to_invoice_email');

/**
 * Add additional settings for this module in the module list area
 */
function module_xml_export_action_links(array $actions): array
{
    $actions[] = '<a href="' . admin_url('settings?group=xml_exports') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('xml_exports/bulk_export') . '">' . _l('Bulk_xml_exports') . '</a>';
    $actions[] = '<a href="https://www.boxvibe.com/support?envato_item_id=25337376" target="_blank">' . _l('help') . '</a>';

    return $actions;
}

function xml_export_activation_hook()
{
    $installer = new Installer();
    $installer->activate();
}

function xml_export_uninstall_hook()
{
    $installer = new Installer();
    $installer->uninstall();
}

function module_xml_exports_attach_xml_to_invoice_email($templateData)
{
    $template = $GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS'];

    if (get_option('xml_export_attach_xml_to_invoice_emails') != '1' || $template->rel_type != 'invoice') {
        return $templateData;
    }

    $invoiceTemplate = new ReflectionObject($template);

    if (!$invoiceTemplate->hasProperty('invoice')) {
        return $templateData;
    }

    $invoiceProperty = $invoiceTemplate->getProperty('invoice');
    $invoiceProperty->setAccessible(true);
    $invoice = $invoiceProperty->getValue($template);

    if (!is_object($invoice)) {
        log_activity("Failed to attach invoice XML to email $template->slug to  $template->send_to");
        return $template;
    }

    $invoiceXml = new InvoiceXML($invoice);
    $writer = new UblWriter();
    $fileName = format_invoice_number($invoice->id) . '.xml';
    $xml = $invoiceXml->generate();

    $template->add_attachment(XmlOutputWriter::emailAttachment($fileName, $writer->export($xml)));
    return $templateData;
}

function module_xml_exports_register_permissions()
{
    register_staff_capabilities(
        'xml_exports',
        [
            'capabilities' => [
                'view' => _l('permission_view'),
                'bulk' => _l('xml_exports_permission_bulk'),
            ]
        ],
        _l('xml_exports')
    );
}

function module_xml_exports_menus()
{
    $CI = &get_instance();

    // Perfex 3.2.0+ uses app->add_settings_section_child (app_tabs->add_settings_tab is deprecated)
    if (function_exists('get_app_version') && version_compare(get_app_version(), '3.2.0', '>=')) {

        $CI->app->add_settings_section_child('other', 'xml_exports', [
            'name'     => _l('settings_xml_exports'),
            'view'     => 'xml_exports/settings/xml_exports',
            'position' => 66,
            'icon'     => 'fa fa-cog',
        ]);

    } else {

        // Backward compatibility for older Perfex
        $CI->app_tabs->add_settings_tab('xml_exports', [
            'name'     => _l('settings_xml_exports'),
            'view'     => 'xml_exports/settings/xml_exports',
            'position' => 66,
            'icon'     => 'fa fa-cog',
        ]);

    }

    if (staff_can('bulk', 'xml_exports')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'csv-export',
            'name'     => _l('Bulk_xml_exports'),
            'href'     => admin_url('xml_exports/bulk_export'),
            'position' => 12,
        ]);
    }
}

function module_xml_exports_invoice_download_button()
{
    if (staff_can('view', 'xml_exports')) {
        $ci = &get_instance();
        $invoiceId = $ci->uri->segment(4);
        ?>
        <script>
            const pdfDropdown = document.querySelector("#invoice .dropdown-toggle > .fa-file-pdf").parentElement?.parentElement;
            const xmlDropdown = document.createElement("div");
            xmlDropdown.classList.add('btn-group');
            xmlDropdown.dataset.toggle = 'tooltip';
            xmlDropdown.dataset.placement = 'bottom';
            xmlDropdown.dataset.title = '<?= _l('xml_exports') ?>';

            let innerHtml = `
<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa-regular fa-file-lines"></i> <?= _l('xml_exports_xml') ?> <span class="caret"></span></a>
<ul class="dropdown-menu dropdown-menu-right">
    <li><a href="<?= admin_url('xml_exports/invoice/' . $invoiceId . '/download') ?>"><?= _l('xml_exports_download') ?></a></li>
    <li><a href="<?= admin_url('xml_exports/invoice/' . $invoiceId . '/stream') ?>"><?= _l('xml_exports_view') ?></a></li>
    <li class="hidden-xs"><a href="<?= admin_url('xml_exports/invoice/' . $invoiceId . '/stream') ?>" target="_blank"><?= _l('xml_exports_view_new_tab') ?></a></li>
</ul>`

            xmlDropdown.innerHTML = innerHtml;

            pdfDropdown.classList.add('mright5');
            pdfDropdown.after(xmlDropdown)
        </script>

        <?php
    }
}