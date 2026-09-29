<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php if (!empty($item)) { ?>
<h4><i class="fa fa-database"></i> <?php echo html_escape($item['source_title']); ?></h4>
<div class="btn-toolbar sc-toolbar"><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/memory_engine'); ?>">Back</a><a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/memory_item/' . (int)$item['id']); ?>">Edit</a></div>
<table class="table table-bordered sc-table mtop15"><tbody>
<tr><th width="200">Source</th><td><?php echo html_escape($item['source_type']); ?> #<?php echo (int)$item['source_id']; ?></td></tr>
<tr><th>Customer ID</th><td><?php echo (int)$item['customer_id']; ?></td></tr>
<tr><th>Project ID</th><td><?php echo (int)$item['project_id']; ?></td></tr>
<tr><th>Category</th><td><?php echo html_escape($item['service_category']); ?></td></tr>
<tr><th>Total</th><td><?php echo app_format_money((float)$item['amount_total'], get_base_currency()); ?></td></tr>
<tr><th>Status</th><td><?php echo html_escape($item['memory_status']); ?></td></tr>
<tr><th>Summary</th><td><?php echo nl2br(html_escape($item['summary'])); ?></td></tr>
<tr><th>Keywords</th><td><?php echo nl2br(html_escape($item['keywords'])); ?></td></tr>
<tr><th>Memory Text</th><td><?php echo nl2br(html_escape($item['memory_text'])); ?></td></tr>
</tbody></table>
<?php } else { ?>
<div class="alert alert-warning">Memory item not found.</div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
