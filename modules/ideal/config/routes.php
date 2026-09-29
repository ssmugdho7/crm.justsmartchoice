<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Optional Smart Choice Stripe dashboard routes.
$route['admin/ideal'] = 'ideal/ideal_admin/index';
$route['admin/ideal/dashboard'] = 'ideal/ideal_admin/index';
$route['admin/ideal/ideal_admin'] = 'ideal/ideal_admin/index';
$route['admin/ideal/ideal_admin/(:any)'] = 'ideal/ideal_admin/$1';

// Legacy route redirects to the native payment-gateway settings page.
$route['admin/ideal_admin'] = 'ideal/ideal_admin/settings_redirect';
$route['admin/ideal_admin/(:any)'] = 'ideal/ideal_admin/settings_redirect';
