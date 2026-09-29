<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$smart_choice_hidden_setup_items = [
    'smart choice client portal',
    'smart choice ai voice agent',
    'smart choice company links',
    'smart choice module settings index',
    'smart choice appointment settings',
    'smart daily productivity',
    'office theme',
    'office theme health',
    'customjs',
    'crm utilities & debug tools',
    'purchasing hub settings',
    'purchasing hub health check',
    'purchasing hub help guide',
    'mailwizz settings',
    'document management',
    'social_lead_funnel_settings',
    'crm file explorer manager settings',
    'smart choice links settings',
    'facebook leads integration',
    'smart choice admin settings',
    'smart choice embedded modules',
    'smart choice update center',
    'smart choice menu icons',
    'smart choice debu',
    'smart choice debug',
    'settings',
];

$smart_choice_hidden_setup_keys = [
    'smart_choice_client_portal',
    'smart_choice_ai_voice_agent',
    'smart_choice_company_links',
    'smart_choice_module_settings_index',
    'smart_choice_appointment_settings',
    'smart_daily_productivity',
    'office_theme',
    'office_theme_health',
    'customjs',
    'crm_utilities_debug_tools',
    'purchasing_hub_settings',
    'purchasing_hub_health_check',
    'purchasing_hub_help_guide',
    'mailwizz_settings',
    'document_management',
    'social_lead_funnel_settings',
    'crm_file_explorer_manager_settings',
    'smart_choice_links_settings',
    'facebook_leads_integration',
];

$smart_choice_should_hide_setup_item = static function ($item) use ($smart_choice_hidden_setup_items, $smart_choice_hidden_setup_keys) {
    $name = isset($item['name']) ? strtolower(trim((string) $item['name'])) : '';
    $slug = isset($item['slug']) ? strtolower(trim((string) $item['slug'])) : '';
    $href = isset($item['href']) ? trim((string) $item['href']) : '';
    $label = $name !== '' ? strtolower(trim((string) _l($name, '', false))) : '';

    if (in_array($name, $smart_choice_hidden_setup_keys, true) || in_array($slug, $smart_choice_hidden_setup_keys, true)) {
        return true;
    }

    foreach ($smart_choice_hidden_setup_items as $blocked) {
        if ($blocked !== '' && (($name !== '' && strpos($name, $blocked) !== false) || ($label !== '' && strpos($label, $blocked) !== false) || ($slug !== '' && strpos(str_replace('_', ' ', $slug), $blocked) !== false))) {
            return true;
        }
    }

    // Remove empty module placeholders that render as plain text and do not open a valid settings page.
    if (($href === '' || $href === '#') && empty($item['children']) && ($name !== '' || $slug !== '')) {
        return true;
    }

    return false;
};

if (isset($setup_menu) && is_array($setup_menu)) {
    foreach ($setup_menu as $key => $item) {
        if ($smart_choice_should_hide_setup_item($item)) {
            unset($setup_menu[$key]);
            continue;
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $childKey => $child) {
                if ($smart_choice_should_hide_setup_item($child)) {
                    unset($setup_menu[$key]['children'][$childKey]);
                }
            }
            $setup_menu[$key]['children'] = array_values($setup_menu[$key]['children']);
        }
    }
}
?>
<div id="setup-menu-wrapper"
    class="sidebar animated<?= $this->session->has_userdata('setup-menu-open')
    && $this->session->userdata('setup-menu-open') == true ? ' display-block' : ''; ?>">
    <ul class="nav metis-menu" id="setup-menu">
        <li class="sc-setup-profile-card">
            <a href="<?= admin_url('profile'); ?>" class="tw-flex tw-items-center tw-gap-x-3 tw-mx-3 tw-mb-3 tw-p-3 tw-rounded-lg tw-bg-white tw-border tw-border-solid tw-border-neutral-200">
                <span><?= staff_profile_image(get_staff_user_id(), ['img','img-responsive','staff-profile-image-small']); ?></span>
                <span class="tw-min-w-0">
                    <span class="tw-block tw-font-semibold tw-truncate"><?= e(get_staff_full_name()); ?></span>
                    <span class="tw-block tw-text-xs tw-text-neutral-500 tw-truncate" style="max-width:150px;"><?= e(get_staff()->email); ?></span>
                </span>
            </a>
        </li>
        <li class="sc-primary-setup-search">
            <div class="sc-setup-search-wrap">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input type="search" id="sc-setup-menu-search" class="form-control" placeholder="<?= e(_l('search')); ?>" autocomplete="off">
            </div>
        </li>
        <div
            class="tw-flex tw-items-center tw-justify-between tw-space-x-2 rtl:tw-space-x-reverse tw-pl-4 tw-pr-2.5 tw-py-3">

            <span class="text-left tw-font-semibold customizer-heading">
                <?= _l('setting_bar_heading'); ?>
            </span>
            <a
                class="close-customizer tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 hover:tw-bg-neutral-200 tw-p-0.5 hover:tw-rounded-md">
                <i class="fa fa-close fa-fw"></i>
            </a>
        </div>
        <?php
        $totalSetupMenuItems = 0;

