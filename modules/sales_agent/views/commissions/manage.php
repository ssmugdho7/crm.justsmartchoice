<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons">
              <?php if (has_permission('sa_commissions', '', 'create') || is_admin()) { ?>
                <a href="<?php echo admin_url('sales_agent/commission'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('sa_add_commission'); ?></a>
              <?php } ?>
              <div class="clearfix"></div>
            </div>
            <hr class="hr-panel-heading" />
            <div class="row mtop15">
              <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center"><h4><?php echo app_format_money($summary['pending'], get_base_currency()); ?></h4><span><?php echo _l('sa_pending_commission'); ?></span></div></div></div>
              <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center"><h4><?php echo app_format_money($summary['approved'], get_base_currency()); ?></h4><span><?php echo _l('sa_approved_commission'); ?></span></div></div></div>
              <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center"><h4><?php echo app_format_money($summary['paid'], get_base_currency()); ?></h4><span><?php echo _l('sa_paid_commission'); ?></span></div></div></div>
              <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center"><h4><?php echo app_format_money($summary['total'], get_base_currency()); ?></h4><span><?php echo _l('sa_total_commission'); ?></span></div></div></div>
            </div>
            <?php echo form_open(admin_url('sales_agent/commissions'), ['method'=>'get']); ?>
            <div class="row">
              <div class="col-md-2"><?php echo render_select('status', [['id'=>'pending','name'=>_l('sa_pending')],['id'=>'approved','name'=>_l('sa_approved')],['id'=>'paid','name'=>_l('sa_paid')]], ['id','name'], _l('status'), $filters['status']); ?></div>
              <div class="col-md-2"><?php echo render_date_input('from_date', _l('from_date'), $filters['from_date']); ?></div>
              <div class="col-md-2"><?php echo render_date_input('to_date', _l('to_date'), $filters['to_date']); ?></div>
              <div class="col-md-3"><?php echo render_select('staff_id', $staff, ['staffid', ['firstname','lastname']], _l('staff'), $filters['staff_id']); ?></div>
              <div class="col-md-3 mtop25"><button class="btn btn-default" type="submit"><?php echo _l('filter'); ?></button> <a href="<?php echo admin_url('sales_agent/commissions'); ?>" class="btn btn-default"><?php echo _l('clear'); ?></a></div>
            </div>
            <?php echo form_close(); ?>
            <div class="table-responsive mtop20">
              <table class="table table-striped table-commissions">
                <thead><tr><th><?php echo _l('id'); ?></th><th><?php echo _l('invoice'); ?></th><th><?php echo _l('customer'); ?></th><th><?php echo _l('staff'); ?></th><th><?php echo _l('sa_base_amount'); ?></th><th><?php echo _l('sa_rate'); ?></th><th><?php echo _l('sa_expense_deduction'); ?></th><th><?php echo _l('sa_net_commission'); ?></th><th><?php echo _l('status'); ?></th><th><?php echo _l('options'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($commissions as $c) { ?>
                  <tr>
                    <td><?php echo (int)$c['id']; ?></td>
                    <td><?php echo !empty($c['invoice_id']) ? '<a href="'.admin_url('invoices/list_invoices/'.$c['invoice_id']).'">'.html_escape($c['invoice_prefix'].$c['invoice_number']).'</a>' : '-'; ?></td>
                    <td><?php echo html_escape($c['customer_name'] ?? ''); ?></td>
                    <td><?php echo html_escape($c['staff_name'] ?? ''); ?></td>
                    <td><?php echo app_format_money($c['base_amount'], get_base_currency()); ?></td>
                    <td><?php echo $c['commission_type'] === 'fixed' ? app_format_money($c['commission_rate'], get_base_currency()) : app_format_number($c['commission_rate']).'%'; ?></td>
                    <td><?php echo app_format_money($c['expense_deduction'], get_base_currency()); ?></td>
                    <td><strong><?php echo app_format_money($c['net_commission'], get_base_currency()); ?></strong></td>
                    <td><span class="label label-<?php echo $c['status'] === 'paid' ? 'success' : ($c['status'] === 'approved' ? 'info' : 'warning'); ?>"><?php echo _l('sa_'.$c['status']); ?></span></td>
                    <td>
                      <a class="btn btn-default btn-icon" href="<?php echo admin_url('sales_agent/commission/'.$c['id']); ?>"><i class="fa fa-pencil"></i></a>
                      <?php if ($c['status'] !== 'paid' && (has_permission('sa_commissions', '', 'edit') || is_admin())) { ?>
                        <?php echo form_open(admin_url('sales_agent/commission_paid/'.$c['id']), ['style'=>'display:inline']); ?><button class="btn btn-success btn-icon" title="<?php echo _l('sa_mark_paid'); ?>"><i class="fa fa-check"></i></button><?php echo form_close(); ?>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
            <p class="text-muted mtop15"><?php echo _l('sa_commission_report_note'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body></html>
