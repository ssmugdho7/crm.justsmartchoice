<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Perfex/MX loads this module route file only when the first URI segment is
 * the module slug: subcontractors. Keep every public alias under that prefix.
 */
$route['subcontractors/contract/(:any)'] = 'subcontractor_contract/index/$1';
$route['subcontractors/contract/sign/(:any)'] = 'subcontractor_contract/sign/$1';
$route['subcontractors/contract/comment/(:any)'] = 'subcontractor_contract/comment/$1';

$route['subcontractors/portal'] = 'subcontractor_portal/register';
$route['subcontractors/portal/register'] = 'subcontractor_portal/register';
$route['subcontractors/portal/profile/(:any)'] = 'subcontractor_portal/profile/$1';
$route['subcontractors/portal/success/(:any)'] = 'subcontractor_portal/success/$1';
