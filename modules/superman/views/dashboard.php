<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$CI = &get_instance();
$CI->load->model('superman/superman_model');
function superman_front_label($value) {
    return ucwords(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', (string)$value))));
}
?>
<div id="wrapper">
  <div class="content superman-wrap">
    <div class="superman-hero">
      <div>
        <h1><i class="fa fa-bolt"></i> <?php echo _l('superman_menu_name'); ?></h1>
        <p><?php echo _l('superman_dashboard_subtitle'); ?></p>
      </div>
      <div class="superman-hero-actions">
        <a href="<?php echo admin_url('superman/settings'); ?>" class="btn superman-btn-light"><i class="fa fa-sliders"></i> <?php echo _l('superman_settings_title'); ?></a>
        <a href="<?php echo admin_url('superman/merge_fields'); ?>" class="btn superman-btn-light"><i class="fa fa-search"></i> <?php echo _l('superman_merge_catalog'); ?></a>
        <a href="<?php echo admin_url('superman/health'); ?>" class="btn superman-btn-dark"><i class="fa fa-heartbeat"></i> <?php echo _l('superman_health_check'); ?></a>
      </div>
    </div>

    <?php $this->load->view('superman/partials/nav'); ?>

    <div class="panel_s superman-panel">
      <div class="panel-heading superman-flex-head">
        <div>
          <h4><?php echo _l('superman_visual_builder'); ?></h4>
          <p class="text-muted no-margin"><?php echo _l('superman_visual_builder_help'); ?></p>
        </div>
        <div class="superman-toolbar-inline">
          <button type="button" class="btn superman-btn btn-sm" id="superman-clear-canvas"><i class="fa fa-eraser"></i> <?php echo _l('clear'); ?></button>
          <button type="button" class="btn superman-btn btn-sm" data-toggle="modal" data-target="#supermanMappingModal"><i class="fa fa-plus"></i> <?php echo _l('superman_manual_mapping'); ?></button>
        </div>
      </div>
      <div class="panel-body">
        <div class="superman-builder-grid superman-builder-grid-v125">
          <div class="superman-source-accordion">
            <?php $bankIndex = 0; foreach ($field_groups as $groupKey => $group) { $bankIndex++; ?>
              <?php
                $groupTitle = superman_front_label($group['title'] ?? $groupKey);
                $groupIcon = $group['icon'] ?? 'fa fa-database';
                $isOpen = $bankIndex <= 3;
              ?>
              <div class="superman-field-bank superman-collapsible-bank <?php echo $isOpen ? 'is-open' : ''; ?>" data-bank="<?php echo html_escape($groupKey); ?>">
                <button type="button" class="superman-bank-toggle" aria-expanded="<?php echo $isOpen ? 'true' : 'false'; ?>">
                  <span><i class="<?php echo html_escape($groupIcon); ?>"></i> <?php echo html_escape($groupTitle); ?></span>
                  <i class="fa fa-chevron-down superman-bank-arrow"></i>
                </button>
                <div class="superman-bank-body" <?php echo $isOpen ? '' : 'style="display:none;"'; ?>>
                  <input type="search" class="form-control superman-bank-search" placeholder="<?php echo _l('search'); ?> <?php echo html_escape($groupTitle); ?>">
                  <div class="superman-chip-list">
                    <?php if (empty($group['items'])) { ?>
                      <div class="text-muted small mtop10"><?php echo _l('superman_no_fields_selected'); ?></div>
                    <?php } ?>
                    <?php foreach (($group['items'] ?? []) as $item) { ?>
                      <div class="superman-field-chip" draggable="true" data-module="<?php echo html_escape($item['module']); ?>" data-field="<?php echo html_escape($item['field']); ?>">
                        <span><?php echo html_escape($item['field_label']); ?></span>
                        <small><?php echo html_escape($item['module_label']); ?></small>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
          <div class="superman-drop-zone" id="superman-drop-zone">
            <div class="superman-drop-empty">
              <i class="fa fa-arrows-to-dot"></i>
              <strong><?php echo _l('superman_center_drop_box'); ?></strong>
              <span><?php echo _l('superman_center_drop_help'); ?></span>
            </div>
          </div>
        </div>
        <?php echo form_open(admin_url('superman/save_visual_flow'), ['id'=>'superman-visual-flow-form']); ?>
          <input type="hidden" name="flow_payload" id="superman-flow-payload">
          <div class="row mtop15">
            <div class="col-md-8"><?php echo render_input('flow_name', 'superman_flow_name', 'Custom Field Combination ' . date('Y-m-d H:i')); ?></div>
            <div class="col-md-4">
              <button type="button" class="btn btn-default btn-block mtop25" id="superman-preview-canvas"><i class="fa fa-eye"></i> <?php echo _l('superman_preview_combination'); ?></button>
              <button type="submit" class="btn superman-btn btn-block mtop10"><i class="fa fa-save"></i> <?php echo _l('superman_save_visual_combination'); ?></button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="panel_s superman-panel">
          <div class="panel-heading superman-flex-head">
            <div><h4><?php echo _l('superman_active_combinations'); ?></h4><span class="label label-success"><?php echo _l('superman_active_from_start'); ?></span></div>
          </div>
          <div class="panel-body">
            <div class="row">
              <?php foreach ($flow_sets as $set) { ?>
                <div class="col-md-6">
                  <div class="superman-flow-card">
                    <div>
                      <strong><?php echo html_escape(superman_front_label($set['name'] ?? 'Field Combination')); ?></strong>
                      <span><?php echo html_escape(superman_front_label($set['status'] ?? 'Active')); ?></span>
                    </div>
                    <p><?php echo html_escape(superman_front_label(implode(' → ', $set['fields'] ?? []))); ?></p>
                    <button type="button" class="btn btn-default btn-xs superman-test-flow" data-flow="<?php echo html_escape(json_encode($set)); ?>">
                      <i class="fa fa-play"></i> <?php echo _l('superman_test_combination'); ?>
                    </button>
                    <button type="button" class="btn btn-info btn-xs superman-preview-flow" data-flow="<?php echo html_escape(json_encode($set)); ?>">
                      <i class="fa fa-eye"></i> <?php echo _l('superman_preview_combination'); ?>
                    </button>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="panel_s superman-panel">
          <div class="panel-heading superman-flex-head">
            <h4><?php echo _l('superman_default_automation'); ?></h4>
            <div class="superman-toolbar-inline">
              <a href="<?php echo admin_url('superman/export_mappings'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('export'); ?></a>
              <a href="<?php echo admin_url('superman/sample_header'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> <?php echo _l('superman_sample_header'); ?></a>
              <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#supermanImportModal"><i class="fa fa-upload"></i> <?php echo _l('import'); ?></button>
            </div>
          </div>
          <div class="panel-body">
            <div class="superman-table-tools"><input type="search" class="form-control superman-table-search" placeholder="<?php echo _l('search'); ?>"></div>
            <?php echo form_open(admin_url('superman/bulk_delete_mappings')); ?>
            <div class="table-responsive superman-table-wrap">
              <table class="table table-striped superman-table">
                <thead><tr><th width="32"><input type="checkbox" class="superman-check-all"></th><th><?php echo _l('superman_source'); ?></th><th><?php echo _l('superman_destination'); ?></th><th><?php echo _l('superman_merge_tag'); ?></th><th><?php echo _l('superman_group'); ?></th><th><?php echo _l('superman_status'); ?></th><th><?php echo _l('options'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($mappings as $map) { ?>
                  <tr>
                    <td><?php if ((int)$map['is_locked_default'] === 0) { ?><input type="checkbox" name="ids[]" value="<?php echo (int)$map['id']; ?>"><?php } ?></td>
                    <td><strong><?php echo html_escape(superman_front_label($map['source_module'])); ?></strong><br><small><?php echo html_escape(superman_front_label($map['source_field'])); ?></small></td>
                    <td><strong><?php echo html_escape(superman_front_label($map['destination_module'])); ?></strong><br><small><?php echo html_escape(superman_front_label($map['destination_field'])); ?></small></td>
                    <td><code><?php echo html_escape($map['merge_tag']); ?></code></td>
                    <td><?php echo html_escape(superman_front_label($map['map_group'])); ?></td>
                    <td><?php echo $map['is_active'] ? '<span class="label label-success">'._l('superman_active').'</span>' : '<span class="label label-default">'._l('superman_inactive').'</span>'; ?></td>
                    <td class="superman-actions">
                      <a href="<?php echo admin_url('superman/toggle_mapping/'.$map['id']); ?>" class="btn btn-default btn-xs"><?php echo $map['is_active'] ? _l('disable') : _l('enable'); ?></a>
                      <?php if ((int)$map['is_locked_default'] === 0) { ?><a href="<?php echo admin_url('superman/delete_mapping/'.$map['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i></a><?php } else { ?><span class="label label-info"><?php echo _l('superman_default'); ?></span><?php } ?>
                    </td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> <?php echo _l('superman_mass_delete'); ?></button>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="panel_s superman-panel">
          <div class="panel-heading"><h4><?php echo _l('superman_quick_status'); ?></h4></div>
          <div class="panel-body">
            <div class="superman-status-row"><span><?php echo _l('superman_automation'); ?></span><strong><?php echo get_option('superman_enable_automation') == '1' ? _l('superman_enabled') : _l('superman_disabled'); ?></strong></div>
            <div class="superman-status-row"><span><?php echo _l('superman_form_autofill'); ?></span><strong><?php echo get_option('superman_enable_form_autofill') == '1' ? _l('superman_enabled') : _l('superman_disabled'); ?></strong></div>
            <div class="superman-status-row"><span><?php echo _l('superman_mappings'); ?></span><strong><?php echo count($mappings); ?></strong></div>
            <div class="superman-status-row"><span><?php echo _l('superman_merge_fields'); ?></span><strong><?php echo count($merge_catalog); ?></strong></div>
          </div>
        </div>
        <div class="panel_s superman-panel" id="tokens">
          <div class="panel-heading superman-flex-head"><h4><?php echo _l('superman_custom_tokens'); ?></h4><button class="btn superman-btn btn-sm" data-toggle="modal" data-target="#supermanTokenModal"><i class="fa fa-plus"></i> <?php echo _l('superman_add_token'); ?></button></div>
          <div class="panel-body">
            <input type="search" class="form-control superman-table-search" placeholder="<?php echo _l('search'); ?>">
            <div class="table-responsive superman-table-wrap mtop10"><table class="table table-striped superman-table"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('superman_token'); ?></th><th><?php echo _l('options'); ?></th></tr></thead><tbody>
            <?php if (empty($tokens)) { ?><tr><td colspan="3" class="text-center text-muted"><?php echo _l('superman_no_tokens'); ?></td></tr><?php } ?>
            <?php foreach ($tokens as $token) { ?><tr><td><?php echo html_escape(superman_front_label($token['token_name'])); ?><br><small><?php echo html_escape(superman_front_label($token['source_module'].' '.$token['source_field'])); ?></small></td><td><code><?php echo html_escape($token['token_key']); ?></code></td><td><a href="<?php echo admin_url('superman/delete_token/'.$token['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i></a></td></tr><?php } ?>
            </tbody></table></div>
          </div>
        </div>
        <div class="panel_s superman-panel">
          <div class="panel-heading"><h4><?php echo _l('superman_saved_profiles'); ?></h4></div>
          <div class="panel-body">
            <?php if (empty($profiles)) { ?><p class="text-muted"><?php echo _l('superman_no_profiles'); ?></p><?php } ?>
            <?php foreach ($profiles as $profile) { ?><div class="superman-profile-row"><span><?php echo html_escape(superman_front_label($profile['profile_name'])); ?></span><a class="btn btn-default btn-xs" href="<?php echo admin_url('superman/restore_profile/'.$profile['id']); ?>"><?php echo _l('superman_restore'); ?></a></div><?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('superman/partials/modals', ['modules'=>$modules]); ?>
<?php init_tail(); ?>
