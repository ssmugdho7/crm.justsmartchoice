<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-folder-open-o"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions"><?php if ($package) { ?><a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_customer_package_ready/' . (int)$package['id']); ?>">Mark Ready To Send</a><?php if ((int)$package['ai_estimate_id'] > 0) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/estimate_review/' . (int)$package['ai_estimate_id']); ?>">Back To Review</a><?php } ?><?php } ?><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/customer_packages'); ?>">Back</a></div></div>
<?php if ($package) { ?>
<?php echo form_open(admin_url('usi_smartchoice_seo/save_customer_package/' . (int)$package['id'])); ?>
<div class="row"><div class="col-md-8"><label>Package Title</label><input name="package_title" class="form-control" value="<?php echo html_escape($package['package_title']); ?>"></div><div class="col-md-4"><label>Status</label><select name="package_status" class="form-control"><?php foreach (['draft','ready_for_review','ready_to_send','sent','archived'] as $status) { ?><option value="<?php echo html_escape($status); ?>" <?php echo (($package['package_status'] ?? '') === $status) ? 'selected' : ''; ?>><?php echo ucwords(str_replace('_',' ',$status)); ?></option><?php } ?></select></div></div>
<div class="row mtop15"><div class="col-md-12"><label>Customer Scope</label><textarea name="customer_scope" rows="8" class="form-control"><?php echo html_escape((string)$package['customer_scope']); ?></textarea></div></div>
<div class="row mtop15"><div class="col-md-6"><label>Exclusions</label><textarea name="exclusions" rows="5" class="form-control"><?php echo html_escape((string)$package['exclusions']); ?></textarea></div><div class="col-md-6"><label>Payment Schedule</label><textarea name="payment_schedule" rows="5" class="form-control"><?php echo html_escape((string)$package['payment_schedule']); ?></textarea></div></div>
<div class="row mtop15"><div class="col-md-12"><label>Internal Handoff</label><textarea name="internal_handoff" rows="8" class="form-control"><?php echo html_escape((string)$package['internal_handoff']); ?></textarea></div></div>
<div class="row mtop15"><div class="col-md-12"><label>Embed / Delivery Notes</label><textarea name="embed_notes" rows="4" class="form-control"><?php echo html_escape((string)$package['embed_notes']); ?></textarea></div></div>
<button class="btn btn-primary btn-sm mtop15" type="submit">Save Package</button>
<?php echo form_close(); ?>
<?php } else { ?><div class="alert alert-warning">Customer package was not found.</div><?php } ?>
</div></div></div></div><?php init_tail(); ?>
