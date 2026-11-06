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

    <?php foreach($leaders_by_currency as $currency=>$podium){ ?>
    <section class="scaf-commission-podium" aria-label="<?= e($currency.' · '.scaf_rep_label('Top commission earners','Principales comisionistas')); ?>">
    <?php foreach($podium as $rank=>$leader){$person=$leader['is_client']?get_company_name($leader['staffid']):get_staff_full_name($leader['staffid']); ?>
    <article class="scaf-podium-card scaf-podium-<?= $rank; ?>"><span><?= e(['Gold · 1','Silver · 2','Bronze · 3'][$rank]); ?></span><h3><?= e($person); ?></h3><strong><?= e(app_format_money($leader['amount'],$currency)); ?></strong><p><?= e(scaf_rep_label('Actual commissions · current report filters','Comisiones reales · filtros actuales')); ?></p></article>
    <?php } ?></section><?php } ?>
    <?php if(is_admin()){ ?><section class="panel_s"><div class="panel-body"><h3><?= e(scaf_rep_label('Monthly payout review batch','Lote de revisión de pagos mensual')); ?></h3><p><?= e(scaf_rep_label('Approval authorizes this review snapshot only. It does not record a payout, mark commissions paid, create receipts or transfer money.','La aprobación autoriza únicamente esta revisión. No registra pagos ni recibos, ni marca comisiones pagadas ni transfiere dinero.')); ?></p><input type="month" data-commission-month value="<?= e(date('Y-m')); ?>"><button type="button" class="btn btn-primary" data-commission-generate><?= e(scaf_rep_label('Generate monthly payout batch','Generar lote mensual')); ?></button><div data-commission-batch aria-live="polite"></div></div></section><?php } ?>
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
          <thead><tr><th>Date</th><th>Invoice</th><th>Person</th><th>Role</th><th>Type</th><th>Rate</th><th>Tier display</th><th>Commission</th><th>Status</th><th>Notes</th></tr></thead>
          <tbody>
          <?php foreach($rows as $row){
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
              <td><?php $tier=scaf_commission_row_tier($row);echo e($tier?$tier['multiplier'].'× · configured current-month display':'Standard · no active configured boost'); ?></td><td><?php echo e(app_format_money($row['amount'], $row['currency_name']??'')); ?></td>
              <td><?php echo (isset($row['paid']) && (int)$row['paid'] === 1) ? '<span class="label label-success">Paid</span>' : '<span class="label label-warning">Owed</span>'; ?></td>
              <td><?php echo html_escape($row['smartchoice_notes'] ?? ''); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        <div class="sc-summary-box"><?php foreach(scaf_commission_totals($rows) as $currency=>$totals){ ?><p><strong><?= e($currency); ?></strong> · Earned <?= e(app_format_money($totals['earned'],$currency)); ?> · Paid <?= e(app_format_money($totals['paid'],$currency)); ?> · Owed <?= e(app_format_money($totals['owed'],$currency)); ?></p><?php } ?></div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
