<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['employees_tracker_client'] = 'employees_tracker_client/index';
$route['employees_tracker_client/project/(:num)'] = 'employees_tracker_client/project/$1';
$route['employees_tracker_client/project_status/(:num)'] = 'employees_tracker_client/project_status/$1';
$route['clients/employees_tracker'] = 'employees_tracker_client/index';
$route['clients/employees_tracker/project/(:num)'] = 'employees_tracker_client/project/$1';
