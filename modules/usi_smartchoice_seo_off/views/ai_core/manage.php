<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo sammy_ai_nav(); ?>
        <div class="panel_s">
            <div class="panel-body">
                <div class="clearfix sc-ai-toolbar-wrap">
                    <div class="pull-left">
                        <h4 class="no-margin">Production AI Core</h4>
                        <p class="text-muted mtop5">Unified context, action routing, search index, timeline, and diagnostics for Sammy AI.</p>
                    </div>
                    <div class="pull-right sc-ai-toolbar">
                        <a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/rebuild_ai_core_index'); ?>">Rebuild Search Index</a>
                        <a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/rebuild_ai_core_diagnostics'); ?>">Run Diagnostics</a>
                        <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_core'); ?>">Reload</a>
                    </div>
                </div>
                <hr>

                <div class="row sc-ai-card-grid">
                    <?php foreach ($summary as $label => $value) { ?>
                        <div class="col-md-2 col-sm-4 col-xs-6">
                            <div class="sc-ai-kpi-card text-center">
                                <strong><?php echo (int) $value; ?></strong>
                                <span><?php echo html_escape(ucwords(str_replace('_', ' ', $label))); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="row mtop20">
                    <div class="col-md-5">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>Create AI Core Action</h4>
                                <?php echo form_open(admin_url('usi_smartchoice_seo/save_ai_core_action')); ?>
                                    <div class="form-group">
                                        <label>Action Title</label>
                                        <input type="text" name="action_title" class="form-control" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Action Type</label>
                                                <select name="action_type" class="form-control">
                                                    <option value="review">Review</option>
                                                    <option value="estimate">Estimate</option>
                                                    <option value="project">Project</option>
                                                    <option value="purchase">Purchase</option>
                                                    <option value="schedule">Schedule</option>
                                                    <option value="customer">Customer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Priority</label>
                                                <select name="priority" class="form-control">
                                                    <option value="low">Low</option>
                                                    <option value="normal" selected>Normal</option>
                                                    <option value="high">High</option>
                                                    <option value="urgent">Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Requested Command</label>
                                        <textarea name="requested_command" rows="3" class="form-control"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Recommended Action</label>
                                        <textarea name="recommended_action" rows="4" class="form-control"></textarea>
                                    </div>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="approval_required" id="approval_required" checked>
                                        <label for="approval_required">Approval required before execution</label>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm">Save Action</button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>Pending AI Actions</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed sc-table">
                                        <thead><tr><th>Title</th><th>Type</th><th>Priority</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($actions)) { ?><tr><td colspan="6" class="text-center text-muted">No AI Core actions found.</td></tr><?php } ?>
                                        <?php foreach ($actions as $action) { ?>
                                            <tr>
                                                <td><?php echo html_escape($action['action_title']); ?></td>
                                                <td><?php echo html_escape(ucwords(str_replace('_', ' ', $action['action_type']))); ?></td>
                                                <td><?php echo html_escape(ucfirst($action['priority'])); ?></td>
                                                <td><span class="label label-default"><?php echo html_escape(ucwords(str_replace('_', ' ', $action['status']))); ?></span></td>
                                                <td><?php echo html_escape($action['updated_at']); ?></td>
                                                <td>
                                                    <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/mark_ai_core_action/' . (int)$action['id'] . '/approved'); ?>">Approve</a>
                                                    <a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/mark_ai_core_action/' . (int)$action['id'] . '/completed'); ?>">Complete</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>Unified Search Index</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed sc-table">
                                        <thead><tr><th>Title</th><th>Area</th><th>Source</th><th>Indexed</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($search_index)) { ?><tr><td colspan="4" class="text-center text-muted">No indexed records found. Click Rebuild Search Index.</td></tr><?php } ?>
                                        <?php foreach ($search_index as $item) { ?>
                                            <tr><td><?php echo html_escape($item['record_title']); ?></td><td><?php echo html_escape($item['source_area']); ?></td><td><?php echo html_escape($item['source_table'] . ' #' . $item['source_id']); ?></td><td><?php echo html_escape($item['last_indexed_at']); ?></td></tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>Core Diagnostics</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed sc-table">
                                        <thead><tr><th>Check</th><th>Status</th><th>Message</th><th>Checked</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($diagnostics)) { ?><tr><td colspan="4" class="text-center text-muted">No diagnostics found. Click Run Diagnostics.</td></tr><?php } ?>
                                        <?php foreach ($diagnostics as $check) { ?>
                                            <tr><td><?php echo html_escape($check['diagnostic_title']); ?></td><td><span class="label label-<?php echo $check['diagnostic_status'] === 'ok' ? 'success' : 'danger'; ?>"><?php echo html_escape(strtoupper($check['diagnostic_status'])); ?></span></td><td><?php echo html_escape($check['diagnostic_message']); ?></td><td><?php echo html_escape($check['checked_at']); ?></td></tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4>AI Timeline</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed sc-table">
                                <thead><tr><th>Event</th><th>Type</th><th>Source</th><th>Summary</th><th>Date</th></tr></thead>
                                <tbody>
                                <?php if (empty($timeline)) { ?><tr><td colspan="5" class="text-center text-muted">No timeline events found.</td></tr><?php } ?>
                                <?php foreach ($timeline as $event) { ?>
                                    <tr><td><?php echo html_escape($event['event_title']); ?></td><td><?php echo html_escape($event['event_type']); ?></td><td><?php echo html_escape($event['source_area']); ?></td><td><?php echo html_escape($event['event_summary']); ?></td><td><?php echo html_escape($event['created_at']); ?></td></tr>
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
</body>
</html>
