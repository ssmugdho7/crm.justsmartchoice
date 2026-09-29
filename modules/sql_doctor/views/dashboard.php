<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h3 class="tw-mt-0"><i class="fa fa-database"></i> <?php echo _l('sql_doctor'); ?></h3>
            <p class="text-muted"><?php echo _l('sql_doctor_description'); ?></p>
            <div class="row">
              <div class="col-md-3"><a class="btn btn-primary btn-block" href="<?php echo admin_url('sql_doctor/clear_cache'); ?>"><i class="fa fa-refresh"></i> <?php echo _l('sql_doctor_clear_cache'); ?></a></div>
              <div class="col-md-3"><a class="btn btn-info btn-block" href="<?php echo admin_url('sql_doctor/clear_temp'); ?>"><i class="fa fa-trash"></i> <?php echo _l('sql_doctor_clear_temp'); ?></a></div>
              <div class="col-md-3"><a class="btn btn-warning btn-block" href="<?php echo admin_url('sql_doctor/create_index_files'); ?>"><i class="fa fa-shield"></i> <?php echo _l('sql_doctor_create_index_files'); ?></a></div>
              <div class="col-md-3"><a class="btn btn-success btn-block" href="<?php echo admin_url('sql_doctor/backup_database'); ?>"><i class="fa fa-download"></i> <?php echo _l('sql_doctor_backup_database'); ?></a></div>
            </div>
            <hr>
            <div class="row">
              <?php foreach($health as $k=>$v){ if($k === 'backups') continue; ?>
              <div class="col-md-3"><div class="well well-sm"><strong><?php echo ucwords(str_replace('_',' ',$k)); ?></strong><br><?php echo html_escape($v); ?></div></div>
              <?php } ?>
            </div>
            <h4><?php echo _l('sql_doctor_recent_backups'); ?></h4>
            <table class="table table-bordered"><thead><tr><th>File</th><th>Size</th><th>Created</th><th>Download</th></tr></thead><tbody>
            <?php foreach($health['backups'] as $b){ ?>
              <tr><td><?php echo html_escape($b->file_name); ?></td><td><?php echo number_format((int)$b->file_size/1024,2); ?> KB</td><td><?php echo html_escape($b->created_at); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('sql_doctor/download_backup/'.$b->id); ?>">Download</a></td></tr>
            <?php } ?>
            </tbody></table>
            <a href="<?php echo admin_url('sql_doctor/logs'); ?>" class="btn btn-default"><i class="fa fa-file-text-o"></i> <?php echo _l('sql_doctor_view_logs'); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
