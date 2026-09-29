<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="smart-productivity-header">
                    <div>
                        <h4><?php echo smart_daily_productivity_label('smart_daily_productivity_health_checker'); ?></h4>
                        <p><?php echo smart_daily_productivity_label('smart_daily_productivity_health_help'); ?></p>
                    </div>
                    <a href="<?php echo admin_url('smart_daily_productivity'); ?>" class="btn btn-default btn-sm"><?php echo smart_daily_productivity_label('smart_daily_productivity_dashboard'); ?></a>
                </div>

                <div class="alert alert-warning"><?php echo smart_daily_productivity_label('smart_daily_productivity_backup_warning'); ?></div>

                <?php foreach ($health as $section => $items) { ?>
                    <div class="panel_s smart-health-panel">
                        <div class="panel-body">
                            <h4><?php echo html_escape($section); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-striped smart-productivity-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo smart_daily_productivity_label('smart_daily_productivity_item'); ?></th>
                                            <th><?php echo smart_daily_productivity_label('smart_daily_productivity_status'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item => $ok) { ?>
                                            <tr>
                                                <td><?php echo html_escape($item); ?></td>
                                                <td>
                                                    <?php if ($ok) { ?>
                                                        <span class="label label-success"><?php echo smart_daily_productivity_label('smart_daily_productivity_ok'); ?></span>
                                                    <?php } else { ?>
                                                        <span class="label label-danger"><?php echo smart_daily_productivity_label('smart_daily_productivity_missing'); ?></span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('smart_daily_productivity/health'), ['class' => 'inline-block']); ?>
                            <input type="hidden" name="repair_database" value="1">
                            <button class="btn btn-warning btn-sm" type="submit"><?php echo smart_daily_productivity_label('smart_daily_productivity_repair_database'); ?></button>
                        <?php echo form_close(); ?>

                        <?php echo form_open(admin_url('smart_daily_productivity/health'), ['class' => 'inline-block mleft10']); ?>
                            <input type="hidden" name="cleanup_cache" value="1">
                            <button class="btn btn-info btn-sm" type="submit"><?php echo smart_daily_productivity_label('smart_daily_productivity_safe_cache_cleanup'); ?></button>
                        <?php echo form_close(); ?>

                        <a href="<?php echo admin_url('smart_daily_productivity/health'); ?>" class="btn btn-default btn-sm mleft10"><?php echo smart_daily_productivity_label('smart_daily_productivity_refresh'); ?></a>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo smart_daily_productivity_label('smart_daily_productivity_logs'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-striped smart-productivity-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('date'); ?></th>
                                        <th><?php echo _l('staff_member'); ?></th>
                                        <th><?php echo smart_daily_productivity_label('smart_daily_productivity_action'); ?></th>
                                        <th><?php echo smart_daily_productivity_label('smart_daily_productivity_message'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log) { ?>
                                        <tr>
                                            <td><?php echo html_escape($log['created_at']); ?></td>
                                            <td><?php echo html_escape(trim((string) $log['firstname'] . ' ' . (string) $log['lastname'])); ?></td>
                                            <td><?php echo html_escape(ucwords(str_replace('_', ' ', (string) $log['action']))); ?></td>
                                            <td><?php echo html_escape($log['message']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
