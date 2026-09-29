<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content sc-ai-wrap">
    <?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
    <div class="panel_s">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-8">
            <h4 class="no-margin"><i class="fa fa-lightbulb-o"></i> AI Business Intelligence &amp; Recommendations</h4>
            <p class="text-muted">Management recommendations built from Sammy AI estimates, purchasing, schedules, closeout, warranties, and communications.</p>
          </div>
          <div class="col-md-4 text-right">
            <a href="<?php echo admin_url('usi_smartchoice_seo/create_business_snapshot'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-camera"></i> Create BI Snapshot</a>
            <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a>
          </div>
        </div>
        <hr>
        <div class="row sc-kpi-grid">
          <div class="col-md-3 col-sm-6"><div class="sc-kpi"><span>Pipeline Value</span><strong><?php echo app_format_money((float)$summary['pipeline_value'], get_base_currency()); ?></strong></div></div>
          <div class="col-md-3 col-sm-6"><div class="sc-kpi"><span>Approved Value</span><strong><?php echo app_format_money((float)$summary['approved_value'], get_base_currency()); ?></strong></div></div>
          <div class="col-md-3 col-sm-6"><div class="sc-kpi"><span>Open PO Value</span><strong><?php echo app_format_money((float)$summary['purchase_open_value'], get_base_currency()); ?></strong></div></div>
          <div class="col-md-3 col-sm-6"><div class="sc-kpi"><span>Risk Level</span><strong><?php echo html_escape(ucfirst($summary['risk_level'])); ?></strong></div></div>
        </div>
        <div class="row sc-kpi-grid">
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Draft Estimates</span><strong><?php echo (int)$summary['ai_estimates_draft']; ?></strong></div></div>
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Ready Messages</span><strong><?php echo (int)$summary['communications_ready']; ?></strong></div></div>
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Open Schedules</span><strong><?php echo (int)$summary['schedules_open']; ?></strong></div></div>
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Open Closeouts</span><strong><?php echo (int)$summary['closeouts_open']; ?></strong></div></div>
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Active Warranties</span><strong><?php echo (int)$summary['warranties_active']; ?></strong></div></div>
          <div class="col-md-2 col-sm-4"><div class="sc-kpi small"><span>Open Recommendations</span><strong><?php echo (int)$summary['open_recommendation_count']; ?></strong></div></div>
        </div>
      </div>
    </div>

    <div class="panel_s">
      <div class="panel-body">
        <h4><i class="fa fa-history"></i> BI Snapshots</h4>
        <div class="table-responsive">
          <table class="table table-striped table-condensed sc-table">
            <thead><tr><th>Date</th><th>Pipeline</th><th>Approved</th><th>Open PO</th><th>Messages</th><th>Drafts</th><th>Risk</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($snapshots as $row) { ?>
              <tr>
                <td><?php echo html_escape($row['snapshot_date']); ?></td>
                <td><?php echo app_format_money((float)$row['gross_pipeline_value'], get_base_currency()); ?></td>
                <td><?php echo app_format_money((float)$row['approved_pipeline_value'], get_base_currency()); ?></td>
                <td><?php echo app_format_money((float)$row['open_purchase_value'], get_base_currency()); ?></td>
                <td><?php echo (int)$row['ready_message_count']; ?></td>
                <td><?php echo (int)$row['draft_estimate_count']; ?></td>
                <td><span class="label label-<?php echo $row['risk_level'] === 'high' ? 'danger' : ($row['risk_level'] === 'low' ? 'success' : 'warning'); ?>"><?php echo html_escape(ucfirst($row['risk_level'])); ?></span></td>
                <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/generate_business_recommendations/' . (int)$row['id']); ?>"><i class="fa fa-magic"></i> Generate Recommendations</a></td>
              </tr>
              <?php } ?>
              <?php if (empty($snapshots)) { ?><tr><td colspan="8" class="text-center text-muted">No BI snapshots yet. Click Create BI Snapshot.</td></tr><?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="panel_s">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-6"><h4><i class="fa fa-list"></i> AI Recommendations</h4></div>
          <div class="col-md-6 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/business_intelligence'); ?>"><i class="fa fa-filter"></i> All</a>
            <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/business_intelligence?status=open'); ?>"><i class="fa fa-folder-open"></i> Open</a>
            <a class="btn btn-default btn-sm" onclick="window.print();"><i class="fa fa-download"></i> Export</a>
          </div>
        </div>
        <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_business_recommendations')); ?>
        <div class="table-responsive">
          <table class="table table-striped table-condensed sc-table">
            <thead><tr><th><input type="checkbox" onclick="$('.sc-bi-check').prop('checked', this.checked);"></th><th>Recommendation</th><th>Type</th><th>Priority</th><th>Impact Area</th><th>Due Date</th><th>Status</th><th>Options</th></tr></thead>
            <tbody>
              <?php foreach ($recommendations as $row) { ?>
              <tr>
                <td><input class="sc-bi-check" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
                <td><strong><?php echo html_escape($row['recommendation_title']); ?></strong><br><span class="text-muted"><?php echo nl2br(html_escape($row['recommended_action'])); ?></span><br><small><?php echo nl2br(html_escape($row['expected_impact'])); ?></small></td>
                <td><?php echo html_escape(str_replace('_', ' ', $row['recommendation_type'])); ?></td>
                <td><?php echo html_escape(ucfirst($row['priority'])); ?></td>
                <td><?php echo html_escape($row['impact_area']); ?></td>
                <td><?php echo html_escape($row['due_date']); ?></td>
                <td><span class="label label-<?php echo $row['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo html_escape(str_replace('_', ' ', $row['status'])); ?></span></td>
                <td><?php if ($row['status'] !== 'completed') { ?><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/mark_business_recommendation_done/' . (int)$row['id']); ?>"><i class="fa fa-check"></i> Done</a><?php } ?></td>
              </tr>
              <?php } ?>
              <?php if (empty($recommendations)) { ?><tr><td colspan="8" class="text-center text-muted">No recommendations yet. Create a BI snapshot, then generate recommendations.</td></tr><?php } ?>
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
