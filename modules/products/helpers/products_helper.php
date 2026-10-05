<?php

defined('BASEPATH') or exit('No direct script access allowed');

function handle_product_upload($product_id)
{
    $CI = &get_instance();
    $path = get_upload_path_by_type('products');
    _maybe_create_upload_path($path);
    $allowed = ['png','jpg','jpeg','gif','webp','bmp'];

    if (isset($_FILES['product']['name']) && $_FILES['product']['name'] !== '') {
        $tmpFilePath = $_FILES['product']['tmp_name'];
        if (!empty($tmpFilePath)) {
            $extension = strtolower(pathinfo($_FILES['product']['name'], PATHINFO_EXTENSION));
            if (in_array($extension, $allowed, true)) {
                $filename = 'product_'.$product_id.'_main_'.time().'.'.$extension;
                if (move_uploaded_file($tmpFilePath, $path.$filename)) {
                    $CI->products_model->edit_product(['product_image' => $filename], $product_id);
                    products_save_gallery_image($product_id, $filename, 1);
                }
            }
        }
    }

    if (isset($_FILES['product_gallery']['name']) && is_array($_FILES['product_gallery']['name'])) {
        foreach ($_FILES['product_gallery']['name'] as $i => $name) {
            if ($name === '') { continue; }
            $tmpFilePath = $_FILES['product_gallery']['tmp_name'][$i] ?? '';
            if (empty($tmpFilePath)) { continue; }
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed, true)) { continue; }
            $filename = 'product_'.$product_id.'_gallery_'.$i.'_'.time().'_'.mt_rand(1000,9999).'.'.$extension;
            if (move_uploaded_file($tmpFilePath, $path.$filename)) {
                products_save_gallery_image($product_id, $filename, 0);
            }
        }
    }
    return true;
}

function products_save_gallery_image($product_id, $filename, $is_primary = 0)
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix().'product_images')) { return false; }
    $exists = $CI->db->where('product_id', $product_id)->where('image', $filename)->get(db_prefix().'product_images')->row();
    if ($exists) { return true; }
    $CI->db->insert(db_prefix().'product_images', [
        'product_id' => (int)$product_id,
        'image' => $filename,
        'is_primary' => (int)$is_primary,
        'datecreated' => date('Y-m-d H:i:s'),
    ]);
    return true;
}

function products_get_gallery_images($product_id)
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix().'product_images')) { return []; }
    return $CI->db->where('product_id', (int)$product_id)->order_by('is_primary DESC, id ASC')->get(db_prefix().'product_images')->result();
}

/** Resolve real uploads before the recovered, service-specific catalog artwork. */
function products_catalog_artwork_urls(array $product, array $gallery = [])
{
    $name = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $product['product_name'] ?? ''), '-'));
    $urls = [];
    $root = realpath(module_dir_path('products', 'uploads'));
    $filenames = array_merge([$product['product_image'] ?? ''], array_map(function ($image) {
        return ((array) $image)['image'] ?? '';
    }, $gallery));
    foreach ($filenames as $filename) {
        // Never resolve customer-controlled paths, URLs or symlinks outside public product uploads.
        if (!is_string($filename) || $filename === '' || basename($filename) !== $filename
            || !preg_match('/\A[A-Za-z0-9_.-]+\.(?:jpe?g|png|webp|gif)\z/i', $filename)) {
            continue;
        }
        // These legacy generated files all contain the same door photo with changed captions.
        if (preg_match('/\Asc-default-service-[123]\.jpg\z/i', $filename)
            || $filename === 'sc-' . $name . '.jpg'
            || in_array(strtolower($filename), ['image-not-available.png', 'no-product.png'], true)) {
            continue;
        }
        $file = $root ? realpath($root . DIRECTORY_SEPARATOR . $filename) : false;
        if ($file && is_file($file) && strpos($file, $root . DIRECTORY_SEPARATOR) === 0) {
            $urls[] = module_dir_url('products', 'uploads/' . rawurlencode($filename));
        }
    }
    if ($urls) {
        return array_values(array_unique($urls));
    }

    static $originals;
    if ($originals === null) {
        $originals = json_decode(file_get_contents(module_dir_path('products', 'assets/images/original-services/manifest.json')), true);
    }
    $artwork = $originals[$name] ?? null;
    if (!$artwork) {
        // Related services reuse the nearest existing artwork; never the universal door template.
        $matches = [
            'window.*clean|screen.*clean' => 6, 'dryer.*vent' => 8, 'gutter' => 4,
            'pressure.*wash' => 5, 'lawn|yard' => 7,
            'garage.*opener' => 18, 'garage.*door' => 19,
            'door.*(?:lock|handle|hardware)' => 14, 'door' => 17, 'window|screen|glass' => 20,
            'smoke|co-detector' => 12, 'panel|ev-charger' => 16,
            'fan|light|fixture' => 9, 'electrical|electric|outlet|switch|circuit' => 15,
            'kitchen.*faucet' => 22, 'toilet' => 21, 'disposal|sink' => 23,
            'faucet|showerhead|plumb|leak|water-heater|shower|tub' => 11,
            'drywall|paint|primer|texture' => 13, 'permit|hoa' => 26,
            'engineering|engineer|stamp|seal|structural|wind-load|inspection' => 24,
            'draft|design|plan|drawing|revision|scope' => 25,
            'shed|storage' => 28, 'fenc|gate|post-replacement' => 29,
            'clean' => 3, 'hvac|duct|air-handler|vent' => 8, 'kitchen|cabinet|pantry' => 22,
        ];
        foreach ($matches as $pattern => $id) {
            if (preg_match('/' . $pattern . '/i', $name)) {
                $artwork = 'product_' . $id . '.webp';
                break;
            }
        }
    }
    if (!$artwork) {
        $category = strtolower($product['p_category_name'] ?? '');
        $artwork = 'home-service.svg';
        foreach (['flooring' => 'floor|tile|baseboard|stair', 'roofing' => 'roof|fascia|soffit', 'concrete' => 'concrete|curb|foot|slab|masonry'] as $type => $pattern) {
            if (preg_match('/' . $pattern . '/i', $name . ' ' . $category)) {
                $artwork = $type . '.svg';
                break;
            }
        }
    }
    return [module_dir_url('products', 'assets/images/original-services/' . $artwork)];
}

