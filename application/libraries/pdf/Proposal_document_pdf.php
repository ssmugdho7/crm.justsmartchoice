<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/App_pdf.php';
require_once APPPATH . 'helpers/proposal_pdf_helper.php';

/** Shared document lifecycle for native and Custom PDF proposal exports. */
abstract class Proposal_document_pdf extends App_pdf
{
    protected $proposal;
    protected $proposal_number;
    private $closing_page_number = 0;
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
        $this->renderPresentationPage('cover_page');
        $this->AddPage();
        $this->set_view_vars([
            'number' => $this->proposal_number,
            'proposal' => $this->proposal,
            'total' => $this->proposal->total != 0 ? 'Total: ' . app_format_money($this->proposal->total, get_currency($this->proposal->currency)) : '',
            'proposal_url' => site_url('proposal/' . $this->proposal->id . '/' . $this->proposal->hash),
        ]);
        return $this->build();
    }

    protected function type() { return 'proposal'; }

    private function sectionOption($section, $field)
    {
        return function_exists('getPdfOptions') ? getPdfOptions('proposals', $section, $field) : '';
    }

    private function sectionImage($section)
    {
        $image = $this->sectionOption($section, 'image');
        return function_exists('custom_pdf_uploaded_image_path') ? custom_pdf_uploaded_image_path('proposals', $image) : '';
    }

    private function sectionText($section)
    {
        $text = $this->sectionOption($section, 'text');
        return function_exists('parsePDFMergeFields') ? parsePDFMergeFields('proposals', $text, $this->proposal) : $text;
    }

    public function Header()
    {
        if ($this->page == 1 || $this->page == $this->closing_page_number) {
            return;
        }
        $w = $this->getPageWidth();
        $image = $this->sectionImage('header');
        if ($image !== '') {
            $this->Image($image, 0, 0, $w, 28);
        }
        $text = $this->sectionText('header');
        $this->SetFont($this->get_font_name(), '', 9);
        $this->SetTextColor(29, 48, 65);
        if ($text !== '') {
            $this->writeHTMLCell($w - 36, 0, 18, 12, $text, 0, 0);
        } elseif ($image === '') {
            $this->SetXY(18, 14);
            $this->Cell($w - 70, 7, (string) get_option('companyname'), 0, 0);
            $this->Cell(34, 7, 'PROPOSAL', 0, 0, 'R');
        }
        $this->SetDrawColor(20, 128, 128);
        $this->Line(18, 27, $w - 18, 27);
    }

    public function Footer()
    {
        if ($this->page == 1 || $this->page == $this->closing_page_number) {
            return;
        }
        $w = $this->getPageWidth();
        $image = $this->sectionImage('footer');
        if ($image !== '') {
            $this->Image($image, 0, $this->getPageHeight() - 24, $w, 24);
        }
        $this->SetFont($this->get_font_name(), '', 8);
        $this->SetTextColor(98, 111, 121);
        $this->SetDrawColor(220, 226, 231);
        $this->Line(18, $this->getPageHeight() - 25, $w - 18, $this->getPageHeight() - 25);
        $text = $this->sectionText('footer');
        if ($text !== '') {
            $this->writeHTMLCell($w - 36, 0, 18, $this->getPageHeight() - 22, $text, 0, 0, false, true, 'C');
        } elseif ($image === '') {
            $this->SetXY(18, $this->getPageHeight() - 20);
            $this->Cell($w - 70, 6, $this->proposal_number, 0, 0);
            $this->Cell(34, 6, 'Page ' . ($this->page - 1), 0, 0, 'R');
        }
    }

    private function renderPresentationPage($section)
    {
        $w = $this->getPageWidth();
        $h = $this->getPageHeight();
        $margin = $this->getBreakMargin();
        $auto = $this->getAutoPageBreak();
        $this->SetAutoPageBreak(false, 0);
        $image = $this->sectionImage($section);
        $text = $this->sectionText($section);
        if ($image !== '') {
            $this->Image($image, 0, 0, $w, $h);
        }
        if ($text !== '') {
            $x = $this->sectionOption($section, 'align_from_left');
            $y = $this->sectionOption($section, 'align_from_top');
            $x = is_numeric($x) ? max(12, min($w - 40, (float) $x)) : 22;
            $y = is_numeric($y) ? max(12, min($h - 40, (float) $y)) : 60;
            $this->writeHTMLCell($w - $x - 22, 0, $x, $y, $text, 0, 0);
        } elseif ($image === '') {
            // Designed defaults also work when uploaded images/settings are absent.
            $this->SetFillColor(22, 43, 60);
            $this->Rect(0, $h * .38, $w, $h * .55, 'F');
            $this->SetFillColor(22, 137, 132);
            $this->Rect(22, $h * .38 + 17, 26, 2, 'F');
            $this->SetTextColor(255, 255, 255);
            $this->SetFont($this->get_font_name(), 'B', 32);
            $this->SetXY(22, $h * .38 + 28);
            $isCover = $section === 'cover_page';
            $this->MultiCell($w - 44, 16, $isCover ? 'Proposal' : 'Thank you.', 0, 'L');
            $this->SetFont($this->get_font_name(), '', 13);
            $this->Ln(5);
            $this->SetX(22);
            $this->MultiCell($w - 44, 7, $isCover ? (string) $this->proposal->subject : 'We look forward to bringing your project to life.', 0, 'L');
            $this->Ln(10);
            $this->SetFont($this->get_font_name(), '', 10);
            $this->SetX(22);
            $details = $isCover
                ? 'Prepared for ' . $this->proposal->proposal_to . "\n" . $this->proposal_number . ' | ' . _d($this->proposal->date)
                : "Next steps\nReview the scope, pricing and terms in this proposal.\nContact our team with questions or to discuss your project.";
            $this->MultiCell($w - 44, 6, $details, 0, 'L');
            $this->SetFont($this->get_font_name(), 'B', 11);
            $this->SetXY(22, $h * .93 + 5);
            $this->SetTextColor(22, 43, 60);
            $this->MultiCell($w - 44, 6, (string) get_option('companyname'), 0, 'L');
            if ($image === '') {
                $this->SetXY(22, 22);
                $this->writeHTMLCell($w - 44, 0, '', '', pdf_logo_url(), 0, 1);
            }
        }
        $this->SetTextColor(35, 45, 55);
        $this->SetFont($this->get_font_name(), '', $this->get_font_size());
        $this->SetAutoPageBreak($auto, $margin);
        $this->setPageMark();
    }

    public function Close()
    {
        if ($this->state == 3) {
            return;
        }
        $ci = $this->ci;
        $language = $this->previous_language;
        $lastLanguage = $this->previous_last_language;
        try {
            if (hooks()->apply_filters('process_pdf_signature_on_close', true)) {
                $this->processSignature();
            }
            hooks()->do_action('pdf_close', ['pdf_instance' => $this, 'type' => $this->type()]);
            $this->last_page_flag = true;
            $this->closing_page_number = $this->getNumPages() + 1;
            $this->AddPage();
            $this->renderPresentationPage('closing_page');
            TCPDF::Close();
        } finally {
            $ci->lang->language = $language;
            $ci->lang->set_last_loaded_language($lastLanguage);
        }
    }
}
