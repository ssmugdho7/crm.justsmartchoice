<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Customer portal routes.
 * IMPORTANT: Perfex/MX automatically prefixes the module name when parsing
 * this file. Route targets therefore start at the controller name and MUST
 * NOT repeat "google_meet/" or the router resolves an invalid double module
 * path and returns 404.
 */
$route['google_meet/meeting_clients'] = 'meeting_clients/meetings';
$route['google_meet/meeting_clients/meetings'] = 'meeting_clients/meetings';
$route['google_meet/meeting_clients/view/(:num)'] = 'meeting_clients/view/$1';
$route['google_meet/meeting_clients/join/(:num)'] = 'meeting_clients/join/$1';

// Backward-compatible customer URLs from earlier module builds.
$route['google_meet/client'] = 'meeting_clients/meetings';
$route['google_meet/client/view/(:num)'] = 'meeting_clients/view/$1';
$route['google_meet/client/join/(:num)'] = 'meeting_clients/join/$1';
