<?php
defined('BASEPATH') or exit('No direct script access allowed');
$route['admin/smart_choice_call_center'] = 'smart_choice_call_center/smart_choice_call_center/index';
$route['admin/smart_choice_call_center/(:any)'] = 'smart_choice_call_center/smart_choice_call_center/$1';
$route['admin/smart_choice_call_center/(:any)/(:any)'] = 'smart_choice_call_center/smart_choice_call_center/$1/$2';
$route['admin/smart_choice_call_center/(:any)/(:any)/(:any)'] = 'smart_choice_call_center/smart_choice_call_center/$1/$2/$3';
$route['smart_choice_call_center/webhook/(:any)'] = 'smart_choice_call_center/webhook/$1';
