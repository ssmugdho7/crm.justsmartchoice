<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="google-meet-header">
            <h1><i class="fa fa-video-camera"></i> Video Meeting Reports</h1>
            <p>Search meetings by employee, date, topic, meeting name, comments, and internal notes.</p>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body"><h4>Total Meetings</h4><h2><?php echo (int)($summary['total'] ?? 0); ?></h2></div></div></div>
            <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body"><h4>Completed</h4><h2><?php echo (int)($summary['completed'] ?? 0); ?></h2></div></div></div>
            <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body"><h4>Total Minutes</h4><h2><?php echo (int)($summary['minutes'] ?? 0); ?></h2></div></div></div>
        </div>

        <div class="panel_s google-meet-card">
            <div class="panel-body">
                <?php echo form_open(admin_url('google_meet/reports'), ['method' => 'get']); ?>
                <div class="row">
                    <div class="col-md-3">
                        <label>Employee</label>
                        <select name="staff_id" class="form-control selectpicker" data-live-search="true" data-width="100%">
                            <option value="">All Employees</option>
                            <?php foreach ((array)$staff as $s) { $sid = (int)$s['staffid']; ?>
                                <option value="<?php echo $sid; ?>" <?php echo ((int)($filters['staff_id'] ?? 0) === $sid) ? 'selected' : ''; ?>><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2"><?php echo render_input('date_from', 'From Date', $filters['date_from'] ?? '', 'date'); ?></div>
                    <div class="col-md-2"><?php echo render_input('date_to', 'To Date', $filters['date_to'] ?? '', 'date'); ?></div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <?php foreach(['scheduled','live','completed'] as $status){ ?>
                                <option value="<?php echo $status; ?>" <?php echo (($filters['status'] ?? '') === $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3"><?php echo render_input('q', 'Topic / Name / Notes', $filters['q'] ?? '', 'text'); ?></div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Filter Meetings</button>
                <a class="btn btn-default" href="<?php echo admin_url('google_meet/reports'); ?>">Reset</a>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div class="panel_s google-meet-card">
            <div class="panel-body table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Meeting</th><th>Employee</th><th>Start</th><th>Status</th><th>Notes / Comments</th><th>Link</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ((array)$meetings as $m) { ?>
                        <tr>
                            <td><strong><?php echo html_escape($m['subject'] ?: $m['title'] ?: 'Video Meeting Meeting'); ?></strong><br><small><?php echo html_escape($m['description'] ?? ''); ?></small></td>
                            <td><?php echo html_escape($m['assigned_staff_name'] ?: $m['created_by_name'] ?: ''); ?></td>
                            <td><?php echo !empty($m['start_time']) ? google_meet_display_datetime($m['start_time']) : ''; ?></td>
                            <td><?php echo html_escape($m['status'] ?? ''); ?><br><small><?php echo html_escape($m['google_api_status'] ?? 'manual'); ?></small></td>
                            <td><?php echo html_escape($m['notes'] ?? ''); ?></td>
                            <td><?php if (!empty($m['meet_link'])) { ?><a  href="<?php echo html_escape($m['meet_link']); ?>">Open</a><?php } ?></td>
                            <td><a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/view/' . (int)$m['id']); ?>">View</a></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
