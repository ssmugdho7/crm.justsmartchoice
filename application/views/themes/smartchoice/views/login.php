
<style id="smart-choice-client-portal-v344">
body.customers,body{--sc-green:#00A651;--sc-orange:#F96302;--sc-yellow:#F5B400;--sc-blue:#0077CC;--sc-light-blue:#2CA8FF;}
.navbar,.customers .navbar{background:linear-gradient(90deg,var(--sc-blue),var(--sc-light-blue),var(--sc-orange))!important;border:0!important;box-shadow:0 3px 14px rgba(0,0,0,.12)!important;}
.navbar .navbar-brand img{max-height:44px!important;width:auto!important;margin-top:3px!important;}
.navbar-nav>li>a{color:#fff!important;font-weight:600!important;}
.navbar-nav>li>a:hover{background:rgba(255,255,255,.16)!important;color:#fff!important;}
@media(max-width:767px){.navbar-collapse{background:#fff!important}.navbar-collapse .navbar-nav>li>a{color:#1f2937!important}.navbar-toggle{border-color:#fff!important}.navbar-toggle .icon-bar{background:#fff!important}.container,.container-fluid{width:100%!important;padding-left:14px!important;padding-right:14px!important}.panel_s,.panel-body{width:100%!important}.table-responsive{border:0!important}}
.kb-search,.knowledge-base-search{background:linear-gradient(135deg,#eaf7ff,#fff2e8)!important;border-radius:18px!important;padding:20px!important;}
.kb-category,.knowledge-base .panel_s,.knowledge-base article,.kb-article-single{border-radius:16px!important;border:1px solid #e5eef7!important;box-shadow:0 8px 22px rgba(0,0,0,.06)!important;overflow:hidden!important;}
.kb-category h4,.knowledge-base h4{color:var(--sc-blue)!important;font-weight:800!important;}
.kb-category a,.knowledge-base a{color:#075985!important;font-weight:600!important;}
</style>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $scClientLoginBg = get_option('sc_client_login_background'); ?>
<?php if ($scClientLoginBg) { ?><style>body.customers{background-image:linear-gradient(rgba(248,250,252,.80),rgba(248,250,252,.80)),url('<?= base_url('uploads/company/' . basename($scClientLoginBg)); ?>')!important;background-size:cover!important;background-position:center!important;background-attachment:fixed!important;min-height:100vh}</style><?php } ?>
<div class="mtop40">
    <div class="col-md-4 col-md-offset-4 text-center">
        <h1 class="tw-font-bold mbot20 login-heading">
            <?= _l(get_option('allow_registration') == 1 ? 'clients_login_heading_register' : 'clients_login_heading_no_register');
?>
        </h1>
    </div>
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2">
        <?= form_open($this->uri->uri_string(), ['class' => 'login-form']); ?>
        <?php hooks()->do_action('clients_login_form_start'); ?>
        <div class="panel_s">
            <div class="panel-body">

                <?php if (! is_language_disabled()) { ?>
                <div class="form-group">
                    <label for="language" class="control-label">
                        <?= _l('language'); ?>
                    </label>
                    <select name="language" id="language" class="form-control selectpicker"
                        onchange="change_contact_language(this)"
                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                        data-live-search="true">
                        <?php $selected = (get_contact_language() != '') ? get_contact_language() : get_option('active_language'); ?>
                        <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                        <option value="<?= e($availableLanguage); ?>"
                            <?= ($availableLanguage == $selected) ? 'selected' : '' ?>>
                            <?= e(ucfirst($availableLanguage)); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group">
                    <label
                        for="email"><?= _l('clients_login_email'); ?></label>
                    <input type="email" autofocus="true" class="form-control" name="email" id="email" autocomplete="username">
                    <?= form_error('email'); ?>
                </div>

                <div class="form-group">
                    <label
                        for="password"><?= _l('clients_login_password'); ?></label>
                    <input type="password" class="form-control" name="password" id="password">
                    <?= form_error('password'); ?>
                </div>

                <?php if (show_recaptcha_in_customers_area()) { ?>
                <div class="g-recaptcha tw-mb-4"
                    data-sitekey="<?= get_option('recaptcha_site_key'); ?>">
                </div>
                <?= form_error('g-recaptcha-response'); ?>
                <?php } ?>

                <div class="checkbox">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <label for="remember">
                        <?= _l('clients_login_remember'); ?>
                    </label>
                </div>

                <div class="form-group tw-mt-6">
                    <p class="text-muted">Remember me keeps this device signed in for up to <?= (int) (app_remember_login_lifetime() / 86400); ?> days. Use it only on a device you trust.</p>
                    <button type="submit" class="btn btn-primary btn-block">
                        <?= _l('clients_login_login_string'); ?>
                    </button>
                    <?php if (get_option('allow_registration') == 1) { ?>
                    <a href="<?= site_url('authentication/register'); ?>"
                        class="btn btn-default btn-block">
                        <?= _l('clients_register_string'); ?>
                    </a>
                    <?php } ?>
                </div>
                <div class="tw-text-center">
                    <a href="<?= site_url('authentication/forgot_password'); ?>"
                        class="text-muted">
                        <?= _l('customer_forgot_password'); ?>
                    </a>
                </div>
                <?php hooks()->do_action('clients_login_form_end'); ?>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('authentication/includes/password_visibility'); ?>
