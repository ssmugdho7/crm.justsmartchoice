<?php

defined('BASEPATH') or exit('No direct script access allowed');

// User-friendly appointment viewing URLs
$route['appointly/appointment/([a-zA-Z0-9]+)'] = 'appointments_public/client_hash';
$route['appointly/my-appointment/([a-zA-Z0-9]+)'] = 'appointments_public/client_hash';
