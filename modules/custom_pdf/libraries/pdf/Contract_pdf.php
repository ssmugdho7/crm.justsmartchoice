<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH.'libraries/pdf/Sales_document_pdf.php';

class Contract_pdf extends Sales_document_pdf
{
    protected $contract;

    protected $page_width;
    protected $page_height;

    public function __construct($contract)
    {
        $this->load_language($contract->client);

        $contract                = hooks()->apply_filters('contract_html_pdf_data', $contract);
        $GLOBALS['contract_pdf'] = $contract;

        parent::__construct();

        $this->contract = $contract;

        $this->page_width  = $this->getPageDimensions()['wk'];
        $this->page_height = $this->getPageDimensions()['hk'];

        $this->SetTitle($this->contract->subject);

        # Don't remove these lines - important for the PDF layout
        $this->contract->content = $this->fix_editor_html($this->contract->content ?? '');
    }

    public function prepare()
    {
        $this->set_view_vars('contract', $this->contract);

        return $this->build();
    }

    // Page header
    protected function renderSalesBodyHeader()
    {
        $header_text = parsePDFMergeFields('contract', getPdfOptions('contract', 'header', 'text'), $this->contract);
        $image_file  = custom_pdf_uploaded_image_path('contract', getPdfOptions('contract', 'header', 'image'));

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
        $footer_text = parsePDFMergeFields('contract', getPdfOptions('contract', 'footer', 'text'), $this->contract);
        $image_file  = custom_pdf_uploaded_image_path('contract', getPdfOptions('contract', 'footer', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, $this->page_height - 30, $this->page_width, 30);
        }

        if ($footer_text !== '') {
            $this->writeHTMLCell(0, 0, 10, -12, $footer_text, 0, 0, 0, true, '', true);
        }

        $this->SetFooterMargin(35);
    }

    protected function type()
    {
        return 'contract';
    }

    protected function file_path()
    {
        $customPath = APPPATH.'views/themes/'.active_clients_theme().'/views/my_contractpdf.php';
        $actualPath = APPPATH.'views/themes/'.active_clients_theme().'/views/contractpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
