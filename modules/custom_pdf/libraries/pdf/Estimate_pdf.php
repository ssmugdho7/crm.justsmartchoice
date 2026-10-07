<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'libraries/pdf/Sales_document_pdf.php';

class Estimate_pdf extends Sales_document_pdf
{
    protected $estimate;



    private $estimate_number;


    public function __construct($estimate, $tag = '')
    {
        $this->load_language($estimate->clientid);

        $estimate                = hooks()->apply_filters('estimate_html_pdf_data', $estimate);
        $GLOBALS['estimate_pdf'] = $estimate;

        parent::__construct();

        $this->tag             = $tag;
        $this->estimate        = $estimate;
        $this->estimate_number = format_estimate_number($this->estimate->id);


        $this->SetTitle($this->estimate_number);

    }

    public function prepare()
    {
        $this->with_number_to_word($this->estimate->clientid);

        $this->set_view_vars([
            'status'          => $this->estimate->status,
            'estimate_number' => $this->estimate_number,
            'estimate'        => $this->estimate,
        ]);

        return $this->build();
    }

    // Page header
    protected function renderSalesBodyHeader()
    {
        $header_text = parsePDFMergeFields('estimate', getPdfOptions('estimate', 'header', 'text'), $this->estimate);
        $image_file  = custom_pdf_uploaded_image_path('estimate', getPdfOptions('estimate', 'header', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, 0, $this->getPageDimensions()['wk'], 30);
        }

        if ($header_text !== '') {
            $this->writeHTMLCell(0, 0, 10, 12, $header_text, 0, 0, 0, true, '', true);
        }

        $this->SetTopMargin(35);
    }

    // Page footer
    protected function renderSalesBodyFooter()
    {
        $footer_text = parsePDFMergeFields('estimate', getPdfOptions('estimate', 'footer', 'text'), $this->estimate);
        $image_file  = custom_pdf_uploaded_image_path('estimate', getPdfOptions('estimate', 'footer', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, $this->getPageHeight() - 30, $this->getPageWidth(), 30);
        }

        if ($footer_text !== '') {
            $this->writeHTMLCell(0, 0, 10, -12, $footer_text, 0, 0, 0, true, '', true);
        }

        $this->SetFooterMargin(35);
    }

    protected function type()
    {
        return 'estimate';
    }

    protected function file_path()
    {
        $customPath = APPPATH.'views/themes/'.active_clients_theme().'/views/my_estimatepdf.php';
        $actualPath = module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_estimate_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
