<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer">
  <div class="content">
    <div class="row"><div class="col-md-12">
      <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?>
      <div class="sc-card">
        <h3 class="sc-title"><i class="fa fa-folder-open"></i> <?php echo _l('crm_file_explorer_manager'); ?></h3>
        <p><?php echo _l('root_folder'); ?>: <span class="sc-pill"><?php echo html_escape(get_option('crm_file_explorer_manager_root_path')); ?></span></p>
        <?php echo form_open(admin_url('crm_file_explorer_manager/scan'), ['class'=>'sc-scan-toolbar']); ?>
          <div class="sc-root-input"><input type="text" name="base_path" class="form-control" value="<?php echo html_escape(get_option('crm_file_explorer_manager_root_path')); ?>" aria-label="<?php echo html_escape(_l('root_folder')); ?>"></div>
          <button class="btn sc-btn sc-btn-sm" type="submit"><i class="fa fa-search"></i> <?php echo _l('scan_files'); ?></button>
          <a class="btn sc-btn-dark sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('open_health_checker'); ?></a>
          <a class="btn sc-btn-green sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/reset_scan'); ?>" onclick="return confirm('<?php echo html_escape(_l('confirm_reset_scan_list')); ?>');"><i class="fa fa-refresh"></i> <?php echo _l('reset_scan_list'); ?></a>
        <?php echo form_close(); ?>
      </div>
      <div class="sc-grid">
        <div class="sc-stat"><strong><?php echo number_format($summary['total_files']); ?></strong><?php echo _l('detected_files'); ?></div>
        <div class="sc-stat"><strong><?php echo number_format($summary['total_size']/1024/1024,2); ?></strong><?php echo _l('megabytes'); ?></div>
        <div class="sc-stat"><strong><?php echo number_format($summary['images']); ?></strong><?php echo _l('images'); ?></div>
        <div class="sc-stat"><strong><?php echo number_format($summary['videos']); ?></strong><?php echo _l('videos'); ?></div>
        <div class="sc-stat"><strong><?php echo number_format($summary['documents']); ?></strong><?php echo _l('documents'); ?></div>
      </div>
      <div class="sc-card">
        <form method="get" class="form-inline m-bot15">
          <select name="type" class="form-control">
            <option value=""><?php echo _l('all_files'); ?></option>
            <?php foreach(['image'=>_l('images'),'video'=>_l('videos'),'document'=>_l('documents'),'code'=>_l('code_files'),'other'=>_l('other_files')] as $k=>$v){ ?>
              <option value="<?php echo $k; ?>" <?php echo $this->input->get('type')===$k?'selected':''; ?>><?php echo $v; ?></option>
            <?php } ?>
          </select>
          <input type="text" name="search" class="form-control" placeholder="<?php echo _l('search_files'); ?>" value="<?php echo html_escape($this->input->get('search')); ?>">
          <button class="btn sc-btn-green sc-btn-sm" type="submit"><?php echo _l('refresh'); ?></button>
        </form>
        <div class="table-responsive">
          <table class="table table-striped sc-table">
            <thead><tr><th><?php echo _l('preview'); ?></th><th><?php echo _l('file_name'); ?></th><th><?php echo _l('file_type'); ?></th><th><?php echo _l('file_size'); ?></th><th><?php echo _l('module_or_folder'); ?></th><th><?php echo _l('modified_time'); ?></th><th><?php echo _l('options'); ?></th></tr></thead>
            <tbody>
            <?php foreach($files as $file){ ?>
              <tr>
                <td><?php if($file['file_type']==='image'){ ?><img class="sc-thumb" src="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>"><?php } else { ?><span class="sc-pill"><?php echo strtoupper($file['extension']); ?></span><?php } ?></td>
                <td><strong><?php echo html_escape($file['file_name']); ?></strong><div class="sc-path"><?php echo html_escape($file['relative_path']); ?></div></td>
                <td><?php echo ucwords(html_escape($file['file_type'])); ?></td>
                <td><?php echo number_format($file['file_size']/1024,2); ?> KB</td>
                <td><?php echo ucwords(str_replace('_',' ',html_escape($file['module_guess']))); ?></td>
                <td><?php echo html_escape($file['modified_time']); ?></td>
                <td class="sc-actions"><a class="btn btn-info btn-xs" target="_blank" href="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>"><?php echo _l('preview'); ?></a><a class="btn btn-default btn-xs" href="<?php echo admin_url('crm_file_explorer_manager/properties/'.$file['id']); ?>"><?php echo _l('properties'); ?></a></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div></div>
  </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/js/crm_file_explorer_manager.js'); ?>"></script>
</body></html>
