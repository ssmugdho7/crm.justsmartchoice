<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content smartchoice-commission">
    <div class="sc-commission-hero">
      <div>
        <h2>Smart Choice Commission Report</h2>
        <p>Track commissions by employee, subcontractor, invoice, date, paid, and owed status.</p>
      </div>
      <a href="<?php echo admin_url('commission/quick_assign'); ?>" class="btn btn-default">Quick Assign</a>
    </div>

    <div class="panel_s sc-card">
      <div class="panel-body">
        <?php echo form_open(admin_url('commission/smartchoice_report'), ['method' => 'get']); ?>
        <div class="row">
          <div class="col-md-3"><?php echo render_select('staffid', $staffs, ['staffid', ['firstname', 'lastname']], 'Employee', $filters['staffid'] ?? '', [], [], '', '', false); ?></div>
          <div class="col-md-3"><?php echo render_select('clientid', $clients, ['userid', 'company'], 'Subcontractor / Non-Employee', $filters['clientid'] ?? '', [], [], '', '', false); ?></div>
          <div class="col-md-2"><?php echo render_date_input('from_date', 'From Date', $filters['from_date'] ?? ''); ?></div>
          <div class="col-md-2"><?php echo render_date_input('to_date', 'To Date', $filters['to_date'] ?? ''); ?></div>
          <div class="col-md-2"><?php echo render_select('paid', [['id'=>'','name'=>'All'],['id'=>'0','name'=>'Owed'],['id'=>'1','name'=>'Paid']], ['id','name'], 'Status', $filters['paid'] ?? '', [], [], '', '', false); ?></div>
        </div>
        <button class="btn btn-primary sc-yellow-btn" type="submit">Filter Report</button>
        <a href="<?php echo admin_url('commission/smartchoice_report'); ?>" class="btn btn-default">Reset</a>
        <?php echo form_close(); ?>
      </div>
    </div>

    <div class="panel_s sc-card">
      <div class="panel-body table-responsive">
        <table class="table table-striped">
          <thead><tr><th>Date</th><th>Invoice</th><th>Person</th><th>Role</th><th>Type</th><th>Rate</th><th>Commission</th><th>Status</th><th>Notes</th></tr></thead>
          <tbody>
          <?php $total=0; $paid=0; foreach($rows as $row){ $total += (float)$row['amount']; if(isset($row['paid']) && (int)$row['paid'] === 1){ $paid += (float)$row['amount']; }
            $person = ((int)($row['is_client'] ?? 0) === 1) ? get_company_name($row['staffid']) : get_staff_full_name($row['staffid']);
            $number = (isset($row['prefix']) ? $row['prefix'] : '') . (isset($row['number']) ? $row['number'] : $row['invoice_id']);
          ?>
            <tr>
              <td><?php echo _d($row['date']); ?></td>
              <td><a href="<?php echo admin_url('invoices/list_invoices/'.$row['invoice_id']); ?>">#<?php echo html_escape($number); ?></a></td>
              <td><?php echo html_escape($person); ?></td>
              <td><?php echo html_escape($row['smartchoice_role_label'] ?? ''); ?></td>
              <td><?php echo html_escape(ucfirst($row['smartchoice_commission_mode'] ?? 'Auto')); ?></td>
              <td><?php echo html_escape($row['smartchoice_commission_rate'] ?? ''); ?></td>
              <td><?php echo app_format_money($row['amount'], get_base_currency()); ?></td>
              <td><?php echo (isset($row['paid']) && (int)$row['paid'] === 1) ? '<span class="label label-success">Paid</span>' : '<span class="label label-warning">Owed</span>'; ?></td>
              <td><?php echo html_escape($row['smartchoice_notes'] ?? ''); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        <div class="sc-summary-box">
          <strong>Total:</strong> <?php echo app_format_money($total, get_base_currency()); ?> &nbsp; | &nbsp;
          <strong>Paid:</strong> <?php echo app_format_money($paid, get_base_currency()); ?> &nbsp; | &nbsp;
          <strong>Owed:</strong> <?php echo app_format_money($total-$paid, get_base_currency()); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
