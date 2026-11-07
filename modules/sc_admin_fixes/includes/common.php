<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_filter('admin_body_class', 'scaf_body_classes');
hooks()->add_action('app_admin_head', 'scaf_head_assets');
function scaf_page() {
    return strtolower(get_instance()->router->fetch_class());
}
function scaf_domain() {
    $page = scaf_page();
    if ($page === 'clients') return 'customers';
    if ($page === 'projects') return 'projects';
    if (in_array($page, ['invoices','estimates','proposals','payments','credit_notes'], true)) return 'sales';
    if (in_array($page, ['sales_center','deals','commission'], true)) return 'saleshub';
    if (get_instance()->uri->segment(2) === 'appointly' || ($page === 'settings' && get_instance()->input->get('group') === 'appointly_settings')) return 'appointments';
    return null;
}
function scaf_body_classes($classes) {
    if (scaf_domain()) { $classes[] = 'scaf-enabled'; $classes[] = 'scaf-' . scaf_page(); $classes[] = 'scaf-domain-' . scaf_domain(); }
    return $classes;
}
function scaf_asset($relative) {
    $file = dirname(__DIR__) . '/' . $relative;
    if (!is_file($file)) return;
    $url = e(module_dir_url(SCAF_MODULE_NAME, $relative) . '?v=' . filemtime($file));
    echo str_ends_with($relative, '.css') ? '<link rel="stylesheet" href="'.$url.'">' : '<script defer src="'.$url.'"></script>';
}
function scaf_head_assets() {
    if (scaf_page() === 'development' && get_instance()->router->fetch_method() === 'view') {
        scaf_asset('assets/js/repair-note-clipboard.js');
    }
    $domain = scaf_domain(); if (!$domain) return;
    $config = ['page'=>scaf_page(),'domain'=>$domain,'decimals'=>(int)get_decimal_places(),'adminUrl'=>admin_url(), 'labels'=>[]];
    foreach (['confirm','continue','cancel','delete','type_to_confirm','copied','copy_failed','wait','selected','failed','saved','select_records'] as $key) $config['labels'][$key] = _l('scaf_'.$key);
    echo '<script>window.scafConfig='.json_encode($config, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script>';
    scaf_asset('assets/css/vendor-sonner.css'); scaf_asset('assets/css/common.css'); scaf_asset('assets/css/'.$domain.'.css');
    scaf_asset('assets/js/vendor-sonner.js'); scaf_asset('assets/js/common.js'); scaf_asset('assets/js/'.$domain.'.js');
}
