<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content usi-seo">
        <?php echo function_exists('sammy_ai_nav') ? sammy_ai_nav() : ''; ?>
        <div class="panel_s sc-sammy-panel">
            <div class="panel-body">
                <div class="clearfix sc-ai-toolbar-wrap">
                    <div class="pull-left">
                        <h4>AI Intelligence Engine</h4>
                        <p class="text-muted">Unified AI request layer for prompts, context profiles, response cache, provider logging, and diagnostics.</p>
                    </div>
                    <div class="pull-right sc-ai-toolbar">
                        <a href="<?php echo admin_url('usi_smartchoice_seo/rebuild_ai_context_profiles'); ?>" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> Rebuild Context</a>
                        <a href="<?php echo admin_url('usi_smartchoice_seo/clear_ai_response_cache'); ?>" class="btn btn-danger btn-sm _delete"><i class="fa fa-trash"></i> Clear Cache</a>
                        <a href="<?php echo admin_url('usi_smartchoice_seo/intelligence_engine'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
                    </div>
                </div>
                <hr>

                <div class="row sc-ai-card-grid">
                    <?php foreach ($summary as $label => $value) { ?>
                        <div class="col-md-2 col-sm-4 col-xs-6">
                            <div class="sc-ai-kpi-card text-center">
                                <strong><?php echo (int)$value; ?></strong>
                                <span><?php echo html_escape($label); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>New Prompt Template</h4>
                                <?php echo form_open(admin_url('usi_smartchoice_seo/save_ai_prompt_template')); ?>
                                <div class="form-group"><label>Template Name</label><input type="text" name="template_name" class="form-control" required></div>
                                <div class="form-group"><label>Template Area</label><input type="text" name="template_area" class="form-control" value="estimating"></div>
                                <div class="form-group"><label>System Prompt</label><textarea name="system_prompt" class="form-control" rows="4"></textarea></div>
                                <div class="form-group"><label>User Prompt</label><textarea name="user_prompt" class="form-control" rows="4"></textarea></div>
                                <div class="row">
                                    <div class="col-md-4"><div class="form-group"><label>Model</label><input type="text" name="default_model" class="form-control"></div></div>
                                    <div class="col-md-4"><div class="form-group"><label>Temperature</label><input type="number" step="0.01" name="temperature" class="form-control" value="0.20"></div></div>
                                    <div class="col-md-4"><div class="form-group"><label>Max Tokens</label><input type="number" name="max_tokens" class="form-control" value="1200"></div></div>
                                </div>
                                <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select></div>
                                <button type="submit" class="btn btn-primary btn-sm">Save Prompt Template</button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4>Test AI Request Logger</h4>
                                <?php echo form_open(admin_url('usi_smartchoice_seo/test_ai_intelligence_request')); ?>
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group"><label>Request Area</label><input type="text" name="request_area" class="form-control" value="manual_test"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Related Type</label><input type="text" name="related_type" class="form-control" value="general"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4"><div class="form-group"><label>Related ID</label><input type="number" name="related_id" class="form-control" value="0"></div></div>
                                    <div class="col-md-4"><div class="form-group"><label>Provider</label><input type="text" name="provider_name" class="form-control" placeholder="OpenAI"></div></div>
                                    <div class="col-md-4"><div class="form-group"><label>Model</label><input type="text" name="model_name" class="form-control" placeholder="gpt model"></div></div>
                                </div>
                                <div class="form-group"><label>Prompt</label><textarea name="test_prompt" class="form-control" rows="7" placeholder="Type a test command or prompt for Sammy AI."></textarea></div>
                                <button type="submit" class="btn btn-success btn-sm">Log Test Request</button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4>Manual Context Profile</h4>
                        <?php echo form_open(admin_url('usi_smartchoice_seo/save_ai_context_profile')); ?>
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>Profile Name</label><input type="text" name="profile_name" class="form-control" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Related Type</label><input type="text" name="related_type" class="form-control" value="customer"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Related ID</label><input type="number" name="related_id" class="form-control" value="0"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Priority</label><select name="priority" class="form-control"><option value="normal">Normal</option><option value="high">High</option><option value="critical">Critical</option></select></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="review">Review</option><option value="archived">Archived</option></select></div></div>
                        </div>
                        <div class="form-group"><label>Context Summary</label><textarea name="context_summary" class="form-control" rows="3"></textarea></div>
                        <div class="form-group"><label>Context Payload / Notes</label><textarea name="context_payload" class="form-control" rows="3"></textarea></div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Context Profile</button>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4>Prompt Templates</h4>
                        <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Name</th><th>Area</th><th>Model</th><th>Status</th><th>Updated</th></tr></thead><tbody>
                        <?php if (empty($prompts)) { ?><tr><td colspan="5">No prompt templates found.</td></tr><?php } ?>
                        <?php foreach ($prompts as $row) { ?><tr><td><?php echo html_escape($row['template_name']); ?></td><td><?php echo html_escape($row['template_area']); ?></td><td><?php echo html_escape($row['default_model']); ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['updated_at']); ?></td></tr><?php } ?>
                        </tbody></table></div>
                    </div>
                    <div class="col-md-6">
                        <h4>Context Profiles</h4>
                        <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Name</th><th>Related</th><th>Priority</th><th>Status</th><th>Updated</th></tr></thead><tbody>
                        <?php if (empty($contexts)) { ?><tr><td colspan="5">No context profiles found.</td></tr><?php } ?>
                        <?php foreach ($contexts as $row) { ?><tr><td><?php echo html_escape($row['profile_name']); ?></td><td><?php echo html_escape($row['related_type']); ?> #<?php echo (int)$row['related_id']; ?></td><td><?php echo html_escape($row['priority']); ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['updated_at']); ?></td></tr><?php } ?>
                        </tbody></table></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4>AI Request History</h4>
                        <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Area</th><th>Related</th><th>Status</th><th>Provider</th><th>Model</th><th>Created</th></tr></thead><tbody>
                        <?php if (empty($requests)) { ?><tr><td colspan="6">No AI requests logged.</td></tr><?php } ?>
                        <?php foreach ($requests as $row) { ?><tr><td><?php echo html_escape($row['request_area']); ?></td><td><?php echo html_escape($row['related_type']); ?> #<?php echo (int)$row['related_id']; ?></td><td><?php echo html_escape($row['request_status']); ?></td><td><?php echo html_escape($row['provider_name']); ?></td><td><?php echo html_escape($row['model_name']); ?></td><td><?php echo html_escape($row['created_at']); ?></td></tr><?php } ?>
                        </tbody></table></div>
                    </div>
                    <div class="col-md-6">
                        <h4>Provider Logs</h4>
                        <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Provider</th><th>Request</th><th>Status</th><th>Latency</th><th>Created</th></tr></thead><tbody>
                        <?php if (empty($provider_logs)) { ?><tr><td colspan="5">No provider logs found.</td></tr><?php } ?>
                        <?php foreach ($provider_logs as $row) { ?><tr><td><?php echo html_escape($row['provider_name']); ?></td><td>#<?php echo (int)$row['request_id']; ?></td><td><?php echo html_escape($row['log_status']); ?></td><td><?php echo (int)$row['latency_ms']; ?> ms</td><td><?php echo html_escape($row['created_at']); ?></td></tr><?php } ?>
                        </tbody></table></div>
                    </div>
                </div>

                <h4>Response Cache</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Cache Key</th><th>Area</th><th>Provider</th><th>Model</th><th>Use Count</th><th>Last Used</th><th>Expires</th></tr></thead><tbody>
                <?php if (empty($cache)) { ?><tr><td colspan="7">No cached responses found.</td></tr><?php } ?>
                <?php foreach ($cache as $row) { ?><tr><td><?php echo html_escape($row['cache_key']); ?></td><td><?php echo html_escape($row['request_area']); ?></td><td><?php echo html_escape($row['provider_name']); ?></td><td><?php echo html_escape($row['model_name']); ?></td><td><?php echo (int)$row['use_count']; ?></td><td><?php echo html_escape($row['last_used_at']); ?></td><td><?php echo html_escape($row['expires_at']); ?></td></tr><?php } ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
