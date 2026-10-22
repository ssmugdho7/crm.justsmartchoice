<?php
// Presentation checks only: no authentication, customer records or database connection.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function _l($key) { return $key; }
function admin_url($path = '') { return 'https://crm.example/admin/' . $path; }
function get_option($key) { return 'Smart Choice <script>unsafe</script>'; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

class SalesWorkspaceFixture
{
    public $app_menu;
    public $router;
    public function render(array $menu, string $section): string
    {
        $this->app_menu = new class($menu) {
            private $menu;
            public function __construct($menu) { $this->menu = $menu; }
            public function get_sidebar_menu_items() { return $this->menu; }
        };
        $this->router = new class($section) {
            private $section;
            public function __construct($section) { $this->section = $section; }
            public function fetch_class() { return $this->section; }
        };
        ob_start();
        include dirname(__DIR__) . '/application/views/admin/includes/sales_workspace.php';
        return ob_get_clean();
    }
}

$sections = ['proposals', 'estimates', 'invoices', 'payments', 'credit_notes'];
$fixture = new SalesWorkspaceFixture();
for ($mask = 0; $mask < 32; $mask++) {
    $children = [];
    foreach ($sections as $index => $section) {
        if ($mask & (1 << $index)) $children[] = ['href' => admin_url($section), 'name' => '<unsafe>'];
    }
    // Ignore unrelated routes, external lookalikes, hash placeholders, and duplicate allowed links.
    $menu = [['children' => array_merge($children, [
        ['href' => admin_url('contracts')], ['href' => 'https://untrusted.example/admin/invoices'], ['href' => '#'],
        ['children' => $children],
    ])]];
    foreach ($sections as $active) {
        $html = $fixture->render($menu, ucfirst($active));
        $dom = new DOMDocument(); @$dom->loadHTML($html); $x = new DOMXPath($dom);
        $expected = substr_count(decbin($mask), '1');
        check($x->query('//nav/a')->length === $expected, 'Navigation must match permission-filtered sidebar exactly');
        check($x->query('//nav')->length === ($expected ? 1 : 0), 'No empty navigation landmark');
        check(strpos($html, '<script>') === false, 'Company name must be escaped');
        foreach ($sections as $index => $section) {
            $nodes = $x->query('//nav/a[@href="' . admin_url($section) . '"]');
            $allowed = (bool) ($mask & (1 << $index));
            check($nodes->length === ($allowed ? 1 : 0), 'Do not introduce unavailable Sales links');
            if ($allowed) check($nodes->item(0)->hasAttribute('aria-current') === ($section === $active), 'Correct accessible active section');
        }
    }
}
echo "PASS 160 Sales navigation variants: permission filtering, duplicate handling, active section and escaping\n";

// Render the actual financial template with deliberately different amounts, currencies and year filters.
function app_format_money($value, $currency) { return $currency . ' ' . $value; }
class SalesFinancialFixture
{
    public $input;
    public function render(bool $badge): string
    {
        $this->input = new class($badge) {
            private $badge;
            public function __construct($badge) { $this->badge = $badge; }
            public function post($key) { return $key === 'display_type' ? ($this->badge ? 'badge' : '') : ($key === 'years' ? ['2024'] : null); }
        };
        $invoices_years = [['year'=>'2024'], ['year'=>'2025']];
        $invoices_total_currencies = [['id'=>2, 'name'=>'EUR', 'symbol'=>'€', 'isdefault'=>1]];
        $total_result = ['due'=>'1234.56', 'overdue'=>'45.67', 'paid'=>'9876.54', 'currency'=>'EUR'];
        ob_start();
        include dirname(__DIR__) . '/application/views/admin/invoices/invoices_total_template.php';
        return ob_get_clean();
    }
}
$fixture = new SalesFinancialFixture();
foreach ([false, true] as $badge) {
    $html = $fixture->render($badge);
    foreach (['EUR 1234.56', 'EUR 45.67', 'EUR 9876.54'] as $value) check(strpos($html, $value) !== false, 'Use current totals, never screenshot sample amounts');
    $dom = new DOMDocument(); @$dom->loadHTML($html); $x = new DOMXPath($dom);
    foreach (['total_currency', 'invoices_total_years'] as $name) check($x->query('//select[@name="'.$name.'" and @onchange="init_invoices_total();"]')->length === 1, 'Keep currency and year filter handlers');
    check($x->query('//select[@name="invoices_total_years"]/option[@value="2024" and @selected]')->length === 1, 'Keep selected years');
    check($x->query('//div[contains(@class,"sales-financial-stat-content")]')->length === ($badge ? 3 : 0), 'Only badge mode gets ribbon presentation');
}
echo "PASS financial summary variants: live totals, currency/year selectors and existing handlers retained\n";

function is_rtl() { return false; }
function base_url($path) { return 'https://crm.example/' . $path; }
function app_compile_css() { return ''; }
function render_admin_js_variables() {}
function get_proposal_templates() { return []; }
function get_contract_templates() { return []; }
function app_admin_head() {}
function admin_body_class($classes) { echo 'class="' . e($classes) . '"'; }
function hooks() { return new class { public function do_action($name) {} }; }
class SalesHeadFixture
{
    public $router;
    public function render(string $controller): string
    {
        $this->router = new class($controller) {
            private $controller;
            public function __construct($controller) { $this->controller = $controller; }
            public function fetch_class() { return $this->controller; }
        };
        $locale = 'en'; $title = 'Example'; $bodyclass = 'native-existing-class';
        $current_user = (object) ['total_unread_notifications'=>0];
        ob_start(); include dirname(__DIR__) . '/application/views/admin/includes/head.php'; return ob_get_clean();
    }
}
$fixture = new SalesHeadFixture();
foreach (array_merge($sections, ['dashboard', 'staff', 'contracts', 'clients']) as $controller) {
    $html = $fixture->render(ucfirst($controller));
    $dom = new DOMDocument(); @$dom->loadHTML($html); $x = new DOMXPath($dom);
    $sales = in_array($controller, $sections, true);
    check($x->query('//link[contains(@href,"assets/css/sales-custom.css")]')->length === ($sales ? 1 : 0), 'Sales asset must stay off other controllers');
    $classes = $x->query('//body')->item(0)->getAttribute('class');
    check(strpos($classes, 'native-existing-class') !== false, 'Preserve existing body classes and handlers');
    check((strpos($classes, 'sc-sales-workspace-page') !== false) === $sales, 'Only Sales pages get scoped presentation');
}
echo "PASS 9 admin controller variants: scoped assets and existing body classes preserved\n";

function staff_can($capability, $feature) { global $paymentEdit, $paymentDelete; return $capability === 'edit' ? $paymentEdit : ($capability === 'delete' ? $paymentDelete : true); }
function staff_cant($capability, $feature) { return !staff_can($capability, $feature); }
function db_prefix() { return 'tbl'; }
function get_sql_select_client_company() { return 'company'; }
function icon_btn(...$args) { return ''; }
function sc_bulk_checkbox_html(...$args) { return '<input type="checkbox">'; }
function format_invoice_number($id) { return 'INV-' . $id; }
function _d($value) { return $value; }
function data_tables_init(...$args) { return ['output'=>['aaData'=>[]], 'rResult'=>[['id'=>12,'invoiceid'=>8,'payment_mode_name'=>'Bank','paymentmodeid'=>1,'paymentmethod'=>'','paymentmode'=>1,'transactionid'=>'<untrusted>','clientid'=>2,'company'=>'<company>','amount'=>'77.23','currency_name'=>'EUR','date'=>'2026-10-22']]]; }
class PaymentRowFixture
{
    public $ci;
    public function render(): array
    {
        $this->ci = (object) [
            'load'=>new class { public function model($name) {} },
            'payment_modes_model'=>new class { public function get_payment_gateways($active) { return []; } },
        ];
        $clientid = '';
        include dirname(__DIR__) . '/application/views/admin/tables/payments.php';
        return $output['aaData'][0];
    }
}
foreach ([false, true] as $paymentEdit) foreach ([false, true] as $paymentDelete) {
    $row = (new PaymentRowFixture())->render();
    $dom = new DOMDocument(); @$dom->loadHTML($row[0]); $x = new DOMXPath($dom);
    check($x->query('//a[@href="'.admin_url('payments/payment/12').'" and contains(@class,"row-btn-view")]')->length === 1, 'Keep payment receipt View route');
    check($x->query('//a[@href="'.admin_url('payments/payment/12#tab_payment').'"]')->length === ($paymentEdit ? 1 : 0), 'Payment Edit shortcut requires existing edit permission');
    check($x->query('//a[contains(@class,"_delete")]')->length === ($paymentDelete ? 1 : 0), 'Preserve delete permission');
    check(strpos($row[3], '<untrusted>') === false && strpos($row[3], '&lt;untrusted&gt;') !== false, 'Keep transaction ID escaped');
    check(strip_tags($row[5]) === 'EUR 77.23', 'Payment amount and currency unchanged');
}
echo "PASS 4 payment permission variants: View/Edit/Delete routes, escaping and amounts preserved\n";
