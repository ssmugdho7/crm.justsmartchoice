<?php

defined('BASEPATH') || exit('No direct script access allowed');

/*
    Module Name: BannerCraft
    Description: Robust tool to effortlessly organize your banners, enhancing the visual appeal and effectiveness of your CRM
    Version: 1.0.2
    Requires at least: 3.0.*
    Module URI: https://codecanyon.net/item/bannercraft-dynamic-banner-management-module-for-perfex-crm/51504146
    Author: <a href="https://codecanyon.net/user/corbitaltech" target="_blank">Corbital Technologies<a/>
*/

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
 */
define('BANNER_MODULE', 'banner');

define('BANNER_MODULE_ATTACHMENTS_FOLDER', FCPATH.'/uploads/banner');

// require_once __DIR__.'/vendor/autoload.php';

/*
 * Register activation module hook
 */
register_activation_hook(BANNER_MODULE, function () {
    require_once __DIR__.'/install.php';
});

/*
 * Register deactivation module hook
 */
register_deactivation_hook(BANNER_MODULE, function () {
    $my_files_list = [
        VIEWPATH.'themes/perfex/views/my_home.php',
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }
});

/*
 * Register language files, must be registered if the module is using languages
 */
register_language_files(BANNER_MODULE, [BANNER_MODULE]);

/*
 * Load module helper file
 */
get_instance()->load->helper(BANNER_MODULE.'/banner');

require_once __DIR__.'/includes/assets.php';
require_once __DIR__.'/includes/staff_permissions.php';
require_once __DIR__.'/includes/sidebar_menu_links.php';

hooks()->add_filter('get_upload_path_by_type', function ($path, $type) {
    switch ($type) {
        case 'banner':
            $path = BANNER_MODULE_ATTACHMENTS_FOLDER;
            break;

        default:
            $path = $path;
            break;
    }

    return $path;
}, 0, 2);

// Removed license verification functions
// \modules\banner\core\Apiinit::ease_of_mind(BANNER_MODULE);
// \modules\banner\core\Apiinit::the_da_vinci_code(BANNER_MODULE);

require_once __DIR__ . '/install.php';
get_instance()->config->load(BANNER_MODULE . '/config');

// Removed cache and header/footer verification logic
// $cache = json_decode(base64_decode(config_item('get_footer')));
// $cache_data = "";
// foreach ($cache as $capture) {
//     $cache_data .= hash("sha1",preg_replace('/\s+/', '', file_get_contents(__DIR__.$capture)));
// }

// $tmp = tmpfile ();
// $tmpf = stream_get_meta_data ( $tmp )['uri'];
// fwrite ( $tmp, "<?php " . base64_decode(config_item("get_header")) . " ?
// $ret = include_once ($tmpf);
// fclose ( $tmp );

function bannerContent($allowArea, $value = '')
{
    $details = getBannerDetails($allowArea);
    if (!empty($details)) {
        return renderBanner($details);
    }
}

hooks()->add_action('before_start_render_dashboard_content', function () {
    $content = bannerContent('admin_area');
    echo $content;
});

hooks()->add_action('display_banner_for_client_area', function () {
    $content = '<div class="row">';
    $content .= bannerContent('clients_area');
    $content .= '</div>';
    echo $content;
});

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */


// Smart Choice Standard Permission Registration
if (!function_exists('banner_smart_choice_standard_permissions')) {
    function banner_smart_choice_standard_permissions()
    {
        if (function_exists('register_staff_capabilities')) {
            register_staff_capabilities('banner', [
                'capabilities' => [
                    'view_own' => _l('permission_view_own'),
                    'view'     => _l('permission_view'),
                    'create'   => _l('permission_create'),
                    'edit'     => _l('permission_edit'),
                    'delete'   => _l('permission_delete'),
                ],
            ], 'Banner');
        }
    }
}
hooks()->add_action('admin_init', 'banner_smart_choice_standard_permissions');
