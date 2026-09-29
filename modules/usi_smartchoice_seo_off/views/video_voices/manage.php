<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-volume-up"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_voice'); ?>"><i class="fa fa-plus"></i> New Voice</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/voices'); ?>"><i class="fa fa-download"></i> Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/voices'); ?>"><i class="fa fa-file-excel-o"></i> Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_voices'); ?>"><i class="fa fa-refresh"></i> Reload</a>
</div></div>
<?php echo form_open_multipart(admin_url('usi_smartchoice_seo/import_video_voices'), ['class'=>'usi-import']); ?>
<input type="file" name="import_file" accept=".csv" class="form-control input-sm">
<button class="btn btn-default btn-sm" type="submit"><i class="fa fa-upload"></i> Import</button>
<?php echo form_close(); ?>
<div class="table-responsive"><table class="table table-condensed sc-table usi-compact-table"><thead><tr><th>Name</th><th>Language</th><th>Gender</th><th>Status</th><th>Preview</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($voices as $row) { ?><tr>
<td><?php echo html_escape($row['voice_name']); ?></td>
<td><?php echo html_escape($row['language']); ?></td>
<td><?php echo html_escape($row['gender']); ?></td>
<td><?php echo html_escape(ucwords(str_replace('_', ' ', $row['status']))); ?></td>
<td><button type="button" class="btn btn-info btn-xs sc-preview-voice" data-lang="<?php echo html_escape($row['language']); ?>" data-text="<?php echo html_escape($row['sample_text'] ?: 'Smart Choice Contractors USA sample voice preview.'); ?>"><i class="fa fa-play"></i> Play 10 Sec</button></td>
<td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/video_voice/' . (int)$row['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_video_voice/' . (int)$row['id']); ?>">Delete</a></td>
</tr><?php } ?>
<?php if (empty($voices)) { ?><tr><td colspan="6" class="text-center text-muted">No voices found.</td></tr><?php } ?>
</tbody></table></div>
</div></div></div></div><?php init_tail(); ?>
