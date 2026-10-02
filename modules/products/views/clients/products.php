<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $cart_url = site_url('products/client/place_order'); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('products', 'assets/css/catalog.css'); ?>?v=2">
<section id="sc-catalog" aria-labelledby="sc-catalog-title">
    <header class="sc-catalog-heading">
        <p class="sc-catalog-eyebrow">SMART CHOICE SERVICES</p>
        <h1 id="sc-catalog-title">Online Shopping</h1>
        <p>Explore services, choose your options, and build your project cart.</p>
    </header>
    <div class="sc-shop-toolbar sc-tw-sticky sc-tw-top-0 sc-tw-z-10 sc-tw-backdrop-blur sc-tw-bg-white/80 sc-tw-border sc-tw-border-slate-200 sc-tw-rounded-xl">
        <div class="sc-shop-utilities" aria-label="Portal shortcuts"></div>
        <div class="sc-shop-controls">
            <div class="sc-shop-search"><label for="sc-catalog-search">Search services</label><div class="sc-shop-search-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input type="search" id="sc-catalog-search" placeholder="Search by name or description" autocomplete="off" aria-controls="filter_html"></div></div>
            <div class="sc-shop-category"><?php echo render_select('product_categories', $product_categories, ['p_category_id', 'display_name'], _l('products_categories'), '', ['multiple'=>true, 'data-none-selected-text'=>_l('products_categories'), 'data-actions-box'=>'true', 'data-live-search'=>'true'], [], 'select_cat', '', false); ?></div>
            <div class="sc-shop-sort"><label for="sc-catalog-sort">Sort by</label><select id="sc-catalog-sort" aria-controls="filter_html"><option value="default">Catalog order</option><option value="name">Name: A–Z</option><option value="name-desc">Name: Z–A</option></select></div>
            <div class="sc-toolbar-actions">
                <a href="<?php echo $cart_url; ?>" class="sc-shop-cart sc-tw-inline-flex sc-tw-items-center sc-tw-gap-2 sc-tw-rounded-lg sc-tw-border sc-tw-border-slate-300 sc-tw-bg-white sc-tw-text-slate-700 sc-tw-font-medium"><i class="fa fa-shopping-cart" aria-hidden="true"></i> <?php echo _l('view_cart'); ?><span id="sc-cart-count" class="sc-cart-count" aria-label="Items in cart" hidden>0</span></a>
                <a href="<?php echo $cart_url; ?>" class="sc-shop-checkout sc-tw-inline-flex sc-tw-items-center sc-tw-gap-2 sc-tw-rounded-lg sc-tw-bg-teal-700 sc-tw-text-white sc-tw-font-medium"><i class="fa fa-credit-card" aria-hidden="true"></i> <?php echo _l('checkout'); ?></a>
            </div>
        </div>
    </div>
    <p id="sc-catalog-count" class="sc-catalog-count" role="status" aria-live="polite"></p>
    <div class="no_product sc-catalog-empty hidden"><i class="fa fa-search" aria-hidden="true"></i><h2>No services found</h2><p>Try another search or category.</p><button type="button" id="sc-catalog-reset">Clear filters</button></div>
    <div id="filter_html" class="sc-product-grid sc-tw-grid sc-tw-grid-cols-1 md:sc-tw-grid-cols-2 lg:sc-tw-grid-cols-3 sc-tw-gap-6" aria-busy="true"></div>
</section>

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
<script type="text/javascript" src="<?php echo module_dir_url('products', 'assets/js/client_products.js'); ?>?v=3.0.0"></script>

<script src="<?php echo module_dir_url('products', 'assets/js/catalog.js'); ?>?v=2"></script>
