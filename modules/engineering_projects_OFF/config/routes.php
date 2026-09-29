<?php
defined('BASEPATH') or exit('No direct script access allowed');
$route['admin/engineering_projects'] = 'engineering_projects/index';
$route['admin/engineering_projects/engineering_project'] = 'engineering_projects/engineering_project';
$route['admin/engineering_projects/engineering_project/(:num)'] = 'engineering_projects/engineering_project/$1';
$route['admin/engineering_projects/drawings'] = 'engineering_projects/drawings';
$route['admin/engineering_projects/drawings/drawing'] = 'engineering_projects/drawings/drawing';
$route['admin/engineering_projects/drawings/drawing/(:num)'] = 'engineering_projects/drawings/drawing/$1';
$route['admin/engineering_projects/documents'] = 'engineering_projects/documents';
$route['admin/engineering_projects/documents/document'] = 'engineering_projects/documents/document';
$route['admin/engineering_projects/documents/document/(:num)'] = 'engineering_projects/documents/document/$1';
$route['admin/engineering_projects/settings'] = 'engineering_projects/settings';
