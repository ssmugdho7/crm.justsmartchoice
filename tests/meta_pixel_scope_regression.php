<?php
define('BASEPATH',true);define('ZILLAPAGE_ASSETS_PATH','modules/zillapage/assets');
function html_escape($value){return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
function base_url($path){return 'https://crm.example/'.$path;}
function render_pixel($slug,$id){$page=(object)['slug'=>$slug];$settingsMap=['facebook_pixel_id'=>$id];ob_start();include __DIR__.'/../modules/zillapage_off/views/publish/meta_pixel.php';return ob_get_clean();}
foreach(['contact-us','en-estimate-request-form','solicitud-de-estimado'] as $slug){if(!str_contains(render_pixel($slug,'1159018933475955'),'sc-meta-consent'))throw new Exception('Marketing page missing consent UI');}
foreach(['banking-info-request','staff-onboarding','',null] as $slug){if(render_pixel($slug,'1159018933475955')!=='')throw new Exception('Nonmarketing page tracking blocked incorrectly');}
if(render_pixel('contact-us','123<script>')!=='')throw new Exception('Invalid Pixel ID accepted');
echo "PASS Pixel limited to reviewed public marketing pages; private/unknown pages and invalid IDs excluded\n";
