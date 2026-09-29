<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="google-meet-header">
            <h1><i class="fa fa-video-camera"></i> Google Meet Dashboard</h1>
            <div>
                <a href="<?php echo admin_url('google_meet/create'); ?>" class="btn btn-primary">New Meeting</a>
                <a href="<?php echo admin_url('google_meet/settings'); ?>" class="btn btn-default">Settings</a>
            </div>
        </div>

        <div class="panel_s google-meet-card">
            <div class="panel-body">
                <table class="table dt-table">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Start Time</th>
                            <th>Meet Link</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($meetings)) { foreach ($meetings as $meeting) { ?>
                        <tr>
                            <td><?php echo html_escape($meeting['title'] ?? 'Google Meet Meeting'); ?></td>
                            <td><?php echo html_escape($meeting['start_time'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($meeting['meet_link'])) { ?>
                                    <a target="_blank" href="<?php echo html_escape($meeting['meet_link']); ?>">Open Google Meet</a>
                                <?php } ?>
                            </td>
                            <td><?php echo html_escape(ucfirst($meeting['status'] ?? 'scheduled')); ?></td>
                        </tr>
                    <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
