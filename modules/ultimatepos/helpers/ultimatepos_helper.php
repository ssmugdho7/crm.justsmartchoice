<?php
defined('BASEPATH') or exit('No direct script access allowed');

function ultimatepos_sync_cron()
{
    $CI = &get_instance();
    $CI->load->model('ultimatepos/ultimatepos_model');

    // Get the sync interval from settings (in minutes)
    $sync_interval = (int) get_option('ultimatepos_sync_interval'); // Assuming interval is in minutes
    $last_sync_time = (int) get_option('ultimatepos_last_sync_time'); // Store the last sync time in a setting

    // Get the current time
    $current_time = time();

    // Calculate the time difference in minutes
    $time_difference = ($current_time - $last_sync_time) / 60;

    if ($time_difference >= $sync_interval) {
        // Run the sync process
        $CI->ultimatepos_model->sync_customers();

        // Update the last sync time
        update_option('ultimatepos_last_sync_time', $current_time);
    }
}

// Register the cron job to run after every cron run
hooks()->add_action('after_cron_run', 'ultimatepos_sync_cron');
