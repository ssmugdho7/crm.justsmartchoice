<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <h4><?php echo html_escape($title); ?></h4>
  <div class="alert alert-success"><?php echo html_escape($result['message']); ?></div>
  <a class="btn btn-primary btn-sm" href="<?php echo admin_url('purchasing_hub/' . ($type === 'items' ? 'items' : ($type === 'vendors' ? 'vendors' : ['orders'=>'purchase_orders','bills'=>'accounts_payable','quotes'=>'vendor_quotes','contracts'=>'contracts'][$type]))); ?>">Open Section</a>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('purchasing_hub/import/'.$type); ?>">Import More</a>
  <hr>
  <div class="table-responsive">
    <table class="table table-bordered dt-table">
      <thead><tr><?php $first = !empty($result['rows'][0]) ? array_keys($result['rows'][0]) : []; foreach($first as $h){ echo '<th>'.html_escape($h).'</th>'; } ?></tr></thead>
      <tbody><?php foreach($result['rows'] as $row){ ?><tr><?php foreach($first as $h){ echo '<td>'.html_escape($row[$h] ?? '').'</td>'; } ?></tr><?php } ?></tbody>
    </table>
  </div>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
