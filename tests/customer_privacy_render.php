<?php
// Render every feature-flag combination without sessions, a database, or submitting requests.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function _l($key) { return $key; }
function site_url($path = '') { return 'https://portal.example/' . $path; }
function terms_url() { return site_url('terms-and-conditions'); }
function get_contact_user_id() { return 7; }
function contact_consent_url($id) { return site_url('consent/' . $id); }
function is_gdpr() { global $enabled; return $enabled; }
function get_option($key) { global $options; return $options[$key] ?? ''; }
function form_open() { return '<form method="post"><input type="hidden" name="csrf_test" value="existing-token">'; }
function form_hidden($key, $value) { return '<input type="hidden" name="'.$key.'" value="'.$value.'">'; }
function form_close() { return '</form>'; }
function verify($ok, $message) { if (!$ok) throw new RuntimeException($message); }
$keys = ['gdpr_enable_terms_and_conditions','gdpr_contact_enable_right_to_be_forgotten','gdpr_data_portability_contacts','gdpr_enable_consent_for_contacts'];
$policy = '<h1>Existing policy</h1><p>Last updated: [Date]</p><p>Keep <strong>this policy text</strong> exactly.</p>';
$expectedPolicy = str_replace('[Date]', 'October 3, 2026', $policy);
foreach ([false, true] as $enabled) {
    for ($mask = 0; $mask < 16; $mask++) {
        foreach ([$policy, ''] as $policyContent) {
            $options = ['gdpr_page_top_information_block' => $policyContent];
            foreach ($keys as $bit => $key) $options[$key] = ($mask & (1 << $bit)) ? '1' : '0';
            ob_start(); include dirname(__DIR__).'/application/views/themes/smartchoice/views/gdpr.php'; $html = ob_get_clean();
            $document = new DOMDocument(); @$document->loadHTML($html); $xpath = new DOMXPath($document);
            $expected = [site_url('clients/profile')];
            if ($enabled && ($mask & 1)) $expected[] = terms_url();
            if ($enabled && ($mask & 2)) $expected[] = '#';
            if ($enabled && ($mask & 4)) $expected[] = site_url('clients/export');
            if ($enabled && ($mask & 8)) $expected[] = contact_consent_url(7);
            $actual = [];
            foreach ($xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," gdpr-right ")]//a') as $link) $actual[] = $link->getAttribute('href');
            sort($expected); sort($actual);
            verify($expected === $actual, 'Only existing enabled privacy routes must render');
            $removal = $enabled && ($mask & 2);
            verify($xpath->query('//form')->length === ($removal ? 1 : 0), 'Removal form guard');
            if ($removal) {
                verify($xpath->query('//form/input[@name="csrf_test"]')->length === 1, 'Native form retains CSRF');
                verify($xpath->query('//form//input[@name="removal_request" and @value="1"]')->length === 1, 'Removal trigger payload');
                verify($xpath->query('//textarea[@name="removal_description" and @id="removal_description"]')->length === 1, 'Removal description payload');
                verify($xpath->query('//a[@data-target="#dataRemoval" and @data-toggle="modal"]')->length === 1, 'Existing modal trigger');
                verify($xpath->query('//*[@id="dataRemoval" and @aria-labelledby="sc-data-removal-title"]')->length === 1, 'Modal accessible title');
            }
            verify($policyContent === '' || strpos($html, $expectedPolicy) !== false, 'Only resolve the update-date placeholder in administrator policy HTML');
            verify($xpath->query('//*[@id="sc-privacy-policy"]')->length === ($policyContent === '' ? 0 : 1), 'No blank policy container');
        }
    }
}
echo "PASS privacy view: 64 enabled, restricted and empty combinations; unchanged actions, payload, CSRF and policy HTML\n";
