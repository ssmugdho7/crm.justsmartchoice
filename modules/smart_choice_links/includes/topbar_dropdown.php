<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->db->where('is_active', 1);
$CI->db->order_by('position', 'asc');
$CI->db->order_by('title', 'asc');
$links = $CI->db->get(SMART_CHOICE_LINKS_TABLE)->result();

$left = [];
$right = [];

foreach ($links as $link) {
    if (!smart_choice_links_user_can_view($link)) {
        continue;
    }

    $placement = isset($link->placement) ? $link->placement : 'both';

    if ($placement === 'left') {
        $left[] = $link;
        continue;
    }

    if ($placement === 'right') {
        $right[] = $link;
        continue;
    }

    /* Legacy records used "both", which duplicated every link in both star
     * panels. Keep each link in one panel only. Setup/settings/module links go
     * to the right star; all other administrative links go to the left star. */
    $haystack = strtolower((string) ($link->title ?? '') . ' ' . (string) ($link->category ?? '') . ' ' . (string) ($link->url ?? ''));
    $isSetupLink = strpos($haystack, 'setup') !== false
        || strpos($haystack, 'setting') !== false
        || strpos($haystack, '/admin/modules') !== false
        || strpos($haystack, '/admin/roles') !== false
        || strpos($haystack, '/admin/staff') !== false
        || strpos($haystack, '/admin/custom_fields') !== false
        || strpos($haystack, '/admin/email_templates') !== false;

    if ($isSetupLink) {
        $right[] = $link;
    } else {
        $left[] = $link;
    }
}

$titleLeft = get_option('smart_choice_links_title_left') ?: 'Smart Choice Quick Links';
$titleRight = get_option('smart_choice_links_title_right') ?: 'Smart Choice Favorites';
$footer = get_option('smart_choice_links_footer_note') ?: 'Smart Choice Contractors USA / Harold Cabrera';
$showLeft = get_option('smart_choice_links_show_left_star') !== '0';
$showRight = get_option('smart_choice_links_show_right_star') !== '0';


