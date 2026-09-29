<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('employees_tracker','assets/css/employees_tracker.css'); ?>">
<div id="wrapper">
<div class="content">
    <div class="et-hero">
        <h3><?php echo _l('employees_tracker_dashboard'); ?></h3>
        <p><?php echo _l('employees_tracker_dashboard_help'); ?></p>
    </div>
    <div class="et-tabs">
        <a class="active" href="<?php echo admin_url('employees_tracker'); ?>"><i class="fa fa-map"></i> Live Map</a>
        <a href="<?php echo admin_url('employees_tracker/assignments'); ?>"><i class="fa fa-truck"></i> Assignments</a>
        <a href="<?php echo admin_url('employees_tracker/settings'); ?>"><i class="fa fa-cog"></i> Settings</a>
    </div>

    <div class="panel_s et-card">
        <div class="panel-body">
            <?php if (empty($api_key)) { ?>
                <div class="alert alert-warning">
                    Google Maps API key is missing. Add it under <strong>Setup → Settings → Installer ETA</strong> or Perfex Google API settings.
                </div>
            <?php } ?>

            <div class="et-actions mtop10 mbot15">
                <button id="et-start" class="btn btn-success mright10"><i class="fa fa-location-arrow"></i> <?php echo _l('employees_tracker_start_sharing'); ?></button>
                <button id="et-stop" class="btn btn-warning mright10"><i class="fa fa-stop"></i> <?php echo _l('employees_tracker_stop_sharing'); ?></button>
                <button id="et-test" class="btn btn-info"><i class="fa fa-bug"></i> Save Test Location</button>
            </div>

            <div id="et-status" class="et-stat"><?php echo _l('employees_tracker_dashboard_ready'); ?></div>
            <div id="map" class="et-map mtop20"></div>

            <script>
                var ET_POLL = <?php echo (int)$poll; ?>;
                var ET_SAVE_URL = '<?php echo admin_url('employees_tracker/save_location'); ?>';
                var ET_LIVE_LOCATIONS_URL = '<?php echo admin_url('employees_tracker/live_locations'); ?>';
                var ET_TEST_LOCATION_URL = '<?php echo admin_url('employees_tracker/save_test_location'); ?>';
                var ET_GOOGLE_API_KEY = '<?php echo html_escape($api_key); ?>';
                window.csrfData = {'<?php echo get_csrf_token_name(); ?>':'<?php echo get_csrf_hash(); ?>'};
            </script>
            <script src="<?php echo module_dir_url('employees_tracker','assets/js/tracker.js'); ?>?v=<?php echo EMPLOYEES_TRACKER_MODULE_VERSION; ?>"></script>
            <?php if (!empty($api_key)) { ?>
                <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo html_escape($api_key); ?>&libraries=places&callback=employeesTrackerGoogleReady" async defer></script>
            <?php } ?>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
