<?php
// Real TCPDF and native/Custom PDF/StyleFlow sales document views; synthetic records only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(function ($level, $message, $file, $line) { if (error_reporting() & $level) { throw new ErrorException($message, 0, $level, $file, $line); } return false; });
define('BASEPATH', __DIR__); define('APPPATH', dirname(__DIR__).'/application/'); define('FCPATH', rtrim(getenv('SC_SALES_PDF_QA_ASSETS') ?: dirname(__DIR__), '/').'/'); define('ENVIRONMENT','testing'); define('CUSTOM_PDF_MODULE','custom_pdf'); define('APP_MODULES_PATH', dirname(__DIR__) . '/modules/');
$engine=$argv[1]??'native'; $type=$argv[2]??'estimate'; $scenario=$argv[3]??'empty';
$output=$argv[4]??sys_get_temp_dir().'/sales-'.$engine.'-'.$type.'-'.$scenario.'.pdf';
if (!in_array($engine,['native','custom','styleflow'],true)||!in_array($type,['estimate','invoice','contract','payment'],true)||!in_array($scenario,['empty','long','configured','missing','image','cover-only','closing-only','bookend-long','foreign','legacy','missing-cover','inactive','signed','paid','cancelled','fallback'],true))throw new RuntimeException('Unknown fixture');
if($engine==='styleflow'&&in_array($type,['contract','payment'],true))throw new RuntimeException('StyleFlow does not provide this renderer');
$options=['companyname'=>'Smart Choice Contractors USA','pdf_font'=>'helvetica','pdf_font_size'=>10,'show_page_number_on_pdf'=>1];
$settings=[];
if(in_array($scenario,['configured','cover-only','closing-only','bookend-long','inactive'],true)){
    if($scenario!=='closing-only')$settings['cover_page']=['text'=>'<h1>'.ucfirst($type).'</h1><p>Configured cover {document_number}</p>'];
    if($scenario!=='cover-only')$settings['closing_page']=['text'=>'<h1>Thank you.</h1><p>Configured closing {document_number}</p>'.($scenario==='bookend-long'?str_repeat('<p>Additional customer guidance.</p>',80):'')];
}
if(in_array($type,['contract','payment'],true)&&in_array($scenario,['configured','inactive'],true))foreach(['cover_page','closing_page','header','footer'] as $section){$settings[$section]['text']=($section==='cover_page'?'<h1>'.ucfirst($type).'</h1>':($section==='closing_page'?'<h1>Thank you.</h1>':'')).'<p>Configured '.$section.' {document_number} {customer_name}</p>';}
if(in_array($scenario,['image','missing','foreign'],true))foreach(['cover_page','closing_page'] as $section)$settings[$section]['image']=$scenario==='foreign'?'https://127.0.0.1:1/never-fetch.png':($scenario==='missing'?'does-not-exist.png':$section.'.png');
if($scenario==='missing-cover')$settings=['cover_page'=>['image'=>'absent-cover.png'],'closing_page'=>['image'=>'closing_page.png']];
if($scenario==='legacy'){ $options[$type.'_pdf_cover_image']='uploads/custom_pdf/'.$type.'/cover_page.png';$options[$type.'_pdf_end_image']='uploads/custom_pdf/'.$type.'/closing_page.png'; }
function get_option($key) { global $options,$settings,$type; return $key===$type.'_pdf_settings'?json_encode($settings):($options[$key]??''); }
function fixture_getPdfOptions($type,$section,$field) { global $settings; return $settings[$section][$field]??''; }
function fixture_custom_pdf_uploaded_image_path($type,$image) { if (getenv('SC_PROPOSAL_QA_ASSET_DIR')) { $path=rtrim(getenv('SC_PROPOSAL_QA_ASSET_DIR'),'/').'/'.basename($image); return is_file($path)?$path:''; } return $image==='logo.png'?FCPATH.'assets/images/logo.png':''; }
if(!in_array($type,['contract','payment'],true)){function getPdfOptions(...$args){return fixture_getPdfOptions(...$args);}function custom_pdf_uploaded_image_path(...$args){return fixture_custom_pdf_uploaded_image_path(...$args);}}elseif($scenario!=='inactive'){require dirname(__DIR__).'/modules/custom_pdf/helpers/custom_pdf_helper.php';}
if($scenario!=='inactive'&&!in_array($type,['contract','payment'],true)){function parsePDFMergeFields($type,$text,$document) { return str_replace('{document_number}',strtoupper($type).'-TEST-001',$text); }}
function hooks() { static $h; return $h??($h=new class { function apply_filters($key,$value,...$args) { return $value; } function do_action($key,...$args) {if($key==='pdf_close'){++$GLOBALS['closeHooks'];$pdf=$args[0]['pdf_instance'];$pdf->AddPage();$pdf->writeHTML('<h1>Hook appendix</h1><p>Close hook remains before closing.</p>');}} }); }
function &get_instance() { global $ci; return $ci; }
class EmptyDB {function __call($method,$args){return $this;} function result(){return [];} function table_exists(){return false;}}
$closeHooks=0;$signatureCalls=0;
$ci=(object)['app_mail_template'=>new class {public $merge_fields=[];function set_merge_fields($slug,$id){global $type;if($type==='payment'&&($slug!=='invoice_merge_fields'||$id!==31))throw new RuntimeException('Receipt merge fields used another invoice/payment-mode ID');if($type==='contract'&&($slug!=='contract_merge_fields'||$id!==12))throw new RuntimeException('Contract merge ID changed');$this->merge_fields=['{document_number}'=>strtoupper(str_replace('_merge_fields','',$slug)).'-TEST-001','{customer_name}'=>'Sample Customer'];return $this;}},'db'=>new EmptyDB(),'lang'=>new class { public $language=['proposal_to'=>'Proposal to','estimate_subtotal'=>'Subtotal']; public $last_loaded='spanish'; function set_last_loaded_language($name) {$this->last_loaded=$name;} },'input'=>new class {function get($key){return '';}},'load'=>new class {function library($name,$params=[],$alias=null){if($name==='merge_fields/contract_merge_fields')get_instance()->contract_merge_fields=new class {function format($id){return ['{C_initial}'=>'SC'];}};if($name==='app_number_to_word')get_instance()->{$alias}=new class {function convert($amount,$currency){return 'Nine thousand six hundred fifty dollars';}};} function model($name){get_instance()->{$name}=new class {function get(){return [];}};}}];
function _l($key,...$args) { global $ci; return $ci->lang->language[$key]??$key; }
function _d($date) { return $date; } function _dt($date){return $date;} function e($text){return html_escape($text);} function get_base_currency(){return (object)['name'=>'USD'];} function sc_sales_links_get($type,$id){return [['title'=>'Related customer guide','url'=>'https://example.test/guide']];} function get_upload_path_by_type($type){return dirname($GLOBALS['output']).'/signatures/';}
function load_pdf_language($language){} function get_client_default_language($id){return 'english';}
function db_prefix(){return 'tbl';} function log_activity($message){}
function get_pdf_format($key) { return ['orientation'=>'P','format'=>'LETTER']; }
function get_pdf_fonts_list() { return ['helvetica']; }
function is_custom_fields_for_customers_portal() { return false; }
function get_custom_fields(...$args) { return []; }
function _bulk_pdf_export_maybe_tag(...$args) {}
function format_estimate_number($id){return 'ESTIMATE-TEST-001';} function format_invoice_number($id){return 'INVOICE-TEST-001';}
function format_proposal_number($id) { return 'PRO-TEST-001'; }
function site_url($path) { return 'https://example.test/'.$path; }
function active_clients_theme() { return 'smartchoice'; }
function module_views_path($module,$path) { return dirname(__DIR__).'/modules/'.$module.'/views/'.$path; }
function pdf_logo_url() { return '<b>SMART CHOICE</b>'; }
function format_organization_info() { return '<b>Smart Choice Contractors USA</b><br />123 Sample Road<br />Tampa, FL'; }
function format_proposal_info($proposal,$format) { return htmlspecialchars($proposal->proposal_to).'<br />456 Example Lane<br />Tampa, FL'; }
function get_project_name_by_id($id) { return 'Residential renovation'; }
function format_customer_info($document,$type,$format){return 'Sample Customer<br />456 Example Lane<br />Tampa, FL';}
class Invoices_model {const STATUS_PAID=2;const STATUS_CANCELLED=5;} class Estimates_model {}
function found_invoice_mode(...$args){return false;} function html_purify($text){return $text;}
function get_currency($id) { return 'USD'; }
function app_format_money($amount,$currency) { return '$'.number_format($amount,2); }
function app_format_number($amount,...$args) { return (string)$amount; }
function is_sale_discount_applied($proposal) { return true; }
function is_sale_discount($proposal,$type) { return $type==='percent'; }
class FixtureItems {
 function set_headings($name) { return $this; }
 function table() {
 global $scenario;
 if($scenario==='long')return '<table border="1" cellpadding="7">'.str_repeat('<tr><td>Scope item - design and installation</td><td>$10,000.00</td></tr>',90).'</table>';
 foreach(['proposal_to','estimate_subtotal','estimate_total','estimate_table_hours_heading'] as $key) { if (strpos(_l($key),'español')!==false || strpos(_l($key),'Propuesta')!==false) { throw new RuntimeException('Spanish PDF label leaked'); } }
 return '<table border="1" cellpadding="7"><thead><tr style="background-color:#162b3c;color:#ffffff;"><th width="55%">'._l('estimate_table_item_heading').'</th><th width="20%">'._l('estimate_table_hours_heading').'</th><th width="25%">'._l('estimate_table_amount_heading').'</th></tr></thead><tbody><tr><td width="55%">Scope item - design and installation</td><td width="20%">10</td><td width="25%">$10,000.00</td></tr></tbody></table>';
 }
 function taxes() { return [['taxname'=>'Sales tax','taxrate'=>7,'total_tax'=>630]]; }
}
function get_items_table_data(...$args) { return new FixtureItems; }
function html_escape($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function styleflow_get_template($name) { return ['primary_color'=>'#162b3c','secondary_color'=>'#f2f5f7','accent_color'=>'#168984','text_color'=>'#23333f','font_family'=>'helvetica']; }
function styleflow_active_template($type) { return 'test'; }
function format_invoice_status(...$args){return 'Draft';} function format_estimate_status(...$args){return 'Draft';}
function styleflow_sanitize_color($value,$default) { return $value ?: $default; }
function styleflow_supported_fonts() { return ['helvetica'=>'Helvetica']; }
function styleflow_design_variant($tpl) { return 'modern-edge'; }
function styleflow_template_staff_id($tpl,$document) { return 0; }
function styleflow_get_items_table_data(...$args) { return new FixtureItems; }
function format_proposal_status($status,...$args) { return 'Draft'; }
function pdf_multi_row($left,$right,$pdf,$width) { $pdf->writeHTML('<table cellpadding="7"><tr><td width="50%">'.$left.'</td><td width="50%">'.$right.'</td></tr></table>'); }
function sc_append_sale_attachments_to_pdf($pdf,$type,$id){$pdf->AddPage();$pdf->writeHTML('<h1>Attachment appendix</h1><p>Customer-visible attachment remains before closing.</p>');}
function customPdfItemsTableData(...$args) { return new FixtureItems; }
require APPPATH.'vendor/tecnickcom/tcpdf/tcpdf.php';
$provider=$engine==='native'?APPPATH.'libraries/pdf/':dirname(__DIR__).'/modules/'.($engine==='custom'?'custom_pdf':'styleflow').'/libraries/pdf/';
require $provider.ucfirst($type).'_pdf.php';
trait SignatureFixture {public function processSignature(){++$GLOBALS['signatureCalls'];$this->writeHTML('<p>Signature record preserved.</p>');if(in_array($GLOBALS['type'],['contract','payment'],true))parent::processSignature();}}
if($type==='invoice'){class FixturePdf extends Invoice_pdf {use SignatureFixture;}}elseif($type==='estimate'){class FixturePdf extends Estimate_pdf {use SignatureFixture;}}elseif($type==='contract'){class FixturePdf extends Contract_pdf {use SignatureFixture;}}else{class FixturePdf extends Payment_pdf {use SignatureFixture;}}
$document=(object)['id'=>1,'clientid'=>1,'client'=>(object)['company'=>'Sample Customer'],'status'=>1,'date'=>'2026-10-07','expirydate'=>'2026-11-07','duedate'=>'2026-11-07','project_id'=>0,'sale_agent'=>0,'reference_no'=>'','hash'=>'test-only','show_quantity_as'=>1,'include_shipping'=>0,'show_shipping_on_estimate'=>0,'show_shipping_on_invoice'=>0,'currency'=>1,'currency_name'=>'USD','subtotal'=>10000,'total'=>9650,'total_left_to_pay'=>9650,'discount_percent'=>10,'discount_total'=>1000,'adjustment'=>20,'payments'=>[],'clientnote'=>'Please contact our team with questions.','terms'=>'Work begins after approval of the scope and schedule.'];
if($type==='contract'){
 $document=(object)['id'=>12,'client'=>1,'company'=>'Sample Customer','subject'=>'Residential renovation contract','datestart'=>'2026-10-07','dateend'=>'2026-11-07','contract_value'=>9650,'content'=>'<h2>Contract scope</h2><p>Prepare and install materials; payment schedule remains unchanged.</p>'.($scenario==='long'?str_repeat('<p>Scope item: site preparation, installation, cleanup and warranty.</p>',140):''),'signed'=>$scenario==='signed'?1:0,'signature'=>$scenario==='signed'?'signature.png':'','acceptance_firstname'=>'Sample','acceptance_lastname'=>'Customer','acceptance_date'=>'2026-10-07 12:00:00','acceptance_ip'=>'192.0.2.1'];
 if($scenario==='signed'){$dir=get_upload_path_by_type('contract').'12';if(!is_dir($dir))mkdir($dir,0700,true);$image=imagecreatetruecolor(180,35);imagefill($image,0,0,imagecolorallocate($image,255,255,255));imagestring($image,4,8,8,'TEST SIGNATURE',imagecolorallocate($image,0,0,0));imagepng($image,$dir.'/signature.png');imagedestroy($image);}
}
if($type==='payment'){
 $document->id=31;$document->total=12000;$document->total_left_to_pay=2350;$document->status=$scenario==='paid'?2:($scenario==='cancelled'?5:1);
 $document=(object)['id'=>7,'paymentid'=>42,'invoiceid'=>31,'invoice_data'=>$document,'date'=>'2026-10-07','name'=>'Bank transfer','paymentmethod'=>'ACH','transactionid'=>'TXN-TEST-42','amount'=>9650];
}
$lang=[];require APPPATH.'language/english/english_lang.php';$ci->lang->language=array_merge($lang,$ci->lang->language);
if($type==='payment'&&function_exists('parsePDFMergeFields')){if(parsePDFMergeFields('payment','{customer_name}',(object)['id'=>7,'invoiceid'=>31])!=='Sample Customer')throw new RuntimeException('Invoice ID fallback merge failed');}
$original=serialize($document);$pdf=new FixturePdf($document);$pdf->setCompression(false);
$pdf->prepare();$preparedPages=$pdf->getNumPages();$pdf->prepare();
if($pdf->getNumPages()!==$preparedPages)throw new RuntimeException('Repeated prepare duplicated cover or body');
if($preparedPages<2)throw new RuntimeException('Cover missing');
$bytes=$pdf->Output('','S');$pages=preg_match_all('~/Type\s*/Page\b~',$bytes);
// Close appends one hook appendix and at least one presentation closing page.
if($pages<$preparedPages+2)throw new RuntimeException('Closing page missing');
$pdf->Close();if($signatureCalls!==1||$closeHooks!==1)throw new RuntimeException('Repeated close duplicated signature or hooks');
if(serialize($document)!==$original)throw new RuntimeException('Source financial record changed');
if(!in_array($type,['contract','payment'],true)&&(strpos($bytes,'$9,650.00')===false||strpos($bytes,'$1,000.00')===false||strpos($bytes,'$630.00')===false))throw new RuntimeException('Totals, discount or taxes changed/missing');
file_put_contents($output,$bytes);echo "PASS $engine/$type/$scenario: $pages pages; cover, closing, totals, signature, hook and source preserved\n";
