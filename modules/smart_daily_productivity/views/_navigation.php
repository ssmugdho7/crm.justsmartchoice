<?php /* Shared navigation: permissions are checked by the controller as well as here. */ ?>
<nav class="sdp-nav" aria-label="Smart Daily Productivity navigation">
 <a href="<?php echo admin_url('smart_daily_productivity'); ?>" class="btn btn-default btn-sm"><i class="fa fa-dashboard"></i> <?php echo smart_daily_productivity_label('smart_daily_productivity_dashboard'); ?></a>
 <a href="<?php echo admin_url('smart_daily_productivity/reminders'); ?>" class="btn btn-warning btn-sm"><i class="fa fa-bell"></i> Audio Alarms</a>
 <a href="<?php echo admin_url('smart_daily_productivity/checklist'); ?>" class="btn btn-default btn-sm"><i class="fa fa-check-square-o"></i> Daily Reminders</a>
 <a href="<?php echo admin_url('smart_daily_productivity/manage_tasks'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> Manage Daily Tasks</a>
 <a href="<?php echo admin_url('smart_daily_productivity/my_day'); ?>" class="btn btn-default btn-sm"><i class="fa fa-calendar-check-o"></i> <?php echo smart_daily_productivity_label('smart_daily_productivity_my_day'); ?></a>
 <?php if (is_admin() || has_permission('smart_daily_productivity', '', 'reports')) { ?><a href="<?php echo admin_url('smart_daily_productivity/reports'); ?>" class="btn btn-default btn-sm"><i class="fa fa-bar-chart"></i> <?php echo smart_daily_productivity_label('smart_daily_productivity_reports'); ?></a><?php } ?>
 <a href="<?php echo admin_url('smart_daily_productivity/help'); ?>" class="btn btn-default btn-sm"><i class="fa fa-question-circle"></i> <?php echo smart_daily_productivity_label('smart_daily_productivity_help_guide'); ?></a>
</nav>
