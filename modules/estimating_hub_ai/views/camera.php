<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-camera"></i> Camera Estimator</h4><p>Upload job photos, connect them to CRM projects, then create estimate drafts from the notes and photo context.</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div>
<?php $this->load->view('estimating_hub_ai/partials/nav',['nav'=>$nav]); ?>
<div class="scie-card"><h4>Upload Job Photo</h4>
<?php echo form_open_multipart(admin_url('estimating_hub_ai/camera')); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="row">
  <div class="col-md-3"><label>Photo</label><input type="file" name="photo" accept="image/*,.pdf" class="form-control" capture="environment" required></div>
  <div class="col-md-3"><label>Related Project</label><select name="project_id" class="form-control"><option value="">No Project Selected</option><?php foreach($projects as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo html_escape($p['name']); ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label>Project Notes / Scope</label><textarea name="notes" class="form-control" rows="3" placeholder="Example: bathroom shower tile replacement, damaged drywall, electrical panel, roof leak..."></textarea></div>
  <div class="col-md-2"><label>Create Estimate</label><div class="checkbox"><label><input type="checkbox" name="create_estimate" value="1"> Create draft now</label></div></div>
</div>
<br><button class="btn btn-success btn-sm"><i class="fa fa-upload"></i> Upload Photo</button><?php echo form_close(); ?></div>
<div class="scie-toolbar"><input class="form-control input-sm scie-filter" placeholder="Filter photos..."><a href="<?php echo admin_url('estimating_hub_ai/reload'); ?>" class="btn btn-default btn-sm">Reload</a></div>
<div class="row scie-grid">
<?php foreach($photos as $p): $url=base_url($p['file_path']); $ext=strtolower(pathinfo($p['file_name'],PATHINFO_EXTENSION)); ?>
<div class="col-md-3 col-sm-6 scie-filter-row"><div class="scie-photo-card">
<?php if(in_array($ext,['jpg','jpeg','png','gif','webp','heic'])): ?><a href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $url; ?>" alt="<?php echo html_escape($p['file_name']); ?>"></a><?php else: ?><div class="scie-file-icon"><i class="fa fa-file"></i></div><?php endif; ?>
<strong><?php echo html_escape($p['file_name']); ?></strong><small><?php echo _dt($p['datecreated']); ?></small><p><?php echo html_escape(ehai_limit($p['notes'],120)); ?></p>
<div class="btn-group btn-group-xs"><a class="btn btn-default" target="_blank" href="<?php echo $url; ?>">Open</a><a class="btn btn-success" href="<?php echo admin_url('estimating_hub_ai/create_estimate_from_photo/'.(int)$p['id']); ?>">Create Estimate</a></div>
</div></div>
<?php endforeach; ?>
</div>
</div></div></div></div><?php init_tail(); ?>
