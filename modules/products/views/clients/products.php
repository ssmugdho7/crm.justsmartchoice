<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $cart_url = site_url('products/client/place_order'); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('products', 'assets/css/legacy_catalog.css'); ?>?v=3">
<section id="sc-service-catalog" aria-label="Online Shopping">
<div class="sc-shop-toolbar">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <?php echo render_select('product_categories', $product_categories, ['p_category_id', 'display_name'], _l('products_categories'), '', ['multiple'=>true, 'data-none-selected-text'=>_l('products_categories'), 'data-actions-box'=>'true'], [], 'select_cat', '', false); ?>
        </div>
        <div class="col-md-8 col-sm-12 text-right sc-toolbar-actions">
            <a href="<?php echo $cart_url; ?>" class="btn btn-info sc-pill-btn"><i class="fa fa-shopping-cart"></i> <?php echo _l('view_cart'); ?></a>
            <a href="<?php echo $cart_url; ?>" class="btn btn-success sc-pill-btn"><i class="fa fa-credit-card"></i> <?php echo _l('checkout'); ?></a>
        </div>
    </div>
</div>


<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 text-center no_product hidden">
        <br><br><img src="<?php echo module_dir_url('products', 'uploads').'/no-product.png'; ?>" class="img1 img-responsive">
    </div>
    <div id="filter_html" class="sc-product-grid"></div>
</div>

</section>

<div class="modal fade" id="scProductOptionsModal" tabindex="-1" role="dialog" aria-labelledby="scProductOptionsTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content product-row">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="scProductOptionsTitle"></h4>
            </div>
            <div class="modal-body" id="scProductOptionsBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-warning add_cart"><?php echo _l('add_to_cart'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="scShareProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-share-alt"></i> <?php echo _l('product_share_title'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sc_share_product_id" value="">
                <div class="form-group">
                    <label for="sc_share_email"><?php echo _l('product_share_friend_email'); ?></label>
                    <input type="email" id="sc_share_email" class="form-control" placeholder="friend@example.com">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-success" id="scSendShareProduct"><i class="fa fa-paper-plane"></i> <?php echo _l('product_share_send'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
window.scProductI18n = <?php echo json_encode($client_i18n ?? []); ?>;
</script>
<?php
    if (!is_client_logged_in()) {
        if (1 == get_option('nlu_hiddenprices_disabled')) {
            echo '<style>.products-pricing { display: none; }</style>';
        }
    }
    if (1 == get_option('b2bmode_disabled')) {
        echo '<style>.products-pricing { display: none; }</style>';
    }
?>
<script type="text/javascript" src="<?php echo module_dir_url('products', 'assets/js/client_products.js'); ?>?v=2.0.6"></script>
