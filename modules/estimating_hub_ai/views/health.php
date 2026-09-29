<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s ehai-panel"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-calculator"></i> <?php echo html_escape($title); ?></h4><p>Smart Choice Construction Intelligence Engine</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div><?php $this->load->view('estimating_hub_ai/partials/nav', ['nav'=>$nav]); ?>

<table class="table table-striped table-bordered"><thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead><tbody><?php foreach($checks as $c){ ?><tr><td><?php echo html_escape($c['name']); ?></td><td><?php echo $c['status']?'<span class="label label-success">Pass</span>':'<span class="label label-danger">Fail</span>'; ?></td><td><?php echo html_escape($c['message']); ?></td></tr><?php } ?></tbody></table>

</div></div></div></div></div></div>
<?php init_tail(); ?>