<?php
define('BASEPATH', __DIR__);
define('RECRUITMENT_MODULE_UPLOAD_FOLDER', sys_get_temp_dir().'/recruitment-repair-fixture-'.getmypid());
class FixtureHooks {
    function add_action(...$args){}
    function apply_filters($name,$value){return $name==='merge_field_logo_img_width'?recruitment_email_logo_width($value):$value;}
}
function hooks(){static $hooks;return $hooks??=new FixtureHooks;}
function get_option($name){if($name==='smart_choice_recruitment_w4_url')return $GLOBALS['fixture_w4_url']??'';return ['company_logo'=>'logo.png','companyname'=>'Fixture Company','email_signature'=>'Regards'][$name]??'';}
function site_url($path=''){return 'https://crm.example.test/'.$path;}
function base_url($path=''){return site_url($path);}
function current_url(){return site_url('recruitment/forms/application');}
function admin_url($path=''){return site_url('admin/'.$path);}
function e($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function html_escape($value){return e($value);}
function is_staff_logged_in(){return false;}
function is_client_logged_in(){return false;}
function is_html($value){return false;}
function terms_url(){return site_url('terms-and-conditions');}
function privacy_policy_url(){return site_url('privacy-policy');}
class App_merge_fields {}
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
$root=dirname(__DIR__);
require $root.'/modules/recruitment/helpers/recruitment_helper.php';
require $root.'/application/libraries/merge_fields/Other_merge_fields.php';
$GLOBALS['language']='english';
check(recruitment_form_action_url()===current_url().'?lang=english','English form action preserves its displayed language');
$GLOBALS['language']='spanish';
check(recruitment_form_action_url()===current_url().'?lang=spanish','Spanish form action preserves its displayed language');
$GLOBALS['language']='unexpected';
check(recruitment_form_action_url()===current_url().'?lang=english','Unexpected form language safely defaults to English');
unset($GLOBALS['language']);
check(recruitment_w4_url('spanish')==='https://www.irs.gov/pub/irs-pdf/fw4sp.pdf','Spanish selects the official Spanish W-4');
check(recruitment_w4_url('english')==='https://www.irs.gov/pub/irs-pdf/fw4.pdf','English has an official English fallback');
$GLOBALS['fixture_w4_url']='https://crm.example.test/english-w4.pdf';
check(recruitment_w4_url('english')===$GLOBALS['fixture_w4_url'],'Configured English form is preserved');
check(recruitment_w4_url('spanish')==='https://www.irs.gov/pub/irs-pdf/fw4sp.pdf','English configuration cannot override the Spanish selection');
unset($GLOBALS['fixture_w4_url']);
$fields=(new Other_merge_fields)->format();
check(strpos($fields['{logo_image_with_url}'],'width="160"')!==false,'Actual logo merge field has an email-safe width');
check(recruitment_email_logo_width(220)===220,'Explicit logo width is preserved');
$folder=RECRUITMENT_MODULE_UPLOAD_FOLDER.'/candidate/driver_licenses/28';mkdir($folder,0700,true);file_put_contents($folder.'/fixture.pdf','fixture');
$file=(object)['rel_type'=>'rec_candidate_driver','rel_id'=>28,'file_name'=>'fixture.pdf'];
check(recruitment_candidate_document_path($file)===realpath($folder.'/fixture.pdf'),'Existing truncated attachment type resolves to the stored file');
$file->rel_type='rec_candidate_driver_license';
check(recruitment_candidate_document_path($file)===realpath($folder.'/fixture.pdf'),'Full historical attachment type remains supported');
$file->file_name='../fixture.pdf';check(recruitment_candidate_document_path($file)===false,'Path traversal is rejected');
$file->file_name='missing.pdf';check(recruitment_candidate_document_path($file)===false,'Missing file is rejected');
$file->file_name='fixture.pdf';$file->external=1;check(recruitment_candidate_document_path($file)===false,'External files cannot become local documents');
unlink($folder.'/fixture.pdf');rmdir($folder);rmdir(dirname($folder));rmdir(dirname(dirname($folder)));rmdir(RECRUITMENT_MODULE_UPLOAD_FOLDER);
class App_Model {}
require $root.'/modules/recruitment/models/Recruitment_model.php';
function _l($key){return $key==='recruitment_portal'?'Recruitment Portal':$key;}
$captured=null;$mailer=new class {public $body;function send_simple_email($to,$subject,$body){$this->body=$body;}};
$model=(new ReflectionClass(Recruitment_model::class))->newInstanceWithoutConstructor();
$model->load=new class {function model($name){}};$model->emails_model=$mailer;
$model->send_candidate_welcome_mail(['candidate_name'=>'Test <name>','last_name'=>'Applicant','email'=>'fixture@example.test'],'fixture-password');
check(strpos($mailer->body,'>Recruitment Portal</a>')!==false,'Welcome email uses a descriptive portal label');
check(strpos($mailer->body,'recruitment/authentication_candidate/login')!==false,'Welcome uses the actual candidate login route');
check(strpos($mailer->body,'Test &lt;name&gt;')!==false,'Candidate names are escaped in email');
echo "Recruitment language-specific W-4, logo rendering, document compatibility/path checks, and welcome-email label/route checks passed.\n";
