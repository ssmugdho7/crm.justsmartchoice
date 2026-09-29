<?php
defined('BASEPATH') or exit('No direct script access allowed');

function sc_theme_opt($name, $default = '')
{
    $value = get_option($name);
    return ($value === '' || $value === null) ? $default : $value;
}
function sc_color_input($label, $name, $default)
{
    $value = html_escape(sc_theme_opt($name, $default));
    echo '<div class="col-md-4 col-sm-6"><div class="form-group"><label>' . html_escape($label) . '</label><input type="color" name="settings[' . html_escape($name) . ']" value="' . $value . '" class="form-control smart-choice-color-input"></div></div>';
}
function sc_text_input($label, $name, $default, $help = '')
{
    $value = html_escape(sc_theme_opt($name, $default));
    echo '<div class="col-md-4 col-sm-6"><div class="form-group"><label>' . html_escape($label) . '</label><input type="text" name="settings[' . html_escape($name) . ']" value="' . $value . '" class="form-control">';
    if ($help !== '') { echo '<small class="text-muted">' . html_escape($help) . '</small>'; }
    echo '</div></div>';
}
function sc_number_input($label, $name, $default, $min = 10, $max = 24)
{
    $value = html_escape(sc_theme_opt($name, $default));
    echo '<div class="col-md-4 col-sm-6"><div class="form-group"><label>' . html_escape($label) . '</label><input type="number" min="' . (int)$min . '" max="' . (int)$max . '" name="settings[' . html_escape($name) . ']" value="' . $value . '" class="form-control"></div></div>';
}
function sc_yes_no($label, $name, $default = '1')
{
    $value = sc_theme_opt($name, $default);
    $yes = ($value !== '0') ? ' checked' : '';
    $no = ($value === '0') ? ' checked' : '';
    $id = preg_replace('/[^a-z0-9_]/i', '_', $name);
    echo '<div class="form-group"><label class="control-label clearfix">' . html_escape($label) . '</label>';
    echo '<div class="radio radio-primary radio-inline"><input type="radio" id="' . $id . '_yes" name="settings[' . html_escape($name) . ']" value="1"' . $yes . '><label for="' . $id . '_yes">' . _l('settings_yes') . '</label></div>';
    echo '<div class="radio radio-primary radio-inline"><input type="radio" id="' . $id . '_no" name="settings[' . html_escape($name) . ']" value="0"' . $no . '><label for="' . $id . '_no">' . _l('settings_no') . '</label></div>';
    echo '</div>';
}
?>
<style id="smart-choice-settings-polish-141">
.smart-choice-office-settings .smart-choice-settings-hero{background:linear-gradient(135deg,#0057b8,#169179)!important;color:#fff!important;border-radius:16px;padding:22px;display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 12px 30px rgba(0,87,184,.14);}
.smart-choice-office-settings .smart-choice-settings-hero .text-muted{color:#eaf4ff!important;}
.smart-choice-office-settings .smart-choice-badge{background:rgba(255,255,255,.18)!important;color:#fff!important;border-radius:999px;padding:8px 13px;display:inline-block;text-align:center;}
.smart-choice-office-settings .smart-choice-color-grid .form-group{background:#fff;border:1px solid #e8eef5;border-radius:14px;padding:14px;text-align:center;box-shadow:0 8px 20px rgba(15,23,42,.06);}
.smart-choice-office-settings .smart-choice-color-input{height:42px;padding:4px;border-radius:10px;}
.smart-choice-office-settings label{display:block;text-align:center;font-weight:600;}
</style>
<div class="panel_s smart-choice-office-settings">
    <div class="panel-body">
        <div class="smart-choice-settings-hero">
            <div>
                <h4 class="no-margin">Smart Choice Office Theme v1.4.1</h4>
                <p class="text-muted mtop10">Settings restored. This keeps the Office Theme clean interface while adding Smart Choice website colors, matching admin/client top bars, transparent client logos, slimmer dashboard/calendar styling, navy menu arrows, mobile-friendly navigation, and non-destructive dashboard-safe styling.</p>
            </div>
            <div class="smart-choice-settings-actions">
                <span class="smart-choice-badge">Office Style + Blue/Green Gradient</span>
                <a class="btn btn-primary btn-sm" href="<?php echo admin_url('settings?group=admin-theme-settings'); ?>">Refresh Settings</a>
                <a class="btn btn-default btn-sm" href="<?php echo admin_url('settings?group=admin-theme-health'); ?>">Health / Verification</a>
            </div>
        </div>
        <hr>

        <?php sc_yes_no('Enable Theme for Staff/Admin Area', 'smart_choice_theme_admin_enabled', '1'); ?>
        <?php sc_yes_no('Enable Theme for Client Area', 'perfex_office_theme_customers', '1'); ?>
        <?php sc_yes_no('Fix Sidebar Scroll and Setup Menu Visibility', 'smart_choice_theme_sidebar_fixed', '1'); ?>

        <hr>
        <h5 class="bold">Office Theme Base Controls</h5>
        <p class="text-muted">These controls keep the Office Theme look while letting you fine-tune the CRM without editing code.</p>
        <div class="row smart-choice-color-grid">
            <?php sc_number_input('Base Font Size', 'smart_choice_theme_base_font_size', '14', 11, 22); ?>
            <?php sc_number_input('Menu Font Size', 'smart_choice_theme_admin_menu_font_size', '14', 11, 22); ?>
            <?php sc_number_input('Client Menu Font Size', 'smart_choice_theme_client_menu_font_size', '14', 11, 22); ?>
            <?php sc_number_input('Card Roundness', 'smart_choice_theme_card_radius', '12', 2, 28); ?>
            <?php sc_number_input('Button Roundness', 'smart_choice_theme_button_radius', '8', 2, 24); ?>
            <?php sc_number_input('Menu Hover Roundness', 'smart_choice_theme_admin_hover_radius', '8', 2, 22); ?>
        </div>

        <hr>
        <h5 class="bold">Smart Choice Brand Colors</h5>
        <p class="text-muted">The client portal now uses the JustSmartChoice.com website color direction: green/teal header, orange accents, light blue hover areas, and blue support buttons.</p>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('Primary Orange', 'smart_choice_theme_orange_color', '#f7941d'); ?>
            <?php sc_color_input('Deep Orange', 'smart_choice_theme_deep_orange_color', '#d96f00'); ?>
            <?php sc_color_input('Primary Blue', 'smart_choice_theme_primary_color', '#0b5cab'); ?>
            <?php sc_color_input('Light Blue', 'smart_choice_theme_light_blue_color', '#2ea8ff'); ?>
            <?php sc_color_input('Green Accent', 'smart_choice_theme_green_color', '#169179'); ?>
            <?php sc_color_input('Soft Background', 'smart_choice_theme_soft_background', '#f8fafc'); ?>
        </div>

        <hr>
        <h5 class="bold">Admin Area Colors</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('Admin Top Bar', 'smart_choice_theme_top_bar_admin', '#0b5cab'); ?>
            <?php sc_color_input('Admin Menu Background', 'smart_choice_theme_admin_menu_bg', '#ffffff'); ?>
            <?php sc_color_input('Admin Menu Text', 'smart_choice_theme_admin_menu_text', '#263238'); ?>
            <?php sc_color_input('Admin Menu Icon Color', 'smart_choice_theme_admin_icon_color', '#0057b8'); ?>
            <?php sc_color_input('Admin Menu Hover Background', 'smart_choice_theme_admin_menu_hover_bg', '#eaf4ff'); ?>
            <?php sc_color_input('Admin Menu Hover Text/Icon', 'smart_choice_theme_admin_menu_hover_text', '#0b5cab'); ?>
            <?php sc_text_input('Admin Text Shadow CSS', 'smart_choice_theme_admin_text_shadow', 'none', 'Example: 0 1px 1px rgba(0,0,0,.25)'); ?>
        </div>
        <?php sc_yes_no('Turn On Admin Menu Text Shadow', 'smart_choice_theme_admin_menu_shadow', '0'); ?>

        <hr>
        <h5 class="bold">Client Portal Colors</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('Client Header / Menu Background', 'smart_choice_theme_client_menu_bg', '#0057b8'); ?>
            <?php sc_color_input('Client Menu Text', 'smart_choice_theme_client_menu_text', '#ffffff'); ?>
            <?php sc_color_input('Client Menu Icon Color', 'smart_choice_theme_client_icon_color', '#0057b8'); ?>
            <?php sc_color_input('Client Menu Hover Background', 'smart_choice_theme_client_menu_hover_bg', '#eaf4ff'); ?>
            <?php sc_color_input('Client Menu Hover Text/Icon', 'smart_choice_theme_client_menu_hover_text', '#0057b8'); ?>
            <?php sc_text_input('Client Text Shadow CSS', 'smart_choice_theme_client_text_shadow', 'none', 'Use none for cleaner website-style text.'); ?>
        </div>
        <?php sc_yes_no('Turn On Client Menu Text Shadow', 'smart_choice_theme_client_menu_shadow', '0'); ?>

        <hr>
        <h5 class="bold">Trim, Bars, Buttons, and Components</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('General Trim / Hover Highlight', 'smart_choice_theme_trim_color', '#0057b8'); ?>
            <?php sc_color_input('Trim Primary', 'smart_choice_theme_trim_primary', '#0057b8'); ?>
            <?php sc_color_input('Trim Secondary', 'smart_choice_theme_trim_secondary', '#0b5cab'); ?>
            <?php sc_color_input('Sidebar Bar', 'smart_choice_theme_sidebar_bar', '#123b63'); ?>
            <?php sc_color_input('Panel / Component Bar', 'smart_choice_theme_component_bar', '#0057b8'); ?>
            <?php sc_color_input('Button Gradient Start', 'smart_choice_theme_gradient_start', '#0057b8'); ?>
            <?php sc_color_input('Button Gradient End', 'smart_choice_theme_gradient_end', '#169179'); ?>
        </div>

        <div class="alert alert-info mtop20">
            <strong>Brand direction:</strong> The client menu uses the Smart Choice website colors. The admin side keeps a clean Office Theme layout with tighter sidebar/dropdown spacing and reduced visual clutter.
        </div>
    </div>
</div>
<style>
.smart-choice-office-settings .panel-body{border-top:4px solid #0057b8;border-radius:14px;background:linear-gradient(180deg,#fff,#f8fbfc)}
.smart-choice-settings-hero{display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap;background:linear-gradient(135deg,#0057b8,#169179);color:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 10px 28px rgba(0,87,184,.14)}
.smart-choice-settings-hero h4{font-weight:700;color:#fff}.smart-choice-settings-hero .text-muted{color:#eaf4ff!important}.smart-choice-badge{background:rgba(255,255,255,.18);color:#fff;padding:8px 14px;border-radius:999px;font-weight:700;box-shadow:0 8px 22px rgba(0,0,0,.08)}
.smart-choice-office-settings h5{margin-top:20px;color:#0057b8}.smart-choice-color-input{height:38px;padding:3px}.smart-choice-office-settings .form-group label{font-weight:600;display:block;text-align:center;color:#263238}.smart-choice-color-grid .form-group{background:#fff;border:1px solid #e5edf2;border-radius:12px;padding:10px;box-shadow:0 5px 16px rgba(15,23,42,.05);text-align:center;min-height:96px;display:flex;flex-direction:column;justify-content:center}.smart-choice-color-grid input{margin-left:auto;margin-right:auto;text-align:center}
.smart-choice-settings-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.smart-choice-settings-actions .btn{border-radius:10px!important;font-weight:700!important}.smart-choice-office-settings .btn-primary{background:#0057b8!important;border-color:#0057b8!important}.smart-choice-office-settings .btn-default{background:#eaf4ff!important;border-color:#d7e3e6!important;color:#0057b8!important}
</style>
