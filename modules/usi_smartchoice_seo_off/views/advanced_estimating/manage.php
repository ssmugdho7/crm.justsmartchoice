<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content usi-seo">
        <?php echo function_exists('sammy_ai_nav') ? sammy_ai_nav() : ''; ?>
        <div class="panel_s sc-sammy-panel">
            <div class="panel-body">
                <div class="clearfix sc-ai-toolbar-wrap">
                    <div class="pull-left">
                        <h4>Advanced Estimating Intelligence</h4>
                        <p class="text-muted">Historical pricing analysis, regional adjustment notes, labor/material guidance, and confidence scoring for AI estimates.</p>
                    </div>
                    <div class="pull-right sc-ai-toolbar">
                        <a href="<?php echo admin_url('usi_smartchoice_seo/rebuild_estimate_price_patterns'); ?>" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> Rebuild Price Patterns</a>
                        <a href="<?php echo admin_url('usi_smartchoice_seo/advanced_estimating'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
                    </div>
                </div>
                <hr>
                <div class="row sc-ai-card-grid">
                    <?php foreach ($summary as $label => $value) { ?>
                        <div class="col-md-2 col-sm-4 col-xs-6"><div class="sc-ai-kpi-card text-center"><strong><?php echo (int)$value; ?></strong><span><?php echo html_escape($label); ?></span></div></div>
                    <?php } ?>
                </div>
                <div class="panel_s"><div class="panel-body">
                    <h4>New Intelligence Run</h4>
                    <?php echo form_open(admin_url('usi_smartchoice_seo/create_estimate_intelligence_run')); ?>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Run Title</label><input type="text" name="run_title" class="form-control" required></div></div>
                        <div class="col-md-2"><div class="form-group"><label>AI Estimate ID</label><input type="number" name="related_estimate_id" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Customer ID</label><input type="number" name="related_customer_id" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Service Type</label><input type="text" name="service_type" class="form-control" value="general"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="draft">Draft</option><option value="review">Review</option><option value="approved">Approved</option></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"><div class="form-group"><label>City</label><input type="text" name="city" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>County</label><input type="text" name="county" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Subtotal</label><input type="number" step="0.01" name="subtotal" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Tax</label><input type="number" step="0.01" name="tax_total" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Overhead</label><input type="number" step="0.01" name="overhead_total" class="form-control" value="0"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Profit</label><input type="number" step="0.01" name="profit_total" class="form-control" value="0"></div></div>
                    </div>
                    <div class="form-group"><label>Scope Summary</label><textarea name="scope_summary" class="form-control" rows="3"></textarea></div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Intelligence Run</button>
                    <?php echo form_close(); ?>
                </div></div>
                <h4>Intelligence Runs</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Title</th><th>Service</th><th>Location</th><th>Matches</th><th>Confidence</th><th>Total</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
                <?php if (empty($runs)) { ?><tr><td colspan="9">No estimate intelligence runs found.</td></tr><?php } ?>
                <?php foreach ($runs as $row) { ?><tr><td><?php echo html_escape($row['run_title']); ?></td><td><?php echo html_escape($row['service_type']); ?></td><td><?php echo html_escape(trim(($row['city'] ?? '') . ' ' . ($row['county'] ?? ''))); ?></td><td><?php echo (int)$row['historical_match_count']; ?></td><td><?php echo html_escape($row['confidence_score']); ?>%</td><td>$<?php echo number_format((float)$row['grand_total'], 2); ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['updated_at']); ?></td><td><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/calculate_estimate_intelligence/' . (int)$row['id']); ?>">Calculate Numbers</a></td></tr><?php } ?>
                </tbody></table></div>
                <h4>Price Patterns</h4>
                <div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Pattern</th><th>Service</th><th>Samples</th><th>Average Unit</th><th>Average Total</th><th>Low</th><th>High</th><th>Rebuilt</th></tr></thead><tbody>
                <?php if (empty($patterns)) { ?><tr><td colspan="8">No price patterns found. Click Rebuild Price Patterns.</td></tr><?php } ?>
                <?php foreach ($patterns as $row) { ?><tr><td><?php echo html_escape($row['pattern_name']); ?></td><td><?php echo html_escape($row['service_type']); ?></td><td><?php echo (int)$row['sample_count']; ?></td><td>$<?php echo number_format((float)$row['average_unit_price'], 2); ?></td><td>$<?php echo number_format((float)$row['average_total'], 2); ?></td><td>$<?php echo number_format((float)$row['low_total'], 2); ?></td><td>$<?php echo number_format((float)$row['high_total'], 2); ?></td><td><?php echo html_escape($row['last_rebuilt_at']); ?></td></tr><?php } ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
