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
            <a href="<?php echo admin_url('smart_choice_field_connector/repair_database'); ?>" class="btn scfc-btn"><i class="fa fa-database"></i> <?php echo _l('scfc_repair_database'); ?></a>
            <a href="<?php echo admin_url('settings?group=smart_choice_field_connector'); ?>" class="btn btn-default"><i class="fa fa-cog"></i> <?php echo _l('settings'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="scfc-top-tabs">
      <a href="#field-builder"><?php echo _l('scfc_visual_builder'); ?></a>
      <a href="#field-groups"><?php echo _l('scfc_groups'); ?></a>
      <a href="#field-mappings"><?php echo _l('scfc_mappings'); ?></a>
      <a href="#field-combinations"><?php echo _l('scfc_combinations'); ?></a>
      <a href="#custom-tokens"><?php echo _l('scfc_custom_tokens'); ?></a>
      <a href="#system-status"><?php echo _l('scfc_database_status'); ?></a>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="panel_s scfc-panel" id="field-builder">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_visual_builder'); ?></h4>
            <button class="btn scfc-btn-blue btn-sm" type="button" id="scfcTestBuilder"><i class="fa fa-play"></i> <?php echo _l('scfc_test_builder'); ?></button>
          </div>
          <div class="panel-body">
            <p class="text-muted"><?php echo _l('scfc_visual_builder_help'); ?></p>
            <div class="scfc-builder-grid">
              <div class="scfc-builder-bank">
                <h5><?php echo _l('scfc_available_fields'); ?></h5>
                <?php foreach ($modules as $key => $label) { ?>
                  <div class="scfc-builder-module">
                    <strong><?php echo html_escape($label); ?></strong>
                    <div class="scfc-field-chip-wrap">
                      <?php foreach ($this->scfc_model->get_fields_for_module($key) as $field) { ?>
                        <?php $token = '{' . $key . '_' . preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $field) . '}'; ?>
                        <button type="button" class="scfc-chip" draggable="true" data-token="<?php echo html_escape(strtolower($token)); ?>"><?php echo html_escape(ucwords(str_replace(['_', '-', ':'], ' ', (string) $field))); ?></button>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
              </div>
              <div class="scfc-builder-canvas">
                <h5><?php echo _l('scfc_drop_zone'); ?></h5>
                <textarea id="scfcBuilderTemplate" class="form-control scfc-drop-textarea" rows="9" placeholder="<?php echo html_escape(_l('scfc_drop_zone_placeholder')); ?>"></textarea>
                <div class="scfc-builder-actions mtop10">
                  <button class="btn scfc-btn" type="button" id="scfcOpenCombinationModal"><i class="fa fa-save"></i> <?php echo _l('scfc_save_as_combination'); ?></button>
                  <button class="btn btn-default" type="button" id="scfcClearBuilder"><i class="fa fa-eraser"></i> <?php echo _l('scfc_clear'); ?></button>
                </div>
                <div id="scfcTestResult" class="scfc-test-result hide"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel_s scfc-panel" id="field-groups">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_groups'); ?></h4>
            <button class="btn scfc-btn btn-sm" data-toggle="modal" data-target="#scfcGroupModal"><i class="fa fa-plus"></i> <?php echo _l('scfc_add_group'); ?></button>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped scfc-table">
                <thead><tr><th><?php echo _l('scfc_name'); ?></th><th><?php echo _l('scfc_description'); ?></th><th><?php echo _l('scfc_status'); ?></th><th><?php echo _l('scfc_options'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($groups)) { ?><tr><td colspan="4" class="text-center text-muted"><?php echo _l('scfc_no_groups'); ?></td></tr><?php } ?>
                <?php foreach ($groups as $group) { ?>
                  <tr>
                    <td><strong><?php echo html_escape($group['group_name']); ?></strong><br><small><?php echo html_escape($group['group_key']); ?></small></td>
                    <td><?php echo html_escape($group['description']); ?></td>
                    <td><?php echo $group['is_active'] ? '<span class="label label-success">' . _l('scfc_active') . '</span>' : '<span class="label label-default">' . _l('scfc_inactive') . '</span>'; ?></td>
                    <td class="scfc-actions">
                      <button type="button" class="btn btn-default btn-xs scfc-edit-group" data-record='<?php echo html_escape(json_encode($group)); ?>'><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></button>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_group/' . $group['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="panel_s scfc-panel" id="field-mappings">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_mappings'); ?></h4>
            <button class="btn scfc-btn btn-sm" data-toggle="modal" data-target="#scfcMappingModal"><i class="fa fa-plus"></i> <?php echo _l('scfc_add_mapping'); ?></button>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped scfc-table">
                <thead><tr><th><?php echo _l('scfc_group'); ?></th><th><?php echo _l('scfc_source'); ?></th><th><?php echo _l('scfc_destination'); ?></th><th><?php echo _l('scfc_merge_tag'); ?></th><th><?php echo _l('scfc_status'); ?></th><th><?php echo _l('scfc_options'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($mappings)) { ?><tr><td colspan="6" class="text-center text-muted"><?php echo _l('scfc_no_mappings'); ?></td></tr><?php } ?>
                <?php foreach ($mappings as $map) { ?>
                  <tr>
                    <td><?php echo html_escape($map['group_name'] ?: _l('scfc_no_group')); ?></td>
                    <td><strong><?php echo html_escape(ucwords(str_replace('_', ' ', $map['source_module']))); ?></strong><br><small><?php echo html_escape(ucwords(str_replace('_', ' ', $map['source_field']))); ?></small></td>
                    <td><strong><?php echo html_escape(ucwords(str_replace('_', ' ', $map['destination_module']))); ?></strong><br><small><?php echo html_escape(ucwords(str_replace('_', ' ', $map['destination_field']))); ?></small></td>
                    <td><code><?php echo html_escape($map['merge_tag']); ?></code></td>
                    <td><?php echo $map['is_active'] ? '<span class="label label-success">' . _l('scfc_active') . '</span>' : '<span class="label label-default">' . _l('scfc_inactive') . '</span>'; ?></td>
                    <td class="scfc-actions">
                      <button type="button" class="btn btn-default btn-xs scfc-edit-mapping" data-record='<?php echo html_escape(json_encode($map)); ?>'><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></button>
                      <button type="button" class="btn btn-success btn-xs scfc-test-template" data-template="<?php echo html_escape($map['merge_tag']); ?>"><i class="fa fa-play"></i> <?php echo _l('scfc_test'); ?></button>
                      <a href="<?php echo admin_url('smart_choice_field_connector/impact/mapping/' . $map['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-warning"></i> <?php echo _l('scfc_impact'); ?></a>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_mapping/' . $map['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="panel_s scfc-panel" id="field-combinations">
          <div class="panel-heading scfc-flex-head">
            <h4><?php echo _l('scfc_combinations'); ?></h4>
            <button class="btn scfc-btn btn-sm" data-toggle="modal" data-target="#scfcCombinationModal"><i class="fa fa-plus"></i> <?php echo _l('scfc_add_combination'); ?></button>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped scfc-table">
                <thead><tr><th><?php echo _l('scfc_group'); ?></th><th><?php echo _l('scfc_name'); ?></th><th><?php echo _l('scfc_template'); ?></th><th><?php echo _l('scfc_status'); ?></th><th><?php echo _l('scfc_options'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($combinations)) { ?><tr><td colspan="5" class="text-center text-muted"><?php echo _l('scfc_no_combinations'); ?></td></tr><?php } ?>
                <?php foreach ($combinations as $combo) { ?>
                  <tr>
                    <td><?php echo html_escape($combo['group_name'] ?: _l('scfc_no_group')); ?></td>
                    <td><strong><?php echo html_escape($combo['combination_name']); ?></strong><br><small><?php echo html_escape($combo['combination_key']); ?></small></td>
                    <td><code><?php echo html_escape(mb_substr($combo['template'], 0, 120)); ?><?php echo mb_strlen($combo['template']) > 120 ? '...' : ''; ?></code></td>
                    <td><?php echo $combo['is_active'] ? '<span class="label label-success">' . _l('scfc_active') . '</span>' : '<span class="label label-default">' . _l('scfc_inactive') . '</span>'; ?></td>
                    <td class="scfc-actions">
                      <button type="button" class="btn btn-default btn-xs scfc-edit-combination" data-record='<?php echo html_escape(json_encode($combo)); ?>'><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></button>
                      <button type="button" class="btn btn-success btn-xs scfc-test-template" data-template="<?php echo html_escape($combo['template']); ?>"><i class="fa fa-play"></i> <?php echo _l('scfc_test'); ?></button>
                      <a href="<?php echo admin_url('smart_choice_field_connector/impact/combination/' . $combo['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-warning"></i> <?php echo _l('scfc_impact'); ?></a>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_combination/' . $combo['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
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
                <thead><tr><th><?php echo _l('scfc_group'); ?></th><th><?php echo _l('scfc_name'); ?></th><th><?php echo _l('scfc_token'); ?></th><th><?php echo _l('scfc_source'); ?></th><th><?php echo _l('scfc_status'); ?></th><th><?php echo _l('scfc_options'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($tokens)) { ?><tr><td colspan="6" class="text-center text-muted"><?php echo _l('scfc_no_tokens'); ?></td></tr><?php } ?>
                <?php foreach ($tokens as $token) { ?>
                  <tr>
                    <td><?php echo html_escape($token['group_name'] ?: _l('scfc_no_group')); ?></td>
                    <td><?php echo html_escape($token['token_name']); ?></td>
                    <td><code><?php echo html_escape($token['token_key']); ?></code> <button class="btn btn-default btn-xs scfc-copy" data-copy="<?php echo html_escape($token['token_key']); ?>"><?php echo _l('copy'); ?></button></td>
                    <td><?php echo html_escape(ucwords(str_replace('_', ' ', $token['source_module'])) . ' → ' . ucwords(str_replace('_', ' ', $token['source_field']))); ?></td>
                    <td><?php echo $token['is_active'] ? '<span class="label label-success">' . _l('scfc_active') . '</span>' : '<span class="label label-default">' . _l('scfc_inactive') . '</span>'; ?></td>
                    <td class="scfc-actions">
                      <button type="button" class="btn btn-default btn-xs scfc-edit-token" data-record='<?php echo html_escape(json_encode($token)); ?>'><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></button>
                      <button type="button" class="btn btn-success btn-xs scfc-test-template" data-template="<?php echo html_escape($token['token_key']); ?>"><i class="fa fa-play"></i> <?php echo _l('scfc_test'); ?></button>
                      <a href="<?php echo admin_url('smart_choice_field_connector/impact/token/' . $token['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-warning"></i> <?php echo _l('scfc_impact'); ?></a>
                      <a href="<?php echo admin_url('smart_choice_field_connector/delete_token/' . $token['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
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
        <div class="panel_s scfc-panel" id="system-status">
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
            <p><strong>1.</strong> <?php echo _l('scfc_help_1'); ?></p>
            <p><strong>2.</strong> <?php echo _l('scfc_help_2'); ?></p>
            <p><strong>3.</strong> <?php echo _l('scfc_help_3'); ?></p>
            <p><strong>4.</strong> <?php echo _l('scfc_help_4'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('smart_choice_field_connector/partials/modals', ['modules' => $modules, 'groups' => $groups]); ?>
<?php init_tail(); ?>
