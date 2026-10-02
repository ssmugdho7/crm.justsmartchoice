<?php
// Exercise the real TCPDF engine and proposal views without an application or DB.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__).'/application/');
define('FCPATH', getenv('SC_PDF_QA_ASSETS') ?: dirname(__DIR__).'/');
define('ENVIRONMENT', 'development');
define('CUSTOM_PDF_MODULE', 'custom_pdf');
define('APP_PDF_MARGIN_LEFT', 15); define('APP_PDF_MARGIN_RIGHT', 15); define('APP_PDF_MARGIN_TOP', 35); define('APP_PDF_MARGIN_BOTTOM', 30);
$engine=$argv[1]??'native';$scenario=$argv[2]??'double';$output=$argv[3]??sys_get_temp_dir().'/proposal-qa.pdf';
if (!str_starts_with($output, '/')) { $output = getcwd().'/'.$output; }
function &get_instance() { static $ci; if (!$ci) { $ci=(object)['lang'=>new class {public $language=['proposal_to'=>'Para','estimate_subtotal'=>'Subtotal ES'];public $last_loaded='spanish';function set_last_loaded_language($v){$this->last_loaded=$v;}},'input'=>new class{function get($key){return null;}},'load'=>new class {function library($name,$args,$alias){get_instance()->numberword=new class {function convert(){return 'One thousand dollars';}};}}]; } return $ci; }
function hooks(){ return new class {function apply_filters($name,$value){return $name==='process_pdf_signature_on_close'?false:$value;} function do_action($name,$data=[]){if($name==='pdf_close')$data['pdf_instance']->writeHTML('<p>Signature record preserved.</p>');}}; }
function get_option($name){global $scenario; $v=['pdf_font'=>'helvetica','pdf_font_size'=>10,'companyname'=>'Smart Choice Contractors USA','company_phonenumber'=>'(727) 755-3786','swap_pdf_info'=>'0']; if($name==='proposals_pdf_settings' && $scenario==='bookend-long')return json_encode(['closing_page'=>['text'=>str_repeat('<p>Additional customer guidance.</p>',80)]]); if($name==='proposals_pdf_settings' && $scenario==='artwork')return json_encode(['cover_page'=>['image'=>'cover_page.'.(getenv('SC_PDF_QA_IMAGE_EXT')?:'png')],'closing_page'=>['image'=>'closing_page.'.(getenv('SC_PDF_QA_IMAGE_EXT')?:'png')]]);return $v[$name]??'';}
function get_pdf_format($name){return ['orientation'=>'P','format'=>'LETTER'];}
function get_pdf_fonts_list(){return ['helvetica'];}
function get_custom_fields(){return [];}
function is_custom_fields_for_customers_portal(){return false;}
function format_proposal_number($id){return 'PRO-000042';}
function _l($key,$a='',$b=true){return get_instance()->lang->language[$key]??$key;}
function _d($date){return $date;}
function site_url($path=''){return 'https://portal.example/'.$path;}
function _bulk_pdf_export_maybe_tag(){}
function active_clients_theme(){return 'smartchoice';}
function module_views_path($module,$path){return dirname(__DIR__).'/modules/'.$module.'/views/'.$path;}
function pdf_logo_url(){return '<span style="font-size:18pt;color:#0e6f5b"><b>SMART CHOICE</b></span>';}
function format_organization_info(){return 'Smart Choice Contractors USA<br />Tampa Bay, Florida';}
function format_proposal_info($proposal,$for){return htmlspecialchars($proposal->proposal_to).'<br />test-customer@example.com';}
function get_currency(){return 'USD';}
function app_format_money($n,$c){return '$'.number_format((float)$n,2);}
function app_format_number($n){return number_format((float)$n,2);}
function is_sale_discount_applied(){return true;}
function is_sale_discount($p,$type){return $type==='percent';}
function getPdfOptions($type,$section,$field){$settings=json_decode(get_option($type.'_pdf_settings'),true);return $settings[$section][$field]??'';}
function custom_pdf_uploaded_image_path($folder,$image){$path=FCPATH.'uploads/custom_pdf/'.$folder.'/'.basename($image);return $image && is_file($path)?$path:'';}
function parsePDFMergeFields($type,$text,$data){return $text;}
function get_items_table_data($p,$type,$for){return new class {function set_headings($v){return $this;} function table(){global $scenario;$html='<table border="1" cellpadding="7"><thead><tr style="background-color:#0e6f5b;color:#ffffff"><th width="60%">Description</th><th width="15%">Quantity</th><th width="25%">Amount</th></tr></thead><tbody>';for($i=1;$i<=($scenario==='long'?70:3);$i++)$html.='<tr><td>Roofing installation - item '.$i.'</td><td>1</td><td>$1,000.00</td></tr>';return $html.'</tbody></table>';}function taxes(){return [['taxname'=>'Sales tax','taxrate'=>7,'total_tax'=>189]];}};}
function customPdfItemsTableData($p,$type,$for){return get_items_table_data($p,$type,$for);}
function sc_append_sale_attachments_to_pdf($pdf,$type,$id){$pdf->AddPage();$pdf->writeHTML('<h1>Attachment appendix</h1><p>Customer-visible attachment remains before closing.</p>');}
require APPPATH.'vendor/tecnickcom/tcpdf/tcpdf.php';
require $engine==='custom'?dirname(__DIR__).'/modules/custom_pdf/libraries/pdf/Proposal_pdf.php':APPPATH.'libraries/pdf/Proposal_pdf.php';
$content=['single'=>'<h2>Scope of work</h2>{proposal_items}','double'=>'<h2>Scope of work</h2>{{proposal_items}}','missing'=>'<h2>Scope of work</h2><p>Replace the existing roofing system.</p>','duplicate'=>'{{proposal_items}}<p>Summary follows.</p>{proposal_items}','long'=>'<h2>Scope of work</h2>{{ proposal_items }}','bookend-long'=>'<h2>Scope of work</h2>{{proposal_items}}','artwork'=>'<h2>Scope of work</h2>{{proposal_items}}'];
$p=(object)['id'=>42,'hash'=>'fixture-only','rel_id'=>42,'rel_type'=>'customer','subject'=>'Roof replacement and project protection','proposal_to'=>'Test Customer','date'=>'2026-10-02','open_till'=>'2026-11-02','total'=>2889,'subtotal'=>3000,'currency'=>1,'currency_name'=>'USD','content'=>$content[$scenario],'project_id'=>'','show_quantity_as'=>1,'discount_percent'=>10,'discount_total'=>300,'adjustment'=>0,'clientnote'=>'We will coordinate the installation schedule with you.','terms'=>'Please review the scope before approving.'];
$original=get_instance()->lang->language;
$pdf=new Proposal_pdf($p);$pdf->prepare();$pdf->Output($output,'F');
if(get_instance()->lang->language!==$original||get_instance()->lang->last_loaded!=='spanish')throw new RuntimeException('PDF generation changed the application language');
if($p->content!==$content[$scenario])throw new RuntimeException('PDF generation mutated the source proposal');
if (!is_file($output) || filesize($output) < 1000) throw new RuntimeException('PDF file was not written');
echo 'PASS: '.$engine.' '.$scenario.' generated; language and source proposal restored' . PHP_EOL;
