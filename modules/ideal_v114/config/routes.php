<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Canonical Smart Choice Stripe admin routes.
$route['admin/ideal_admin'] = 'ideal/ideal_admin/index';
$route['admin/ideal_admin/(:any)'] = 'ideal/ideal_admin/$1';

// Backward-compatible module-prefixed routes.
$route['admin/ideal/ideal_admin'] = 'ideal/ideal_admin/index';
$route['admin/ideal/ideal_admin/(:any)'] = 'ideal/ideal_admin/$1';
