<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
              <h4 class="no-margin">Project Media</h4>
              <a href="<?php echo admin_url('prchat/Prchat_Controller/project_media'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
            </div>
            <p class="text-muted">Group uploads are copied here when the setting is enabled. Use this folder as the CRM media bridge for project pictures and documents.</p>
            <div class="alert alert-info"><strong>Storage Path:</strong> <?php echo html_escape($media_path); ?></div>
            <table class="table table-bordered table-striped">
              <thead><tr><th>Folder</th></tr></thead>
              <tbody>
              <?php if (!empty($folders)) { foreach ($folders as $folder) { ?>
                <tr><td><?php echo html_escape($folder); ?></td></tr>
              <?php } } else { ?>
                <tr><td>No project media folders found yet.</td></tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
