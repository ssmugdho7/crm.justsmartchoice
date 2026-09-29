<?php
defined('BASEPATH') or exit('No direct script access allowed');
$route['admin/cabinet_maker'] = 'cabinet_maker/cabinet_maker/index';
$route['admin/cabinet_maker/designer/(:num)'] = 'cabinet_maker/cabinet_maker/designer/$1';
$route['admin/cabinet_maker/save/(:num)'] = 'cabinet_maker/cabinet_maker/save/$1';
$route['admin/cabinet_maker/delete/(:num)'] = 'cabinet_maker/cabinet_maker/delete/$1';
$route['admin/cabinet_maker/share/(:num)'] = 'cabinet_maker/cabinet_maker/share/$1';
$route['admin/cabinet_maker/pdf/(:num)'] = 'cabinet_maker/cabinet_maker/pdf/$1';
$route['admin/cabinet_maker/materials'] = 'cabinet_maker/cabinet_maker/materials';
$route['admin/cabinet_maker/vendors'] = 'cabinet_maker/cabinet_maker/vendors';
$route['admin/cabinet_maker/vendor/(:num)'] = 'cabinet_maker/cabinet_maker/vendor/$1';
$route['admin/cabinet_maker/offcuts'] = 'cabinet_maker/cabinet_maker/offcuts';
$route['admin/cabinet_maker/settings'] = 'cabinet_maker/cabinet_maker/settings';
$route['admin/cabinet_maker/ai_plan'] = 'cabinet_maker/cabinet_maker/ai_plan';
$route['cabinet-maker/(:any)'] = 'cabinet_maker/public_design/view/$1';
$route['admin/cabinet_maker/ai_render/(:num)'] = 'cabinet_maker/cabinet_maker/ai_render/$1';
$route['admin/cabinet_maker/email_share/(:num)'] = 'cabinet_maker/cabinet_maker/email_share/$1';
$route['admin/cabinet_maker/download_module'] = 'cabinet_maker/cabinet_maker/download_module';

$route['admin/cabinet_maker/health_check'] = 'cabinet_maker/cabinet_maker/health_check';
$route['admin/cabinet_maker/upgrade_database'] = 'cabinet_maker/cabinet_maker/upgrade_database';
$route['admin/cabinet_maker/export_json/(:num)'] = 'cabinet_maker/cabinet_maker/export_json/$1';
$route['admin/cabinet_maker/export_cutlist/(:num)'] = 'cabinet_maker/cabinet_maker/export_cutlist/$1';
$route['admin/cabinet_maker/export_dxf/(:num)'] = 'cabinet_maker/cabinet_maker/export_dxf/$1';
$route['admin/cabinet_maker/export_sketchup/(:num)'] = 'cabinet_maker/cabinet_maker/export_sketchup/$1';

$route['admin/cabinet_maker/dashboard'] = 'cabinet_maker/cabinet_maker/dashboard';
$route['admin/cabinet_maker/import_materials'] = 'cabinet_maker/cabinet_maker/import_materials';
$route['admin/cabinet_maker/import_vendors'] = 'cabinet_maker/cabinet_maker/import_vendors';
$route['admin/cabinet_maker/import_designs'] = 'cabinet_maker/cabinet_maker/import_designs';
$route['admin/cabinet_maker/sample_materials'] = 'cabinet_maker/cabinet_maker/sample_materials';
$route['admin/cabinet_maker/sample_vendors'] = 'cabinet_maker/cabinet_maker/sample_vendors';
$route['admin/cabinet_maker/sample_designs'] = 'cabinet_maker/cabinet_maker/sample_designs';
