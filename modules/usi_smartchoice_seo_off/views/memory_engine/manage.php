<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
            <div class="clearfix"></div>
            <h4 class="no-margin"><i class="fa fa-database"></i> AI Memory Engine</h4>
            <p class="text-muted mtop10">Central searchable memory for Sammy AI. This index learns from AI estimates, CRM estimates, pricing rows, customer packages, project handoffs, and customer communications.</p>
            <div class="row mtop15">
              <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['memory_items'] ?? 0); ?></strong><br>Memory Items</div></div>
              <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['memory_runs'] ?? 0); ?></strong><br>Index Runs</div></div>
              <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['customers_indexed'] ?? 0); ?></strong><br>Customers</div></div>
              <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['projects_indexed'] ?? 0); ?></strong><br>Projects</div></div>
              <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['estimates_indexed'] ?? 0); ?></strong><br>Estimates</div></div>
            </div>
            <hr>
            <div class="btn-toolbar sc-toolbar" role="toolbar">
              <a href="<?php echo admin_url('usi_smartchoice_seo/memory_item'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Memory Item</a>
              <a href="<?php echo admin_url('usi_smartchoice_seo/rebuild_memory_index'); ?>" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> Rebuild Memory Index</a>
              <a href="<?php echo admin_url('usi_smartchoice_seo/export/memory_items'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</a>
              <a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/memory_items'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
              <a href="<?php echo admin_url('usi_smartchoice_seo/memory_engine'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
            </div>
            <?php echo form_open(admin_url('usi_smartchoice_seo/memory_search'), ['class' => 'mtop15']); ?>
              <div class="row">
                <div class="col-md-8">
                  <input type="text" name="query" class="form-control" value="<?php echo html_escape($query ?? ($filters['search'] ?? '')); ?>" placeholder="Ask Sammy AI memory: impact windows, bathroom remodel, Hernando, Samuel Cabrera, permit, estimate price...">
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-info btn-block"><i class="fa fa-search"></i> Search Memory</button>
                </div>
              </div>
            <?php echo form_close(); ?>
            <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_memory_items')); ?>
            <div class="table-responsive mtop20">
              <table class="table table-bordered table-striped sc-table">
                <thead>
                  <tr>
                    <th width="35"><input type="checkbox" onclick="$('.sc-memory-check').prop('checked', this.checked);"></th>
                    <th>Title</th>
                    <th>Source</th>
                    <th>Customer</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th width="150">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($items)) { foreach ($items as $item) { ?>
                    <tr>
                      <td><input type="checkbox" class="sc-memory-check" name="ids[]" value="<?php echo (int)$item['id']; ?>"></td>
                      <td><strong><?php echo html_escape($item['source_title']); ?></strong><br><small class="text-muted"><?php echo html_escape($item['summary']); ?></small></td>
                      <td><?php echo html_escape($item['source_type']); ?> #<?php echo (int)$item['source_id']; ?></td>
                      <td><?php echo (int)$item['customer_id']; ?></td>
                      <td><?php echo (int)$item['project_id']; ?></td>
                      <td><?php echo html_escape($item['service_category']); ?></td>
                      <td><?php echo app_format_money((float)$item['amount_total'], get_base_currency()); ?></td>
                      <td><span class="label label-default"><?php echo html_escape($item['memory_status']); ?></span></td>
                      <td><?php echo html_escape($item['updated_at']); ?></td>
                      <td>
                        <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_memory_item/' . (int)$item['id']); ?>"><i class="fa fa-eye"></i> View</a>
                        <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/memory_item/' . (int)$item['id']); ?>"><i class="fa fa-pencil"></i></a>
                        <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_memory_item/' . (int)$item['id']); ?>"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php } } else { ?>
                    <tr><td colspan="10" class="text-center text-muted">No memory items yet. Click Rebuild Memory Index.</td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button>
            <?php echo form_close(); ?>
            <hr>
            <h5>Recent Index Runs</h5>
            <div class="table-responsive">
              <table class="table table-condensed table-bordered sc-table">
                <thead><tr><th>ID</th><th>Status</th><th>Items Indexed</th><th>Records Scanned</th><th>Sources</th><th>Finished</th></tr></thead>
                <tbody>
                  <?php if (!empty($runs)) { foreach ($runs as $run) { ?>
                    <tr><td><?php echo (int)$run['id']; ?></td><td><?php echo html_escape($run['run_status']); ?></td><td><?php echo (int)$run['items_indexed']; ?></td><td><?php echo (int)$run['records_scanned']; ?></td><td><?php echo html_escape($run['source_summary']); ?></td><td><?php echo html_escape($run['finished_at']); ?></td></tr>
                  <?php } } else { ?>
                    <tr><td colspan="6" class="text-center text-muted">No index run has been logged yet.</td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
