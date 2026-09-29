<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['import_tasks/(:any)'] = '$1/data';
$route['import_tasks/import'] = '$1/data';
$route['import_tasks/delete/(:num)'] = '$1/data';
