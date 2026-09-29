<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
              <h4 class="no-margin">Chat Health Check</h4>
              <a href="<?php echo admin_url('prchat/Prchat_Controller/health_check'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
            </div>
            <p class="text-muted">Smart Choice diagnostic screen for folders, uploads, calls, Pusher, CRM media storage, and allowed file types.</p>
            <table class="table table-bordered table-striped">
              <thead><tr><th>Check</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($checks as $label => $ok) { ?>
                <tr>
                  <td><?php echo html_escape($label); ?></td>
                  <td><?php if ($ok) { ?><span class="label label-success">Passed</span><?php } else { ?><span class="label label-danger">Needs Review</span><?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
            <div class="alert alert-info"><strong>Project Media Folder:</strong> <?php echo html_escape($media_path); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
