<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'libraries/pdf/Proposal_document_pdf.php';

class Proposal_pdf extends Proposal_document_pdf
{
    public function prepare()
    {
        $this->set_view_vars([
            'document' => $this->proposal,
            'document_type' => 'proposal',
            'document_number' => $this->proposal_number,
            'status' => $this->proposal->status,
        ]);
        return parent::prepare();
    }

    protected function file_path()
    {
        return APP_MODULES_PATH . 'styleflow/views/pdf/styleflow_document.php';
    }
}
