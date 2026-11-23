<?php
 defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Optional integration with Smart Choice Custom PDF. Read the same options and
 * upload directories as that module; never require it to be active.
 * This helper does not modify Custom PDF or the Perfex PDF engine.
 */
if (!function_exists('solar_pro_custom_pdf_bookend')) {
    function solar_pro_custom_pdf_bookend($documentType, $section)
    {
        if (!in_array($documentType, ['proposals', 'estimate'], true) || !in_array($section, ['cover_page', 'closing_page'], true)) {
            return '';
        }
        if (!function_exists('get_option')) { return ''; }
        $settings = json_decode((string) get_option($documentType . '_pdf_settings'), true);
        if (!is_array($settings)) { return ''; }
        $sectionSettings = $settings[$section] ?? [];
        if (!is_array($sectionSettings)) { return ''; }
        $filename = (string) ($sectionSettings['image'] ?? '');
        $text = (string) ($sectionSettings['text'] ?? '');
        $path = '';
        if ($filename !== '' && basename($filename) === $filename) {
            $root = realpath(FCPATH . 'uploads/custom_pdf/' . $documentType);
            $candidate = $root ? realpath($root . DIRECTORY_SEPARATOR . $filename) : false;
            if ($candidate && strpos($candidate, $root . DIRECTORY_SEPARATOR) === 0 && is_readable($candidate) && @getimagesize($candidate)) {
                $path = $candidate;
            }
        }
        if ($path === '' && trim($text) === '') { return ''; }
        // TCPDF can render local JPEG/PNG paths in its HTML image element.
        $html = $section === 'closing_page' ? '<div style="page-break-before:always;"></div>' : '';
        if ($path !== '') {
            $html .= '<div style="text-align:center;"><img src="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '" style="width:185mm;max-height:245mm;" /></div>';
        }
        if (trim($text) !== '') {
            $html .= '<div style="font-family:helvetica;font-size:11pt;">' . (function_exists('html_purify') ? html_purify($text) : htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8')) . '</div>';
        }
        if ($section === 'cover_page') { $html .= '<div style="page-break-after:always;"></div>'; }
        return $html;
    }
}
