<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="sc-products-nav panel_s">
  <div class="panel-body">
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products'); ?>"><i class="fa fa-th-large"></i> Product Catalog</a>
<?php } ?>
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/products_categories'); ?>"><i class="fa fa-folder-open"></i> Product Categories</a>
<?php } ?>
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/order_history'); ?>"><i class="fa fa-history"></i> Order History</a>
<?php } ?>
<?php if (has_permission('products', '', 'create')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/staff_order'); ?>"><i class="fa fa-cash-register"></i> New Order POS</a>
<?php } ?>
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/variations'); ?>"><i class="fa fa-sliders"></i> Variations</a>
<?php } ?>
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/coupons'); ?>"><i class="fa fa-ticket"></i> Coupons</a>
<?php } ?>
<?php if (has_permission('products', '', 'view')) { ?>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('products/order_report'); ?>"><i class="fa fa-chart-line"></i> Reports</a>
<?php } ?>
<?php if (has_permission('settings', '', 'view')) { ?>
    <a class="btn btn-warning btn-sm" href="<?php echo admin_url('settings?group=products'); ?>"><i class="fa fa-cog"></i> Settings</a>
<?php } ?>
  </div>
</div>
