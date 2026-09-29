<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['smart-installer-tracking/client/(:any)'] = 'client_tracking/view/$1';
$route['smart-installer-tracking/client-data/(:any)'] = 'client_tracking/data/$1';
