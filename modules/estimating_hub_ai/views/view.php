<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s ehai-panel"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-calculator"></i> <?php echo html_escape($title); ?></h4><p>Smart Choice Construction Intelligence Engine</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div><?php $this->load->view('estimating_hub_ai/partials/nav', ['nav'=>$nav]); ?>

<?php if($estimate){ ?>
<h3><?php echo html_escape($estimate->name); ?></h3>
<p><strong>Project Address:</strong> <?php echo html_escape($estimate->project_address ?? ''); ?></p>
<p><?php echo nl2br(html_escape($estimate->scope_summary)); ?></p>
<div class="alert alert-warning"><strong>AI Follow Up:</strong><br><?php echo nl2br(html_escape($estimate->ai_questions ?? '')); ?></div>
<table class="table table-bordered ehai-table"><thead><tr><th>Trade</th><th>Description</th><th>Unit</th><th>Quantity</th><th>Typical Cost</th><th>Total</th></tr></thead><tbody>
<?php foreach($items as $i){ ?><tr><td><?php echo html_escape($i['trade']); ?></td><td><?php echo html_escape($i['description']); ?></td><td><?php echo html_escape($i['unit']); ?></td><td><?php echo (float)$i['qty']; ?></td><td>$<?php echo number_format((float)$i['material_cost'],2); ?></td><td>$<?php echo number_format((float)$i['total'],2); ?></td></tr><?php } ?>
</tbody></table>
<?php } else { ?><div class="alert alert-warning">Estimate not found.</div><?php } ?>

</div></div></div></div></div></div>
<?php init_tail(); ?>