<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s ehai-panel"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-calculator"></i> <?php echo html_escape($title); ?></h4><p>Smart Choice Construction Intelligence Engine</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div><?php $this->load->view('estimating_hub_ai/partials/nav', ['nav'=>$nav]); ?>

<div class="ehai-hero"><h2>Estimating Hub AI</h2><p>Build estimates from scope notes, cost rules, photos, documents, and Smart Choice historical pricing.</p></div>
<div class="row">
<div class="col-md-3"><a class="ehai-stat" href="<?php echo admin_url('estimating_hub_ai/search?q=estimate'); ?>"><span>Estimate Drafts</span><strong><?php echo (int)$stats['estimates']; ?></strong></a></div>
<div class="col-md-3"><a class="ehai-stat" href="<?php echo admin_url('estimating_hub_ai/cost_database'); ?>"><span>Cost Items</span><strong><?php echo (int)$stats['cost_items']; ?></strong></a></div>
<div class="col-md-3"><a class="ehai-stat" href="<?php echo admin_url('estimating_hub_ai/documents'); ?>"><span>Training Documents</span><strong><?php echo (int)$stats['documents']; ?></strong></a></div>
<div class="col-md-3"><a class="ehai-stat" href="<?php echo admin_url('estimating_hub_ai/camera'); ?>"><span>Photo Intakes</span><strong><?php echo (int)$stats['photos']; ?></strong></a></div>
</div>
<div class="row mtop20">
<div class="col-md-4"><a class="ehai-card-link" href="<?php echo admin_url('estimating_hub_ai/wizard'); ?>"><i class="fa fa-magic"></i><strong>Start AI Estimate</strong><span>Create a draft from a scope description.</span></a></div>
<div class="col-md-4"><a class="ehai-card-link" href="<?php echo admin_url('estimating_hub_ai/camera'); ?>"><i class="fa fa-camera"></i><strong>Use Camera Estimator</strong><span>Upload photos from phone or desktop.</span></a></div>
<div class="col-md-4"><a class="ehai-card-link" href="<?php echo admin_url('estimating_hub_ai/material_collector'); ?>"><i class="fa fa-cloud-download-alt"></i><strong>Review Collector Data</strong><span>Home Depot and Homewyse public pricing records.</span></a></div>
</div>
<div class="row mtop25">
<div class="col-md-6"><div class="scie-card"><h4>Recent Estimate Drafts</h4><div class="table-responsive"><table class="table table-condensed scie-table"><thead><tr><th>Name</th><th>Status</th><th>Total</th><th>Open</th></tr></thead><tbody><?php foreach(array_slice($estimates,0,8) as $e): ?><tr><td><?php echo html_escape($e['name']); ?></td><td><?php echo html_escape(ucwords($e['status'])); ?></td><td>$<?php echo number_format((float)$e['total'],2); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('estimating_hub_ai/view/'.(int)$e['id']); ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<div class="col-md-6"><div class="scie-card"><h4>Recent Job Photos</h4><div class="row"><?php foreach(array_slice($photos,0,4) as $p): $url=base_url($p['file_path']); $ext=strtolower(pathinfo($p['file_name'],PATHINFO_EXTENSION)); ?><div class="col-xs-6 scie-dashboard-photo"><?php if(in_array($ext,['jpg','jpeg','png','gif','webp'])): ?><a href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $url; ?>" alt="<?php echo html_escape($p['file_name']); ?>"></a><?php else: ?><a href="<?php echo $url; ?>" target="_blank"><i class="fa fa-file"></i> <?php echo html_escape($p['file_name']); ?></a><?php endif; ?><small><?php echo html_escape(ehai_limit($p['notes'],80)); ?></small></div><?php endforeach; ?></div></div></div>
</div>

</div></div></div></div></div></div>
<?php init_tail(); ?>