function products_nav_links()
{
    return [
        ['Product Catalog', admin_url('products'), 'fa fa-th-large'],
        ['Product Categories', admin_url('products/products_categories'), 'fa fa-folder-open'],
        ['Order History', admin_url('products/order_history'), 'fa fa-history'],
        ['New Order POS', admin_url('products/staff_order'), 'fa fa-cash-register'],
        ['Variations', admin_url('products/variations'), 'fa fa-sliders'],
        ['Coupons', admin_url('products/coupons'), 'fa fa-ticket'],
        ['Order Report', admin_url('products/order_report'), 'fa fa-chart-line'],
        ['Settings', admin_url('settings?group=products'), 'fa fa-cog'],
    ];
}

function get_coupon_used_times($coupon_id)
{
    $CI = &get_instance();
    $coupon_used_times = $CI->coupons_model->get_used_times($coupon_id);
    return $coupon_used_times;
}

function get_product_variations($product_id)
{
    $CI = &get_instance();
    $product_variations = $CI->products_model->get_by_id_variations($product_id);
    $variations = '';
    foreach ($product_variations as $product_variation) {
        $variations .= '<span class="label label-warning">' . html_escape($product_variation->variation_name) . '</span> ';
    }
    return $variations;
}

function get_product_variation_price($product_id)
{
    $CI = &get_instance();
    $base_currency = $CI->currencies_model->get_base_currency();
    $product_variations = $CI->products_model->get_by_id_variations($product_id);
    $min_price = 0; $max_price = 0;
    foreach ($product_variations as $product_variation) {
        if (!$min_price) $min_price = $product_variation->rate;
        if (!$max_price) $max_price = $product_variation->rate;
        if ($min_price > $product_variation->rate) $min_price = $product_variation->rate;
        if ($max_price < $product_variation->rate) $max_price = $product_variation->rate;
    }
    $variation_price = app_format_money($min_price, $base_currency->name);
    if ($min_price != $max_price) {
        if ($base_currency->placement == 'before') {
            $variation_price .= ' - ' . str_replace($base_currency->symbol, '', str_replace($base_currency->name, '', app_format_money($max_price, $base_currency->name)));
        } else {
            $variation_price = str_replace($base_currency->symbol, '', str_replace($base_currency->name, '', $variation_price));
            $variation_price .= ' - ' . app_format_money($max_price, $base_currency->name);
        }
    }
    return $variation_price;
}

function get_product_variation_values($product_id)
{
    $CI = &get_instance();
    $product_variation_values = $CI->products_model->get_by_id_variation_values($product_id);
    $variation = '';
    $variation_values = '';
    foreach ($product_variation_values as $product_variation_value) {
        if ($variation != $product_variation_value->variation_name) {
            $variation = $product_variation_value->variation_name;
            if ($variation_values) { $variation_values .= '</div>'; }
            $variation_values .= '<div>' . html_escape($product_variation_value->variation_name) . ' - ';
        }
        $variation_values .= '<span class="label label-warning">' . html_escape($product_variation_value->variation_value) . '</span> ';
    }
    $variation_values .= '</div>';
    return $variation_values;
}

function get_variation_values($variation_id)
{
    $CI = &get_instance();
    $variation_values = $CI->variations_model->get_values($variation_id);
    $values = '';
    foreach ($variation_values as $variation_value) {
        $values .= '<span class="label label-warning">' . html_escape($variation_value['value']) . '</span> ';
    }
    return $values;
}

function toPlainArray($arr)
{
    $output = "['";
    foreach ($arr as $val) { $output .= $val."', '"; }
    return substr($output, 0, -3).']';
}
