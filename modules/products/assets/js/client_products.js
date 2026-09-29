"use strict";

var cart_items = [];
var scProductI18n = window.scProductI18n || {};
function scT(key, fallback) { return scProductI18n[key] || fallback || key; }
function scEscape(value) { return $('<div/>').text(value == null ? '' : value).html(); }
function scCleanText(value) {
    return (value == null ? '' : String(value))
        .replace(/&nbsp;/gi, ' ')
        .replace(/\u00a0/g, ' ')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}
function scProductDescription(value) {
    var text = scCleanText(value);
    if (text.length > 165) { text = text.substring(0, 162) + '...'; }
    return scEscape(text);
}
function scLoadCategory(categoryId) {
    var postData = {};
    if (categoryId) { postData.p_category_id = [categoryId]; }
    filter_data(postData);
}

$(function() {
    filter_data();

    $(document).on('click', '.sc-category-chip', function() {
        $('.sc-category-chip').removeClass('active');
        $(this).addClass('active');
        var categoryId = $(this).data('category');
        $('#product_categories').selectpicker('val', categoryId ? [String(categoryId)] : []);
        scLoadCategory(categoryId);
    });

    $(document).on('click', '.qty-plus', function() {
        var input = $(this).closest('.sc-qty-control').find('input[name="quantity"]');
        var current = parseInt(input.val() || '0', 10);
        var max = parseInt(input.attr('max') || '0', 10);
        var next = current + 1;
        if (max > 0 && next > max) { next = max; }
        input.val(next > 0 ? next : 1).trigger('change');
    });

    $(document).on('click', '.qty-minus', function() {
        var input = $(this).closest('.sc-qty-control').find('input[name="quantity"]');
        var current = parseInt(input.val() || '1', 10);
        input.val(Math.max(current - 1, 1)).trigger('change');
    });

    $(document).on('click', '.sc-share-product', function() {
        $('#sc_share_product_id').val($(this).data('product-id'));
        $('#sc_share_email').val('');
        $('#scShareProductModal').modal('show');
    });

    $(document).on('click', '#scSendShareProduct', function() {
        var button = $(this);
        var email = $('#sc_share_email').val();
        var productId = $('#sc_share_product_id').val();
        if (!email || email.indexOf('@') === -1) {
            alert_float('danger', scT('product_share_error', 'Unable to send this product email.'));
            return false;
        }
        button.prop('disabled', true).addClass('disabled');
        $.post(site_url + 'products/client/share_product', {product_id: productId, email: email}, function(resp) {
            var data = {};
            try { data = typeof resp === 'object' ? resp : $.parseJSON(resp); } catch(e) { data = {}; }
            if (data.success) {
                $('#scShareProductModal').modal('hide');
                alert_float('success', scT('product_share_success', 'Product email sent successfully.'));
            } else {
                alert_float('danger', data.message || scT('product_share_error', 'Unable to send this product email.'));
            }
        }).fail(function(){
            alert_float('danger', scT('product_share_error', 'Unable to send this product email.'));
        }).always(function(){
            button.prop('disabled', false).removeClass('disabled');
        });
    });

    $(document).on('click', '.sc-slider-arrow', function(e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-product-slider');
        var img = wrap.find('img.sc-product-image');
        var images = [];
        try { images = JSON.parse(img.attr('data-images') || '[]'); } catch(err) { images = []; }
        if (!images.length) { return; }
        var current = parseInt(wrap.attr('data-index') || '0', 10);
        if ($(this).hasClass('sc-slider-next')) { current = (current + 1) % images.length; }
        else { current = (current - 1 + images.length) % images.length; }
        wrap.attr('data-index', current);
        img.attr('src', images[current]);
        wrap.find('.sc-slider-count').text((current + 1) + ' / ' + images.length);
    });

    $(document).on('click', '.add_cart', function() {
        var button = $(this);
        var row = button.closest('.product-row');
        var quantityInput = row.find('input[name="quantity"]');
        var quantity = parseInt(quantityInput.val() || '0', 10);
        var max = parseInt(quantityInput.attr('max') || '0', 10);
        var variation_max = parseInt(row.find('input.variation_quantity').attr('max') || '0', 10);

        if (quantity <= 0 || !$.isNumeric(quantity)) {
            alert_float('danger', scT('product_quantity_error', 'Quantity must be greater than 0.'));
            return false;
        }
        var product_id = row.find('input[name="product_id"]').val();
        var product_variation_id = row.find('input[name="product_variation_id"]').val();
        if (!row.hasClass('without-variations') && !product_variation_id) {
            alert_float('danger', scT('product_choose_option', 'Please choose a product option.'));
            return false;
        }
        if (product_variation_id && variation_max > 0 && quantity > variation_max) {
            alert_float('danger', scT('product_stock_error', 'Only %s item(s) are available.').replace('%s', variation_max));
            return false;
        }
        if (!product_variation_id && max > 0 && quantity > max) {
            alert_float('danger', scT('product_stock_error', 'Only %s item(s) are available.').replace('%s', max));
            return false;
        }
        button.prop('disabled', true).addClass('disabled');
        $.post(site_url + 'products/client/add_cart', {quantity: quantity, product_id: product_id, product_variation_id: product_variation_id}, function(data) {
            try { cart_items = $.parseJSON(data); } catch(e) { cart_items = []; }
            button.text(scT('update_cart', 'Update Cart'));
            alert_float('success', scT('product_added_to_cart_success', 'Item added to cart.'));
        }).always(function() { button.prop('disabled', false).removeClass('disabled'); });
    });

    $(document).on('change', '#product_categories', function() {
        $('.sc-category-chip').removeClass('active');
        filter_data({'p_category_id': $(this).val()});
    });
});

