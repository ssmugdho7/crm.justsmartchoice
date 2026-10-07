<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Sales_document_pdf.php';
require_once APPPATH . 'helpers/proposal_pdf_helper.php';

/** Shared document lifecycle for native and Custom PDF proposal exports. */
abstract class Proposal_document_pdf extends Sales_document_pdf
{
    protected $proposal;
    protected $proposal_number;
    private $prepared = false;
    private $previous_language;
    private $previous_last_language;

    public function __construct($proposal, $tag = '')
    {
        $CI = &get_instance();
        $this->previous_language = $CI->lang->language;
        $this->previous_last_language = $CI->lang->last_loaded;
        // Loading an already loaded language can be a no-op in CI. Merge directly
        // so customer/admin language cannot leak Spanish labels into this PDF.
        $lang = [];
        require APPPATH . 'language/english/english_lang.php';
        $styleflowLanguage = FCPATH . 'modules/styleflow/language/english/styleflow_lang.php';
        if (is_file($styleflowLanguage)) { require $styleflowLanguage; }
        $CI->lang->language = array_merge($CI->lang->language, $lang);
        $CI->lang->set_last_loaded_language('english');

        $this->proposal = clone hooks()->apply_filters('proposal_html_pdf_data', $proposal);
        $GLOBALS['proposal_pdf'] = $this->proposal;
        $this->proposal_number = format_proposal_number($proposal->id);
        $this->tag = $tag;
        parent::__construct();
        $this->SetTitle('Proposal - ' . $this->proposal_number);
        $this->SetDisplayMode('default', 'OneColumn');
        $this->proposal->content = $this->fix_editor_html($this->proposal->content);
        $this->SetMargins(18, 35, 18);
        $this->SetAutoPageBreak(true, 30);
    }

    public function prepare()
    {
        if ($this->prepared) {
            return $this;
        }
        $this->prepared = true;
        $this->with_number_to_word('unknown');
        $this->set_view_vars([
            'number' => $this->proposal_number,
            'proposal' => $this->proposal,
            'total' => $this->proposal->total != 0 ? 'Total: ' . app_format_money($this->proposal->total, get_currency($this->proposal->currency)) : '',
            'proposal_url' => site_url('proposal/' . $this->proposal->id . '/' . $this->proposal->hash),
        ]);
        return $this->build();
    }

    protected function type() { return 'proposal'; }

    public function Close()
    {
        if ($this->state == 3) { return; }
        $ci = $this->ci;
        try {
            parent::Close();
        } finally {
            $ci->lang->language = $this->previous_language;
            $ci->lang->set_last_loaded_language($this->previous_last_language);
        }
    }
}
