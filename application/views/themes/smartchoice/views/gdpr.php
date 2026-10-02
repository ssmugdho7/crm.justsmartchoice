<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$privacyInformation = get_option('gdpr_page_top_information_block');
// Resolve the policy's unfinished update-date placeholder; retain any published date.
$privacyInformation = str_replace('[Date]', 'October 3, 2026', (string) $privacyInformation);
?>
<section class="sc-privacy-page" aria-labelledby="sc-privacy-title">
    <header class="sc-privacy-header">
        <span class="sc-privacy-eyebrow"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> YOUR PRIVACY</span>
        <h1 id="sc-privacy-title">Privacy &amp; data</h1>
        <p>Review your information and manage the privacy options available for your account.</p>
        <?php if (trim((string) $privacyInformation) !== '') { ?>
        <a class="sc-privacy-policy-link" href="#sc-privacy-policy"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> Read our privacy policy <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a>
        <?php } ?>
    </header>
    <section aria-labelledby="sc-privacy-actions-title">
        <h2 id="sc-privacy-actions-title" class="sc-privacy-section-title">Your privacy options</h2>
        <div class="sc-privacy-actions">
    <?php if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions') == '1') { ?>
    <div class="sc-privacy-action">
        <div class="gdpr-right">
            <span class="sc-privacy-icon" aria-hidden="true"><i class="fa-regular fa-file-lines"></i></span>
            <h3 class="gdpr-right-heading">
                <?= _l('gdpr_right_to_be_informed'); ?>
            </h3>
            <p>Read the terms that apply to your use of our services.</p>
            <a href="<?= terms_url(); ?>"
                class="btn btn-primary"><?= _l('terms_and_conditions'); ?></a>
        </div>
    </div>
    <?php } ?>
    <div class="sc-privacy-action">
        <div class="gdpr-right">
            <span class="sc-privacy-icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
            <h3 class="gdpr-right-heading">
                <?= _l('gdpr_right_of_access'); ?>
            </h3>
            <p>Review and update the information in your customer profile.</p>
            <a href="<?= site_url('clients/profile'); ?>"
                class="btn btn-primary"><?= _l('edit_my_information'); ?></a>
        </div>
    </div>
    <?php if (is_gdpr() && get_option('gdpr_contact_enable_right_to_be_forgotten') == '1') { ?>
    <div class="sc-privacy-action">
        <div class="gdpr-right sc-privacy-removal">
            <span class="sc-privacy-icon" aria-hidden="true"><i class="fa-regular fa-trash-can"></i></span>
            <h3 class="gdpr-right-heading">
                <?= _l('gdpr_right_to_erasure'); ?>
            </h3>
            <p>Open a request to remove your personal data.</p>
            <a href="#" data-toggle="modal" data-target="#dataRemoval"
                class="btn btn-primary"><?= _l('request_data_removal'); ?></a>
        </div>
    </div>
    <?php } ?>
    <?php if (is_gdpr() && get_option('gdpr_data_portability_contacts') == '1') { ?>
    <div class="sc-privacy-action">
        <div class="gdpr-right">
            <span class="sc-privacy-icon" aria-hidden="true"><i class="fa-solid fa-download"></i></span>
            <h3 class="gdpr-right-heading">
                <?= _l('gdpr_right_to_data_portability'); ?>
            </h3>
            <p>Download a copy of your personal data.</p>
            <a href="<?= site_url('clients/export'); ?>"
                class="btn btn-primary"><?= _l('export_my_data'); ?></a>
        </div>
    </div>
    <?php } ?>
    <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') { ?>
    <div class="sc-privacy-action">
        <div class="gdpr-right">
            <span class="sc-privacy-icon" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
            <h3 class="gdpr-right-heading">
                <?= _l('gdpr_consent'); ?>
            </h3>
            <p>Review and manage your consent preferences.</p>
            <a href="<?= contact_consent_url(get_contact_user_id()); ?>"
                class="btn btn-primary"><?= _l('gdpr_consent'); ?></a>
        </div>
    </div>
    <?php } ?>
</div>

    </section>
    <?php if (trim((string) $privacyInformation) !== '') { ?>
    <section id="sc-privacy-policy" class="sc-privacy-policy" aria-label="Privacy policy">
        <div class="sc-privacy-policy-label"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> PRIVACY POLICY</div>
        <div class="sc-privacy-policy-content"><?= $privacyInformation; ?></div>
    </section>
    <?php } ?>
<?php if (is_gdpr() && get_option('gdpr_contact_enable_right_to_be_forgotten') == '1') { ?>
<div class="modal fade" tabindex="-1" role="dialog" id="dataRemoval" aria-labelledby="sc-data-removal-title">
    <div class="modal-dialog" role="document">
        <?= form_open(); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="sc-data-removal-title">
                    <?= _l('request_data_removal'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?= form_hidden('removal_request', true); ?>
                    <label for="removal_description"
                        class="control-label"><?= _l('explanation_for_data_removal'); ?></label>
                    <textarea name="removal_description" id="removal_description" class="form-control" rows="4"
                        placeholder="<?= _l('briefly_describe_why_remove_data'); ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit"
                    class="btn btn-primary _delete"><?= _l('confirm'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?= form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php } ?>
</section>
