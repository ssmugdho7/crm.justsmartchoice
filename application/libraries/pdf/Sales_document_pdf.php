<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/App_pdf.php';

/** One cover/body/closing lifecycle for all sales PDF providers. */
abstract class Sales_document_pdf extends App_pdf
{
    protected $closing_page_number = 0;
    protected $presentationPages = [1 => true];
    protected $renderingPresentation = false;
    private $salesPrepared = false;

    public function __construct()
    {
        parent::__construct();
        if ($this->type() === 'proposal') {
            $this->SetMargins(18, 35, 18);
            $this->SetAutoPageBreak(true, 30);
        }
    }

    protected function build()
    {
        if ($this->salesPrepared) { return $this; }
        $this->salesPrepared = true;
        $this->renderPresentationPage('cover_page');
        $this->AddPage();
        return parent::build();
    }

    protected function presentationDocument()
    {
        $type = $this->type();
        return $this->{$type};
    }

    protected function presentationNumber()
    {
        $key = $this->type() === 'proposal' ? 'number' : $this->type() . '_number';
        foreach ([$key, 'document_number'] as $name) {
            $value = $this->get_view_vars($name);
            if (is_string($value) && $value !== '') { return $value; }
        }
        if ($this->type() === 'contract') { return _l('contract') . ' #' . $this->contract->id; }
        if ($this->type() === 'payment') { return _l('payment') . ' #' . $this->payment->paymentid; }
        $formatter = 'format_' . $this->type() . '_number';
        return $formatter($this->presentationDocument()->id);
    }

    protected function presentationSubject()
    {
        return in_array($this->type(), ['proposal', 'contract'], true) ? (string) $this->presentationDocument()->subject : $this->presentationNumber();
    }

    protected function presentationCustomer()
    {
        $document = $this->presentationDocument();
        if ($this->type() === 'proposal') { return (string) $document->proposal_to; }
        if ($this->type() === 'payment') { $document = $document->invoice_data; }
        return (string) ($document->client->company ?? $document->company ?? 'Customer');
    }

    private function settingsType()
    {
        return $this->type() === 'proposal' ? 'proposals' : $this->type();
    }

    private function sectionOption($section, $field)
    {
        // Read installed options even when Custom PDF is inactive or StyleFlow wins the class hook.
        $settings = json_decode((string) get_option($this->settingsType() . '_pdf_settings'), true);
        return is_array($settings) ? ($settings[$section][$field] ?? '') : '';
    }

    private function sectionImage($section)
    {
        $image = $this->sectionOption($section, 'image');
        // Uploaded artwork is local and restricted to this document's folder. No remote URL fetching.
        $folder = realpath(FCPATH . 'uploads/custom_pdf/' . $this->settingsType());
        $path = is_string($image) && $image !== '' && basename($image) === $image && $folder
            ? realpath($folder . '/' . $image) : false;
        if ($path && strpos($path, $folder . DIRECTORY_SEPARATOR) === 0 && $this->isPresentationImage($path)) {
            return $path;
        }
        if (!in_array($section, ['cover_page', 'closing_page'], true)) { return ''; }
        if ($this->type() === 'proposal') { return $this->bundledPresentationImage($section); }
        // Preserve local legacy cover/end artwork without the old extra-page renderer.
        $kind = $section === 'cover_page' ? 'cover' : 'end';
        $type = $this->type();
        foreach ([$type . '_pdf_' . $kind . '_image', $type . '_pdf_' . $kind . '_page',
            'pdf_' . $type . '_' . $kind . '_image', 'pdf_' . $type . '_' . $kind . '_page',
            'custom_pdf_' . $type . '_' . $kind, 'custom_crm_pdf_' . $type . '_' . $kind,
            'custom_pdf_' . $kind . '_image', 'custom_pdf_' . $kind . '_page',
            'custom_crm_pdf_' . $kind, 'sc_pdf_' . $kind . '_image', 'pdf_' . $kind . '_image'] as $name) {
            $value = get_option($name);
            if (!is_string($value) || $value === '' || strpos($value, ':') !== false || strpos($value, "\0") !== false) { continue; }
            foreach ([FCPATH . ltrim($value, '/'), FCPATH . 'uploads/' . ltrim($value, '/')] as $candidate) {
                $resolved = realpath($candidate);
                foreach (['uploads', 'assets'] as $allowed) {
                    $root = realpath(FCPATH . $allowed);
                    if ($root && $resolved && strpos($resolved, $root . DIRECTORY_SEPARATOR) === 0 && $this->isPresentationImage($resolved)) {
                        return $resolved;
                    }
                }
            }
        }
        return $this->bundledPresentationImage($section);
    }

