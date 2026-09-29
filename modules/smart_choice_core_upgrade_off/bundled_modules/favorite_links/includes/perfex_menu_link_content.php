<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
if (!function_exists('favorite_links_smartchoice_normalize_href')) {
    function favorite_links_smartchoice_normalize_href($href)
    {
        $href = trim((string) $href);
        $href = preg_replace('/\s+/', '', $href);

        if ($href === '') {
            return admin_url();
        }

        if (stripos($href, 'ttps://') === 0) {
            $href = 'h' . $href;
        }

        $href = str_replace('crm.justsmartchoice.com//', 'crm.justsmartchoice.com/', $href);
        $href = str_replace('/adminmodules', '/admin/modules', $href);
        $href = str_replace('adminmodules', 'admin/modules', $href);

        if (stripos($href, 'http://') === 0 || stripos($href, 'https://') === 0 || stripos($href, '#') === 0) {
            return $href;
        }

        $href = ltrim($href, '/');

        if (stripos($href, 'admin/') === 0) {
            return admin_url(substr($href, 6));
        }

        return site_url($href);
    }
}

$menu_links = get_instance()->db->select('*')
    ->from(db_prefix() . 'perfex_menu_links')
    ->order_by('pml_order', 'ASC')
    ->order_by('pml_title', 'ASC')
    ->get()
    ->result();

$short_keys = [];
?>

<div id="perfex_menu_link">
    <ul class="nav navbar-nav visible-md visible-lg favorite-links-nav">
        <li class="icon tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5 favorite-links-dropdown" title="<?php echo html_escape(_l('perfex_menu_link_text')); ?>" data-toggle="tooltip" data-placement="bottom">
            <a href="#" class="favorite-links-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding-right: 6px!important;">
                <span><i class="far fa-star fa-lg"></i></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base favorite-links-dropdown-menu" style="width: 280px!important;">
                <?php if (!empty($menu_links)) { ?>
                    <?php foreach ($menu_links as $menu_link) { ?>
                        <?php
                        $short_key_id = '';
                        if (get_option('favorite_links_enable_hotkeys') !== '0' && !empty($menu_link->pml_hotkeys) && !empty($menu_link->pml_hotkey_numbers)) {
                            $short_key_id = $menu_link->pml_hotkeys . '_' . $menu_link->pml_hotkey_numbers;
                            $short_keys[(string) $menu_link->pml_hotkeys][(string) $menu_link->pml_hotkey_numbers] = (string) $menu_link->pml_hotkey_numbers;
                        }

                        $target = !empty($menu_link->pml_target) ? $menu_link->pml_target : (get_option('favorite_links_default_target') ?: '_self');
                        if (get_option('favorite_links_open_new_tab') === '1') {
                            $target = '_blank';
                        }
                        $rel = !empty($menu_link->pml_rels) ? $menu_link->pml_rels : 'nofollow';
                        if ($target === '_blank' && strpos($rel, 'noopener') === false) {
                            $rel .= ' noopener noreferrer';
                        }
                        ?>

                        <li class="perfex-menu-item favorite-links-menu-item">
                            <a href="<?php echo html_escape(favorite_links_smartchoice_normalize_href($menu_link->pml_link)); ?>" id="perfex_link_<?php echo html_escape($short_key_id); ?>" target="<?php echo html_escape($target); ?>" rel="<?php echo html_escape(trim($rel)); ?>">
                                <?php echo html_escape($menu_link->pml_title); ?>
                                <?php if (!empty($short_key_id)) { ?>
                                    <span class="perfex-menu-hotkey"><?php echo html_escape($menu_link->pml_hotkeys . '+' . $menu_link->pml_hotkey_numbers); ?></span>
                                <?php } ?>
                            </a>

                            <?php if (is_admin()) : ?>
                                <div class="perfex-menu-actions">
                                    <a href="#" onclick="perfex_menu_link_menu_modal(<?php echo (int) $menu_link->id; ?>); return false;" title="<?php echo html_escape(_l('edit')); ?>">
                                        <span class="fa fa-edit fa-lg"></span>
                                    </a>
                                    <a href="<?php echo admin_url('favorite_links/delete/' . (int) $menu_link->id); ?>" class="_delete" title="<?php echo html_escape(_l('delete')); ?>">
                                        <span class="fa fa-remove fa-lg"></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="favorite-links-add-new" style="border-top: 1px solid #c0ccda">
                    <a href="#" onclick="perfex_menu_link_menu_modal(0); return false;"><span class="fa fa-plus"></span> <?php echo html_escape(_l('pml_add_new_link')); ?></a>
                </li>
            </ul>
        </li>
    </ul>
</div>

<div class="modal fade" id="perfex_menu_link_modal" tabindex="-1" role="dialog" aria-labelledby="favoriteLinksModalLabel"></div>

<?php if (!empty($short_keys)) { ?>
<script>
(function($){
    "use strict";
    $(document).on('keydown', function(e) {
        <?php foreach ($short_keys as $short_key => $short_numbers) { ?>
        if ((e.key || '').toUpperCase() === '<?php echo html_escape($short_key); ?>') {
            $(document).one('keydown.favoriteLinksShortcut', function(e2) {
                <?php foreach ($short_numbers as $short_number) { ?>
                if ((e2.key || '') === '<?php echo html_escape($short_number); ?>') {
                    var link = $('#perfex_link_<?php echo html_escape($short_key . '_' . $short_number); ?>');
                    if (link.length) { link[0].click(); }
                }
                <?php } ?>
            });
            setTimeout(function(){ $(document).off('keydown.favoriteLinksShortcut'); }, 1000);
        }
        <?php } ?>
    });
})(jQuery);
</script>
<?php } ?>
