<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('products_smartchoice_slug_203')) {
    function products_smartchoice_slug_203($value)
    {
        $value = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $value), '-'));
        return $value !== '' ? $value : 'product';
    }
}

if (!function_exists('products_smartchoice_catalog_seed_203')) {
    function products_smartchoice_catalog_seed_203()
    {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'product_master')) {
            return true;
        }

        add_option('products_appointment_booking_url', site_url('appointments'));

        $defaultImage = 'sc-default-service-1.jpg';
        $products = $CI->db->get(db_prefix() . 'product_master')->result();
        foreach ($products as $product) {
            $slug = products_smartchoice_slug_203($product->product_name);
            $mainImage = 'sc-' . $slug . '.jpg';
            $mainPath = module_dir_path('products', 'uploads/' . $mainImage);
            if (!file_exists($mainPath)) {
                $galleryPath = module_dir_path('products', 'uploads/service-gallery/' . $slug . '/' . $slug . '-1.jpg');
                if (file_exists($galleryPath)) {
                    @copy($galleryPath, $mainPath);
                }
            }
            if (file_exists($mainPath)) {
                $CI->db->where('id', $product->id)->update(db_prefix() . 'product_master', ['product_image' => $mainImage]);
            } else {
                $CI->db->where('id', $product->id)->update(db_prefix() . 'product_master', ['product_image' => $defaultImage]);
            }
        }

        update_option('smartchoice_products_catalog_upgrade_203', '1');
        return true;
    }
}

class Migration_Version_203 extends App_module_migration
{
    public function up()
    {
        return products_smartchoice_catalog_seed_203();
    }

    public function down()
    {
        return true;
    }
}
