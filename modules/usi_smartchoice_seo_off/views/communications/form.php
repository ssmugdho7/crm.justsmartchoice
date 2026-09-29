<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body"><h4><?php echo html_escape($title); ?></h4>
<?php echo form_open(current_url()); ?>
<div class="row">
<div class="col-md-3"><?php echo render_input('ai_estimate_id','AI Estimate ID',$communication['ai_estimate_id'] ?? '','number'); ?></div>
<div class="col-md-3"><?php echo render_input('customer_package_id','Customer Package ID',$communication['customer_package_id'] ?? '','number'); ?></div>
<div class="col-md-3"><?php echo render_input('customer_id','Customer ID',$communication['customer_id'] ?? '','number'); ?></div>
<div class="col-md-3"><?php echo render_input('lead_id','Lead ID',$communication['lead_id'] ?? '','number'); ?></div>
</div>
<div class="row">
<div class="col-md-4"><label>Communication Type</label><select name="communication_type" class="form-control"><?php foreach(['follow_up','estimate_follow_up','customer_package_follow_up','approval_request','revision_request'] as $type){ ?><option value="<?php echo $type; ?>" <?php echo (($communication['communication_type'] ?? 'follow_up') === $type ? 'selected' : ''); ?>><?php echo ucwords(str_replace('_',' ',$type)); ?></option><?php } ?></select></div>
<div class="col-md-4"><label>Language</label><select name="language" class="form-control"><option value="english" <?php echo (($communication['language'] ?? 'english') === 'english' ? 'selected' : ''); ?>>English</option><option value="spanish" <?php echo (($communication['language'] ?? '') === 'spanish' ? 'selected' : ''); ?>>Spanish</option></select></div>
<div class="col-md-4"><label>Status</label><select name="send_status" class="form-control"><?php foreach(['draft','ready_to_send','sent','needs_revision','cancelled'] as $status){ ?><option value="<?php echo $status; ?>" <?php echo (($communication['send_status'] ?? 'draft') === $status ? 'selected' : ''); ?>><?php echo ucwords(str_replace('_',' ',$status)); ?></option><?php } ?></select></div>
</div>
<?php echo render_input('subject','Subject',$communication['subject'] ?? ''); ?>
<?php echo render_textarea('message_body','Message Body',$communication['message_body'] ?? '', ['rows'=>12]); ?>
<button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-save"></i> Save</button>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/communications'); ?>">Cancel</a>
<?php echo form_close(); ?></div></div></div></div></div></div>
<?php init_tail(); ?>
</body></html>
