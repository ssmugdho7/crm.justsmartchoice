<?php
// Real TCPDF and production proposal exports with synthetic CRM fixtures only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(function ($level, $message, $file, $line) { if (error_reporting() & $level) { throw new ErrorException($message, 0, $level, $file, $line); } return false; });
define('BASEPATH', __DIR__); define('APPPATH', dirname(__DIR__).'/application/'); define('FCPATH', dirname(__DIR__).'/'); define('ENVIRONMENT','testing'); define('CUSTOM_PDF_MODULE','custom_pdf'); define('APP_MODULES_PATH', FCPATH . 'modules/');
$engine=$argv[1]??'native'; $scenario=$argv[2]??'short'; $output=$argv[3]??sys_get_temp_dir().'/proposal-'.$engine.'-'.$scenario.'.pdf';
if (!in_array($engine,['native','custom','styleflow']) || !in_array($scenario,['short','long','configured','image'])) { throw new RuntimeException('Unknown scenario'); }
$options=['companyname'=>'Smart Choice Contractors USA','pdf_font'=>'helvetica','pdf_font_size'=>10,'show_project_on_proposal'=>1,'show_page_number_on_pdf'=>1]; $settings=[];
if ($scenario==='configured') { $settings=['cover_page'=>['text'=>'<h1>Proposal</h1><p>Configured cover</p>'],'closing_page'=>['text'=>'<h1>Thank you.</h1><p>Configured closing</p>']]; }
if ($scenario==='image') { $settings=['cover_page'=>['image'=>getenv('SC_PROPOSAL_QA_ASSET_DIR')?'crm-existing-cover.png':'logo.png'],'closing_page'=>['image'=>getenv('SC_PROPOSAL_QA_ASSET_DIR')?'crm-existing-closing.png':'logo.png']]; }
function get_option($key) { global $options,$settings; return $key==='proposals_pdf_settings'?json_encode($settings):($options[$key]??''); }
function getPdfOptions($type,$section,$field) { global $settings; return $settings[$section][$field]??''; }
function custom_pdf_uploaded_image_path($type,$image) { if (getenv('SC_PROPOSAL_QA_ASSET_DIR')) { $path=rtrim(getenv('SC_PROPOSAL_QA_ASSET_DIR'),'/').'/'.basename($image); return is_file($path)?$path:''; } return $image==='logo.png'?FCPATH.'assets/images/logo.png':''; }
function parsePDFMergeFields($type,$text,$proposal) { return $text; }
function hooks() { static $h; return $h??($h=new class { function apply_filters($key,$value,...$args) { return $key==='process_pdf_signature_on_close'?false:$value; } function do_action($key,...$args) {} }); }
function &get_instance() { global $ci; return $ci; }
$ci=(object)['lang'=>new class { public $language=['proposal_to'=>'Propuesta para','estimate_subtotal'=>'Subtotal en español']; public $last_loaded='spanish'; function set_last_loaded_language($name) {$this->last_loaded=$name;} },'input'=>new class {function get($key){return '';}},'load'=>new class {function library(...$args){}}];
function _l($key,...$args) { global $ci; return $ci->lang->language[$key]??$key; }
function _d($date) { return $date; }
function get_pdf_format($key) { return ['orientation'=>'P','format'=>'LETTER']; }
function get_pdf_fonts_list() { return ['helvetica']; }
function is_custom_fields_for_customers_portal() { return false; }
function get_custom_fields(...$args) { return []; }
function _bulk_pdf_export_maybe_tag(...$args) {}
function format_proposal_number($id) { return 'PRO-TEST-001'; }
function site_url($path) { return 'https://example.test/'.$path; }
function active_clients_theme() { return 'smartchoice'; }
function module_views_path($module,$path) { return FCPATH.'modules/'.$module.'/views/'.$path; }
function pdf_logo_url() { return '<b>SMART CHOICE</b>'; }
function format_organization_info() { return '<b>Smart Choice Contractors USA</b><br />123 Sample Road<br />Tampa, FL'; }
function format_proposal_info($proposal,$format) { return htmlspecialchars($proposal->proposal_to).'<br />456 Example Lane<br />Tampa, FL'; }
function get_project_name_by_id($id) { return 'Residential renovation'; }
function get_currency($id) { return 'USD'; }
function app_format_money($amount,$currency) { return '$'.number_format($amount,2); }
function app_format_number($amount,...$args) { return (string)$amount; }
function is_sale_discount_applied($proposal) { return true; }
function is_sale_discount($proposal,$type) { return $type==='percent'; }
class FixtureItems {
 function set_headings($name) { return $this; }
 function table() {
 foreach(['proposal_to','estimate_subtotal','estimate_total','estimate_table_hours_heading'] as $key) { if (strpos(_l($key),'español')!==false || strpos(_l($key),'Propuesta')!==false) { throw new RuntimeException('Spanish PDF label leaked'); } }
 return '<table border="1" cellpadding="7"><thead><tr style="background-color:#162b3c;color:#ffffff;"><th width="55%">'._l('estimate_table_item_heading').'</th><th width="20%">'._l('estimate_table_hours_heading').'</th><th width="25%">'._l('estimate_table_amount_heading').'</th></tr></thead><tbody><tr><td width="55%">Scope item - design and installation</td><td width="20%">10</td><td width="25%">$10,000.00</td></tr></tbody></table>';
 }
 function taxes() { return [['taxname'=>'Sales tax','taxrate'=>7,'total_tax'=>630]]; }
}
function get_items_table_data(...$args) { return new FixtureItems; }
function html_escape($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function styleflow_get_template($name) { return ['primary_color'=>'#162b3c','secondary_color'=>'#f2f5f7','accent_color'=>'#168984','text_color'=>'#23333f','font_family'=>'helvetica']; }
function styleflow_active_template($type) { return 'test'; }
function styleflow_sanitize_color($value,$default) { return $value ?: $default; }
function styleflow_supported_fonts() { return ['helvetica'=>'Helvetica']; }
function styleflow_design_variant($tpl) { return 'modern-edge'; }
function styleflow_template_staff_id($tpl,$document) { return 0; }
function styleflow_get_items_table_data(...$args) { return new FixtureItems; }
function format_proposal_status($status,...$args) { return 'Draft'; }
function pdf_multi_row($left,$right,$pdf,$width) { $pdf->writeHTML('<table cellpadding="7"><tr><td width="50%">'.$left.'</td><td width="50%">'.$right.'</td></tr></table>'); }
function customPdfItemsTableData(...$args) { return new FixtureItems; }
require FCPATH.'application/vendor/tecnickcom/tcpdf/tcpdf.php';
require $engine==='native'?APPPATH.'libraries/pdf/Proposal_pdf.php':FCPATH.'modules/'.($engine==='custom'?'custom_pdf':$engine).'/libraries/pdf/Proposal_pdf.php';
foreach(['{proposal_items}','{{proposal_items}}','{{ proposal_items }}','{PROPOSAL_ITEMS}'] as $token) { if (sc_proposal_items_content('<p>'.$token.'</p>','ITEM-TABLE')!=='<p>ITEM-TABLE</p>') { throw new RuntimeException('Bad placeholder: '.$token); } }
if(substr_count(sc_proposal_items_content('{{proposal_items}} {proposal_items}','ITEM-TABLE'),'ITEM-TABLE')!==1) {throw new RuntimeException('Duplicate table');}
if(strpos(sc_proposal_items_content('Scope','ITEM-TABLE'),'ITEM-TABLE')===false) {throw new RuntimeException('Missing table fallback');}
$body='<h2>Scope of work</h2><p>Prepare the site, complete the planned installation and leave the work area clean.</p>{{proposal_items}}';
if($scenario==='long') {$body.=str_repeat('<h3>Project detail</h3><p>Materials, installation, site preparation and quality checks are included in the proposed scope. Our team coordinates scheduling and communicates project milestones.</p>',40);}
$proposal=(object)['id'=>1,'rel_id'=>1,'status'=>1,'rel_type'=>'customer','subject'=>'Residential renovation proposal','proposal_to'=>'Sample Customer','date'=>'2026-10-02','open_till'=>'2026-11-02','project_id'=>1,'hash'=>'test-only','show_quantity_as'=>2,'currency'=>1,'currency_name'=>'USD','subtotal'=>10000,'total'=>9650,'discount_percent'=>10,'discount_total'=>1000,'adjustment'=>20,'content'=>$body,'clientnote'=>'Please contact our team with questions.','terms'=>'Work begins after approval of the scope and schedule.'];
$beforeLanguage=$ci->lang->language; $pdf=new Proposal_pdf($proposal); $pdf->setCompression(false); $pdf->prepare(); $pages=$pdf->getNumPages();
if($pages<2) {throw new RuntimeException('No separate cover');}
$bytes=$pdf->Output('','S');
$pageCount=preg_match_all('~/Type\s*/Page\b~', $bytes);
if($pageCount!==$pages+1) {throw new RuntimeException('Closing page missing or duplicated');}
$pdf->Close();
file_put_contents($output,$bytes);
if($ci->lang->language!==$beforeLanguage || $ci->lang->last_loaded!=='spanish') {throw new RuntimeException('UI language not restored');}
if($proposal->content!==$body) {throw new RuntimeException('Source proposal mutated');}
if(strpos($bytes,'{{proposal_items}}')!==false || strpos($bytes,'{proposal_items}')!==false) {throw new RuntimeException('Placeholder leaked');}
echo "PASS $engine/$scenario: cover, closing, English, tokens, source preservation; body pages=".($pages-1)."\n";
