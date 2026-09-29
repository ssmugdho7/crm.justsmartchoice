<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s supplier-panel">
                    <div class="panel-body">
                        <div class="supplier-header-flex">
                            <div>
                                <h3 class="supplier-title"><i class="fa fa-upload"></i> <?php echo _l('supplier_import'); ?></h3>
                                <div class="supplier-subtitle"><?php echo _l('supplier_import_description'); ?></div>
                            </div>
                            <div class="supplier-actions">
                                <a href="<?php echo admin_url('supplier'); ?>" class="btn btn-default supplier-btn"><i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?></a>
                                <a href="<?php echo admin_url('supplier/sample_header'); ?>" class="btn btn-default supplier-btn"><i class="fa fa-download"></i> <?php echo _l('supplier_sample_header'); ?></a>
                                <a href="<?php echo admin_url('supplier/standard_suppliers_file'); ?>" class="btn btn-info supplier-btn"><i class="fa fa-file-text-o"></i> <?php echo _l('supplier_standard_supplier_file'); ?></a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart(admin_url('supplier/import'), ['id' => 'supplier-import-form']); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file_csv" class="control-label"><?php echo _l('supplier_import_csv_file'); ?></label>
                                    <input type="file" name="file_csv" id="file_csv" class="form-control" accept=".csv" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info supplier-import-help">
                                    <?php echo _l('supplier_import_help'); ?><br><br><?php echo _l('supplier_import_standard_help'); ?>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info supplier-btn"><i class="fa fa-upload"></i> <?php echo _l('import'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
appValidateForm($('#supplier-import-form'), {file_csv: 'required'});
</script>
</body>
</html>
