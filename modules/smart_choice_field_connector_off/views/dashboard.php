<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content scfc-wrap">
    <div class="row">
      <div class="col-md-12">
        <div class="scfc-hero">
          <div>
            <h1><i class="fa fa-random"></i> <?php echo _l('scfc_menu_name'); ?></h1>
            <p><?php echo _l('scfc_dashboard_subtitle'); ?></p>
          </div>
          <div class="scfc-hero-actions">
            <a href="<?php echo admin_url('smart_choice_field_connector/health'); ?>" class="btn scfc-btn-blue"><i class="fa fa-heartbeat"></i> <?php echo _l('scfc_health_check'); ?></a>
            <a href="<?php echo admin_url('smart_choice_field_connector/refresh'); ?>" class="btn scfc-btn"><i class="fa fa-refresh"></i> <?php echo _l('scfc_refresh'); ?></a>
            <a href="<?php echo admin_url('settings?group=smart_choice_field_connector'); ?>" class="btn btn-default"><i class="fa fa-cog"></i> <?php echo _l('settings'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="panel_s scfc-panel">
          <div class="panel-heading"><h4><?php echo _l('scfc_visual_connector'); ?></h4></div>
          <div class="panel-body">
            <div class="scfc-flow">
              <div class="scfc-node">Customer</div><span>→</span>
              <div class="scfc-node">Contact</div><span>→</span>
              <div class="scfc-node">Project</div><span>→</span>
              <div class="scfc-node">Estimate</div><span>→</span>
              <div class="scfc-node">Proposal</div><span>→</span>
              <div class="scfc-node">Contract</div><span>→</span>
              <div class="scfc-node">Invoice</div>
            </div>
            <p class="text-muted mtop15"><?php echo _l('scfc_visual_connector_help'); ?></p>
          </div>
        </div>

        <div class="panel_s scfc-panel">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_mappings'); ?></h4>
            <button class="btn scfc-btn btn-sm" data-toggle="modal" data-target="#scfcMappingModal"><i class="fa fa-plus"></i> <?php echo _l('scfc_add_mapping'); ?></button>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped scfc-table">
                <thead>
                  <tr>
                    <th>Source</th><th>Destination</th><th>Merge Tag</th><th>Status</th><th>Options</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (empty($mappings)) { ?>
                  <tr><td colspan="5" class="text-center text-muted"><?php echo _l('scfc_no_mappings'); ?></td></tr>
                <?php } ?>
                <?php foreach ($mappings as $map) { ?>
                  <tr>
                    <td><strong><?php echo html_escape(ucfirst($map['source_module'])); ?></strong><br><small><?php echo html_escape($map['source_field']); ?></small></td>
                    <td><strong><?php echo html_escape(ucfirst($map['destination_module'])); ?></strong><br><small><?php echo html_escape($map['destination_field']); ?></small></td>
                    <td><code><?php echo html_escape($map['merge_tag']); ?></code></td>
                    <td><?php echo $map['is_active'] ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
                    <td class="scfc-actions">
                      <a href="<?php echo admin_url('smart_choice_field_connector/impact/mapping/'.$map['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-warning"></i> Impact</a>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_mapping/'.$map['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="panel_s scfc-panel" id="custom-tokens">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_custom_tokens'); ?></h4>
            <button class="btn scfc-btn btn-sm" data-toggle="modal" data-target="#scfcTokenModal"><i class="fa fa-plus"></i> <?php echo _l('scfc_add_token'); ?></button>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped scfc-table">
                <thead><tr><th>Name</th><th>Token</th><th>Source</th><th>Status</th><th>Options</th></tr></thead>
                <tbody>
                <?php if (empty($tokens)) { ?>
                  <tr><td colspan="5" class="text-center text-muted"><?php echo _l('scfc_no_tokens'); ?></td></tr>
                <?php } ?>
                <?php foreach ($tokens as $token) { ?>
                  <tr>
                    <td><?php echo html_escape($token['token_name']); ?></td>
                    <td><code><?php echo html_escape($token['token_key']); ?></code> <button class="btn btn-default btn-xs scfc-copy" data-copy="<?php echo html_escape($token['token_key']); ?>">Copy</button></td>
                    <td><?php echo html_escape(ucfirst($token['source_module']) . ' → ' . $token['source_field']); ?></td>
                    <td><?php echo $token['is_active'] ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
                    <td class="scfc-actions">
                      <a href="<?php echo admin_url('smart_choice_field_connector/impact/token/'.$token['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-warning"></i> Impact</a>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_token/'.$token['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="panel_s scfc-panel">
          <div class="panel-heading"><h4><?php echo _l('scfc_database_status'); ?></h4></div>
          <div class="panel-body">
            <?php foreach ($db_report as $row) { ?>
              <div class="scfc-status-row"><span><?php echo html_escape($row['name']); ?></span><strong class="<?php echo $row['status'] === 'OK' ? 'text-success' : 'text-danger'; ?>"><?php echo html_escape($row['status']); ?></strong></div>
            <?php } ?>
          </div>
        </div>
        <div class="panel_s scfc-panel">
          <div class="panel-heading"><h4><?php echo _l('scfc_quick_help'); ?></h4></div>
          <div class="panel-body">
            <p><strong>1.</strong> Create a mapping between a source field and destination area.</p>
            <p><strong>2.</strong> Create custom tokens for repeated data like customer email, authorization numbers, or project IDs.</p>
            <p><strong>3.</strong> Click Impact before deleting a token to see which modules may be affected.</p>
            <p><strong>4.</strong> Use Health Check to verify required tables and custom fields exist.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('smart_choice_field_connector/partials/modals', ['modules'=>$modules]); ?>
<?php init_tail(); ?>
