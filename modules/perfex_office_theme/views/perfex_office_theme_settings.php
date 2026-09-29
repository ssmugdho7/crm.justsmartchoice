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
.smart-choice-office-settings .smart-choice-settings-hero{background:linear-gradient(135deg,#0077CC,#00A651)!important;color:#fff!important;border-radius:16px;padding:22px;display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 12px 30px rgba(0,87,184,.14);}
.smart-choice-office-settings .smart-choice-settings-hero .text-muted{color:#EAF7FF!important;}
.smart-choice-office-settings .smart-choice-badge{background:rgba(255,255,255,.18)!important;color:#fff!important;border-radius:999px;padding:8px 13px;display:inline-block;text-align:center;}
.smart-choice-office-settings .smart-choice-color-grid .form-group{background:#fff;border:1px solid #e8eef5;border-radius:14px;padding:14px;text-align:center;box-shadow:0 8px 20px rgba(15,23,42,.06);}
.smart-choice-office-settings .smart-choice-color-input{height:42px;padding:4px;border-radius:10px;}
.smart-choice-office-settings label{display:block;text-align:center;font-weight:600;}
</style>
<div class="panel_s smart-choice-office-settings">
    <div class="panel-body">
        <div class="smart-choice-settings-hero">
            <div>
                <h4 class="no-margin">Smart Choice Office Theme v1.4.9</h4>
                <p class="text-muted mtop10">CRM-wide visual control center. This page controls the admin header, client portal navigation, logo size, hamburger visibility, dropdown scrollbars, table width behavior, buttons, colors, preview, and rollback profile controls from one organized settings area.</p>
            </div>
            <div class="smart-choice-settings-actions">
                <span class="smart-choice-badge">Office Style + Blue/Green Gradient</span>
                <a class="btn btn-primary btn-sm" href="<?php echo admin_url('perfex_office_theme/settings'); ?>">Refresh Settings</a>
                <a class="btn btn-default btn-sm" href="<?php echo admin_url('perfex_office_theme/health'); ?>">Health</a>
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
            <?php sc_color_input('Primary Orange', 'smart_choice_theme_orange_color', '#F96302'); ?>
            <?php sc_color_input('Deep Orange', 'smart_choice_theme_deep_orange_color', '#d96f00'); ?>
            <?php sc_color_input('Primary Blue', 'smart_choice_theme_primary_color', '#0077CC'); ?>
            <?php sc_color_input('Light Blue', 'smart_choice_theme_light_blue_color', '#2ea8ff'); ?>
            <?php sc_color_input('Green Accent', 'smart_choice_theme_green_color', '#00A651'); ?>
            <?php sc_color_input('Soft Background', 'smart_choice_theme_soft_background', '#f8fafc'); ?>
        </div>

        <hr>
        <h5 class="bold">Admin Area Colors</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('Admin Top Bar', 'smart_choice_theme_top_bar_admin', '#0077CC'); ?>
            <?php sc_color_input('Admin Menu Background', 'smart_choice_theme_admin_menu_bg', '#ffffff'); ?>
            <?php sc_color_input('Admin Menu Text', 'smart_choice_theme_admin_menu_text', '#263238'); ?>
            <?php sc_color_input('Admin Menu Icon Color', 'smart_choice_theme_admin_icon_color', '#0077CC'); ?>
            <?php sc_color_input('Admin Menu Hover Background', 'smart_choice_theme_admin_menu_hover_bg', '#EAF7FF'); ?>
            <?php sc_color_input('Admin Menu Hover Text/Icon', 'smart_choice_theme_admin_menu_hover_text', '#0077CC'); ?>
            <?php sc_text_input('Admin Text Shadow CSS', 'smart_choice_theme_admin_text_shadow', 'none', 'Example: 0 1px 1px rgba(0,0,0,.25)'); ?>
        </div>
        <?php sc_yes_no('Turn On Admin Menu Text Shadow', 'smart_choice_theme_admin_menu_shadow', '0'); ?>

        <hr>
        <h5 class="bold">Client Portal Colors</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('Client Header / Menu Background', 'smart_choice_theme_client_menu_bg', '#0077CC'); ?>
            <?php sc_color_input('Client Menu Text', 'smart_choice_theme_client_menu_text', '#ffffff'); ?>
            <?php sc_color_input('Client Menu Icon Color', 'smart_choice_theme_client_icon_color', '#0077CC'); ?>
            <?php sc_color_input('Client Menu Hover Background', 'smart_choice_theme_client_menu_hover_bg', '#EAF7FF'); ?>
            <?php sc_color_input('Client Menu Hover Text/Icon', 'smart_choice_theme_client_menu_hover_text', '#0077CC'); ?>
            <?php sc_text_input('Client Text Shadow CSS', 'smart_choice_theme_client_text_shadow', 'none', 'Use none for cleaner website-style text.'); ?>
        </div>
        <?php sc_yes_no('Turn On Client Menu Text Shadow', 'smart_choice_theme_client_menu_shadow', '0'); ?>




        <hr>
        <h5 class="bold">Navigation Bar, Logo, and Mobile Controls</h5>
        <p class="text-muted">Use this section for the white hamburger icon, bigger company logo, wider mobile navigation, and cleaner admin/client top bars.</p>
        <div class="row smart-choice-color-grid">
            <?php sc_number_input('Company Logo Height', 'smart_choice_theme_nav_logo_height', '46', 24, 90); ?>
            <?php sc_number_input('Admin Top Bar Height', 'smart_choice_theme_topbar_height', '62', 46, 96); ?>
            <?php sc_number_input('Mobile Top Bar Height', 'smart_choice_theme_mobile_nav_height', '64', 46, 96); ?>
            <?php sc_number_input('Hamburger Icon Size', 'smart_choice_theme_hamburger_size', '22', 16, 42); ?>
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Force White Hamburger on Green/Blue Header', 'smart_choice_theme_force_white_hamburger', '1'); ?></div>
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Gradient Backgrounds Use White Text', 'smart_choice_theme_force_gradient_white_text', '1'); ?></div>
        </div>

        <hr>
        <h5 class="bold">Dropdowns, Search Selects, and Scrollbar Controls</h5>
        <p class="text-muted">This fixes the double and triple scrollbar problem in staff selectors, bootstrap select menus, quick menus, and Smart Choice link dropdowns.</p>
        <div class="row smart-choice-color-grid">
            <?php sc_number_input('Main Dropdown Maximum Height', 'smart_choice_theme_dropdown_max_height', '360', 180, 720); ?>
            <?php sc_number_input('Search Select Maximum Height', 'smart_choice_theme_select_dropdown_max_height', '320', 160, 640); ?>
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Remove Nested Scrollbars Where Safe', 'smart_choice_theme_remove_nested_scrollbars', '1'); ?></div>
        </div>

        <hr>
        <h5 class="bold">Tables, Supplier Lists, and Column Fit</h5>
        <p class="text-muted">Use this for the Full Name and Email columns so long names stop pushing into the next column. Social fields such as Facebook, Telegram, LinkedIn, and Instagram still require the supplier module database/view fields if they are missing from that module.</p>
        <div class="row smart-choice-color-grid">
            <?php sc_number_input('Full Name Column Width', 'smart_choice_theme_table_fullname_width', '170', 100, 320); ?>
            <?php sc_number_input('Email Column Width', 'smart_choice_theme_table_email_width', '180', 90, 360); ?>
            <?php sc_number_input('Client Login Button Height', 'smart_choice_theme_client_login_button_height', '36', 26, 60); ?>
        </div>

        <hr>
        <h5 class="bold">Live Preview and Rollback Profile</h5>
        <p class="text-muted">Preview the main colors before saving. You can also store a design profile while testing another look, then paste it back if you want to return to the older configuration.</p>
        <div class="smart-choice-live-preview" id="smart-choice-live-preview">
            <div class="sc-preview-nav"><span class="sc-preview-logo">Smart Choice</span><span>Dashboard</span><span>Customers</span><span>Projects</span><button type="button">Save</button></div>
            <div class="sc-preview-body"><div class="sc-preview-card"><strong>Navigation Preview</strong><p>Button, menu, and panel samples update after changing colors and clicking Preview.</p><a>Sample link</a></div><div class="sc-preview-gradient">Gradient text must stay white. Hover rule changes it to black.</div></div>
        </div>
        <div class="form-group mtop15">
            <label>Saved Design Profile / Rollback Copy</label>
            <textarea name="settings[smart_choice_theme_saved_profile]" id="smart_choice_theme_saved_profile" class="form-control" rows="4"><?php echo html_escape(sc_theme_opt('smart_choice_theme_saved_profile', '')); ?></textarea>
            <small class="text-muted">Click Save Current Profile, then submit settings. To restore later, paste the saved profile and click Apply Profile, then submit settings.</small>
        </div>
        <div class="btn-group mtop10">
            <button type="button" class="btn btn-info" id="sc-preview-settings">Preview Current Colors</button>
            <button type="button" class="btn btn-default" id="sc-save-profile">Save Current Profile</button>
            <button type="button" class="btn btn-warning" id="sc-apply-profile">Apply Profile From Box</button>
            <button type="button" class="btn btn-danger" id="sc-reset-million-dollar">Load Sleek Default</button>
        </div>

        <hr>
        <h5 class="bold">Global Compact Layout Fixes</h5>
        <p class="text-muted">These controls affect the admin dashboard, modules page, staff tables, calendar, timesheet arrows, customer portal buttons, and top menu bars.</p>
        <div class="row smart-choice-color-grid">
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Compact Buttons and Arrows', 'smart_choice_theme_compact_mode', '1'); ?></div>
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Force White Hamburger Menu Lines', 'smart_choice_theme_force_white_hamburger', '1'); ?></div>
            <div class="col-md-4 col-sm-6"><?php sc_yes_no('Remove Nested Scrollbars Where Safe', 'smart_choice_theme_remove_nested_scrollbars', '1'); ?></div>
            <?php sc_number_input('Client Login Button Width', 'smart_choice_theme_login_button_width', '190', 120, 320); ?>
            <?php sc_number_input('Dashboard Progress Bar Height', 'smart_choice_theme_progress_height', '8', 4, 20); ?>
            <?php sc_color_input('Terms Link Color', 'smart_choice_theme_terms_link_color', '#0077CC'); ?>
            <?php sc_color_input('Development Notice Background', 'smart_choice_theme_development_notice_bg', '#fff4dc'); ?>
            <?php sc_color_input('Development Notice Text', 'smart_choice_theme_development_notice_text', '#8a4b00'); ?>
        </div>

        <hr>
        <h5 class="bold">Trim, Bars, Buttons, and Components</h5>
        <div class="row smart-choice-color-grid">
            <?php sc_color_input('General Trim / Hover Highlight', 'smart_choice_theme_trim_color', '#0077CC'); ?>
            <?php sc_color_input('Trim Primary', 'smart_choice_theme_trim_primary', '#0077CC'); ?>
            <?php sc_color_input('Trim Secondary', 'smart_choice_theme_trim_secondary', '#0077CC'); ?>
            <?php sc_color_input('Sidebar Bar', 'smart_choice_theme_sidebar_bar', '#123b63'); ?>
            <?php sc_color_input('Panel / Component Bar', 'smart_choice_theme_component_bar', '#0077CC'); ?>
            <?php sc_color_input('Button Gradient Start', 'smart_choice_theme_gradient_start', '#0077CC'); ?>
            <?php sc_color_input('Button Gradient End', 'smart_choice_theme_gradient_end', '#00A651'); ?>
        </div>

        <div class="alert alert-info mtop20">
            <strong>Brand direction:</strong> The client menu uses the Smart Choice website colors. The admin side keeps a clean Office Theme layout with tighter sidebar/dropdown spacing and reduced visual clutter.
        </div>
    </div>
</div>
<style>
.smart-choice-office-settings .panel-body{border-top:4px solid #0077CC;border-radius:14px;background:linear-gradient(180deg,#fff,#f8fbfc)}
.smart-choice-settings-hero{display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap;background:linear-gradient(135deg,#0077CC,#00A651);color:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 10px 28px rgba(0,87,184,.14)}
.smart-choice-settings-hero h4{font-weight:700;color:#fff}.smart-choice-settings-hero .text-muted{color:#EAF7FF!important}.smart-choice-badge{background:rgba(255,255,255,.18);color:#fff;padding:8px 14px;border-radius:999px;font-weight:700;box-shadow:0 8px 22px rgba(0,0,0,.08)}
.smart-choice-office-settings h5{margin-top:20px;color:#0077CC}.smart-choice-color-input{height:38px;padding:3px}.smart-choice-office-settings .form-group label{font-weight:600;display:block;text-align:center;color:#263238}.smart-choice-color-grid .form-group{background:#fff;border:1px solid #e5edf2;border-radius:12px;padding:10px;box-shadow:0 5px 16px rgba(15,23,42,.05);text-align:center;min-height:96px;display:flex;flex-direction:column;justify-content:center}.smart-choice-color-grid input{margin-left:auto;margin-right:auto;text-align:center}
.smart-choice-settings-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.smart-choice-settings-actions .btn{border-radius:10px!important;font-weight:700!important}.smart-choice-office-settings .btn-primary{background:#0077CC!important;border-color:#0077CC!important}.smart-choice-office-settings .btn-default{background:#EAF7FF!important;border-color:#d7e3e6!important;color:#0077CC!important}
</style>

<style id="smart-choice-settings-control-center-145">
.smart-choice-live-preview{border:1px solid #dfeaf0;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 10px 28px rgba(15,23,42,.08)}
.smart-choice-live-preview .sc-preview-nav{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(135deg,#0077CC,#00A651);color:#fff;padding:14px 16px}.smart-choice-live-preview .sc-preview-logo{font-weight:800;background:rgba(255,255,255,.16);padding:8px 12px;border-radius:12px}.smart-choice-live-preview .sc-preview-nav button{margin-left:auto;background:#F96302;color:#111;border:0;border-radius:9px;padding:7px 14px;font-weight:700}.smart-choice-live-preview .sc-preview-body{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:16px}.smart-choice-live-preview .sc-preview-card{border:1px solid #e5edf2;border-radius:14px;padding:14px;background:#f8fafc}.smart-choice-live-preview .sc-preview-card a{color:#0077CC;font-weight:700}.smart-choice-live-preview .sc-preview-gradient{border-radius:14px;padding:18px;background:linear-gradient(135deg,#0077CC,#00A651);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;text-align:center}.smart-choice-live-preview .sc-preview-gradient:hover{color:#111}@media(max-width:767px){.smart-choice-live-preview .sc-preview-body{grid-template-columns:1fr}.smart-choice-live-preview .sc-preview-nav button{margin-left:0}}
</style>
<script>
(function(){
  function q(name){return document.querySelector('[name="settings['+name+']"]');}
  var fields=['smart_choice_theme_primary_color','smart_choice_theme_green_color','smart_choice_theme_orange_color','smart_choice_theme_gradient_start','smart_choice_theme_gradient_end','smart_choice_theme_top_bar_admin','smart_choice_theme_client_menu_bg','smart_choice_theme_soft_background','smart_choice_theme_button_radius','smart_choice_theme_nav_logo_height','smart_choice_theme_topbar_height','smart_choice_theme_hamburger_size','smart_choice_theme_dropdown_max_height','smart_choice_theme_select_dropdown_max_height','smart_choice_theme_table_fullname_width','smart_choice_theme_table_email_width','smart_choice_theme_client_login_button_height','smart_choice_theme_force_gradient_white_text'];
  function val(n){var e=q(n); if(!e){return '';} if(e.type==='radio'){var r=document.querySelector('[name="settings['+n+']"]:checked'); return r?r.value:'';} return e.value;}
  function setv(n,v){var e=q(n); if(!e){return;} if(e.type==='radio'){var r=document.querySelector('[name="settings['+n+']"][value="'+v+'"]'); if(r){r.checked=true;} return;} e.value=v;}
  function collect(){var out={}; fields.forEach(function(n){out[n]=val(n);}); return out;}
  function preview(){var p=document.getElementById('smart-choice-live-preview'); if(!p){return;} var g1=val('smart_choice_theme_gradient_start')||'#0077CC', g2=val('smart_choice_theme_gradient_end')||'#00A651', orange=val('smart_choice_theme_orange_color')||'#F96302', soft=val('smart_choice_theme_soft_background')||'#f8fafc', radius=val('smart_choice_theme_button_radius')||'8'; p.querySelector('.sc-preview-nav').style.background='linear-gradient(135deg,'+g1+','+g2+')'; p.querySelector('.sc-preview-gradient').style.background='linear-gradient(135deg,'+g1+','+g2+')'; p.querySelector('.sc-preview-card').style.background=soft; p.querySelector('button').style.background=orange; p.querySelectorAll('button,.sc-preview-logo,.sc-preview-card,.sc-preview-gradient').forEach(function(el){el.style.borderRadius=radius+'px';});}
  document.addEventListener('click',function(e){
    if(e.target && e.target.id==='sc-preview-settings'){preview();}
    if(e.target && e.target.id==='sc-save-profile'){var box=document.getElementById('smart_choice_theme_saved_profile'); if(box){box.value=JSON.stringify(collect(),null,2);} }
    if(e.target && e.target.id==='sc-apply-profile'){var box=document.getElementById('smart_choice_theme_saved_profile'); if(!box||!box.value){return;} try{var data=JSON.parse(box.value); Object.keys(data).forEach(function(k){setv(k,data[k]);}); preview();}catch(err){alert('The saved profile box does not contain valid JSON.');}}
    if(e.target && e.target.id==='sc-reset-million-dollar'){var d={'smart_choice_theme_gradient_start':'#0077CC','smart_choice_theme_gradient_end':'#00A651','smart_choice_theme_primary_color':'#0077CC','smart_choice_theme_green_color':'#00A651','smart_choice_theme_orange_color':'#F96302','smart_choice_theme_deep_orange_color':'#E59A00','smart_choice_theme_soft_background':'#F5F5F5','smart_choice_theme_top_bar_admin':'#0077CC','smart_choice_theme_client_menu_bg':'#0077CC','smart_choice_theme_nav_logo_height':'48','smart_choice_theme_topbar_height':'64','smart_choice_theme_mobile_nav_height':'66','smart_choice_theme_hamburger_size':'24','smart_choice_theme_button_radius':'10'}; Object.keys(d).forEach(function(k){setv(k,d[k]);}); preview();}
  });
  document.addEventListener('input',function(e){if(e.target && e.target.name && e.target.name.indexOf('settings[')===0){preview();}});
  preview();
})();
</script>