function filter_data(post_data) {
    post_data = post_data || {};
    $('.no_product').addClass('hidden');
    $('#filter_html').html('<div class="col-xs-12 text-center sc-loading">' + scEscape(scT('product_loading_catalog', 'Loading catalog...')) + '</div>');
    $.ajax({url: site_url + 'products/client/filter', type: 'POST', dataType: 'json', data: post_data,
        success: function(data) { render_product_data(data || []); },
        error: function() { $('#filter_html').html('<div class="col-xs-12"><div class="alert alert-danger">' + scEscape(scT('product_unable_load', 'Unable to load products.')) + '</div></div>'); }
    });
}

function scVariationGroups(variations) {
    var groups = {};
    $.each(variations || [], function(_, variation) {
        var key = String(variation.variation_id || variation.variation_name || 'option');
        if (!groups[key]) {
            groups[key] = { id: variation.variation_id, name: variation.display_variation_name || variation.variation_name || scT('product_option', 'Option'), values: [] };
        }
        groups[key].values.push(variation);
    });
    return groups;
}

function scGalleryHtml(val, productName, noImageUrl) {
    var images = val.product_gallery_urls && val.product_gallery_urls.length ? val.product_gallery_urls : [val.product_image_url];
    var html = '<div class="sc-product-image-wrap sc-product-slider" data-index="0">';
    html += '<img src="' + scEscape(images[0]) + '" alt="' + scEscape(productName) + '" class="img1 sc-product-image" data-images="' + scEscape(JSON.stringify(images)) + '" onerror="this.src=\'' + noImageUrl + '\'">';
    if (images.length > 1) {
        html += '<button type="button" class="sc-slider-arrow sc-slider-prev" aria-label="Previous image">‹</button>';
        html += '<button type="button" class="sc-slider-arrow sc-slider-next" aria-label="Next image">›</button>';
        html += '<div class="sc-slider-count">1 / ' + images.length + '</div>';
    }
    html += '</div>';
    return html;
}

