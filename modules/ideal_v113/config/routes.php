<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['admin/ideal_admin'] = 'ideal/ideal_admin/index';
$route['admin/ideal_admin/'] = 'ideal/ideal_admin/index';
$route['admin/ideal_admin/index'] = 'ideal/ideal_admin/index';
$route['admin/ideal_admin/settings'] = 'ideal/ideal_admin/settings';
$route['admin/ideal_admin/save_module_settings'] = 'ideal/ideal_admin/save_module_settings';
$route['admin/ideal_admin/create_subscription_link'] = 'ideal/ideal_admin/create_subscription_link';
$route['admin/ideal_admin/test_connection'] = 'ideal/ideal_admin/test_connection';

$route['ideal/create_webhook'] = 'ideal/ideal/create_webhook';
$route['ideal/enable_webhook'] = 'ideal/ideal/enable_webhook';
$route['ideal/webhook'] = 'ideal/ideal/webhook';
$route['ideal/make_payment/(:num)/(:any)'] = 'ideal/ideal/make_payment/$1/$2';
$route['ideal/callback/(:num)/(:any)'] = 'ideal/ideal/callback/$1/$2';
