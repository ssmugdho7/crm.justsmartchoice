<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4><i class="fa fa-database"></i> Cost Database</h4><?php $this->load->view('estimating_hub_ai/partials/nav',['nav'=>$nav]); ?>
<div class="scie-toolbar">
  <button type="button" class="btn btn-info btn-sm" onclick="$('.scie-advanced-filters').toggle();"><i class="fa fa-filter"></i> Filters</button>
  <a href="<?php echo admin_url('estimating_hub_ai/sync_sources'); ?>" class="btn btn-success btn-sm"><i class="fa fa-link"></i> Sync CRM Sources</a>
  <a href="<?php echo admin_url('estimating_hub_ai/load_sample_database'); ?>" class="btn btn-warning btn-sm"><i class="fa fa-database"></i> Load Tampa Bay Database</a>
  <a href="<?php echo admin_url('estimating_hub_ai/sample_cost_database'); ?>" class="btn btn-default btn-sm">Sample Header</a>
  <button type="button" class="btn btn-primary btn-sm" onclick="$('#costImportBox').toggle();">Import</button>
  <a href="<?php echo admin_url('estimating_hub_ai/export_cost_database'); ?>" class="btn btn-default btn-sm">Export</a>
  <a href="<?php echo admin_url('estimating_hub_ai/reload'); ?>" class="btn btn-default btn-sm">Reload</a>
  <button form="costMassForm" class="btn btn-danger btn-sm" onclick="return scieConfirmMassAction('costMassForm');">Mass Delete</button>
  <?php echo form_open(admin_url('estimating_hub_ai/cost_database'), ['method'=>'get','class'=>'scie-search-form']); ?><input name="q" value="<?php echo html_escape($q ?? ''); ?>" class="form-control input-sm" placeholder="Search cost items..."><button class="btn btn-default btn-sm"><i class="fa fa-search"></i></button><?php echo form_close(); ?>
</div>
<div id="costImportBox" class="scie-import-box" style="display:none"><?php echo form_open_multipart(admin_url('estimating_hub_ai/import_cost_database')); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?><input type="file" name="import_file" accept=".csv" required> <button class="btn btn-success btn-sm">Accept And Import</button><?php echo form_close(); ?></div>
<div class="scie-advanced-filters" style="display:none"><div class="row"><div class="col-md-3"><input class="form-control input-sm scie-filter" placeholder="Filter by trade"></div><div class="col-md-3"><input class="form-control input-sm scie-filter" placeholder="Filter by source"></div><div class="col-md-3"><input class="form-control input-sm scie-filter" placeholder="Filter by region"></div><div class="col-md-3"><input class="form-control input-sm scie-filter" placeholder="Filter by category"></div></div></div>
<?php echo form_open(admin_url('estimating_hub_ai/mass_delete_cost_items'), ['id'=>'costMassForm']); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="table-responsive"><table class="table table-striped table-condensed scie-table"><thead><tr><th><input type="checkbox" class="scie-check-all"></th><th>Trade</th><th>Category</th><th>Item</th><th>Unit</th><th>Low</th><th>Typical</th><th>High</th><th>Source</th><th>Region</th></tr></thead><tbody>
<?php foreach($items as $r): ?><tr class="scie-filter-row"><td><input type="checkbox" name="ids[]" value="<?php echo (int)$r['id']; ?>"></td><td><?php echo html_escape($r['trade']); ?></td><td><?php echo html_escape($r['category']); ?></td><td><strong><?php echo html_escape($r['item_name']); ?></strong><br><small><?php echo html_escape(ehai_limit($r['description'],120)); ?></small></td><td><?php echo html_escape($r['unit']); ?></td><td>$<?php echo number_format((float)$r['material_cost_low'],2,'.',','); ?></td><td>$<?php echo number_format((float)$r['material_cost_typical'],2,'.',','); ?></td><td>$<?php echo number_format((float)$r['material_cost_high'],2,'.',','); ?></td><td><span class="label label-info"><?php echo html_escape($r['source'] ?? 'Manual'); ?></span></td><td><?php echo html_escape($r['region']); ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?>
