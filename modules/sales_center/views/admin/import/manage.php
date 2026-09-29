<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo function_exists('sales_center_admin_submenu') ? sales_center_admin_submenu($target) : ''; ?>
        <div class="panel_s smartsource-panel">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0"><?php echo html_escape($title); ?></h4>
                        <p class="text-muted tw-mb-0">Upload a CSV file, preview the mapped columns, then confirm before the data is written to the CRM.</p>
                    </div>
                    <div class="smartsource-toolbar-inline">
                        <a href="<?php echo admin_url('sales_center/sample_file/' . $target); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
                        <a href="<?php echo admin_url('sales_center/table_export/' . $target); ?>" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Export</a>
                        <a href="<?php echo admin_url('sales_center/' . ($target === 'reports' ? 'reports' : ($target === 'contracts' ? 'contracts' : ($target === 'salespersons' ? '' : ($target === 'templates' ? 'templates' : 'documents'))))); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <?php echo form_open_multipart(admin_url('sales_center/import/' . $target)); ?>
                <div class="row">
                    <div class="col-md-4">
                        <label>Import Format</label>
                        <select name="import_format" class="form-control">
                            <option value="csv">CSV</option>
                            <option value="xlsx" disabled>Excel XLSX - Save As CSV First</option>
                            <option value="pdf" disabled>PDF - Not A Table Import Format</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Header Row</label>
                        <select name="has_header" class="form-control">
                            <option value="1">File Has Header Row</option>
                            <option value="0">File Has No Header Row</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>CSV File</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    </div>
                </div>
                <div class="tw-mt-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Preview Import</button>
                    <a href="<?php echo admin_url('sales_center/sample_file/' . $target); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Download Sample Header</a>
                </div>
                <?php echo form_close(); ?>
                <hr>
                <h5>Expected Header</h5>
                <code><?php echo html_escape(implode(', ', $headers)); ?></code>
                <?php if (!empty($preview_rows)) { ?>
                    <hr>
                    <h5>Preview Before Import</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped smartsource-table">
                            <thead><tr><?php foreach (array_keys($preview_rows[0]) as $header) { ?><th><?php echo html_escape(ucwords(str_replace('_', ' ', $header))); ?></th><?php } ?></tr></thead>
                            <tbody><?php foreach ($preview_rows as $row) { ?><tr><?php foreach ($row as $value) { ?><td><?php echo html_escape($value); ?></td><?php } ?></tr><?php } ?></tbody>
                        </table>
                    </div>
                    <?php echo form_open(admin_url('sales_center/import_confirm/' . $target)); ?>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Accept And Import</button>
                    <a href="<?php echo admin_url('sales_center/import/' . $target); ?>" class="btn btn-default btn-sm">Cancel</a>
                    <?php echo form_close(); ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
