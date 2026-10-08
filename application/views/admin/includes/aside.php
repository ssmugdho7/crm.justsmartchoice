<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!isset($sidebar_menu) || !is_array($sidebar_menu)) {
    $sidebar_menu = [];
    $CI = &get_instance();
    if (isset($CI->app_menu) && is_object($CI->app_menu) && method_exists($CI->app_menu, 'get_sidebar_menu_items')) {
        $sidebar_menu = $CI->app_menu->get_sidebar_menu_items();
    }
    if (!is_array($sidebar_menu)) {
        $sidebar_menu = [];
    }
}
?>

<aside id="menu" class="sidebar">
    <?php $isSidebarDark = function_exists('is_admin_sidebar_background_light') ?
            is_admin_sidebar_background_light() :
            false; ?>
    <div class="dropdown sidebar-user-profile tw-mt-0 tw-mx-1.5 ">
        <a href="#"
            class="dropdown-toggle profile -tw-mt-1 tw-font-medium tw-border tw-border-solid tw-rounded-lg tw-bg-white tw-py-2 tw-px-2.5 tw-block tw-shadow-xs <?= $isSidebarDark ? 'tw-text-white tw-border-white/10 hover:tw-border-white/30 focus:tw-border-white/30 hover:tw-text-white focus:tw-text-white hover:tw-bg-neutral-900/10 focus:tw-bg-neutral-900/10' : 'tw-border-neutral-300 tw-text-neutral-700 hover:tw-text-neutral-800 focus:tw-text-neutral-800 hover:tw-bg-neutral-900/5 focus:tw-bg-neutral-900/5'; ?>"
            data-toggle="dropdown" aria-expanded="false">
            <span class="tw-inline-flex tw-items-center tw-gap-x-3 tw-pt-0.5">
                <span class="sc-profile-avatar"><?= staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-small']); ?></span>
                <span>
                    <span
                        class="tw-truncate tw-block tw-w-[140px] tw-font-semibold"><?= get_staff_full_name(); ?></span>
                    <span
                        class="tw-font-normal tw-truncate tw-block tw-w-[140px] tw-text-xs <?= $isSidebarDark ? 'tw-text-neutral-300' : 'tw-text-neutral-500' ?>">
                        <?= e(get_staff()->email); ?>
                    </span>
                </span>
            </span>
        </a>
        <ul class="dropdown-menu tw-w-full">
            <li class="header-my-profile"><a
                    href="<?= admin_url('profile'); ?>"><?= _l('nav_my_profile'); ?></a>
            </li>
            <li class="header-my-timesheets"><a
                    href="<?= admin_url('staff/timesheets'); ?>"><?= _l('my_timesheets'); ?></a>
            </li>
            <li class="header-edit-profile"><a
                    href="<?= admin_url('staff/edit_profile'); ?>"><?= _l('nav_edit_profile'); ?>
                </a>
            </li>
            <?php if (! is_language_disabled()) { ?>
            <li class="dropdown-submenu pull-left header-languages">
                <a href="#"
                    tabindex="-1"><?= _l('language'); ?></a>
                <ul class="dropdown-menu dropdown-menu">
                    <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                    <li
                        class="<?= $current_user->default_language == $user_lang ? 'active' : ''; ?>">
                        <a
                            href="<?= admin_url('staff/change_language/' . $user_lang); ?>">
                            <?= e(ucfirst($user_lang)); ?>
                        </a>
                        <?php } ?>
                </ul>
            </li>
            <?php } ?>
            <li class="header-logout">
                <a href="#"
                    onclick="logout(); return false;"><?= _l('nav_logout'); ?></a>
            </li>
        </ul>
    </div>
    <ul class="nav metis-menu tw-mt-[15px]" id="side-menu">

        <?php
 hooks()->do_action('before_render_aside_menu');
?>
        <?php foreach ($sidebar_menu as $key => $item) {
            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {
                continue;
            } ?>
        <li class="menu-item-<?= e($item['slug']); ?>"
            <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
            <a href="<?= count($item['children']) > 0 ? '#' : $item['href']; ?>"
                aria-expanded="false"
                <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                <i
                    class="<?= e(!empty($item['icon']) ? $item['icon'] : 'fa fa-circle-o'); ?> menu-icon"></i>
                <span class="menu-text">
                    <?= e(_l($item['name'], '', false)); ?>
                </span>
                <?php if (count($item['children']) > 0) { ?>
                <span class="fa arrow pleft5 fa-sm tw-mt-1.5"></span>
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
            <?php if (count($item['children']) > 0) { ?>
            <ul class="nav nav-second-level collapse" aria-expanded="false">
                <?php foreach ($item['children'] as $submenu) { ?>
                <li class="sub-menu-item-<?= e($submenu['slug']); ?>"
                    <?= _attributes_to_string($submenu['li_attributes'] ?? []); ?>>
                    <a href="<?= e($submenu['href']); ?>"
                        <?= _attributes_to_string($submenu['href_attributes'] ?? []); ?>>
                        <i class="<?= e($submenu['slug'] === 'payments' ? 'fa-solid fa-credit-card' : (!empty($submenu['icon']) ? $submenu['icon'] : 'fa fa-angle-right')); ?> menu-icon" aria-hidden="true"></i>
                        <span class="sub-menu-text">
                            <?= _l($submenu['name'], '', false); ?>
                        </span>
                    </a>
                    <?php if (isset($submenu['badge'], $submenu['badge']['value']) && ! empty($submenu['badge'])) {?>
                    <span
                        class="badge pull-right
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
        <?php hooks()->do_action('after_render_single_aside_menu', $item); ?>
        <?php
        } ?>
        <?php if ($this->app->show_setup_menu() == true && (is_staff_member() || is_admin())) { ?>
        <li<?php if (get_option('show_setup_menu_item_only_on_hover') == 1) {
            echo ' style="display:none;"';
        } ?> id="setup-menu-item">
            <a href="#" class="open-customizer"><i class="fa fa-cog menu-icon"></i>
                <span class="menu-text">
                    <?= _l('setting_bar_heading'); ?>
                    <?php
               if ($modulesNeedsUpgrade = $this->app_modules->number_of_modules_that_require_database_upgrade()) {
                   echo '<span class="badge menu-badge !tw-bg-warning-600">' . $modulesNeedsUpgrade . '</span>';
               }
            ?>
                </span>
            </a>
            <?php } ?>
            </li>
            <?php hooks()->do_action('after_render_aside_menu'); ?>
            <?php $this->load->view('admin/projects/pinned'); ?>
    </ul>
</aside>
