<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Whatsbot sidebar + hooks
 * Fixes:
 * - Removes duplicate bootstrap code
 * - Removes hard dependency on $CI->app_cache (not available in Mods controller in your Perfex 3.4.0 context)
 * - Ensures $cache_data is always defined and safe in PHP 8+
 */

$cache_data = [];

/*
 * Inject sidebar menu and links for whatsbot module
 */
hooks()->add_action('admin_init', function () use ($cache_data) {

    // Keep vendor license gate behavior but avoid undefined variable warnings
    // NOTE: original logic had: if(!isset($cache_data) && $cache_data != 'HASH') return;
    // That condition was contradictory. This preserves the intent (gate access unless cache_data equals hash).
    $expected_hash = "9c711db91e21a84d6d5433374537bb2e75735e33fd35c81af0e55985df0d6d9f48d46a2523f09031b43756526e1a949971f192dfac6c7069137994d94a2048d2e11f5606438911f79430fd33d1c65db8bf4f126f2564818dedef9d158653525a032e6d8e69b825175362dc3a288553335190d073f490ad8d8424a80085266e981c6907a832d3587f195603dcd3dff5740fd2c172a7940f802e47fc2f04e93a469d544d27a3fab32c65efedde08cf63751eef4da7";

    // If $cache_data isn't a scalar string equal to the expected hash, stop.
    // Because $cache_data is an array by default, this will return (module will not inject menu).
    // If you WANT the menu to show, comment out the next 2 lines.
    if (!is_string($cache_data) || $cache_data !== $expected_hash) {
        return;
    }

    if (get_instance()->app_modules->is_active('whatsbot')) {
        if (staff_can('connect', 'wtc_connect_account') || staff_can('view', 'wtc_message_bot') || staff_can('view', 'wtc_template_bot') || staff_can('view', 'wtc_template') || staff_can('view', 'wtc_campaign') || staff_can('view', 'wtc_chat') || staff_can('view', 'wtc_log_activity') || staff_can('view', 'wtc_settings') || staff_can('view', 'wtc_canned_reply') || staff_can('view', 'wtc_ai_prompts') || staff_can('view_own', 'wtc_canned_reply') || staff_can('view_own', 'wtc_ai_prompts') || staff_can('send', 'wtc_bulk_campaign') || staff_can('view', 'wtc_bot_flow') || staff_can('view', 'wtc_pa')) {
            get_instance()->app_menu->add_sidebar_menu_item('whatsbot', [
                'slug'     => 'whatsbot',
                'name'     => _l('whatsbot'),
                'icon'     => 'fa-brands fa-whatsapp',
                'href'     => '#',
                'position' => 20,
            ]);
        }

        if (staff_can('connect', 'wtc_connect_account')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'connect_account',
                'name'     => _l('connect_account'),
                'icon'     => 'fa-solid fa-link',
                'href'     => admin_url(WHATSBOT_MODULE . '/connect'),
                'position' => 1,
            ]);
        }

        if (staff_can('view', 'wtc_message_bot')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'whatsapp_message_bot',
                'name'     => _l('message_bot'),
                'icon'     => 'fa-solid fa-share',
                'href'     => admin_url(WHATSBOT_MODULE . '/bots'),
                'position' => 2,
            ]);
        }

        if (staff_can('view', 'wtc_template_bot')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'whatsapp_template_bot',
                'name'     => _l('template_bot'),
                'icon'     => 'fa-solid fa-robot',
                'href'     => admin_url(WHATSBOT_MODULE . '/bots?group=template'),
                'position' => 3,
            ]);
        }

        if (staff_can('view', 'wtc_pa')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'whatsbot_personal_assistant',
                'name'     => _l('ai_assistant'),
                'icon'     => 'fa-solid fa-user-secret',
                'href'     => admin_url(WHATSBOT_MODULE . '/personal_assistants'),
                'position' => 3,
            ]);
        }

        if (staff_can('view', 'wtc_template')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'whatsbot_templates',
                'name'     => _l('templates'),
                'icon'     => 'fa-solid fa-scroll',
                'href'     => admin_url(WHATSBOT_MODULE . '/templates'),
                'position' => 4,
            ]);
        }

        if (staff_can('view', 'wtc_template')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'flow_templates',
                'name'     => _l('flow_templates'),
                'icon'     => 'fa-diagram-project fa-solid',
                'href'     => admin_url(WHATSBOT_MODULE . '/flows'),
                'position' => 4,
            ]);
        }

        if (staff_can('view', 'wtc_template')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'marketing_automation',
                'name'     => _l('marketing_automation'),
                'icon'     => 'fa-users fa-solid',
                'href'     => admin_url(WHATSBOT_MODULE . '/marketing_automation'),
                'position' => 4,
            ]);
        }

        if (staff_can('view', 'wtc_campaign')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'campaigns',
                'name'     => _l('campaigns'),
                'icon'     => 'fa-solid fa-bullhorn',
                'href'     => admin_url(WHATSBOT_MODULE . '/campaigns'),
                'position' => 5,
            ]);
        }

        if (staff_can('send', 'wtc_bulk_campaign')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'bulk_campaigns',
                'name'     => _l('csv_campaign'),
                'icon'     => 'fa-solid fa-file-import',
                'href'     => admin_url(WHATSBOT_MODULE . '/bulk_campaigns'),
                'position' => 6,
            ]);
        }

        if (staff_can('view', 'wtc_log_activity')) {
            get_instance()->app_menu->add_sidebar_children_item('whatsbot', [
                'slug'     => 'whtasbot_activity_log',
                'name'     => _l('activity_log'),
                'icon'     => 'fa-brands fa-autoprefixer',
                'href'     => admin_url('whatsbot/activity_log'),
                'position' => 7,
            ]);
        }

        if (staff_can('view', 'wtc_chat') || staff_can('view_own', 'wtc_chat')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'whatsapp_chat_integration',
                'name'     => _l('chat'),
                'icon'     => 'fa-regular fa-comment-dots',
                'href'     => admin_url(WHATSBOT_MODULE . '/chat'),
                'position' => 8,
            ]);
        }

        if (staff_can('view', 'wtc_canned_reply') || staff_can('view_own', 'wtc_canned_reply')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'canned_reply',
                'name'     => _l('canned_reply_menu'),
                'icon'     => 'fa-regular fa-pen-to-square',
                'href'     => admin_url(WHATSBOT_MODULE . '/canned_reply'),
                'position' => 9,
            ]);
        }

        if (staff_can('view', 'wtc_ai_prompts') || staff_can('view_own', 'wtc_ai_prompts')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'ai_prompts',
                'name'     => _l('ai_prompts'),
                'icon'     => 'fa-solid fa-pen-nib',
                'href'     => admin_url(WHATSBOT_MODULE . '/ai_prompts'),
                'position' => 10,
            ]);
        }

        if (staff_can('view', 'wtc_settings')) {
            get_instance()->app_menu->add_sidebar_children_item('whatsbot', [
                'slug'     => 'whtasbot_settings',
                'name'     => _l('settings'),
                'icon'     => 'fa-solid fa-gears',
                'href'     => admin_url('settings?group=whatsbot'),
                'position' => 11,
            ]);
        }

        if (staff_can('view', 'wtc_settings')) {
            if (get_app_version() >= '3.2.0') {
                get_instance()->app->add_settings_section_child('other', 'whatsbot', [
                    'name'     => _l('whatsbot'),
                    'view'     => 'whatsbot/settings/central_settings',
                    'icon'     => 'fa-brands fa-whatsapp',
                    'position' => 1,
                ]);
            } else {
                get_instance()->app_tabs->add_settings_tab('whatsbot', [
                    'name'     => _l('whatsbot'),
                    'view'     => 'whatsbot/settings/central_settings',
                    'icon'     => 'fa-brands fa-whatsapp',
                    'position' => 6,
                ]);
            }
        }

        if (staff_can('view', 'wtc_bot_flow')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug'     => 'bot_flow',
                'name'     => _l('bot_flow_builder'),
                'icon'     => 'fa-solid fa-arrow-trend-up',
                'href'     => admin_url(WHATSBOT_MODULE . '/bot_flow'),
                'position' => 3,
            ]);
        }

        get_instance()->app_tabs->add_project_tab('flow_response', [
            'name'     => _l('flow_response'),
            'icon'     => 'fa-solid fa-bars-staggered',
            'view'     => WHATSBOT_MODULE . '/flow_response/admin_project_flow_response',
            'position' => 70,
        ]);
    }
});

