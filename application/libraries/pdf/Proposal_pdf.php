<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Proposal_document_pdf.php';

class Proposal_pdf extends Proposal_document_pdf
{
    protected function file_path()
    {
        $base = APPPATH . 'views/themes/' . active_clients_theme() . '/views/';
        return file_exists($base . 'my_proposalpdf.php') ? $base . 'my_proposalpdf.php' : $base . 'proposalpdf.php';
    }
}