function smart_choice_links_render_panel_template($links, $title, $side, $footer)
{
    ?>
    <ul id="smart-choice-links-<?php echo html_escape($side); ?>-panel-template" class="smart-choice-links-panel-template smart-choice-links-dropdown smart-choice-links-dropdown-<?php echo html_escape($side); ?>" data-scl-side="<?php echo html_escape($side); ?>">
        <li class="scl-dropdown-header">
            <strong><?php echo html_escape($title); ?></strong>
            <?php if (is_admin()) { ?>
                <a href="<?php echo admin_url('smart_choice_links/settings'); ?>" class="pull-right"><i class="fa fa-cog"></i></a>
            <?php } ?>
        </li>
        <?php if (empty($links)) { ?>
            <li class="scl-empty"><a href="<?php echo admin_url('smart_choice_links/settings'); ?>"><?php echo _l('smart_choice_links_empty'); ?></a></li>
        <?php } else { ?>
            <?php foreach ($links as $link) {
                $url = smart_choice_links_normalize_url($link->url);
                $target = !empty($link->target) ? $link->target : (get_option('smart_choice_links_default_open_behavior') ?: '_self');
                if (get_option('smart_choice_links_open_new_tab') === '1' && get_option('smart_choice_links_default_open_behavior') === false) { $target = '_blank'; }
                $htmlTarget = in_array($target, ['_blank'], true) ? '_blank' : '_self';
                $textColor = !empty($link->text_color) ? $link->text_color : '';
                $shadow = !empty($link->text_shadow) ? 'text-shadow:0 1px 2px rgba(0,0,0,.35);' : '';
                $titleStyle = trim(($textColor ? 'color:' . html_escape($textColor) . ';' : '') . $shadow);
            ?>
                <li class="scl-link-item">
                    <a href="<?php echo html_escape($url); ?>" target="<?php echo html_escape($htmlTarget); ?>" rel="<?php echo html_escape($link->rel); ?>" data-scl-open-behavior="<?php echo html_escape($target); ?>">
                        <span class="scl-link-main-line">
                            <span class="scl-link-title-wrap"><i class="<?php echo html_escape($link->icon ?: 'fa fa-link'); ?>"></i><span style="<?php echo $titleStyle; ?>"><?php echo html_escape($link->title); ?></span></span>
                            <?php if (!empty($link->category)) { ?><small class="scl-link-category-inline"><?php echo html_escape($link->category); ?></small><?php } ?>
                        </span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
        <li class="scl-footer"><?php echo html_escape($footer); ?></li>
    </ul>
    <?php
}

function smart_choice_links_render_dropdown_list($links, $title, $side, $footer)
{
    ?>
    <li class="icon smart-choice-links-nav smart-choice-links-<?php echo html_escape($side); ?> tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5" data-scl-side="<?php echo html_escape($side); ?>" data-scl-label="<?php echo html_escape($title); ?>">
        <a href="#" aria-expanded="false" class="smart-choice-links-trigger" data-scl-side="<?php echo html_escape($side); ?>" aria-label="<?php echo html_escape($title); ?>">
            <span class="scl-star-shell"><i class="fa fa-star"></i></span>
        </a>
        <ul class="dropdown-menu animated fadeIn smart-choice-links-dropdown smart-choice-links-dropdown-<?php echo html_escape($side); ?>">
            <li class="scl-dropdown-header">
                <strong><?php echo html_escape($title); ?></strong>
                <?php if (is_admin()) { ?>
                    <a href="<?php echo admin_url('smart_choice_links/settings'); ?>" class="pull-right"><i class="fa fa-cog"></i></a>
                <?php } ?>
            </li>

            <?php if (empty($links)) { ?>
                <li class="scl-empty"><a href="<?php echo admin_url('smart_choice_links/settings'); ?>"><?php echo _l('smart_choice_links_empty'); ?></a></li>
            <?php } else { ?>
                <?php foreach ($links as $link) { ?>
                    <?php
                    $url = smart_choice_links_normalize_url($link->url);
                    $target = !empty($link->target) ? $link->target : (get_option('smart_choice_links_default_open_behavior') ?: '_self');
                    if (get_option('smart_choice_links_open_new_tab') === '1' && get_option('smart_choice_links_default_open_behavior') === false) {
                        $target = '_blank';
                    }
                    $htmlTarget = in_array($target, ['_blank'], true) ? '_blank' : '_self';
                    $textColor = !empty($link->text_color) ? $link->text_color : '';
                    $shadow = !empty($link->text_shadow) ? 'text-shadow:0 1px 2px rgba(0,0,0,.35);' : '';
                    $titleStyle = trim(($textColor ? 'color:' . html_escape($textColor) . ';' : '') . $shadow);
                    ?>
                    <li class="scl-link-item">
                        <a href="<?php echo html_escape($url); ?>" target="<?php echo html_escape($htmlTarget); ?>" rel="<?php echo html_escape($link->rel); ?>" data-scl-open-behavior="<?php echo html_escape($target); ?>">
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
    <?php
}
?>
<div id="smart-choice-links-source" style="display:none;">
    <ul>
        <?php if ($showLeft) { smart_choice_links_render_dropdown_list($left, $titleLeft, 'left', $footer); } ?>
        <?php if ($showRight) { smart_choice_links_render_dropdown_list($right, $titleRight, 'right', $footer); } ?>
    </ul>
</div>
<div id="smart-choice-links-panel-templates" style="display:none !important;">
    <?php if ($showLeft) { smart_choice_links_render_panel_template($left, $titleLeft, 'left', $footer); } ?>
    <?php if ($showRight) { smart_choice_links_render_panel_template($right, $titleRight, 'right', $footer); } ?>
</div>