hooks()->add_action('module_deactivated', function ($module_name) {
    if (WHATSBOT_MODULE == $module_name['system_name']) {
        $url = basename(get_instance()->app_modules->get(WHATSBOT_MODULE)['headers']['uri']) . '-' . trim(preg_replace(['#/admin.*#', '#https?://#', '/[^a-zA-Z0-9]+/'], ['', '', '-'], current_full_url()), '-');
        write_file(TEMP_FOLDER . $url . '.lic', '');
        echo '<script>
            var _css = "' . basename(get_instance()->app_modules->get(WHATSBOT_MODULE)['headers']['uri']) . '.lic"' . ';
            sessionStorage.setItem(_css, "");
        </script>';
    }
});

// add flow response tab in project view as a client
hooks()->add_action('after_customers_area_project_overview_tab', 'add_flow_response_tab_in_client_view');
function add_flow_response_tab_in_client_view($project)
{ ?>
    <li role="presentation" class="project_tab_activity">
        <a data-group="project_activity"
           href="<?= site_url('clients/project/' . $project->id . '?group=project_flow_response'); ?>"
           role="tab">
            <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
            <?= _l('flow_response'); ?>
        </a>
    </li>
<?php }

// add flow response tab in ticket view (admin)
hooks()->add_action('after_admin_single_ticket_tab_menu_last_item', function ($ticket) { ?>
    <li role="presentation">
        <a href="#flow_response"
           aria-controls="flow_response" role="tab" data-toggle="tab">
            <i class="fa-solid fa-bars-staggered"></i>
            <?= _l('flow_response'); ?>
        </a>
    </li>
<?php
});

hooks()->add_action('after_admin_single_ticket_tab_menu_last_content', function ($tickets) {
    $data['tickets'] = $tickets;
    get_instance()->load->view('whatsbot/flow_response/admin_ticket_flow_response', $data);
});