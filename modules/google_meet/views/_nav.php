<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="gm-nav-tabs">
<?php if (has_permission('google_meet', '', 'view') || has_permission('google_meet', '', 'view_own')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet'); ?>"><i class="fa fa-dashboard"></i> Dashboard</a>
<?php } ?>
<?php if (has_permission('google_meet', '', 'create')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/create'); ?>"><i class="fa fa-plus"></i> New Meeting</a>
<?php } ?>
<?php if (has_permission('google_meet', '', 'view') || has_permission('google_meet', '', 'view_own')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/join'); ?>"><i class="fa fa-sign-in"></i> Join Meeting</a>
<?php } ?>
<?php if (has_permission('google_meet', '', 'view') || has_permission('google_meet', '', 'view_own')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/reports'); ?>"><i class="fa fa-bar-chart"></i> Reports</a>
<?php } ?>
<?php if (has_permission('settings', '', 'view')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/test_notifications'); ?>"><i class="fa fa-bell"></i> Test Notifications</a>
<?php } ?>
<?php if (has_permission('settings', '', 'view')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/settings'); ?>"><i class="fa fa-cog"></i> Settings</a>
<?php } ?>
<?php if (has_permission('google_meet', '', 'view') || has_permission('google_meet', '', 'view_own')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/help'); ?>"><i class="fa fa-question-circle"></i> Help</a>
<?php } ?>
<?php if (has_permission('settings', '', 'view')) { ?>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/health'); ?>"><i class="fa fa-heartbeat"></i> Health</a>
<?php } ?>
</div>
