<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('employees_tracker','assets/css/employees_tracker.css'); ?>">
<div id="wrapper">
<div class="content">
    <div class="et-hero">
        <h3><?php echo _l('employees_tracker_settings'); ?></h3>
        <p>Control Google Maps, client ETA alerts, installer location refresh, and default construction service text.</p>
    </div>
    <div class="panel_s et-card">
        <div class="panel-body">
            <?php echo form_open(admin_url('employees_tracker/settings')); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_input('google_api_key', 'Google Maps Browser API Key', $google_api_key, 'text', ['placeholder'=>'Use a Maps JavaScript API key, usually starts with AIza...']); ?>
                        <p class="text-muted">Do not use an OAuth Client ID ending in <code>apps.googleusercontent.com</code>. Use a Google Maps JavaScript API browser key.</p>
                        <p class="text-muted">Core Perfex API key detected: <?php echo !empty($core_google_api_key) ? 'Yes' : 'No'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('poll_interval', _l('employees_tracker_poll_interval'), $poll, 'number', ['min'=>10]); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('eta_notify_minutes', 'Notify client when ETA is under minutes', $eta_notify_minutes, 'number', ['min'=>5]); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_select('allow_staff_self_share', [['id'=>1,'name'=>'Yes'],['id'=>0,'name'=>'No']], ['id','name'], _l('employees_tracker_allow_staff_self_share'), $allow_staff_self_share); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_select('enable_email_notifications', [['id'=>1,'name'=>'Yes'],['id'=>0,'name'=>'No']], ['id','name'], 'Automatic client ETA email notifications', $enable_email_notifications); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_select('distance_provider', [['id'=>'google','name'=>'Google Maps preferred'],['id'=>'simple','name'=>'Simple distance fallback']], ['id','name'], 'ETA provider', $distance_provider); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('default_service_type', 'Default job/service description', $default_service_type); ?>
                    </div>
                </div>
                <button class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
            <?php echo form_close(); ?>
            <hr>
            <p class="text-muted"><?php echo _l('employees_tracker_settings_note'); ?></p>
            <div class="et-health-box">
                <strong>Health Checker</strong>
                <p class="text-muted">Open the module health check directly. This verifies the module version, PHP runtime, Google Maps key availability, and tracking endpoints.</p>
                <a href="<?php echo admin_url('employees_tracker/health'); ?>" target="_blank" class="btn btn-info"><i class="fa fa-heartbeat"></i> Open Health Checker</a>
                <code class="et-health-url"><?php echo admin_url('employees_tracker/health'); ?></code>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
