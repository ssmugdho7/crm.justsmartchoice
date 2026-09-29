<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s smart-choice-links-settings-panel">
    <div class="panel-body">
        <h4 class="tw-font-semibold tw-mb-4"><i class="fa fa-star"></i> <?php echo _l('smart_choice_links_settings'); ?></h4>
        <p class="text-muted"><?php echo _l('smart_choice_links_settings_description'); ?></p>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <?php render_yes_no_option('smart_choice_links_enabled', 'smart_choice_links_enabled'); ?>
                <?php render_yes_no_option('smart_choice_links_show_topbar', 'smart_choice_links_show_topbar'); ?>
                <?php render_yes_no_option('smart_choice_links_show_left_star', 'smart_choice_links_show_left_star'); ?>
                <?php render_yes_no_option('smart_choice_links_show_right_star', 'smart_choice_links_show_right_star'); ?>
                <?php render_yes_no_option('smart_choice_links_open_new_tab', 'smart_choice_links_open_new_tab'); ?>
            </div>
            <div class="col-md-6">
                <?php render_yes_no_option('smart_choice_links_fix_bad_urls', 'smart_choice_links_fix_bad_urls'); ?>
                <?php render_yes_no_option('smart_choice_links_enable_menu_search', 'smart_choice_links_enable_menu_search'); ?>
                <?php echo render_input('settings[smart_choice_links_panel_width]', 'smart_choice_links_panel_width', get_option('smart_choice_links_panel_width') ?: '285', 'number'); ?>
                <?php echo render_input('settings[smart_choice_links_title_left]', 'smart_choice_links_title_left', get_option('smart_choice_links_title_left')); ?>
                <?php echo render_input('settings[smart_choice_links_title_right]', 'smart_choice_links_title_right', get_option('smart_choice_links_title_right')); ?>
            </div>
        </div>

        <?php echo render_textarea('settings[smart_choice_links_footer_note]', 'smart_choice_links_footer_note', get_option('smart_choice_links_footer_note')); ?>

        <div class="alert alert-info">
            <strong><?php echo _l('smart_choice_links_manage'); ?>:</strong>
            <a href="<?php echo admin_url('smart_choice_links'); ?>"><?php echo _l('smart_choice_links_open_manager'); ?></a>
        </div>

        <div class="scl-settings-guide">
            <h4><i class="fa fa-book"></i> <?php echo _l('smart_choice_links_how_to_use'); ?></h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="scl-guide-card">
                        <h5><?php echo _l('smart_choice_links_guide_left_star'); ?></h5>
                        <p><?php echo _l('smart_choice_links_guide_left_star_text'); ?></p>
                    </div>
                    <div class="scl-guide-card">
                        <h5><?php echo _l('smart_choice_links_guide_right_star'); ?></h5>
                        <p><?php echo _l('smart_choice_links_guide_right_star_text'); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="scl-guide-card">
                        <h5><?php echo _l('smart_choice_links_guide_menu_search'); ?></h5>
                        <p><?php echo _l('smart_choice_links_guide_menu_search_text'); ?></p>
                    </div>
                    <div class="scl-guide-card">
                        <h5><?php echo _l('smart_choice_links_guide_old_module'); ?></h5>
                        <p><?php echo _l('smart_choice_links_guide_old_module_text'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
