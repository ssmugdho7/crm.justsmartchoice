<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open(admin_url('recruitment/save_recruitment_portal_branding'), ['autocomplete' => 'off']); ?>
<div class="row">
    <div class="col-md-12">
        <h5 class="no-margin font-bold h5-color">Recruitment Portal Branding</h5>
        <hr class="hr-color">
    </div>
</div>

<div class="form-group">
    <label for="recruitment_portal_logo_url">Portal Logo URL</label>
    <input type="text" id="recruitment_portal_logo_url" name="recruitment_portal_logo_url" class="form-control" value="<?php echo html_escape(get_recruitment_option('recruitment_portal_logo_url') ?: 'https://crm.justsmartchoice.com/media/Logos%20and%20Banners/Smart_Choice_Logo.png?_t=1760693505'); ?>">
    <p class="text-muted mtop5">Recommended logo URL: https://crm.justsmartchoice.com/media/Logos%20and%20Banners/Smart_Choice_Logo.png?_t=1760693505</p>
</div>

<div class="form-group">
    <label for="recruitment_portal_footer_text">Recruitment Portal Footer Text</label>
    <textarea id="recruitment_portal_footer_text" name="recruitment_portal_footer_text" class="form-control" rows="3"><?php echo html_escape(get_recruitment_option('recruitment_portal_footer_text') ?: '© ' . date('Y') . ' Smart Choice Contractors USA. Done Right Through Professional Service.'); ?></textarea>
</div>

<div class="alert alert-info">
    These settings control the public recruitment portal logo and footer. They do not change candidate data, job campaigns, or application workflows.
</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>
