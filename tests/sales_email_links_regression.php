<?php
// Render synthetic templates only; no database, customer records or mail delivery.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function site_url($path = '') { return 'https://portal.example.test/crm/' . $path; }
function app_generate_hash() { return 'fixture-id'; }
function &get_instance() { return $GLOBALS['ci']; }
function hooks() { return new class { function apply_filters($name, $value) { return $value; } }; }
class other_merge_fields { function format() { return ['{companyname}' => 'Fixture Company']; } }
$ci = (object) [
    'input' => new class { public $data = []; function post($key, $xss = true) { return $this->data[$key] ?? false; } },
    'other_merge_fields' => new other_merge_fields(),
];
require dirname(__DIR__) . '/application/helpers/email_templates_helper.php';
require dirname(__DIR__) . '/application/services/utilities/StrClickable.php';
class FixtureClickable { use \app\services\utilities\StrClickable; }
function verify($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
    echo "PASS $message\n";
}
function fixture($slug, $body, $plaintext = 0) {
    return (object) ['slug' => $slug, 'message' => $body, 'subject' => 'Review document', 'fromname' => '{companyname}', 'plaintext' => $plaintext];
}
$documents = [
    'proposal-send-to-customer' => 'proposal', 'estimate-send-to-client' => 'estimate',
    'estimate-already-send' => 'estimate', 'invoice-send-to-client' => 'invoice',
    'invoice-already-send' => 'invoice', 'send-contract' => 'contract',
];
foreach ($documents as $slug => $type) {
    $url = site_url($type . '/42/' . str_repeat('a', 32));
    $fields = ['{' . $type . '_link}' => $url];
    $body = '<p>Please review your <strong>' . ucfirst($type) . '</strong>.</p><p>{' . $type . '_link}</p>';
    $result = parse_email_template(fixture($slug, $body), $fields);
    verify(strpos($result->message, '<a href="' . $url . '">' . ucfirst($type) . '</a>') !== false, "$slug links visible document name to its secure route");
    verify($result->subject === 'Review document' && $result->fromname === 'Fixture Company', "$slug preserves headers and normal merge fields");
    $rendered = FixtureClickable::clickable($result->message);
    verify(substr_count($rendered, '<a ') === 2 && strpos($rendered, '<a href="' . $url . '">' . ucfirst($type) . '</a>') !== false, "$slug retains document label and raw URL links through final rendering");
    verify(sc_link_sales_document_email(clone $result, $fields)->message === $result->message, "$slug is idempotent");
    verify(sc_link_sales_document_email(fixture($slug, $body, 1), $fields)->message === $body, "$slug respects plain-text templates");
    foreach (['', 'javascript:alert(1)', 'https://evil.example.test/' . $type . '/42/abc', site_url('proposal/42/abc') . '?next=evil', site_url($type . '/0/abc'), site_url($type . '/42/'), site_url($type . '/42/abc') . '" onclick="bad'] as $bad) {
        verify(sc_link_sales_document_email(fixture($slug, $body), ['{' . $type . '_link}' => $bad])->message === $body, "$slug leaves body intact when destination is absent or invalid");
    }
    $existing = '<a title="review > later" href="https://admin-selected.example.test/review"><strong>' . ucfirst($type) . '</strong></a>'
        . '<img alt="' . $type . '" src="https://assets.example.test/' . $type . '.png">'
        . '<!-- ' . $type . ' --><script>var label="' . $type . '";</script><style>.' . $type . '{color:red}</style>'
        . '<pre>' . $type . '</pre><textarea>' . $type . '</textarea>';
    verify(sc_link_sales_document_email(fixture($slug, $existing), $fields)->message === $existing, "$slug preserves explicit admin links, attributes and protected content");
    $raw = $url . ' ' . $type . '@example.test ' . $type . 's subcontractor my_' . $type;
    verify(sc_link_sales_document_email(fixture($slug, $raw), $fields)->message === $raw, "$slug does not corrupt URLs, addresses or partial words");
}
verify(sc_link_sales_document_email(fixture('contact-verification-email', '<p>Verify your email for the Proposal</p>'), ['{proposal_link}' => site_url('proposal/42/abc')])->message === '<p>Verify your email for the Proposal</p>', 'account verification cannot inherit an unrelated sales link');
verify(sc_link_sales_document_email(fixture('proposal-send-to-customer', '<p>Proposal Estimate Contract Invoice</p>'), ['{proposal_link}' => site_url('proposal/42/abc'), '{estimate_link}' => site_url('estimate/7/abc')])->message === '<p><a href="' . site_url('proposal/42/abc') . '">Proposal</a> Estimate Contract Invoice</p>', 'only the document belonging to the delivery template is linked');
// The send modal allows an administrator to replace the stored template body.
class emails_model { function get($where, $mode) { return fixture($where['slug'], '<p>Stored proposal</p>'); } }
$ci->emails_model = new emails_model();
$ci->input->data = ['template_name' => 'proposal-send-to-customer', 'email_template_custom' => '<p>My custom <em>proposal</em> is ready.</p>'];
$custom = parse_email_template(fixture('proposal-send-to-customer', 'Original'), ['{proposal_link}' => site_url('proposal/42/abc')]);
verify($custom->message === '<p>My custom <em><a href="' . site_url('proposal/42/abc') . '">proposal</a></em> is ready.</p>', 'send-modal custom message also receives the correct link');
echo "PASS sales email links; no external messages sent\n";
