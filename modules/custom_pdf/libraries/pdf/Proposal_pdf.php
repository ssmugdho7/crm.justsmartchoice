<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'libraries/pdf/Proposal_document_pdf.php';

class Proposal_pdf extends Proposal_document_pdf
{
    protected function file_path()
    {
        $custom = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proposalpdf.php';
        return file_exists($custom) ? $custom : module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_proposal_pdf.php');
    }
}
