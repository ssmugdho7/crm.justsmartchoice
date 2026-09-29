<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('ticket_status_class')) {
    function ticket_status_class($status)
    {
        switch ($status) {
            case 1:
                return 'badge-warning'; // Open
            case 2:
                return 'badge-info'; // In Progress
            case 3:
                return 'badge-success'; // Answered
            case 4:
                return 'badge-danger'; // Closed
            default:
                return 'badge-secondary'; // Default status
        }
    }
}
