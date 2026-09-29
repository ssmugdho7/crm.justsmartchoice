<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('smartchoice_remainder_assignment_options')) {
    function smartchoice_remainder_assignment_options()
    {
        return [
            'self'  => 'Myself',
            'staff' => 'Staff Member',
        ];
    }
}

if (!function_exists('smartchoice_remainder_default_assigned_staff')) {
    function smartchoice_remainder_default_assigned_staff()
    {
        return function_exists('get_staff_user_id') ? get_staff_user_id() : null;
    }
}
