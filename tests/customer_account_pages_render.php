<?php
// Customer view checks without application bootstrap, database access, or form submission.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function _l($v) { return $v; }
function site_url($v='') { return 'https://portal.example/'.$v; }
function get_option($v) { global $flags; return $flags[$v] ?? 0; }
function is_primary_contact() { global $primary; return $primary; }
function is_language_disabled() { global $languageDisabled; return $languageDisabled; }
function is_empty_customer_company($v) { return false; }
function set_value($key,$default='') { return e($default); }
function set_select($key,$id,$selected) { return $selected ? 'selected' : ''; }
function form_error($key) { return ''; }
function clear_textarea_breaks($v) { return e($v); }
function get_all_countries() { return [['country_id'=>1,'short_name'=>'United States'],['country_id'=>2,'short_name'=>'Canada']]; }
function form_open_multipart($url,$attrs) { return '<form action="'.site_url($url).'" id="'.$attrs['id'].'" method="post" enctype="multipart/form-data"><input name="csrf_test" type="hidden" value="existing-token">'; }
function form_open($url,$attrs) { return '<form action="'.$url.'" method="'.$attrs['method'].'" id="'.$attrs['id'].'">'; }
function form_hidden($key,$v) { return '<input name="'.$key.'" type="hidden" value="'.$v.'">'; }
function form_close() { return '</form>'; }
function render_custom_fields($type,$id,$scope) { if ($scope !== ['show_on_client_portal'=>1]) throw new RuntimeException('Custom field scope'); return '<label for="custom77">Birthday</label><input id="custom77" name="custom_fields[customers][77]" value="2020-01-01">'; }
function get_clients_area_tickets_summary($statuses) { return $statuses; }
function has_contact_permission($permission) { return $permission === 'support'; }
function hooks() { return new class { function do_action($key) {} }; }
function get_template_part($name,$data=[]) { global $tableRenders; if ($name === 'tickets_table') { $tableRenders++; echo '<table class="dt-table"><tbody><tr><td>Existing ticket</td></tr></tbody></table>'; } else { extract($data); include dirname(__DIR__).'/application/views/themes/smartchoice/template_parts/'.$name.'.php'; } }
function verify($ok,$msg) { if (!$ok) throw new RuntimeException($msg); }
function document($html) { $d=new DOMDocument(); @$d->loadHTML($html); return new DOMXPath($d); }
class AccountViewFixture {
    public $app;
    public $input;
    function __construct() { $this->app = new class { function get_available_languages() { return ['english','spanish']; } }; $this->input = new class { function get($key,$clean) { return '"><script>unsafe</script>'; } }; }
    function render($name,$vars=[]) { extract($vars); ob_start(); include dirname(__DIR__).'/application/views/themes/smartchoice/'.$name; return ob_get_clean(); }
}
$fixture=new AccountViewFixture();
$client=(object) array_fill_keys(['company','vat','phonenumber','website','city','address','zip','state','billing_street','billing_city','billing_state','billing_zip','shipping_street','shipping_city','shipping_state','shipping_zip'],'Existing <data>');
$client->userid=42; $client->country=$client->billing_country=$client->shipping_country=1; $client->default_language='english';
for ($mask=0;$mask<16;$mask++) {
    $primary=(bool)($mask&1); $languageDisabled=(bool)($mask&2); $flags=['company_requires_vat_number_field'=>($mask&4)?1:0,'allow_primary_contact_to_view_edit_billing_and_shipping'=>($mask&8)?1:0];
    $html=$fixture->render('views/company_profile.php',['client'=>$client,'contact'=>(object)['is_primary'=>$primary?1:0]]); $x=document($html);
    verify($x->query('//form[@id="company-profile-form" and @method="post" and @enctype="multipart/form-data" and @action="https://portal.example/clients/company"]')->length===1,'Keep native company form');
    verify($x->query('//input[@name="csrf_test"]')->length===1 && $x->query('//input[@name="company_form" and @value="1"]')->length===1,'Keep CSRF and form trigger');
    verify($x->query('//button[@type="submit"]')->length===($primary?1:0),'Primary contact save guard');
    verify($x->query('//input[@name="vat"]')->length===($flags['company_requires_vat_number_field']?1:0),'VAT feature guard');
    verify($x->query('//select[@name="default_language"]')->length===($languageDisabled?0:1),'Language feature guard');
    verify($x->query('//textarea[@name="billing_street"]')->length===($primary&&$flags['allow_primary_contact_to_view_edit_billing_and_shipping']?1:0),'Billing contact permission guard');
    verify($x->query('//input[@name="custom_fields[customers][77]"]')->length===1,'Preserve custom fields');
    foreach (['company','phonenumber','website','city','zip','state'] as $name) verify($x->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('value')==='Existing <data>','Keep stored field '.$name);
    foreach (['company','country'] as $id) verify($x->query('//label[@for="'.$id.'"]')->length===1 && $x->query('//*[@id="'.$id.'"]')->length===1,'Correct label association '.$id);
}
foreach ([[],[['ticketid'=>14]]] as $tickets) {
    $tableRenders=0; $list_statuses=[1]; $statuses=[['url'=>site_url('clients/tickets/1'),'ticketstatusid'=>1,'statuscolor'=>'#165c45','translated_name'=>'Open','total_tickets'=>count($tickets)]];
    $html=$fixture->render('views/tickets.php',['tickets'=>$tickets,'ticket_statuses'=>$statuses,'list_statuses'=>$list_statuses]); $x=document($html);
    verify($x->query('//a[@href="https://portal.example/clients/tickets/1"]')->length===1,'Keep status filter route');
    verify($x->query('//a[contains(@class,"new-ticket") and @href="https://portal.example/clients/open_ticket"]')->length===1,'Keep ticket creation route');
    verify($tableRenders===(count($tickets)?1:0),'Populated table retained, empty state avoids duplicate empty table');
}
$articles=[['group_slug'=>'existing-category','name'=>'<unsafe>','description'=>'Helpful description','articles'=>[[],[]]]];
$html=$fixture->render('template_parts/knowledge_base/categories.php',['articles'=>$articles]); $x=document($html);
verify(strpos($html,'<unsafe>')===false && strpos($html,'&lt;unsafe&gt;')!==false,'Category names escaped');
verify($x->query('//a[@href="https://portal.example/knowledge-base/category/existing-category"]')->length===2,'Keep category routes');
verify($x->query('//span[@aria-label="2 articles"]')->length===1,'Article count retained and labelled');
$html=$fixture->render('template_parts/knowledge_base/search.php'); $x=document($html);
verify($x->query('//form[@method="GET" and @action="https://portal.example/knowledge-base/search"]')->length===1,'Keep search endpoint and GET');
verify($x->query('//input[@name="q"]')->item(0)->getAttribute('value')==='"><script>unsafe</script>','Preserve and escape query');
verify($x->query('//script')->length===0,'Query cannot inject scripts');
echo "PASS 16 company permission/feature combinations, populated/empty support, category links/counts and search payload\n";
