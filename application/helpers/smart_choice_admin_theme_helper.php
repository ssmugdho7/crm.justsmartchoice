<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smartchoice_admin_themes()
{
    // Trusted modules can register a new local stylesheet; settings store only its key.
    return hooks()->apply_filters('smartchoice_admin_themes', [
        'emerald' => ['label' => 'sc_admin_theme_emerald', 'stylesheet' => 'assets/css/admin-themes/emerald.css', 'modern' => true],
        'classic' => ['label' => 'sc_admin_theme_classic', 'stylesheet' => null, 'modern' => false],
    ]);
}

function smartchoice_admin_theme()
{
    $themes = smartchoice_admin_themes();
    $saved = get_option('smartchoice_admin_theme');
    return is_string($saved) && isset($themes[$saved]) ? $saved : 'emerald';
}

function smartchoice_admin_theme_is_modern()
{
    return !empty(smartchoice_admin_themes()[smartchoice_admin_theme()]['modern']);
}

function smartchoice_admin_theme_body($classes)
{
    $classes[] = 'sc-theme-' . smartchoice_admin_theme();
    return $classes;
}

function smartchoice_admin_theme_menu()
{
    if (is_admin()) {
        get_instance()->app_menu->add_setup_menu_item('theme-style', [
            'href' => admin_url('theme_style'), 'name' => _l('theme_style'), 'position' => 65,
        ]);
    }
}
