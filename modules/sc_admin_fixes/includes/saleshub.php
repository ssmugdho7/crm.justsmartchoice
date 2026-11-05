<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_action('app_admin_head','scaf_saleshub_native_asset_replace',5);
function scaf_saleshub_native_asset_replace() {
    $CI=&get_instance();if(!in_array(strtolower($CI->router->fetch_class()),['sales_center','deals','commission'],true))return;
    // The domain bundle retains the existing Sales Center behavior, changing only asynchronous confirmation.
    hooks()->remove_action('app_admin_footer','sales_center_admin_footer');
}
