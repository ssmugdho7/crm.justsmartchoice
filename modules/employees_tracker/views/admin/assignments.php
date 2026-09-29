<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('employees_tracker','assets/css/employees_tracker.css'); ?>">
<div id="wrapper">
<div class="content">
    <div class="et-hero">
        <h3><?php echo _l('employees_tracker_assignments'); ?></h3>
        <p>Assign installers to CRM projects, connect optional appointments, define job details, and publish the ETA view to the client portal.</p>
    </div>
    <div class="et-tabs">
        <a  href="<?php echo admin_url('employees_tracker'); ?>"><i class="fa fa-map"></i> Live Map</a>
        <a class="active" href="<?php echo admin_url('employees_tracker/assignments'); ?>"><i class="fa fa-truck"></i> Assignments</a>
        <a  href="<?php echo admin_url('employees_tracker/settings'); ?>"><i class="fa fa-cog"></i> Settings</a>
    </div>

    <div class="panel_s et-card">
        <div class="panel-body">
            <?php echo form_open(admin_url('employees_tracker/assignments')); ?>
                <div class="row">
                    <div class="col-md-4">
                        <label>Project</label>
                        <select name="project_id" class="form-control selectpicker" data-live-search="true" required>
                            <?php foreach($projects as $p){ ?>
                                <option value="<?php echo (int)$p['id']; ?>"><?php echo html_escape($p['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Staff / Installer</label>
                        <select name="staff_id" class="form-control selectpicker" data-live-search="true" required>
                            <?php foreach($staff as $s){ ?>
                                <option value="<?php echo (int)$s['staffid']; ?>"><?php echo html_escape($s['firstname'].' '.$s['lastname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Appointment ID / Schedule Link</label>
                        <input type="number" name="appointment_id" class="form-control" placeholder="Optional appointment ID">
                    </div>
                    <div class="col-md-4 mtop15">
                        <label>Scheduled Start</label>
                        <input type="text" name="scheduled_start" class="form-control datetimepicker" placeholder="Date/time">
                    </div>
                    <div class="col-md-4 mtop15">
                        <label>Service Type</label>
                        <input type="text" name="service_type" class="form-control" placeholder="Deck build, electrical, painting, kitchen remodel">
                    </div>
                    <div class="col-md-2 mtop15">
                        <label>Enabled</label>
                        <select name="enabled" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-md-2 mtop15">
                        <label>Notify Client</label>
                        <select name="notify_client" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-md-6 mtop15">
                        <label>Project Address</label>
                        <input type="text" name="project_address" class="form-control" placeholder="Jobsite address">
                    </div>
                    <div class="col-md-3 mtop15">
                        <label>Project Latitude</label>
                        <input type="text" name="project_lat" class="form-control" placeholder="28.0000000">
                    </div>
                    <div class="col-md-3 mtop15">
                        <label>Project Longitude</label>
                        <input type="text" name="project_lng" class="form-control" placeholder="-82.0000000">
                    </div>
                    <div class="col-md-12 mtop15">
                        <label>Job Notes</label>
                        <textarea name="job_notes" class="form-control" rows="3" placeholder="Tell the customer what the installer is coming to do."></textarea>
                    </div>
                    <div class="col-md-12 mtop20">
                        <button class="btn btn-primary"><i class="fa fa-save"></i> Save Assignment</button>
                    </div>
                </div>
            <?php echo form_close(); ?>

            <hr/>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Project</th><th>Installer</th><th>Service</th><th>Appointment</th><th>Enabled</th><th>Notify</th><th>ETA</th><th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($assignments as $a){
                            $p = null; foreach($projects as $x){ if((int)$x['id']===(int)$a['project_id']){$p=$x;break;} }
                            $s = null; foreach($staff as $x){ if((int)$x['staffid']===(int)$a['staff_id']){$s=$x;break;} }
                        ?>
                        <tr>
                            <td><?php echo $p ? html_escape($p['name']) : (int)$a['project_id']; ?></td>
                            <td><?php echo $s ? html_escape($s['firstname'].' '.$s['lastname']) : (int)$a['staff_id']; ?></td>
                            <td><?php echo html_escape($a['service_type'] ?? ''); ?></td>
                            <td><?php echo !empty($a['appointment_id']) ? (int)$a['appointment_id'] : '-'; ?></td>
                            <td><?php echo !empty($a['enabled']) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
                            <td><?php echo !empty($a['notify_client']) ? '<span class="label label-info">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
                            <td><?php echo html_escape($a['last_eta_text'] ?? '-'); ?></td>
                            <td><?php echo html_escape($a['updated_at'] ?? $a['created_at']); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
