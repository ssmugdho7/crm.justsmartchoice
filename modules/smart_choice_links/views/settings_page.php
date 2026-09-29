<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content smart-choice-links-page">
        <div class="row">
            <div class="col-md-12">
                <div class="scl-hero panel_s">
                    <div class="panel-body">
                        <div class="scl-hero-flex">
                            <div>
                                <h3><i class="fa fa-sliders-h"></i> <?php echo _l('smart_choice_links_settings'); ?></h3>
                                <p><?php echo _l('smart_choice_links_settings_description'); ?></p>
                            </div>
                            <div class="scl-hero-actions">
                                <a href="<?php echo admin_url('smart_choice_links'); ?>" class="btn btn-default btn-sm"><i class="fa fa-list"></i> <?php echo _l('smart_choice_links_manage'); ?></a>
                                <a href="<?php echo admin_url('smart_choice_links/help'); ?>" class="btn btn-default btn-sm"><i class="fa fa-question-circle"></i> <?php echo _l('smart_choice_links_how_to_use'); ?></a>
                                <a href="<?php echo admin_url('smart_choice_links/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-heartbeat"></i> <?php echo _l('smart_choice_links_health_check'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="scl-module-tabs">
                    <a href="<?php echo admin_url('smart_choice_links'); ?>"><i class="fa fa-list"></i> <?php echo _l('smart_choice_links_manage'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/settings'); ?>" class="active"><i class="fa fa-cog"></i> <?php echo _l('smart_choice_links_settings'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/help'); ?>"><i class="fa fa-question-circle"></i> <?php echo _l('smart_choice_links_how_to_use'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('smart_choice_links_health_check'); ?></a>
                </div>

                <?php echo form_open(admin_url('smart_choice_links/save_settings')); ?>
                <div class="panel_s smart-choice-links-settings-panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fa fa-star"></i> <?php echo _l('smart_choice_links_general_settings'); ?></h4>
                                <div class="scl-compact-options">
                                    <?php render_yes_no_option('smart_choice_links_enabled', 'smart_choice_links_enabled'); ?>
                                    <?php render_yes_no_option('smart_choice_links_show_topbar', 'smart_choice_links_show_topbar'); ?>
                                    <?php render_yes_no_option('smart_choice_links_show_left_star', 'smart_choice_links_show_left_star'); ?>
                                    <?php render_yes_no_option('smart_choice_links_show_right_star', 'smart_choice_links_show_right_star'); ?>
                                    <?php render_yes_no_option('smart_choice_links_open_new_tab', 'smart_choice_links_open_new_tab'); ?>
                                    <div class="form-group">
                                        <label for="smart_choice_links_default_open_behavior" class="control-label"><?php echo _l('smart_choice_links_default_open_behavior'); ?></label>
                                        <select name="settings[smart_choice_links_default_open_behavior]" id="smart_choice_links_default_open_behavior" class="form-control selectpicker" data-width="100%">
                                            <option value="_self" <?php echo get_option('smart_choice_links_default_open_behavior') === '_self' ? 'selected' : ''; ?>><?php echo _l('smart_choice_links_same_tab'); ?></option>
                                            <option value="_blank" <?php echo get_option('smart_choice_links_default_open_behavior') === '_blank' ? 'selected' : ''; ?>><?php echo _l('smart_choice_links_new_tab'); ?></option>
                                            <option value="_window" <?php echo get_option('smart_choice_links_default_open_behavior') === '_window' ? 'selected' : ''; ?>><?php echo _l('smart_choice_links_new_window'); ?></option>
                                            <option value="_popup" <?php echo get_option('smart_choice_links_default_open_behavior') === '_popup' ? 'selected' : ''; ?>><?php echo _l('smart_choice_links_popup_window'); ?></option>
                                        </select>
                                        <p class="text-muted small"><?php echo _l('smart_choice_links_default_open_behavior_help'); ?></p>
                                    </div>

                                    <?php render_yes_no_option('smart_choice_links_fix_bad_urls', 'smart_choice_links_fix_bad_urls'); ?>
                                    <?php render_yes_no_option('smart_choice_links_enable_menu_search', 'smart_choice_links_enable_menu_search'); ?>
                                    <?php render_yes_no_option('smart_choice_links_enable_command_palette', 'smart_choice_links_enable_command_palette'); ?>
                                    <?php render_yes_no_option('smart_choice_links_enable_setup_shortcut', 'smart_choice_links_enable_setup_shortcut'); ?>
                                </div>
                                <?php echo render_input('settings[smart_choice_links_title_left]', 'smart_choice_links_title_left', get_option('smart_choice_links_title_left')); ?>
                                <?php echo render_input('settings[smart_choice_links_title_right]', 'smart_choice_links_title_right', get_option('smart_choice_links_title_right')); ?>
                                <?php echo render_textarea('settings[smart_choice_links_footer_note]', 'smart_choice_links_footer_note', get_option('smart_choice_links_footer_note'), ['rows' => 3]); ?>
                            </div>
                            <div class="col-md-6">
                                <h4><i class="fa fa-paint-brush"></i> <?php echo _l('smart_choice_links_popup_appearance'); ?></h4>
                                <div class="row">
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_panel_width]', 'smart_choice_links_panel_width', get_option('smart_choice_links_panel_width') ?: '285', 'number', ['min'=>220,'max'=>500]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_row_height]', 'smart_choice_links_row_height', get_option('smart_choice_links_row_height') ?: '34', 'number', ['min'=>28,'max'=>60]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_icon_size]', 'smart_choice_links_icon_size', get_option('smart_choice_links_icon_size') ?: '16', 'number', ['min'=>12,'max'=>28]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_font_size]', 'smart_choice_links_font_size', get_option('smart_choice_links_font_size') ?: '13', 'number', ['min'=>10,'max'=>18]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_padding]', 'smart_choice_links_padding', get_option('smart_choice_links_padding') ?: '8', 'number', ['min'=>4,'max'=>20]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_radius]', 'smart_choice_links_radius', get_option('smart_choice_links_radius') ?: '8', 'number', ['min'=>0,'max'=>24]); ?></div>
                                    <div class="col-sm-6"><?php echo render_input('settings[smart_choice_links_animation_speed]', 'smart_choice_links_animation_speed', get_option('smart_choice_links_animation_speed') ?: '180', 'number', ['min'=>0,'max'=>1000]); ?></div>
                                </div>
                                <div class="scl-live-preview">
                                    <div class="scl-preview-star"><i class="fa fa-star"></i></div>
                                    <div class="scl-preview-panel">
                                        <strong><?php echo _l('smart_choice_links_preview'); ?></strong>
                                        <a><i class="fa fa-home"></i> Dashboard</a>
                                        <a><i class="fa fa-cog"></i> Setup Menu</a>
                                        <a><i class="fa fa-search"></i> Search Modules</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
                        <a href="<?php echo admin_url('smart_choice_links'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?php echo _l('smart_choice_links_manage'); ?></a>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
