<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content usi-seo">
        <?php echo function_exists('sammy_ai_nav') ? sammy_ai_nav() : ''; ?>
        <div class="panel_s sc-sammy-panel">
            <div class="panel-body">
                <div class="clearfix sc-ai-toolbar-wrap">
                    <div class="pull-left">
                        <h4>Multi-Agent AI</h4>
                        <p class="text-muted">Estimator, Scheduler, Purchasing, Project Manager, Sales, and Customer Support agents working from the same Sammy AI CRM context.</p>
                    </div>
                    <div class="pull-right sc-ai-toolbar">
                        <a href="<?php echo admin_url('usi_smartchoice_seo/multi_agent_ai'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
                    </div>
                </div>
                <hr>
                <div class="row sc-ai-card-grid">
                    <?php foreach ($summary as $label => $value) { ?>
                        <div class="col-md-3 col-sm-6 col-xs-6"><div class="sc-ai-kpi-card text-center"><strong><?php echo (int)$value; ?></strong><span><?php echo html_escape($label); ?></span></div></div>
                    <?php } ?>
                </div>
                <div class="panel_s"><div class="panel-body">
                    <h4>Create Agent Task</h4>
                    <?php echo form_open(admin_url('usi_smartchoice_seo/create_ai_agent_task')); ?>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Agent</label><select name="agent_id" class="form-control">
                            <?php foreach ($agents as $agent) { ?><option value="<?php echo (int)$agent['id']; ?>"><?php echo html_escape($agent['agent_name']); ?></option><?php } ?>
                        </select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Task Title</label><input type="text" name="task_title" class="form-control" required></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Related Type</label><select name="related_type" class="form-control"><option value="estimate">Estimate</option><option value="project">Project</option><option value="customer">Customer</option><option value="lead">Lead</option><option value="purchase_order">Purchase Order</option><option value="schedule">Schedule</option><option value="general">General</option></select></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Related ID</label><input type="number" name="related_id" class="form-control" value="0"></div></div>
                    </div>
                    <div class="form-group"><label>Task Prompt</label><textarea name="task_prompt" class="form-control" rows="4" placeholder="Tell the selected agent what to prepare. Example: Review estimate #25 and create a production-ready task plan."></textarea></div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Agent Task</button>
                    <?php echo form_close(); ?>
                </div></div>
                <h4>Agents</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Name</th><th>Role</th><th>Department</th><th>Status</th><th>Priority</th><th>Prompt</th></tr></thead><tbody>
                <?php if (empty($agents)) { ?><tr><td colspan="6">No agents found.</td></tr><?php } ?>
                <?php foreach ($agents as $row) { ?><tr><td><?php echo html_escape($row['agent_name']); ?></td><td><?php echo html_escape($row['agent_role']); ?></td><td><?php echo html_escape($row['department']); ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['priority']); ?></td><td><?php echo html_escape($row['system_prompt']); ?></td></tr><?php } ?>
                </tbody></table></div>
                <h4>Agent Tasks</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Title</th><th>Agent</th><th>Related</th><th>Status</th><th>Confidence</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
                <?php if (empty($tasks)) { ?><tr><td colspan="7">No agent tasks found.</td></tr><?php } ?>
                <?php foreach ($tasks as $row) { ?><tr><td><?php echo html_escape($row['task_title']); ?></td><td>#<?php echo (int)$row['agent_id']; ?></td><td><?php echo html_escape($row['related_type']); ?> #<?php echo (int)$row['related_id']; ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['confidence_score']); ?>%</td><td><?php echo html_escape($row['updated_at']); ?></td><td><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/run_ai_agent_task/' . (int)$row['id']); ?>">Run Agent</a></td></tr><?php } ?>
                </tbody></table></div>
                <h4>Agent Logs</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Agent</th><th>Task</th><th>Type</th><th>Message</th><th>Created</th></tr></thead><tbody>
                <?php if (empty($logs)) { ?><tr><td colspan="5">No agent logs found.</td></tr><?php } ?>
                <?php foreach ($logs as $row) { ?><tr><td>#<?php echo (int)$row['agent_id']; ?></td><td>#<?php echo (int)$row['task_id']; ?></td><td><?php echo html_escape($row['log_type']); ?></td><td><?php echo html_escape($row['message']); ?></td><td><?php echo html_escape($row['created_at']); ?></td></tr><?php } ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
