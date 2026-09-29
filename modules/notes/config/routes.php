<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['admin/notes'] = 'notes/notes/note';
$route['admin/notes/note'] = 'notes/notes/note';
$route['admin/notes/notes/note'] = 'notes/notes/note';
$route['admin/notes/upgrade_database'] = 'notes/notes/upgrade_database';
$route['admin/modules/upgrade_database/notes'] = 'notes/notes/upgrade_database';
$route['admin/notes/view/(:num)'] = 'notes/notes/view/$1';
$route['admin/notes/edit/(:num)'] = 'notes/notes/edit/$1';

$route['admin/notes/save_source'] = 'notes/notes/save_source';
$route['admin/notes/delete_source/(:num)'] = 'notes/notes/delete_source/$1';
$route['admin/notes/save_type'] = 'notes/notes/save_type';
$route['admin/notes/delete_type/(:num)'] = 'notes/notes/delete_type/$1';
$route['admin/notes/save_preferences'] = 'notes/notes/save_preferences';
$route['admin/notes/share_link/(:num)'] = 'notes/notes/share_link/$1';
$route['notes/share/(:any)'] = 'notes/share/index/$1';
