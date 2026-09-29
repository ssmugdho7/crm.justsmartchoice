<?php

namespace Techy4m\XmlExports\Bulk;

use app\services\utilities\Str;
use CI_Controller;
use Einvoicing\Writers\UblWriter;
use RuntimeException;
use Techy4m\XmlExports\InvoiceXML;

class BulkExporter
{
    private BulkExporterConfig $config;
    /**
     * @var CI_Controller|object
     */
    private $ci;

    public function __construct(BulkExporterConfig $config)
    {
        $this->config = $config;
        $this->ci = &get_instance();


        $this->ci->load->model('invoices_model');
        $this->ci->load->library('zip');

        if (!is_really_writable(TEMP_FOLDER)) {
            show_error(TEMP_FOLDER . ' folder is not writable. You need to change the permissions to 0755');
        }


        if (is_dir($this->config->getDir())) {
            $this->clearDirectory($this->config->getDir());
        }

        mkdir($this->config->getDir(), 0755);
        register_shutdown_function([$this, 'clearDirectory'], $this->config->getDir());
    }

    public function exportInvoices()
    {
        $noPermissionQuery = get_invoices_where_sql_for_staff(get_staff_user_id());
        $notSentQuery = 'sent=0 AND status NOT IN(2,5)' . (!$this->config->hasPermission() ? ' AND (' . $noPermissionQuery . ')' : '');

        $this->ci->db->select('id, date');
        $this->ci->db->from(db_prefix() . 'invoices');
        if ($this->config->getStatus() != 'all') {
            if (is_numeric($this->config->getStatus())) {
                $this->ci->db->where('status', $this->config->getStatus());
            } else {
                $this->ci->db->where($notSentQuery);
            }
        }

        if (!$this->config->hasPermission()) {
            $this->ci->db->where($noPermissionQuery);
        }

        $this->ci->db->order_by($this->config->getDateColumn(), 'desc');

        $data = $this->finalize();

        foreach ($data as $invoice) {
            $invoice = $this->ci->invoices_model->get($invoice['id']);
            $xml = new InvoiceXML($invoice);
            $this->saveToDir($invoice, $xml, strtoupper(slug_it(format_invoice_number($invoice->id))) . '.xml');
        }

        $this->zip('invoice');
    }


    private function saveToDir(object $object, InvoiceXML $xml, string $file_name)
    {
        $dateColumn = str_replace('`', '', $this->config->getDateColumn());
        $dir = $this->config->getDir() . '/';

        if (strpos($dateColumn, '.') !== false) {
            $dateColumn = strafter($dateColumn, '.');
        }

        if (!empty($object->{$dateColumn})) {
            $dir .= date('Y', strtotime($object->{$dateColumn})) . '/';
        }

        $filename = $dir . $file_name;

        $outPut = new UblWriter();
        $this->writeToFile($filename, $outPut->export($xml->generate()));
    }

    private function writeToFile(string $filename, string $data): void
    {
        if (strpos($filename, '://') === false) {
            $filename = 'file://' . $filename;
        } elseif (stream_is_local($filename) !== true) {
            return;
        }
        $f = fopen($filename, 'wb');
        // save PDF to a local file

        if (!$f) {
            throw new RuntimeException('Unable to create output file: ' . $filename);
        }

        fwrite($f, $data, strlen($data));
        fclose($f);
    }

    private function finalize(): array
    {
        $this->setDateQuery();

        $data = $this->ci->db->get()->result_array();
        $last_query = $this->ci->db->last_query();
        $withoutSelect = Str::after($last_query, 'FROM');

        $yearSelectQuery = 'SELECT DISTINCT(YEAR(' . $this->config->getDateColumn() . ')) as year, `date` FROM' . str_replace('ORDER BY ' . $this->config->getDateColumn(), 'ORDER BY year', $withoutSelect);

        $years = $this->ci->db->query($yearSelectQuery)->result_array();

        if (count($data) == 0) {
            set_alert('warning', _l('no_data_found_bulk_pdf_export'));
            redirect($this->config->getErrorRedirectURL());
        }

        $this->setYearsAndCreateDirectories($years);

        return $data;
    }

    private function setDateQuery(): void
    {
        if ($this->config->getDateFrom() && $this->config->getDateTo()) {
            $date_field = $this->config->getDateColumn();
            if ($this->config->getDateFrom() == $this->config->getDateTo()) {
                $this->ci->db->where($date_field, $this->config->getDateFrom());
            } else {
                $this->ci->db->where($date_field . ' BETWEEN "' . $this->config->getDateFrom() . '" AND "' . $this->config->getDateTo() . '"');
            }
        }
    }

    private function setYearsAndCreateDirectories($years): void
    {
        $dir = $this->config->getDir() . '/';

        foreach ($years as $year) {
            if (!is_dir($dir . $year['year'])) {
                mkdir($dir . $year['year'], 0755, true);
            }
        }
    }

    /**
     * Used to zip the data in the folder
     * @param string $type
     * @return void
     */
    public function zip(string $type): void
    {
        $this->ci->zip->read_dir($this->config->getDir(), false);
        $this->ci->zip->download(slug_it(get_option('companyname')) . '-' . $type . '.zip');
        $this->ci->zip->clear_data();
    }

    public function clearDirectory(string $dir): void
    {
        if ($dir == TEMP_FOLDER) {
            return;
        }

        if (is_dir($dir)) {
            delete_files($dir);
            delete_dir($dir);
        }
    }
}
