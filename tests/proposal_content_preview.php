<?php
// Render the actual view with synthetic data only; no CRM/database bootstrap.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/application/language/english/english_lang.php';
$canEdit = false;
function staff_can($action, $feature) { global $canEdit; return $canEdit; }
function _l($key) { global $lang; return $lang[$key] ?? $key; }
function render_content_preview($content, $editable) {
    global $canEdit;
    $canEdit = $editable;
    $proposal = (object) ['content' => $content];
    $proposal_merge_fields = [];
    ob_start();
    require dirname(__DIR__) . '/application/views/admin/proposals/content_preview.php';
    $html = ob_get_clean();
    if ($proposal->content !== $content) { throw new RuntimeException('Saved content mutated'); }
    return $html;
}
if (($argv[1] ?? '') === '--fixture') {
    echo render_content_preview('<p>Original scope</p><p>{{ proposal_items }}</p>', true);
    exit;
}
foreach (['{proposal_items}', '{{proposal_items}}', '{{ proposal_items }}', '{PROPOSAL_ITEMS}'] as $token) {
    foreach ([false, true] as $editable) {
        $content = '<p>Scope and {proposal_subject}</p><p>' . $token . '</p><img src="sample.png">';
        $html = render_content_preview($content, $editable);
        preg_match('~id="sc-proposal-content-preview"[^>]*>(.*?)</div>~s', $html, $preview);
        if (strpos($preview[1], $token) !== false || strpos($preview[1], '{proposal_subject}') === false
            || strpos($preview[1], 'sample.png') === false) { throw new RuntimeException('Incorrect preview'); }
        if ($editable) {
            if (strpos($html, $content) === false || !preg_match('~<details id="sc-proposal-content-editor" class="mtop15">~', $html)) {
                throw new RuntimeException('Template not preserved in closed editor');
            }
        } elseif (strpos($html, 'id="proposal_content_area"') !== false) {
            throw new RuntimeException('Read-only user received editor');
        }
    }
}
foreach ([null, '', '<p>{proposal_items}</p>', '<p>Scope only</p>'] as $content) {
    render_content_preview($content, true);
}
echo "PASS proposal preview: token variants hidden, prose/images retained, raw template preserved, read-only/editable and empty states\n";
