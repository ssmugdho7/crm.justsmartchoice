<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Einvoicing\Writers\UblWriter;
use Techy4m\XmlExports\Bulk\BulkExporter;
use Techy4m\XmlExports\Bulk\BulkExporterConfig;
use Techy4m\XmlExports\InvoiceXML;
use Techy4m\XmlExports\XmlOutputWriter;

/**
 * @property-read  Invoices_model $invoices_model
 * @property-read App_Input $input
 */
class Xml_exports extends AdminController
{
    public function bulk_export()
    {
        if (!staff_can('bulk', 'xml_exports')) {
            access_denied('view xml_exports');
        }
        $this->load->model('invoices_model');

        if ($this->input->post()) {
            $config = new BulkExporterConfig(
                'invoices',
                staff_can('view', 'invoices'),
                $this->input->post('date-from'),
                $this->input->post('date-to'),
                $this->input->post('status'),
            );

            $bulkExporter = new BulkExporter($config);
            $bulkExporter->exportInvoices();
            return;
        }

        $features = ['invoices' => _l('bulk_export_pdf_invoices'),];
        $data['features'] = $features;
        $data['title'] = _l('Bulk_xml_exports');
        $data['bodyclass'] = 'invoices-total-manual';
        $data['invoiceStatuses'] = $this->invoices_model->get_statuses();


        $this->load->view('xml_exports/bulk_export', $data);
    }

    public function invoice($id, $action = 'download')
    {
        if (!staff_can('view', 'xml_exports')) {
            access_denied('view xml_exports');
        }

        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($id);
        $fileName = format_invoice_number($invoice->id) . '.xml';

        $invoiceXMLGen = new InvoiceXML($invoice);
        $invoiceXml = $invoiceXMLGen;
        $writer = new UblWriter();

        $xml = $invoiceXml->generate();
        try {
            $xml->validate();
        } catch (\Einvoicing\Exceptions\ValidationException $e) {
            set_alert('error', $e->getMessage());
            redirect(admin_url('invoices#' . $id));
        }
        if ($action === 'download') {
            XmlOutputWriter::download($fileName, $writer->export($xml));
        } else {
            XmlOutputWriter::stream($fileName, $writer->export($xml));
        }
    }
}
