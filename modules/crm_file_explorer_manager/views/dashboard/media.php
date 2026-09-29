<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer"><div class="content">
  <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?>
  <div class="row"><div class="col-md-12">
    <div class="sc-card">
      <h3 class="sc-title"><i class="fa fa-image"></i> <?php echo _l('media_viewer'); ?></h3>
      <p class="text-muted"><?php echo _l('media_refresh_explanation'); ?></p>
      <div class="sc-media-toolbar">
        <form method="get" class="form-inline sc-media-filter">
          <select name="type" class="form-control"><option value="image" <?php echo $type==='image'?'selected':''; ?>><?php echo _l('images'); ?></option><option value="video" <?php echo $type==='video'?'selected':''; ?>><?php echo _l('videos'); ?></option><option value="document" <?php echo $type==='document'?'selected':''; ?>><?php echo _l('documents'); ?></option></select>
          <input type="text" name="search" class="form-control" placeholder="<?php echo _l('search_files'); ?>" value="<?php echo html_escape($this->input->get('search')); ?>">
          <button class="btn sc-btn-blue sc-btn-sm" type="submit"><i class="fa fa-filter"></i> <?php echo _l('apply_filter'); ?></button>
        </form>
        <?php echo form_open(admin_url('crm_file_explorer_manager/scan'), ['class'=>'sc-media-rescan']); ?>
          <input type="hidden" name="base_path" value="<?php echo html_escape(get_option('crm_file_explorer_manager_root_path')); ?>">
          <input type="hidden" name="redirect_to" value="media">
          <button class="btn sc-btn sc-btn-sm" type="submit"><i class="fa fa-refresh"></i> <?php echo _l('rescan_media'); ?></button>
        <?php echo form_close(); ?>
      </div>
    </div>
    <?php if (empty($files)) { ?>
      <div class="sc-card sc-empty-state"><i class="fa fa-picture-o"></i><h4><?php echo _l('no_media_indexed'); ?></h4><p><?php echo _l('no_media_indexed_help'); ?></p></div>
    <?php } else { ?>
      <div class="sc-media-grid">
        <?php foreach($files as $file){ $pid='media_'.$file['id']; ?>
          <div class="sc-media-card">
            <?php if($file['file_type']==='video'){ ?><video id="<?php echo $pid; ?>" controls src="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>"></video><?php } elseif($file['file_type']==='image'){ ?><img id="<?php echo $pid; ?>" src="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>" alt="<?php echo html_escape($file['file_name']); ?>"><?php } else { ?><div class="sc-stat"><strong><?php echo strtoupper($file['extension']); ?></strong><?php echo _l('documents'); ?></div><?php } ?>
            <h5><?php echo html_escape($file['file_name']); ?></h5>
            <div class="sc-path"><?php echo html_escape($file['relative_path']); ?></div>
            <p><?php echo number_format($file['file_size']/1024,2); ?> KB</p>
            <div class="sc-actions"><a class="btn btn-default btn-xs" href="<?php echo admin_url('crm_file_explorer_manager/properties/'.$file['id']); ?>"><?php echo _l('properties'); ?></a><button class="btn btn-info btn-xs" data-fullscreen-target="#<?php echo $pid; ?>"><?php echo _l('full_screen'); ?></button></div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div></div>
</div></div>
<?php init_tail(); ?><script src="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/js/crm_file_explorer_manager.js'); ?>"></script></body></html>
