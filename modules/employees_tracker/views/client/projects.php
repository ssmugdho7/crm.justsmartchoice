<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('employees_tracker','assets/css/employees_tracker.css'); ?>">
<div class="panel_s et-card">
    <div class="panel-body">
        <div class="et-hero">
            <h3><?php echo _l('employees_tracker_client_menu'); ?></h3>
            <p>See your scheduled installer, job type, live distance, and estimated arrival time.</p>
        </div>
        <div class="row">
            <?php foreach($projects as $p){ ?>
                <div class="col-md-6">
                    <div class="et-stat">
                        <h4><?php echo html_escape($p['name']); ?></h4>
                        <p class="et-muted">Track assigned installers for this project.</p>
                        <a class="btn btn-primary" href="<?php echo site_url('employees_tracker_client/project/'.(int)$p['id']); ?>">
                            <i class="fa fa-map-marker"></i> View ETA
                        </a>
                    </div>
                </div>
            <?php } ?>
            <?php if(empty($projects)){ ?>
                <div class="col-md-12"><div class="alert alert-info">No active projects are available for live tracking yet.</div></div>
            <?php } ?>
        </div>
    </div>
</div>
