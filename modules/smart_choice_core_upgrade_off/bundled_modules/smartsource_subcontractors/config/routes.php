<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['subcontractor-portal'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/register';
$route['subcontractor-portal/register'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/register';
$route['subcontractor-portal/profile/(:any)'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/profile/$1';
$route['subcontractors/register'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/register';
$route['subcontractors/profile/(:any)'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/profile/$1';

$route['subcontractor-portal/success/(:any)'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/success/$1';
$route['smartsource_subcontractors/smartsource_subcontractor_portal/success/(:any)'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/success/$1';

$route['subcontractor-portal/success'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/success';
$route['smartsource_subcontractors/smartsource_subcontractor_portal/success'] = 'smartsource_subcontractors/smartsource_subcontractor_portal/success';
