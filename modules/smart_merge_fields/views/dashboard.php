<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s smf-panel">
                    <div class="panel-body">
                        <h4 class="smf-title">Merge Fields Automation</h4>
                        <p class="text-muted">Scan CRM tables, map merge fields, and synchronize shared information across CRM modules.</p>
                        <?php $this->load->view('smart_merge_fields/_nav'); ?>
                        <div class="row smf-stat-row">
                            <div class="col-md-4"><div class="smf-stat"><span><?php echo count($fields); ?></span><small>Discovered Fields</small></div></div>
                            <div class="col-md-4"><div class="smf-stat"><span><?php echo count($mappings); ?></span><small>Saved Mappings</small></div></div>
                            <div class="col-md-4"><div class="smf-stat"><span><?php echo count($tables); ?></span><small>Scanned Tables</small></div></div>
                        </div>
                        <div class="smf-flow-card">
                            <div class="smf-flow-node">Lead</div>
                            <div class="smf-flow-arrow">→</div>
                            <div class="smf-flow-node">Customer</div>
                            <div class="smf-flow-arrow">→</div>
                            <div class="smf-flow-node">Project</div>
                            <div class="smf-flow-arrow">→</div>
                            <div class="smf-flow-node">Contract</div>
                            <div class="smf-flow-arrow">→</div>
                            <div class="smf-flow-node">Invoice</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <div class="panel_s smf-panel">
                            <div class="panel-body">
                                <h4 class="smf-section-title">Available Merge Fields</h4>
                                <form method="get" action="<?php echo admin_url('smart_merge_fields'); ?>" class="smf-search-form">
                                    <input type="text" name="search" class="form-control smf-search" placeholder="Search fields" value="<?php echo html_escape($this->input->get('search')); ?>">
                                    <button class="btn btn-default smf-btn" type="submit">Search</button>
                                </form>
                                <div class="smf-field-list">
                                    <?php foreach ($fields as $field) { ?>
                                        <div class="smf-field-card" draggable="true" data-table="<?php echo html_escape($field['table_name']); ?>" data-field="<?php echo html_escape($field['field_name']); ?>">
                                            <strong><?php echo html_escape(smart_merge_fields_human_name($field['table_name'])); ?></strong>
                                            <span><?php echo html_escape(smart_merge_fields_human_name($field['field_name'])); ?></span>
                                            <code><?php echo html_escape($field['merge_tag']); ?></code>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel_s smf-panel smf-drop-panel">
                            <div class="panel-body">
                                <h4 class="smf-section-title">Mapping Center</h4>
                                <div class="smf-drop-zone" id="smfSourceDrop">Drop Source Field</div>
                                <div class="smf-link-line"></div>
                                <div class="smf-drop-zone" id="smfTargetDrop">Drop Target Field</div>
                                <a href="<?php echo admin_url('smart_merge_fields/mapping'); ?>" class="btn btn-info smf-btn smf-full">Open Mapping Builder</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel_s smf-panel">
                            <div class="panel-body">
                                <h4 class="smf-section-title">Saved Mappings</h4>
                                <div class="table-responsive">
                                    <table class="table table-condensed smf-table">
                                        <thead><tr><th>Name</th><th>Status</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($mappings as $mapping) { ?>
                                                <tr>
                                                    <td><?php echo html_escape($mapping['name']); ?></td>
                                                    <td><span class="label label-success"><?php echo html_escape(ucwords($mapping['status'])); ?></span></td>
                                                    <td>
                                                        <a class="btn btn-default smf-btn-xs" href="<?php echo admin_url('smart_merge_fields/mapping/' . $mapping['id']); ?>">Edit</a>
                                                        <a class="btn btn-info smf-btn-xs" href="<?php echo admin_url('smart_merge_fields/sync/' . $mapping['id']); ?>">Sync</a>
                                                        <?php if (has_permission('smart_merge_fields', '', 'delete')) { ?>
                                                            <a class="btn btn-danger smf-btn-xs _delete" href="<?php echo admin_url('smart_merge_fields/delete/' . $mapping['id']); ?>">Delete</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="panel_s smf-panel">
                            <div class="panel-body">
                                <h4 class="smf-section-title">Recent Activity</h4>
                                <?php foreach ($logs as $log) { ?>
                                    <div class="smf-log"><strong><?php echo html_escape($log['action']); ?></strong><br><small><?php echo html_escape($log['created_at']); ?> · <?php echo html_escape($log['message']); ?></small></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