foreach ((isset($setup_menu) && is_array($setup_menu)) ? $setup_menu : [] as $key => $item) {
    $children = isset($item['children']) && is_array($item['children']) ? $item['children'] : [];
    if (isset($item['collapse']) && count($children) === 0) {
        continue;
    }
    $totalSetupMenuItems++; ?>
        <li
            class="menu-item-<?= e($item['slug']); ?>">
            <a href="<?= count($children) > 0 ? '#' : $item['href']; ?>"
                aria-expanded="false">
                <i
                    class="<?= e(!empty($item['icon']) ? $item['icon'] : 'fa fa-cog'); ?> menu-icon"></i>
                <span class="menu-text">
                    <?= html_purify(_l($item['name'], '', false)); ?>
                </span>
                <?php if (count($children) > 0) { ?>
                <span class="fa arrow"></span>
                <?php } ?>
                <?php if (isset($item['badge'], $item['badge']['value']) && ! empty($item['badge'])) {?>
                <span
                    class="badge pull-right
               <?= isset($item['badge']['type']) && $item['badge']['type'] != '' ? "bg-{$item['badge']['type']}" : 'bg-info' ?>"
                    <?= (isset($item['badge']['type']) && $item['badge']['type'] == '')
                || isset($item['badge']['color']) ? "style='background-color: {$item['badge']['color']}'" : '' ?>>
                    <?= e($item['badge']['value']) ?>
                </span>
                <?php } ?>
            </a>
            <?php if (count($children) > 0) { ?>
            <ul class="nav nav-second-level collapse" aria-expanded="false">
                <?php foreach ($children as $submenu) { ?>
                <li
                    class="sub-menu-item-<?= e($submenu['slug']); ?>">
                    <a
                        href="<?= e($submenu['href']); ?>">
                        <i class="<?= e(!empty($submenu['icon']) ? $submenu['icon'] : 'fa fa-angle-right'); ?> menu-icon"></i>
                        <span class="sub-menu-text">
                            <?= e(_l($submenu['name'], '', false)); ?>
                        </span>
                    </a>
                    <?php if (isset($submenu['badge'], $submenu['badge']['value']) && ! empty($submenu['badge'])) {?>
                    <span
                        class="badge pull-right mright5
                    <?= isset($submenu['badge']['type']) && $submenu['badge']['type'] != '' ? "bg-{$submenu['badge']['type']}" : 'bg-info' ?>"
                        <?= (isset($submenu['badge']['type']) && $submenu['badge']['type'] == '')
                || isset($submenu['badge']['color']) ? "style='background-color: {$submenu['badge']['color']}'" : '' ?>>
                        <?= e($submenu['badge']['value']) ?>
                    </span>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </li>
        <?php hooks()->do_action('after_render_single_setup_menu', $item); ?>
        <?php } ?>
        <?php if (get_option('show_help_on_setup_menu') == 1 && is_admin()) {
            $totalSetupMenuItems++; ?>
        <li>
            <a href="<?= hooks()->apply_filters('help_menu_item_link', 'https://help.perfexcrm.com'); ?>"
                target="_blank">
                <?= hooks()->apply_filters('help_menu_item_text', _l('setup_help')); ?>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php $this->app->set_setup_menu_visibility($totalSetupMenuItems); ?>