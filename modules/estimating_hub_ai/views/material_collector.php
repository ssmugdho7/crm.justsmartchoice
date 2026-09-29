<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="ehai-page-head"><div><h4><i class="fa fa-cloud-download-alt"></i> Material Price Collector</h4><p>Receive public material and labor price records from the Smart Choice Python collector app.</p></div><a href="<?php echo admin_url('estimating_hub_ai/help'); ?>" class="btn btn-info btn-xs"><i class="fa fa-question-circle"></i> Guide</a></div>
<?php $this->load->view('estimating_hub_ai/partials/nav',['nav'=>$nav]); ?>

<div class="scie-card"><h4>Collect Public Product Or Cost URL</h4>
<?php echo form_open(admin_url('estimating_hub_ai/collect_public_url')); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="row"><div class="col-md-3"><label>Vendor / Source</label><select name="vendor" class="form-control input-sm"><option>Home Depot</option><option>Homewyse</option><option>Public Source</option></select></div><div class="col-md-7"><label>Public URL</label><input name="source_url" class="form-control input-sm" placeholder="https://www.homedepot.com/... or https://www.homewyse.com/..."></div><div class="col-md-2"><label>&nbsp;</label><button class="btn btn-success btn-sm btn-block"><i class="fa fa-cloud-download-alt"></i> Collect</button></div></div>
<p class="text-muted mtop5">This collects public title/price information when the server can access the page. If the website blocks server requests, use CSV import or the Python collector app.</p>
<?php echo form_close(); ?>
</div>

<div class="alert alert-info">Use this page for collected public product records, Home Depot product URL records, and Homewyse public cost-page records. Every imported record is saved with source, ZIP code, timestamp, and price history.</div>
<div class="scie-toolbar">
  <button type="button" class="btn btn-info btn-sm" onclick="$('.scie-advanced-filters').toggle();"><i class="fa fa-filter"></i> Filters</button>
  <a href="<?php echo admin_url('estimating_hub_ai/collector_sample'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Sample Header</a>
  <button type="button" class="btn btn-primary btn-sm" onclick="$('#collectorImportBox').toggle();"><i class="fa fa-upload"></i> Import</button>
  <a href="<?php echo admin_url('estimating_hub_ai/collector_export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-file-excel"></i> Export</a>
  <a href="<?php echo admin_url('estimating_hub_ai/reload'); ?>" class="btn btn-default btn-sm"><i class="fa fa-sync"></i> Reload</a>
  <button form="collectorMassForm" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete the selected records?');"><i class="fa fa-trash"></i> Mass Delete</button>
  <input class="form-control input-sm scie-filter" placeholder="Search material prices...">
</div>
<div id="collectorImportBox" class="scie-import-box" style="display:none"><?php echo form_open_multipart(admin_url('estimating_hub_ai/collector_import')); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?><input type="file" name="import_file" accept=".csv" required> <button class="btn btn-success btn-sm">Accept And Import</button><?php echo form_close(); ?></div>
<div class="scie-advanced-filters" style="display:none"><input class="form-control input-sm scie-filter" placeholder="Filter by vendor, SKU, brand, category, ZIP, or source URL..."></div>
<?php echo form_open(admin_url('estimating_hub_ai/collector_mass_delete'), ['id'=>'collectorMassForm']); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="table-responsive"><table class="table table-striped table-condensed scie-table"><thead><tr><th><input type="checkbox" class="scie-check-all"></th><th>Vendor</th><th>SKU</th><th>Brand</th><th>Item</th><th>Category</th><th>Low</th><th>Mid</th><th>High</th><th>ZIP</th><th>Source</th><th>Checked</th></tr></thead><tbody>
<?php foreach($sources as $r): ?><tr class="scie-filter-row"><td><input type="checkbox" name="ids[]" value="<?php echo (int)$r['id']; ?>"></td><td><?php echo html_escape($r['vendor']); ?></td><td><?php echo html_escape($r['sku']); ?></td><td><?php echo html_escape($r['brand']); ?></td><td><?php echo html_escape($r['item_name']); ?></td><td><?php echo html_escape($r['category']); ?></td><td>$<?php echo number_format((float)$r['price_low'],2,'.',','); ?></td><td>$<?php echo number_format((float)$r['price_mid'],2,'.',','); ?></td><td>$<?php echo number_format((float)$r['price_high'],2,'.',','); ?></td><td><?php echo html_escape($r['zip_code']); ?></td><td><?php if(!empty($r['source_url'])): ?><a href="<?php echo html_escape($r['source_url']); ?>" target="_blank">Open</a><?php endif; ?></td><td><?php echo $r['last_checked'] ? _dt($r['last_checked']) : ''; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?>
