<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content usi-seo">
        <?php echo function_exists('sammy_ai_nav') ? sammy_ai_nav() : ''; ?>
        <div class="panel_s sc-sammy-panel">
            <div class="panel-body">
                <div class="clearfix sc-ai-toolbar-wrap">
                    <div class="pull-left">
                        <h4>Advanced Vision Intelligence</h4>
                        <p class="text-muted">Room recognition, object detection, material identification, measurement assistance, damage notes, and before/after review workflow.</p>
                    </div>
                    <div class="pull-right sc-ai-toolbar">
                        <a href="<?php echo admin_url('usi_smartchoice_seo/rebuild_advanced_vision_index'); ?>" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> Rebuild Vision Index</a>
                        <a href="<?php echo admin_url('usi_smartchoice_seo/advanced_vision'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
                    </div>
                </div>
                <hr>
                <div class="row sc-ai-card-grid">
                    <?php foreach ($summary as $label => $value) { ?>
                        <div class="col-md-2 col-sm-4 col-xs-6"><div class="sc-ai-kpi-card text-center"><strong><?php echo (int)$value; ?></strong><span><?php echo html_escape($label); ?></span></div></div>
                    <?php } ?>
                </div>
                <div class="panel_s"><div class="panel-body">
                    <h4>New Advanced Vision Session</h4>
                    <?php echo form_open(admin_url('usi_smartchoice_seo/create_advanced_vision_session')); ?>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Session Title</label><input type="text" name="session_title" class="form-control" required></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Related Type</label><select name="related_type" class="form-control"><option value="project">Project</option><option value="customer">Customer</option><option value="lead">Lead</option><option value="estimate">Estimate</option><option value="field_verification">Field Verification</option></select></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Related ID</label><input type="number" name="related_id" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Service Type</label><input type="text" name="service_type" class="form-control" value="general"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Room / Area</label><input type="text" name="room_area" class="form-control"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Photo Reference</label><input type="text" name="photo_reference" class="form-control" placeholder="CRM file name, URL, or photo ID"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="draft">Draft</option><option value="indexed">Indexed</option><option value="analyzed">Analyzed</option><option value="field_verify">Field Verify</option><option value="ready_for_estimate">Ready For Estimate</option></select></div></div>
                    </div>
                    <div class="form-group"><label>Analysis Prompt</label><textarea name="analysis_prompt" class="form-control" rows="3" placeholder="Describe what Sammy AI should identify from the image."></textarea></div>
                    <div class="form-group"><label>Measurement Notes</label><textarea name="measurement_notes" class="form-control" rows="3" placeholder="Enter known dimensions, scale references, or field measurements."></textarea></div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Vision Session</button>
                    <?php echo form_close(); ?>
                </div></div>
                <h4>Advanced Vision Sessions</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Title</th><th>Related</th><th>Service</th><th>Area</th><th>Confidence</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
                <?php if (empty($sessions)) { ?><tr><td colspan="8">No advanced vision sessions found.</td></tr><?php } ?>
                <?php foreach ($sessions as $row) { ?><tr><td><?php echo html_escape($row['session_title']); ?></td><td><?php echo html_escape($row['related_type']); ?> #<?php echo (int)$row['related_id']; ?></td><td><?php echo html_escape($row['service_type']); ?></td><td><?php echo html_escape($row['room_area']); ?></td><td><?php echo html_escape($row['confidence_score']); ?>%</td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['updated_at']); ?></td><td><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/analyze_advanced_vision/' . (int)$row['id']); ?>">Analyze</a></td></tr><?php } ?>
                </tbody></table></div>
                <h4>Detected Objects</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Session</th><th>Object</th><th>Type</th><th>Confidence</th><th>Notes</th></tr></thead><tbody>
                <?php if (empty($detections)) { ?><tr><td colspan="5">No detections found.</td></tr><?php } ?>
                <?php foreach ($detections as $row) { ?><tr><td>#<?php echo (int)$row['session_id']; ?></td><td><?php echo html_escape($row['object_name']); ?></td><td><?php echo html_escape($row['object_type']); ?></td><td><?php echo html_escape($row['confidence_score']); ?>%</td><td><?php echo html_escape($row['notes']); ?></td></tr><?php } ?>
                </tbody></table></div>
                <h4>Measurement Assistance</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Session</th><th>Label</th><th>Type</th><th>Value</th><th>Unit</th><th>Confidence</th><th>Notes</th></tr></thead><tbody>
                <?php if (empty($measurements)) { ?><tr><td colspan="7">No measurement records found.</td></tr><?php } ?>
                <?php foreach ($measurements as $row) { ?><tr><td>#<?php echo (int)$row['session_id']; ?></td><td><?php echo html_escape($row['measurement_label']); ?></td><td><?php echo html_escape($row['measurement_type']); ?></td><td><?php echo html_escape($row['measurement_value']); ?></td><td><?php echo html_escape($row['unit']); ?></td><td><?php echo html_escape($row['confidence_score']); ?>%</td><td><?php echo html_escape($row['notes']); ?></td></tr><?php } ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
