<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<?php
$addUrl = admin_url('purchasing_hub/simple/'.$type);
$columns = [
  'orders' => ['','Created By','Order Number','Project','Client','Invoice','Shipping Address','Status','Receipt','Total','Created Date'],
  'bills' => ['','Created By','Bill Number','Project','Status','Payment','Amount','Paid','Balance','Created Date'],
  'quotes' => ['','Created By','Quote Number','Project','Status','Amount','Created Date'],
  'contracts' => ['','Created By','Subject','Project','Type','Status','Value','Created Date'],
];
$methodMap = ['orders'=>'purchase_orders','bills'=>'accounts_payable','quotes'=>'vendor_quotes','contracts'=>'contracts'];
?>
<div class="panel_s"><div class="panel-body">
  <div class="ph-action-row purchasing-hub-action-row ph-primary-action-row">
    <a href="<?php echo $addUrl; ?>" class="btn btn-primary btn-sm ph-btn-compact">Add <?php echo html_escape($title); ?></a>
    <a href="<?php echo admin_url('purchasing_hub/sample/'.$type); ?>" class="btn btn-info btn-sm ph-btn-compact">Sample Import File</a>
  </div>
  <?php echo form_open(admin_url('purchasing_hub/'.$methodMap[$type]), ['method'=>'get','class'=>'form-inline m-b-md ph-filter-row ph-search-purchase-orders']); ?>
    <input type="text" name="q" class="form-control input-sm" placeholder="Search" value="<?php echo html_escape($this->input->get('q')); ?>">
    <input type="text" name="project_name" class="form-control input-sm" placeholder="Project" value="<?php echo html_escape($this->input->get('project_name')); ?>">
    <input type="text" name="status" class="form-control input-sm" placeholder="Status" value="<?php echo html_escape($this->input->get('status')); ?>">
    <?php if($type === 'bills'){ ?><select name="payment_status" class="form-control input-sm"><option value="">Payment Status</option><option value="Paid" <?php echo $this->input->get('payment_status') === 'Paid' ? 'selected' : ''; ?>>Paid</option><option value="Pending" <?php echo $this->input->get('payment_status') === 'Pending' ? 'selected' : ''; ?>>Pending</option></select><?php } ?>
    <input type="text" name="created_from" class="form-control input-sm datepicker" placeholder="Created From" value="<?php echo html_escape($this->input->get('created_from')); ?>">
    <input type="text" name="created_to" class="form-control input-sm datepicker" placeholder="Created To" value="<?php echo html_escape($this->input->get('created_to')); ?>">
    <button type="submit" class="btn btn-default btn-sm">Filter</button>
  <?php echo form_close(); ?>
  <?php echo form_open(admin_url('purchasing_hub/massive_delete/'.$type), ['id' => 'ph-mass-delete-form']); ?>
  <div class="ph-dt-actions" data-ph-dt-actions="1" aria-label="Table actions">
    <a href="<?php echo admin_url('purchasing_hub/import/'.$type); ?>" class="btn btn-default btn-sm ph-btn-compact">Import</a>
    <a href="<?php echo admin_url('purchasing_hub/'.$methodMap[$type]); ?>" class="btn btn-default btn-sm ph-btn-compact">Refresh</a>
    <button type="submit" form="ph-mass-delete-form" class="btn btn-danger btn-sm ph-btn-compact" onclick="return confirm('Delete selected records?');">Massive Delete</button>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered dt-table purchasing-hub-table">
      <thead><tr><?php foreach($columns[$type] as $c){ echo '<th>'.($c === '' ? '<input type="checkbox" onclick="$(\'.ph-row-check\').prop(\'checked\', this.checked);">' : html_escape($c)).'</th>'; } ?><th>Options</th></tr></thead>
      <tbody><?php foreach($rows as $row){ $staff = !empty($row['created_by']) ? get_staff($row['created_by']) : null; ?><tr>
        <td><input class="ph-row-check" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
        <td><?php if($staff){ echo staff_profile_image($row['created_by'], ['staff-profile-image-small','mright5']); echo html_escape(trim($staff->firstname.' '.$staff->lastname)); } else { echo '-'; } ?></td>
        <?php if($type==='orders'){ ?><td><?php echo html_escape($row['order_number'] ?? ''); ?></td><td><?php echo html_escape($row['project_name'] ?? ''); ?></td><td><?php echo html_escape($row['client_name'] ?? ''); ?></td><td><?php echo html_escape($row['invoice_number'] ?? ''); ?></td><td><?php echo html_escape($row['shipping_address'] ?? ''); ?></td><td><?php echo html_escape($row['status'] ?? ''); ?></td><td><?php echo !empty($row['receipt_confirmed']) ? '<span class="label label-success">Received</span>' : '<span class="label label-default">Pending</span>'; ?></td><td><?php echo app_format_money($row['total'] ?? 0, get_base_currency()); ?></td><td><?php echo _dt($row['datecreated'] ?? ''); ?></td><?php } ?>
        <?php if($type==='bills'){ ?><td><?php echo html_escape($row['bill_number'] ?? ''); ?></td><td><?php echo html_escape($row['project_name'] ?? ''); ?></td><td><?php echo html_escape($row['status'] ?? ''); ?></td><td><?php echo (($row['payment_status'] ?? 'Pending') === 'Paid') ? '<span class="label label-success">PAID</span>' : '<span class="label label-warning">PENDING</span>'; ?></td><td><?php echo app_format_money($row['amount'] ?? 0, get_base_currency()); ?></td><td><?php echo app_format_money($row['amount_paid'] ?? 0, get_base_currency()); ?></td><td><?php echo app_format_money(((float)($row['amount'] ?? 0)-(float)($row['amount_paid'] ?? 0)), get_base_currency()); ?></td><td><?php echo _dt($row['datecreated'] ?? ''); ?></td><?php } ?>
        <?php if($type==='quotes'){ ?><td><?php echo html_escape($row['quote_number'] ?? ''); ?></td><td><?php echo html_escape($row['project_name'] ?? ''); ?></td><td><?php echo html_escape($row['status'] ?? ''); ?></td><td><?php echo app_format_money($row['amount'] ?? 0, get_base_currency()); ?></td><td><?php echo _dt($row['datecreated'] ?? ''); ?></td><?php } ?>
        <?php if($type==='contracts'){ ?><td><?php echo html_escape($row['subject'] ?? ''); ?></td><td><?php echo html_escape($row['project_name'] ?? ''); ?></td><td><?php echo html_escape($row['contract_type'] ?? ''); ?></td><td><?php echo html_escape($row['status'] ?? ''); ?></td><td><?php echo app_format_money($row['contract_value'] ?? 0, get_base_currency()); ?></td><td><?php echo _dt($row['datecreated'] ?? ''); ?></td><?php } ?>
        <td>
          <a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/view/'.$type.'/'.$row['id']); ?>">View</a>
          <a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/simple/'.$type.'/'.$row['id']); ?>">Edit</a>
          <a class="btn btn-info btn-xs" target="_blank" href="<?php echo admin_url('purchasing_hub/pdf/'.$type.'/'.$row['id']); ?>">PDF</a>
          <a class="btn btn-success btn-xs" href="<?php echo admin_url('purchasing_hub/send_email/'.$type.'/'.$row['id']); ?>">Email</a>
          <?php if($type==='orders' && empty($row['receipt_confirmed'])){ ?><a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/mark_po_received/'.$row['id']); ?>">Mark Received</a><?php } ?>
          <a class="btn btn-warning btn-xs" href="<?php echo admin_url('purchasing_hub/copy/'.$type.'/'.$row['id']); ?>">Copy</a>
          <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('purchasing_hub/delete/'.$type.'/'.$row['id']); ?>">Delete</a>
        </td>
      </tr><?php } ?></tbody>
    </table>
  </div>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
