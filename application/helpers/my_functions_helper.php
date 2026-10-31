<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/smart_choice_numbering_helper.php';
require_once __DIR__ . '/smart_choice_admin_theme_helper.php';
hooks()->add_filter('admin_body_class', 'smartchoice_admin_theme_body');
hooks()->add_action('admin_init', 'smartchoice_admin_theme_menu');

// Perfex already registers custom.css. Render it once, after the other head assets.
hooks()->add_action('app_admin_assets_added', 'smartchoice_prepare_modern_theme');
hooks()->add_action('app_admin_head', 'smartchoice_enqueue_modern_theme');
hooks()->add_action('app_admin_footer', 'smartchoice_enqueue_modern_scripts');
hooks()->add_action('after_body_start', 'smartchoice_restore_sidebar');

function smartchoice_prepare_modern_theme()
{
    get_instance()->app_css->remove('custom-css', 'admin');
}

function smartchoice_theme_asset_url($path)
{
    // Stable URLs keep browser caching effective, while changes invalidate the cache.
    return base_url($path . '?v=' . filemtime(FCPATH . $path));
}

function smartchoice_enqueue_modern_theme()
{
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;
    echo '<link id="custom-css" rel="stylesheet" href="' . html_escape(smartchoice_theme_asset_url('assets/css/custom.css')) . '">' . PHP_EOL;
    $theme = smartchoice_admin_themes()[smartchoice_admin_theme()];
    $path = $theme['stylesheet'] ?? null;
    if (is_string($path) && preg_match('~^assets/css/admin-themes/[a-z0-9_-]+\.css$~D', $path) && is_file(FCPATH . $path)) {
        echo '<link id="sc-admin-theme-css" rel="stylesheet" href="' . html_escape(smartchoice_theme_asset_url($path)) . '">' . PHP_EOL;
    }

}

function smartchoice_enqueue_modern_scripts()
{
    echo '<script src="' . html_escape(smartchoice_theme_asset_url('assets/js/sc-document-prefixes.js')) . '"></script>' . PHP_EOL;
    echo '<script id="smartchoice-custom-js" src="' . html_escape(smartchoice_theme_asset_url('assets/js/custom.js')) . '"></script>' . PHP_EOL;
}

function smartchoice_restore_sidebar()
{
    // Runs immediately after the admin body opens, before the navigation is painted.
    echo <<<'HTML'
<script id="smartchoice-sidebar-restore">
(function () {
    var body = document.body;
    if (!body.classList.contains('admin') || body.classList.contains('mobile') || window.innerWidth < 769) return;
    try {
        if (localStorage.getItem('smartchoice_sidebar_collapsed') === 'true') {
            body.classList.add('hide-sidebar');
            body.classList.remove('show-sidebar');
        }
    } catch (ignored) { /* Storage may be disabled by browser privacy settings. */ }
})();
</script>
HTML;
}