    private function bundledPresentationImage($section)
    {
        // Preserve text-only custom pages; supply branded defaults when artwork is absent.
        $text = $this->sectionOption($section, 'text');
        if (is_string($text) && trim($text) !== '') { return ''; }
        if (!in_array($this->type(), ['proposal', 'estimate', 'invoice', 'contract', 'payment'], true)) { return ''; }
        $root = realpath(FCPATH . 'modules/custom_pdf/assets/bookends');
        $name = $section === 'cover_page' ? $this->type() . '-cover.png' : 'closing-page.png';
        $path = $root ? realpath($root . '/' . $name) : false;
        return $path && strpos($path, $root . DIRECTORY_SEPARATOR) === 0 && $this->isPresentationImage($path) ? $path : '';
    }

    private function isPresentationImage($path)
    {
        if (!is_file($path) || !is_readable($path)) { return false; }
        $size = @getimagesize($path);
        return $size && in_array($size['mime'] ?? '', ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true);
    }

    private function sectionText($section)
    {
        $text = $this->sectionOption($section, 'text');
        if (!is_string($text) || $text === '') { return ''; }
        if (function_exists('parsePDFMergeFields')) {
            return parsePDFMergeFields($this->settingsType(), $text, $this->presentationDocument());
        }
        // Native merge fields remain available when the Custom PDF module is inactive.
        if ($text !== '' && isset($this->ci->app_mail_template)) {
            $mergeType = $this->type() === 'payment' ? 'invoice' : $this->settingsType();
            $document = $this->type() === 'payment' ? $this->payment->invoice_data : $this->presentationDocument();
            $fields = $this->ci->app_mail_template
                ->set_merge_fields($mergeType . '_merge_fields', $document->id)->merge_fields;
            if (is_array($fields)) {
                foreach ($fields as $key => $value) {
                    if (is_scalar($value) || $value === null) { $text = str_replace((string) $key, (string) $value, $text); }
                }
            }
        }
        return function_exists('html_purify') ? html_purify($text) : $text;
    }

    public function Header()
    {
        if ($this->renderingPresentation) { $this->presentationPages[$this->page] = true; }
        if (isset($this->presentationPages[$this->page]) || $this->page == $this->closing_page_number) {
            return;
        }
        $this->renderSalesBodyHeader();
    }

    protected function renderSalesBodyHeader()
    {
        if ($this->type() !== 'proposal') { parent::Header(); return; }
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
            $this->Cell(34, 7, strtoupper($this->type()), 0, 0, 'R');
        }
        $this->SetDrawColor(20, 128, 128);
        $this->Line(18, 27, $w - 18, 27);
    }

    public function Footer()
    {
        if ($this->renderingPresentation) { $this->presentationPages[$this->page] = true; }
        if (isset($this->presentationPages[$this->page]) || $this->page == $this->closing_page_number) {
            return;
        }
        $this->renderSalesBodyFooter();
    }

    protected function renderSalesBodyFooter()
    {
        if ($this->type() !== 'proposal') { parent::Footer(); return; }
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
            $this->Cell($w - 70, 6, $this->presentationNumber(), 0, 0);
            $this->Cell(34, 6, 'Page ' . ($this->page - 1), 0, 0, 'R');
        }
    }

    protected function renderPresentationPage($section)
    {
        $this->renderingPresentation = true;
        $this->presentationPages[$this->page] = true;
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
            $this->SetAutoPageBreak(true, 20);
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
            $this->MultiCell($w - 44, 16, $isCover ? ucfirst($this->type()) : 'Thank you.', 0, 'L');
            $this->SetFont($this->get_font_name(), '', 13);
            $this->Ln(5);
            $this->SetX(22);
            $this->MultiCell($w - 44, 7, $isCover ? $this->presentationSubject() : 'We look forward to bringing your project to life.', 0, 'L');
            $this->Ln(10);
            $this->SetFont($this->get_font_name(), '', 10);
            $this->SetX(22);
            $details = $isCover
                ? ($this->type() === 'invoice' ? 'Bill to ' : 'Prepared for ') . $this->presentationCustomer() . "\n" . $this->presentationNumber() . ' | ' . _d($this->type() === 'contract' ? $this->contract->datestart : $this->presentationDocument()->date)
                : ($this->type() === 'payment'
                    ? "Keep this payment receipt for your records.\nContact our team with questions about your payment."
                    : "Next steps\nReview the scope, pricing and terms in this " . $this->type() . ".\nContact our team with questions or to discuss your project.");
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
        $this->renderingPresentation = false;
    }

    public function Close()
    {
        if ($this->state == 3) { return; }
        if (hooks()->apply_filters('process_pdf_signature_on_close', true)) { $this->processSignature(); }
        hooks()->do_action('pdf_close', ['pdf_instance' => $this, 'type' => $this->type()]);
        $this->last_page_flag = true;
        $this->closing_page_number = $this->getNumPages() + 1;
        $this->AddPage();
        $this->renderPresentationPage('closing_page');
        TCPDF::Close();
    }
}
