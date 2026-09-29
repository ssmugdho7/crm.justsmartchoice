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
                                <h3 class="supplier-title"><i class="fa fa-truck"></i> <?php echo _l('supplier_menu_name'); ?></h3>
                                <div class="supplier-subtitle"><?php echo _l('supplier_description'); ?></div>
                            </div>
                            <div class="supplier-actions">
                                <a href="#" onclick="init_supplier_modal(0); return false;" class="btn btn-info supplier-btn"><i class="fa fa-plus"></i> <?php echo _l('supplier_add_new'); ?></a>
                            </div>
                        </div>

                        <div class="supplier-filter-bar">
                            <div class="row">
                                <div class="col-md-3"><input type="text" id="supplier_filter_keyword" class="form-control input-sm" placeholder="<?php echo _l('supplier_filter_keyword'); ?>"></div>
                                <div class="col-md-2"><select id="supplier_filter_trade" class="form-control input-sm"><option value=""><?php echo _l('supplier_all_trades'); ?></option><?php foreach($trades as $trade){ ?><option value="<?php echo html_escape($trade); ?>"><?php echo html_escape($trade); ?></option><?php } ?></select></div>
                                <div class="col-md-2"><select id="supplier_filter_location" class="form-control input-sm"><option value=""><?php echo _l('supplier_all_locations'); ?></option><?php foreach($locations as $location){ ?><option value="<?php echo (int)$location['id']; ?>"><?php echo html_escape($location['location_name']); ?></option><?php } ?></select></div>
                                <div class="col-md-2"><select id="supplier_filter_visibility" class="form-control input-sm"><option value=""><?php echo _l('supplier_all_visibility'); ?></option><option value="public"><?php echo _l('supplier_public'); ?></option><option value="private"><?php echo _l('supplier_private'); ?></option></select></div>
                                <div class="col-md-3"><button type="button" class="btn btn-info btn-sm supplier-btn" onclick="supplierApplyFilters();"><i class="fa fa-filter"></i> <?php echo _l('filter'); ?></button> <button type="button" class="btn btn-default btn-sm supplier-btn" onclick="supplierClearFilters();"><i class="fa fa-times"></i> <?php echo _l('clear'); ?></button></div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <div id="supplier-static-toolbar" class="supplier-static-toolbar">
                            <a href="<?php echo admin_url('supplier/import'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-upload"></i> <?php echo _l('import'); ?></a>
                            <a href="<?php echo admin_url('supplier/sample_header'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-download"></i> <?php echo _l('supplier_sample_header'); ?></a>
                            <a href="<?php echo admin_url('supplier/locations'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-map-marker"></i> <?php echo _l('supplier_locations'); ?></a>
                            <div class="btn-group supplier-export-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle supplier-toolbar-btn supplier-main-export" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-download"></i> <?php echo _l('export'); ?> <span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="#" onclick="supplierExport('excel'); return false;"><i class="fa fa-file-excel-o"></i> Excel</a></li>
                                    <li><a href="#" onclick="supplierExport('csv'); return false;"><i class="fa fa-file-text-o"></i> CSV</a></li>
                                    <li><a href="#" onclick="supplierExport('pdf'); return false;"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
                                    <li><a href="#" onclick="supplierPrint(); return false;"><i class="fa fa-print"></i> Print</a></li>
                                </ul>
                            </div>
                            <a href="#" data-toggle="modal" data-target="#supplier_bulk_actions" class="btn btn-danger btn-sm supplier-toolbar-btn"><i class="fa fa-trash"></i> <?php echo _l('mass_delete'); ?></a>
                        </div>
<div id="supplier-toolbar-template" class="supplier-dt-toolbar" style="display:none;">
                            <a href="<?php echo admin_url('supplier/import'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-upload"></i> <?php echo _l('import'); ?></a>
                            <a href="<?php echo admin_url('supplier/sample_header'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-download"></i> <?php echo _l('supplier_sample_header'); ?></a>
                            <a href="<?php echo admin_url('supplier/locations'); ?>" class="btn btn-default btn-sm supplier-toolbar-btn"><i class="fa fa-map-marker"></i> <?php echo _l('supplier_locations'); ?></a>
                            <div class="btn-group supplier-export-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle supplier-toolbar-btn supplier-main-export" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-download"></i> <?php echo _l('export'); ?> <span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="#" onclick="supplierExport('excel'); return false;"><i class="fa fa-file-excel-o"></i> Excel</a></li>
                                    <li><a href="#" onclick="supplierExport('csv'); return false;"><i class="fa fa-file-text-o"></i> CSV</a></li>
                                    <li><a href="#" onclick="supplierExport('pdf'); return false;"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
                                    <li><a href="#" onclick="supplierPrint(); return false;"><i class="fa fa-print"></i> Print</a></li>
                                </ul>
                            </div>
                            <a href="#" data-toggle="modal" data-target="#supplier_bulk_actions" class="btn btn-danger btn-sm supplier-toolbar-btn"><i class="fa fa-trash"></i> <?php echo _l('mass_delete'); ?></a>
                        </div>
                        <?php render_datatable([
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="suppliers"><label></label></div>',
                            _l('supplier_name'),
                            _l('supplier_website'),
                            _l('supplier_phone'),
                            _l('supplier_email'),
                            _l('supplier_trade'),
                            _l('supplier_short_description'),
                            _l('supplier_tags'),
                            _l('options'),
                        ], 'suppliers', ['supplier-compact-table'], ['data-last-order-identifier'=>'suppliers']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplier_bulk_actions" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('supplier_mass_action'); ?></h4></div>
        <div class="modal-body"><div class="checkbox checkbox-danger"><input type="checkbox" name="mass_delete" id="mass_delete"><label for="mass_delete"><?php echo _l('mass_delete'); ?></label></div><p class="text-muted"><?php echo _l('supplier_mass_action_note'); ?></p></div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><a href="#" class="btn btn-danger" onclick="supplierBulkAction(this); return false;"><?php echo _l('confirm'); ?></a></div>
    </div></div>
</div>

<div class="modal fade" id="supplier_email_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <?php echo form_open_multipart(admin_url('supplier/send_email'), ['id' => 'supplier-email-form']); ?>
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('supplier_send_email'); ?></h4></div>
        <div class="modal-body" id="supplier-email-modal-content"></div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-info"><?php echo _l('send'); ?></button></div>
        <?php echo form_close(); ?>
    </div></div>
</div>

<?php init_tail(); ?>
<?php $this->load->view('supplier/modals/supplier_form'); ?>
<script>
$(function(){
    initDataTable('.table-suppliers', admin_url + 'supplier', [0], [0], {}, [1, 'asc']);
    <?php if($this->input->get('new')){ ?>init_supplier_modal(0);<?php } ?>
});
</script>
</body>
</html>
