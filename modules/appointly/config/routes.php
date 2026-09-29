<?php

defined('BASEPATH') or exit('No direct script access allowed');

// User-friendly appointment viewing URLs
$route['appointly/appointment/([a-zA-Z0-9]+)'] = 'appointments_public/client_hash';
$route['appointly/my-appointment/([a-zA-Z0-9]+)'] = 'appointments_public/client_hash';


// Clean public booking links for Smart Choice Appointments. These routes do not require admin login.
$route['booking-link'] = 'appointments_public/public_booking_link';
$route['smart-choice-booking'] = 'appointments_public/public_booking_link';
$route['appointly/public-booking'] = 'appointments_public/public_booking_link';

// Clean public booking links for AI, website buttons, QR codes, and customer messages.
$route['appointments'] = 'appointments_public/public_booking_link';
$route['book-appointment'] = 'appointments_public/public_booking_link';
$route['schedule-appointment'] = 'appointments_public/public_booking_link';
$route['appointly/appointments'] = 'appointments_public/public_booking_link';
$route['appointly/book-appointment'] = 'appointments_public/public_booking_link';
