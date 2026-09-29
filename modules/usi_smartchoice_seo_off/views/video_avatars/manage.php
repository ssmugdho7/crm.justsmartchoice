<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-user-circle"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_avatar'); ?>"><i class="fa fa-plus"></i> New Avatar</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/avatars'); ?>"><i class="fa fa-download"></i> Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/avatars'); ?>"><i class="fa fa-file-excel-o"></i> Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_avatars'); ?>"><i class="fa fa-refresh"></i> Reload</a>
</div></div>
<?php echo form_open_multipart(admin_url('usi_smartchoice_seo/import_video_avatars'), ['class'=>'usi-import']); ?>
<input type="file" name="import_file" accept=".csv" class="form-control input-sm"><button class="btn btn-default btn-sm" type="submit"><i class="fa fa-upload"></i> Import</button>
<?php echo form_close(); ?>
<div class="table-responsive"><table class="table table-condensed sc-table usi-compact-table sc-wide-actions-table"><thead><tr><th class="sc-left">Name</th><th class="sc-left">Type</th><th class="sc-left">Position</th><th class="sc-left">Status</th><th class="sc-left">Preview</th><th class="sc-actions-col">Actions</th></tr></thead><tbody>
<?php foreach ($avatars as $row) { ?><tr><td class="sc-left"><?php echo html_escape($row['avatar_name']); ?></td><td class="sc-left"><?php echo html_escape(ucwords(str_replace('_', ' ', $row['avatar_type']))); ?></td><td class="sc-left"><?php echo html_escape(ucwords(str_replace('_', ' ', $row['position_name']))); ?></td><td class="sc-left"><?php echo html_escape(ucwords(str_replace('_', ' ', $row['status']))); ?></td><td class="sc-left"><?php if (!empty($row['source_photo'])) { ?><a class="btn btn-info btn-xs" href="<?php echo base_url($row['source_photo']); ?>" target="_blank"><i class="fa fa-eye"></i> Preview</a><?php } else { ?><span class="text-muted">Stock avatar</span><?php } ?></td><td class="sc-actions-col"><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/video_avatar/' . (int)$row['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_video_avatar/' . (int)$row['id']); ?>">Delete</a></td></tr><?php } ?>
<?php if (empty($avatars)) { ?><tr><td colspan="6" class="text-center text-muted">No avatars found.</td></tr><?php } ?>
</tbody></table></div>
</div></div></div></div><?php init_tail(); ?>
