<?php

defined('BASEPATH') or exit('No direct script access allowed');
$route['admin/smart_choice_core_upgrade'] = 'smart_choice_core_upgrade/smart_choice_core_upgrade';
$route['admin/smart_choice_core_upgrade/(:any)'] = 'smart_choice_core_upgrade/smart_choice_core_upgrade/$1';
$route['admin/smart_choice_core_upgrade/(:any)/(:num)'] = 'smart_choice_core_upgrade/smart_choice_core_upgrade/$1/$2';
