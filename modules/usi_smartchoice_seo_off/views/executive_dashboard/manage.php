<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content sc-ai-wrap">
    <?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
    <div class="panel_s">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-8">
            <h4 class="no-margin"><i class="fa fa-line-chart"></i> AI Executive Dashboard</h4>
            <p class="text-muted">Company-wide AI operations overview for estimates, field workflow, purchasing, scheduling, closeout, warranty, and communications.</p>
          </div>
          <div class="col-md-4 text-right">
            <a href="<?php echo admin_url('usi_smartchoice_seo/create_executive_snapshot'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-camera"></i> Create Snapshot</a>
            <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a>
          </div>
        </div>
        <hr>
        <div class="row sc-kpi-grid">
          <div class="col-md-3 col-sm-6"><a class="sc-kpi sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>"><span>Pipeline Value</span><strong><?php echo app_format_money((float)$summary['pipeline_value'], get_base_currency()); ?></strong></a></div>
          <div class="col-md-3 col-sm-6"><a class="sc-kpi sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/estimate_review_queue?status=approved'); ?>"><span>Approved Estimate Value</span><strong><?php echo app_format_money((float)$summary['approved_value'], get_base_currency()); ?></strong></a></div>
          <div class="col-md-3 col-sm-6"><a class="sc-kpi sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>"><span>Open PO Value</span><strong><?php echo app_format_money((float)$summary['purchase_open_value'], get_base_currency()); ?></strong></a></div>
          <div class="col-md-3 col-sm-6"><a class="sc-kpi sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty?status=warranty_active'); ?>"><span>Active Warranties</span><strong><?php echo (int)$summary['warranties_active']; ?></strong></a></div>
        </div>
        <div class="row sc-kpi-grid">
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates?status=draft'); ?>"><span>Draft Estimates</span><strong><?php echo (int)$summary['ai_estimates_draft']; ?></strong></a></div>
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/field_verifications?status=ready_for_estimate'); ?>"><span>Field Ready</span><strong><?php echo (int)$summary['field_ready']; ?></strong></a></div>
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>"><span>Open POs</span><strong><?php echo (int)$summary['purchase_orders_open']; ?></strong></a></div>
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/scheduling'); ?>"><span>Open Schedules</span><strong><?php echo (int)$summary['schedules_open']; ?></strong></a></div>
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty'); ?>"><span>Open Closeouts</span><strong><?php echo (int)$summary['closeouts_open']; ?></strong></a></div>
          <div class="col-md-2 col-sm-4"><a class="sc-kpi small sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/communications?send_status=ready_to_send'); ?>"><span>Ready Messages</span><strong><?php echo (int)$summary['communications_ready']; ?></strong></a></div>
        </div>
      </div>
    </div>

    <div class="panel_s">
      <div class="panel-body">
        <h4><i class="fa fa-history"></i> Executive Snapshots</h4>
        <div class="table-responsive">
          <table class="table table-striped table-condensed sc-table">
            <thead><tr><th>Date</th><th>Pipeline</th><th>Approved</th><th>Open PO Value</th><th>Schedules</th><th>Closeouts</th><th>Warranties</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($snapshots as $row) { ?>
              <tr>
                <td><?php echo html_escape($row['snapshot_date']); ?></td>
                <td><?php echo app_format_money((float)$row['pipeline_value'], get_base_currency()); ?></td>
                <td><?php echo app_format_money((float)$row['approved_estimate_value'], get_base_currency()); ?></td>
                <td><?php echo app_format_money((float)$row['open_purchase_order_value'], get_base_currency()); ?></td>
                <td><?php echo (int)$row['open_schedule_count']; ?></td>
                <td><?php echo (int)$row['open_closeout_count']; ?></td>
                <td><?php echo (int)$row['active_warranty_count']; ?></td>
                <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/generate_executive_actions/' . (int)$row['id']); ?>"><i class="fa fa-magic"></i> Generate Actions</a></td>
              </tr>
              <?php } ?>
              <?php if (empty($snapshots)) { ?><tr><td colspan="8" class="text-center text-muted">No snapshots yet. Click Create Snapshot.</td></tr><?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="panel_s">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-6"><h4><i class="fa fa-check-square-o"></i> Executive Actions</h4></div>
          <div class="col-md-6 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/executive_dashboard'); ?>"><i class="fa fa-filter"></i> All</a>
            <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/executive_dashboard?status=open'); ?>"><i class="fa fa-folder-open"></i> Open</a>
            <a class="btn btn-default btn-sm" onclick="window.print();"><i class="fa fa-download"></i> Export</a>
          </div>
        </div>
        <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_executive_actions')); ?>
        <div class="table-responsive">
          <table class="table table-striped table-condensed sc-table">
            <thead><tr><th><input type="checkbox" onclick="$('.sc-action-check').prop('checked', this.checked);"></th><th>Action</th><th>Type</th><th>Priority</th><th>Area</th><th>Due Date</th><th>Status</th><th>Options</th></tr></thead>
            <tbody>
              <?php foreach ($actions as $row) { ?>
              <tr>
                <td><input class="sc-action-check" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
                <td><strong><?php echo html_escape($row['action_title']); ?></strong><br><span class="text-muted"><?php echo nl2br(html_escape($row['notes'])); ?></span></td>
                <td><?php echo html_escape(str_replace('_', ' ', $row['action_type'])); ?></td>
                <td><?php echo html_escape(ucfirst($row['priority'])); ?></td>
                <td><?php echo html_escape($row['related_area']); ?></td>
                <td><?php echo html_escape($row['due_date']); ?></td>
                <td><span class="label label-<?php echo $row['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo html_escape(str_replace('_', ' ', $row['status'])); ?></span></td>
                <td><?php if ($row['status'] !== 'completed') { ?><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/mark_executive_action_done/' . (int)$row['id']); ?>"><i class="fa fa-check"></i> Done</a><?php } ?></td>
              </tr>
              <?php } ?>
              <?php if (empty($actions)) { ?><tr><td colspan="8" class="text-center text-muted">No executive actions. Generate actions from a snapshot.</td></tr><?php } ?>
            </tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-danger btn-sm _delete"><i class="fa fa-trash"></i> Mass Delete</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
