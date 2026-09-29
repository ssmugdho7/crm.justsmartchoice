<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->db->where('is_active', 1);
$CI->db->order_by('position', 'asc');
$CI->db->order_by('title', 'asc');
$links = $CI->db->get(SMART_CHOICE_LINKS_TABLE)->result();
$visible = [];
foreach ($links as $link) {
    if (smart_choice_links_user_can_view($link)) {
        $visible[] = $link;
    }
}
$title = get_option('smart_choice_links_title') ?: 'Smart Choice Links';
$label = get_option('smart_choice_links_button_label') ?: 'Links';
$footer = get_option('smart_choice_links_footer_note') ?: 'Smart Choice Contractors USA / Harold Cabrera';
?>
<div id="smart-choice-links-source" style="display:none;">
    <li class="icon smart-choice-links-nav tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5" title="<?php echo html_escape($title); ?>" data-toggle="tooltip" data-placement="bottom">
        <a href="#" data-toggle="dropdown" aria-expanded="false" class="smart-choice-links-trigger">
            <span><i class="far fa-star fa-lg"></i></span>
            <span class="scl-label"><?php echo html_escape($label); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right animated fadeIn smart-choice-links-dropdown">
            <li class="scl-dropdown-header">
                <strong><?php echo html_escape($title); ?></strong>
                <?php if (is_admin()) { ?>
                    <a href="<?php echo admin_url('smart_choice_links'); ?>" class="pull-right"><i class="fa fa-cog"></i></a>
                <?php } ?>
            </li>

            <?php if (empty($visible)) { ?>
                <li><a href="<?php echo admin_url('smart_choice_links'); ?>">No links yet. Add your first shortcut.</a></li>
            <?php } else { ?>
                <?php foreach ($visible as $link) { ?>
                    <?php
                    $url = smart_choice_links_normalize_url($link->url);
                    $target = get_option('smart_choice_links_open_new_tab') === '1' ? '_blank' : $link->target;
                    $textColor = !empty($link->text_color) ? $link->text_color : '';
                    $shadow = !empty($link->text_shadow) ? 'text-shadow:0 1px 2px rgba(0,0,0,.35);' : '';
                    $titleStyle = trim(($textColor ? 'color:' . html_escape($textColor) . ';' : '') . $shadow);
                    ?>
                    <li class="scl-link-item">
                        <a href="<?php echo html_escape($url); ?>" target="<?php echo html_escape($target); ?>" rel="<?php echo html_escape($link->rel); ?>">
                            <span class="scl-link-main-line">
                                <span class="scl-link-title-wrap">
                                    <i class="<?php echo html_escape($link->icon ?: 'fa fa-link'); ?>"></i>
                                    <span style="<?php echo $titleStyle; ?>"><?php echo html_escape($link->title); ?></span>
                                </span>
                                <?php if (!empty($link->category)) { ?>
                                    <small class="scl-link-category-inline"><?php echo html_escape($link->category); ?></small>
                                <?php } ?>
                            </span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>

            <li class="scl-footer"><?php echo html_escape($footer); ?></li>
        </ul>
    </li>
</div>
