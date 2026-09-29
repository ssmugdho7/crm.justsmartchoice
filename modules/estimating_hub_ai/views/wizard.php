<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s ehai-panel"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-calculator"></i> <?php echo html_escape($title); ?></h4><p>Smart Choice Construction Intelligence Engine</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div><?php $this->load->view('estimating_hub_ai/partials/nav', ['nav'=>$nav]); ?>

<?php echo form_open(admin_url('estimating_hub_ai/wizard')); ?>
<div class="row"><div class="col-md-6"><label>Estimate Name</label><input name="name" class="form-control" placeholder="Kitchen Remodel - Tampa"></div><div class="col-md-6"><label>Project Address / Client Notes</label><input name="project_address" class="form-control" placeholder="Enter address or notes"></div></div>
<br><label>Describe The Work</label><textarea name="scope_summary" class="form-control" rows="9" placeholder="Example: Remodel kitchen, remove cabinets, install quartz, tile backsplash, update lighting, replace flooring..."></textarea>
<br><button class="btn btn-primary"><i class="fa fa-magic"></i> Create AI Estimate Draft</button>
<?php echo form_close(); ?>
<div class="alert alert-info mtop20">This form now uses the CRM security token, so the expired page error should stop. AI suggestions are support tools. Review all pricing before sending.</div>

</div></div></div></div></div></div>
<?php init_tail(); ?>