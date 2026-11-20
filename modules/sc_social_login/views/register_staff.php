<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>
<body class="tw-bg-neutral-100 login_admin">
<main class="tw-max-w-md tw-mx-auto tw-py-12">
    <div class="company-logo text-center"><?php get_dark_company_logo(); ?></div>
    <div class="tw-bg-white tw-p-8 tw-rounded-lg tw-shadow-sm">
        <h1 class="tw-text-2xl tw-font-bold"><?= _l('scsl_staff_signup'); ?></h1>
        <p><?= _l('scsl_staff_signup_hint'); ?></p>
        <?php $this->load->view('authentication/includes/alerts'); ?>
        <?= validation_errors('<div class="alert alert-danger">','</div>'); ?>
        <?= form_open(site_url('sc_social_login/register')); ?>
        <div class="form-group"><label for="signup-email"><?= _l('staff_add_edit_email'); ?></label>
            <input id="signup-email" class="form-control" type="email" value="<?= html_escape($profile['email']); ?>" readonly></div>
        <div class="form-group"><label for="firstname"><?= _l('staff_add_edit_firstname'); ?></label>
            <input id="firstname" class="form-control" name="firstname" autocomplete="given-name" maxlength="50" required value="<?= set_value('firstname',$profile['firstname']??''); ?>"></div>
        <div class="form-group"><label for="lastname"><?= _l('staff_add_edit_lastname'); ?></label>
            <input id="lastname" class="form-control" name="lastname" autocomplete="family-name" maxlength="50" required value="<?= set_value('lastname',$profile['lastname']??''); ?>"></div>
        <?php if ($terms): ?><p><label><input type="checkbox" name="accept_terms_and_conditions" value="1" required> <?= _l('terms_and_conditions'); ?></label>
            <a href="<?= site_url('terms-and-conditions'); ?>" target="_blank" rel="noopener"><?= _l('view'); ?></a></p><?php endif; ?>
        <button class="btn btn-primary" type="submit"><?= _l('scsl_create_account'); ?></button>
        <?= form_close(); ?>
        <p class="mtop15"><a href="<?= admin_url('authentication'); ?>"><?= _l('scsl_existing'); ?></a></p>
    </div>
</main>
</body></html>
