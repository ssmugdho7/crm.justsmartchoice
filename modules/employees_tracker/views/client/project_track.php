<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('employees_tracker','assets/css/employees_tracker.css'); ?>">
<div class="panel_s et-card">
    <div class="panel-body">
        <div class="et-hero">
            <h3><?php echo _l('employees_tracker_project_track'); ?></h3>
            <p><?php echo html_escape($project['name'] ?? 'Project'); ?> — live installer distance, ETA, service details, and status.</p>
        </div>

        <?php if (empty($api_key)) { ?>
            <div class="alert alert-warning">Map is not configured yet. The company needs to add a Google Maps API key.</div>
        <?php } ?>

        <div class="et-grid">
            <div>
                <div id="project-map" class="et-map"></div>
            </div>
            <div>
                <h4 class="bold">Installer Status</h4>
                <div id="installer-list">
                    <div class="et-stat">Loading current location and ETA...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var ET_PROJECT_ID = <?php echo (int)$project['id']; ?>;
    var ET_POLL = <?php echo (int)$poll; ?>;
    var ET_STATUS_URL = '<?php echo site_url('employees_tracker_client/project_status/'.(int)$project['id']); ?>';
</script>
<script src="<?php echo module_dir_url('employees_tracker','assets/js/client_track.js'); ?>?v=<?php echo EMPLOYEES_TRACKER_MODULE_VERSION; ?>"></script>
<?php if (!empty($api_key)) { ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo html_escape($api_key); ?>&libraries=places&callback=employeesTrackerClientGoogleReady" async defer></script>
<?php } ?>
