<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
<?php $this->load->view('products/nav'); ?>

    <div class="row">
      <div class="col-md-12">
        <div class="panel_s " id="TableData">
          <div class="panel-body">
            <?php if (has_permission('products', '', 'create')) { ?>
            <a href="<?php echo admin_url('products/add_product'); ?>" class="btn btn-info pull-left display-block">
              <?php echo _l('new_product'); ?>
            </a>
            <?php } ?>
          </div>  
        </div>
        <div class="row">
          <div class="col-md-12" id="panel">
           <div class="panel_s">
              <div class="panel-body">
                <?php
                $table_data = [
                    '<input type="checkbox" class="sc-products-check-all">',
                    _l('product_name'),
                    _l('product_image'),
                    _l('product_variations'),
                    _l('product_description'),
                    _l('products_categories'),
                    _l('invoice_item_add_edit_rate_currency'),
                    _l('quantity'),
                    _l('tax'),
                  ];
                  echo '<div class="sc-products-toolbar"><input type="text" class="form-control sc-products-filter" placeholder="Search products...">'.(has_permission('products', '', 'create') ? '<form method="post" action="'.admin_url('products/import_products').'" enctype="multipart/form-data" style="display:inline-flex;gap:4px;align-items:center"><input type="file" name="import_file" class="form-control input-sm" style="max-width:170px"><button class="btn btn-default" type="submit"><i class="fa fa-upload"></i> Import</button></form>' : '').'<a class="btn btn-default" href="'.admin_url('products/sample_header/products').'"><i class="fa fa-file"></i> Sample Header</a><button type="button" class="btn btn-default sc-products-export"><i class="fa fa-download"></i> Export</button><button type="button" class="btn btn-default sc-products-reload"><i class="fa fa-refresh"></i> Reload</button>'.(has_permission('products', '', 'delete') ? '<button type="button" class="btn btn-danger sc-products-mass-delete"><i class="fa fa-trash"></i> Mass Delete</button>' : '').'</div>';
                  echo form_open(admin_url('products/mass_delete'), ['id'=>'sc-products-mass-form']);
                  render_datatable($table_data, ($class ?? 'products'));
                  echo form_close(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php init_tail(); ?>
<script type="text/javascript">
  $(function(){
    initDataTable('.table-products', window.location.href,'undefined','undefined','');
  });
</script>