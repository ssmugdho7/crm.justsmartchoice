<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['salesperson-portal'] = 'sales_center/sales_center_portal/register';
$route['salesperson-portal/register'] = 'sales_center/sales_center_portal/register';
$route['salesperson-portal/profile/(:any)'] = 'sales_center/sales_center_portal/profile/$1';
$route['salespersons/register'] = 'sales_center/sales_center_portal/register';
$route['salespersons/profile/(:any)'] = 'sales_center/sales_center_portal/profile/$1';

$route['salesperson-portal/success/(:any)'] = 'sales_center/sales_center_portal/success/$1';
$route['sales_center/sales_center_portal/success/(:any)'] = 'sales_center/sales_center_portal/success/$1';

$route['salesperson-portal/success'] = 'sales_center/sales_center_portal/success';
$route['sales_center/sales_center_portal/success'] = 'sales_center/sales_center_portal/success';