function render_product_data(data) {
    var html = '';
    cart_items = [];
    $.each(data, function(index, val) {
        var cart_data_quantity = '';
        var button = '';
        var product_class = '';
        var total_taxes = '';
        if (val.cart_data) { cart_items.push(val.cart_data); }
        if (parseFloat(val.total_tax || 0) !== 0) {
            total_taxes = "<span class='total_taxes text-warning'>(+ " + scEscape(val.total_tax) + "% taxes)</span>";
        }
        if (parseInt(val.quantity_number || 0, 10) < 1 && val.is_digital != 1) {
            button = '<button type="button" class="btn btn-danger sc-cart-btn" disabled>' + scEscape(val.out_of_stock) + '</button>';
        } else {
            var label = val.add_to_cart || scT('add_to_cart', 'Add to Cart');
            if (val.cart_data && val.cart_data.quantity) { label = val.update_cart || scT('update_cart', 'Update Cart'); cart_data_quantity = val.cart_data.quantity; }
            button = '<button type="button" class="btn btn-warning add_cart sc-cart-btn">' + scEscape(label) + '</button>';
        }
        var max_attr = '';
        if (val.is_digital != 1) { max_attr = 'max="' + scEscape(val.quantity_number) + '"'; }
        var recurring_type = '';
        var cycles_text = '';
        if (val.recurring != 0) {
            recurring_type = val.recurring_type || 'month';
            recurring_type = '/ ' + ((val.recurring != 1) ? val.recurring : '') + ' ' + recurring_type;
            if (val.cycles == 0) cycles_text = 'Infinite recurring';
            if (val.cycles == 1) cycles_text = '1 time total';
            if (val.cycles > 1) cycles_text = val.cycles + ' times total';
        }
        var variations_content = '';
        var product_rate = val.rate;
        var min_price = parseFloat(val.rate || 0), max_price = parseFloat(val.rate || 0);
        if (val.variations && val.variations.length) {
            variations_content += '<div class="variations sc-variation-row">';
            var groups = scVariationGroups(val.variations);
            $.each(groups, function(groupKey, group) {
                variations_content += '<div class="sc-variation-line">';
                variations_content += '<label class="sc-small-label">' + scEscape(group.name) + '</label>';
                variations_content += '<select class="selectpicker variation_value_id form-control" data-width="100%">';
                variations_content += '<option value="" data-price="" data-quantity="">' + scEscape(scT('product_no_selection', 'No Selection')) + '</option>';
                $.each(group.values, function(_, item) {
                    var price = parseFloat(item.rate || val.rate || 0);
                    if (!isNaN(price)) { min_price = Math.min(min_price || price, price); max_price = Math.max(max_price || price, price); }
                    variations_content += '<option value="' + scEscape(item.id) + '" data-quantity="' + scEscape(item.quantity_number) + '" data-price="' + scEscape(item.rate) + '">' + scEscape(item.display_variation_value || item.variation_value) + '</option>';
                });
                variations_content += '</select></div>';
            });
            product_rate = (min_price != max_price) ? (min_price.toFixed(2) + ' - ' + max_price.toFixed(2)) : min_price.toFixed(2);
            variations_content += '</div>';
        } else {
            product_class = 'without-variations';
            variations_content = '<div class="variations sc-variation-row"><div class="sc-variation-line"><label class="sc-small-label">' + scEscape(scT('product_option', 'Option')) + '</label><select class="selectpicker variation_value_id form-control" disabled><option value="">' + scEscape(scT('product_no_selection', 'No Selection')) + '</option></select></div></div>';
        }
        var imageUrl = scEscape(val.product_image_url);
        var noImageUrl = scEscape(val.no_image_url);
        var qtyValue = cart_data_quantity ? scEscape(cart_data_quantity) : '1';
        var productName = val.display_product_name || val.product_name;
        var productDescription = val.display_product_description || val.product_description;
        var categoryName = val.display_category_name || val.p_category_name;
        html += '<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 product-row ' + product_class + '">' +
            '<div class="thumbnail shadow sc-product-card">' +
                scGalleryHtml(val, productName, noImageUrl) +
                '<div class="sc-product-body">' +
                    '<h4 class="sc-product-title">' + scEscape(productName) + '</h4>' +
                    '<div class="sc-product-description">' + scProductDescription(productDescription) + '</div>' +
                    '<div class="sc-category-label">' + scEscape(categoryName) + '</div>' +
                    '<div class="rates products-pricing sc-price"><span>' + scEscape(scT('product_starting_at', 'Starting at')) + ': ' + scEscape(val.base_currency_name) + ' <strong class="product-price">' + scEscape(product_rate) + '</strong> ' + scEscape(recurring_type) + '</span> ' + total_taxes + '<small class="product-cycles">' + scEscape(cycles_text) + '</small></div>' +
                    variations_content +
                    '<div class="input_data sc-card-actions">' +
                        '<div class="products-pricing sc-qty-control"><button type="button" class="qty-minus" aria-label="Decrease quantity">−</button><input type="number" inputmode="numeric" pattern="[0-9]*" name="quantity" min="1" ' + max_attr + ' value="' + qtyValue + '" class="form-control" placeholder="' + scEscape(val.qty) + '"><button type="button" class="qty-plus" aria-label="Increase quantity">+</button><input type="hidden" name="product_id" value="' + scEscape(val.id) + '" class="form-control"><input type="hidden" name="product_variation_id" value="" class="form-control"><input type="hidden" min="1" ' + max_attr + ' class="form-control variation_quantity"></div>' +
                        '<div class="products-pricing sc-action-button-wrap">' + button + '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-default btn-block sc-share-product" data-product-id="' + scEscape(val.id) + '"><i class="fa fa-share-alt"></i> ' + scEscape(scT('product_share', 'Share')) + '</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    });
    $('#filter_html').hide().html(html).fadeIn('fast');
    if (html === '') { $('.no_product').removeClass('hidden'); }
    $(document).off('change.scVariationValue', '.selectpicker.variation_value_id').on('change.scVariationValue', '.selectpicker.variation_value_id', function() {
        var row = $(this).closest('.product-row');
        var product_variation_id = $(this).val();
        row.find('input[name="product_variation_id"]').val(product_variation_id);
        var selected = $(this).find('option:selected');
        if (selected.length && product_variation_id) {
            row.find('input.variation_quantity').attr('max', selected.data('quantity'));
            row.find('input[name="quantity"]').attr('max', selected.data('quantity'));
            row.find('.product-price').html(scEscape(selected.data('price')));
        }
        row.find('.add_cart').text(scT('add_to_cart', 'Add to Cart'));
    });
    appSelectPicker();
}

function render_product_variation_values_data(el, data) { return; }
