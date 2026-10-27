<?php
define('BASEPATH',__DIR__);
function current_url(){return 'https://crm.example.test/recruitment/forms/application';}
function get_recruitment_option($key){return '';}
function site_url($path){return 'https://crm.example.test/'.$path;}
function get_dark_company_logo(){return '<img src="/logo.png" alt="Company">';}
function html_escape($value){return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
function _l($key){return $key;}
function is_candidate_logged_in(){return $GLOBALS['fixture_candidate_logged_in'];}
$path=dirname(__DIR__).'/modules/recruitment/views/recruitment_portal/template_parts/navigation.php';
$_GET=['other'=>'safe value','lang'=>'spanish'];
foreach([true,false] as $logged_in){$GLOBALS['fixture_candidate_logged_in']=$logged_in;foreach([true,false,null] as $display_login){if($display_login===null){unset($show_login);}else{$show_login=$display_login;}ob_start();include $path;$html=ob_get_clean();$doc=new DOMDocument();@$doc->loadHTML($html);$xpath=new DOMXPath($doc);$login=$xpath->query('//a[contains(@href,"authentication_candidate/login")]')->length;if($login!==(int)($display_login!==false&&!$logged_in))throw new RuntimeException('Login visibility mismatch');if($xpath->query('//a[contains(@href,"lang=english")]')->length!==1||$xpath->query('//a[contains(@href,"lang=spanish")]')->length!==1)throw new RuntimeException('Missing language links');if(!str_contains($html,'other=safe+value'))throw new RuntimeException('Query not preserved');if($xpath->query('//img')->length!==1)throw new RuntimeException('Logo mismatch');}
}
echo "Shared navbar render: guest login shown, candidate login hidden, form login omitted, both languages and query preserved.\n";
