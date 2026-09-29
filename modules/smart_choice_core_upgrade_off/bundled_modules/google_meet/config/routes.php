<?php

defined('BASEPATH') or exit('No direct script access allowed');

/* Client portal routes. Friendly routes avoid controller/path conflicts. */
$route['google-meet-client'] = 'google_meet/google_meet_client/index';
$route['google-meet-client/(:num)'] = 'google_meet/google_meet_client/view/$1';
$route['google-meet-join/(:num)'] = 'google_meet/google_meet_client/join/$1';

/* Backward-compatible legacy URLs. */
$route['google_meet/client'] = 'google_meet/google_meet_client/index';
$route['google_meet/client/(:num)'] = 'google_meet/google_meet_client/view/$1';
$route['google_meet/join/(:num)'] = 'google_meet/google_meet_client/join/$1';
