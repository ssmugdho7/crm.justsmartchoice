<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4><i class="fa fa-search"></i> Search Estimating Hub AI</h4><?php $this->load->view('estimating_hub_ai/partials/nav',['nav'=>$nav]); ?>
<?php echo form_open(admin_url('estimating_hub_ai/search'), ['method'=>'get']); ?><div class="input-group"><input name="q" value="<?php echo html_escape($q); ?>" class="form-control" placeholder="Search cost items, documents, photos, Home Depot SKUs, estimates, invoices, proposals..."><span class="input-group-btn"><button class="btn btn-success">Search</button></span></div><?php echo form_close(); ?>
<br>
<?php foreach($results as $type=>$rows): ?><h4><?php echo html_escape(ucwords(str_replace('_',' ', $type))); ?></h4><div class="table-responsive"><table class="table table-striped table-condensed"><tbody><?php foreach($rows as $r): ?><tr><?php foreach($r as $v): ?><td><?php echo html_escape(ehai_limit((string)$v,120)); ?></td><?php endforeach; ?></tr><?php endforeach; if(empty($rows)): ?><tr><td>No records found.</td></tr><?php endif; ?></tbody></table></div><?php endforeach; ?>
</div></div></div></div><?php init_tail(); ?>
