<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body">
<div class="scv-toolbar"><h4>Videos</h4><div><a class="btn btn-success btn-sm" href="<?php echo admin_url('sc_ai_video_studio/video'); ?>">New Video</a><a class="btn btn-default btn-sm" href="<?php echo current_url(); ?>">Reload</a></div></div>
<div class="table-responsive"><table class="table table-striped scv-table"><thead><tr><th>Title</th><th>Language</th><th>Status</th><th>Logo</th><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($videos as $video) { ?>
<tr><td><?php echo html_escape($video['title']); ?></td><td><?php echo html_escape($video['language']); ?></td><td><?php echo html_escape($video['status']); ?></td><td><?php echo ((int)$video['logo_enabled'] === 1) ? 'Yes' : 'No'; ?></td><td><?php echo html_escape($video['datecreated']); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('sc_ai_video_studio/view_video/' . $video['id']); ?>">View</a> <a class="btn btn-default btn-xs" href="<?php echo admin_url('sc_ai_video_studio/video/' . $video['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('sc_ai_video_studio/delete_video/' . $video['id']); ?>">Delete</a></td></tr>
<?php } ?>
</tbody></table></div></div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>
