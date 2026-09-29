<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['admin/smart_daily_productivity'] = 'smart_daily_productivity/smart_daily_productivity/index';
$route['admin/smart_daily_productivity/my_day'] = 'smart_daily_productivity/smart_daily_productivity/my_day';
$route['admin/smart_daily_productivity/reports'] = 'smart_daily_productivity/smart_daily_productivity/reports';
$route['admin/smart_daily_productivity/settings'] = 'smart_daily_productivity/smart_daily_productivity/settings';
$route['admin/smart_daily_productivity/help'] = 'smart_daily_productivity/smart_daily_productivity/help';
$route['admin/smart_daily_productivity/health'] = 'smart_daily_productivity/smart_daily_productivity/health';
$route['admin/smart_daily_productivity/database_checker'] = 'smart_daily_productivity/smart_daily_productivity/database_checker';
$route['admin/smart_daily_productivity/delete_entry/(:num)'] = 'smart_daily_productivity/smart_daily_productivity/delete_entry/$1';
