<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/vuejs/3.4.27/vue.global.prod.js') . '"></script>';
echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/css/lib/select2/select2.min.css') . '">';

$defaults = [
    'active' => 0,
    'login_background' => '',
    'dashboard_background' => '',
    'login_page_background_color' => '',
    'login_page_text_color' => ''
];

foreach ($defaults as $key => $default) {
    $$key = isset($poly_utilities_appearance[$key]) ? $poly_utilities_appearance[$key] : $default;
}

$current_user_id = get_staff_user_id();

echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/effects.js') . '"></script>';
?>
<script>
    window.initialData = <?php echo json_encode($poly_utilities_appearance); ?>;
</script>
<div id="wrapper">
    <div class="content">
        <div class="row poly_utilities_settings">
            <!-- Roles -->
            <div id="polyApp" v-cloak>
                <?php echo form_open(admin_url('poly_utilities/appearance_update'), ['id' => 'poly_utilities_ext_form', '@submit.prevent' => 'handleSubmit']); ?>
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4><?php echo _l('poly_utilities_login_page_header'); ?></h4>
                            <div class="row">
                                <?php echo poly_utilities_common_helper::render_toggle_vuejs('active', _l('poly_utilities_login_page_activate'), '', [], [], 'col-lg-12', '', 'handle_item.active'); ?>
                            </div>
                            <div class="row tw-mt-2">
                                <?php echo poly_utilities_common_helper::render_select('effect', poly_utilities_common_helper::$page_effects, 'handle_item.effect', _l('poly_utilities_login_page_effects'), 'col-md-6', '', array('v-model' => 'handle_item.effect', '@change' => 'previewEffectChange(handle_item.effect)')); ?>
                            </div>
                            <div class="row tw-mt-2">
                                <div class="form-group col-lg-6 tw-mb-0">
                                    <?php
                                    poly_utilities_common_helper::render_file_upload('login_background', _l('poly_utilities_login_page_upload_label'), 'handle_item.login_background', '.jpg, .jpeg, .png, .webp, .bmp', 'No file input');
                                    ?>
                                </div>
                                <div class="form-group col-lg-6 tw-mb-0">
                                    <?php
                                    poly_utilities_common_helper::render_file_upload('dashboard_background',  _l('poly_utilities_dashboard_page_upload_label'), 'handle_item.dashboard_background', '.jpg, .jpeg, .png, .webp, .bmp', 'No file input');
                                    ?>
                                </div>
                                <div class="form-group col-lg-12">
                                    <div class="poly-help-message-small">
                                        <?php echo _l('poly_utilities_background_image_supported_formats', admin_url('settings?group=general')) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group inline-block">
                                        <label><?php echo _l('poly_utilities_login_page_background_color_label') ?></label>
                                        <div class="input-group colorpicker-input colorpicker-element">
                                            <input type="text" value="<?php echo $login_page_background_color ?>" name="login_page_background_color" id="login_page_background_color" class="form-control login_page_background_color" data-fieldto="login_page_background_color" data-fieldid="login_page_background_color">
                                            <span class="input-group-addon cursor">&nbsp;</span>
                                        </div>
                                        <div class="poly-help-message-small"><?php echo _l('poly_utilities_login_page_background_color_message') ?></div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group inline-block">
                                        <label><?php echo _l('poly_utilities_login_page_text_color_label') ?></label>
                                        <div class="input-group colorpicker-input colorpicker-element">
                                            <input type="text" value="<?php echo $login_page_text_color ?>" name="login_page_text_color" id="login_page_text_color" class="form-control login_page_text_color" data-fieldto="login_page_text_color" data-fieldid="login_page_text_color">
                                            <span class="input-group-addon cursor">&nbsp;</span>
                                        </div>
                                        <div class="poly-help-message-small"><?php echo _l('poly_utilities_login_page_text_color_message') ?></div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<?php
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/appearance_customize_login.js') . '"></script>'; ?>