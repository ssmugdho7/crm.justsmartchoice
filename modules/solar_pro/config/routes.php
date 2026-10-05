<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Solar Pro v1.0.2 module routes.
 *
 * This CRM uses MX_Router::parse_routes(), which only loads a module's routes
 * when the first URI segment is that module's system name.  Therefore vanity
 * routes such as /solar/estimate cannot be declared safely from this module.
 * The supported public aliases deliberately begin with /solar_pro/.
 */
$route['solar_pro/estimate'] = 'solar_portal/estimate';
$route['solar_pro/portal/(:any)/pdf'] = 'solar_portal/pdf/$1';
$route['solar_pro/portal/(:any)/document/(:num)'] = 'solar_portal/document/$1/$2';
$route['solar_pro/portal/(:any)/sign'] = 'solar_portal/sign/$1';
$route['solar_pro/portal/(:any)'] = 'solar_portal/view/$1';
$route['solar_pro/my'] = 'solar_customer/index';

$route['solar_pro/proposal/(:any)/pdf'] = 'solar_portal/proposal_pdf/$1';
$route['solar_pro/proposal/(:any)'] = 'solar_portal/proposal/$1';

$route['solar_pro/proposal_templates'] = 'solar_pro/proposal_templates';
$route['solar_pro/proposal_template/(:num)'] = 'solar_pro/proposal_template/$1';
$route['solar_pro/proposal_template'] = 'solar_pro/proposal_template/0';
$route['solar_pro/delete_proposal_template/(:num)'] = 'solar_pro/delete_proposal_template/$1';
$route['solar_pro/equipment_import'] = 'solar_pro/equipment_import';
$route['solar_pro/equipment_sample'] = 'solar_pro/equipment_sample';
$route['solar_pro/equipment_mass_delete'] = 'solar_pro/equipment_mass_delete';
$route['solar_pro/equipment_spec/(:num)'] = 'solar_pro/equipment_spec/$1';
