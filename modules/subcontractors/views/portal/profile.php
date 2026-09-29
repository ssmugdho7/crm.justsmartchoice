<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$lang = $this->input->get('lang') === 'es' ? 'es' : 'en';
$is_register = !empty($is_register);
$company_logo = function_exists('get_option') ? get_option('company_logo') : '';
$logo_url = !empty($company_logo) ? base_url('uploads/company/' . $company_logo) : base_url('media/public/smartchoice%20logo.png');
$home_url = 'https://justsmartchoice.com/';
$login_url = admin_url();
$submitted_data = isset($submitted_data) && is_array($submitted_data) ? $submitted_data : [];
$field_errors = isset($field_errors) && is_array($field_errors) ? $field_errors : [];
$value = static function ($record, $field, array $submitted) {
    if (array_key_exists($field, $submitted)) {
        return (string) $submitted[$field];
    }
    return $record && isset($record->$field) ? (string) $record->$field : '';
};
$error = static function ($field, array $errors) {
    return isset($errors[$field]) ? (string) $errors[$field] : '';
};
$portal_error = (string) $this->input->get('error');
$page_title = $is_register ? _l('smartsource_portal_registration_heading') : _l('smartsource_portal_profile_heading');
$page_subtitle = $is_register ? _l('smartsource_portal_registration_subtitle') : _l('smartsource_portal_profile_subtitle');
?>
<!DOCTYPE html>
<html lang="<?php echo html_escape($lang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0E6F5B">
    <title><?php echo html_escape($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        *{box-sizing:border-box}html{-webkit-text-size-adjust:100%}body{background:#eef3f1;font-family:Arial,sans-serif;color:#1f2937;margin:0;padding:24px;overflow-x:hidden}.portal-wrap{max-width:1040px;margin:0 auto}.portal-header{position:relative;overflow:hidden;background:linear-gradient(135deg,#0f172a,#169179);color:#fff;border-radius:18px;box-shadow:0 18px 45px rgba(0,0,0,.18);padding:28px;margin-bottom:22px;border-bottom:5px solid #F28C28}.logo-stage{position:relative;display:inline-flex;align-items:center;justify-content:center;min-width:300px;min-height:104px;margin-bottom:12px}.portal-logo{position:relative;z-index:2;max-height:78px;max-width:280px;object-fit:contain;background:#fff;border-radius:14px;padding:8px;box-shadow:0 8px 24px rgba(0,0,0,.16)}.logo-particle{position:absolute;z-index:3;width:7px;height:7px;background:rgba(255,255,255,.96);border-radius:2px;pointer-events:none;animation:logoBreak 2.8s ease-in-out infinite;opacity:0}.logo-particle:nth-child(3n){background:rgba(53,152,219,.9)}.logo-particle:nth-child(4n){background:rgba(242,140,40,.9)}@keyframes logoBreak{0%,15%{transform:translate(0,0) scale(1);opacity:0}28%{opacity:.95}65%{transform:translate(var(--dx),var(--dy)) rotate(var(--rot)) scale(.45);opacity:.15}100%{transform:translate(0,0) scale(.2);opacity:0}}@media (prefers-reduced-motion:reduce){.logo-particle{display:none}}.portal-card{background:#fff;border-radius:16px;box-shadow:0 14px 38px rgba(0,0,0,.12);padding:28px;margin-bottom:22px;border-top:5px solid #169179}.portal-title{font-weight:800;margin:4px 0 10px;color:#fff}.portal-card h4{font-weight:800;color:#111827;margin:0 0 18px}.profile-image{width:118px;height:118px;border-radius:50%;object-fit:cover;border:4px solid #F28C28;background:#fff}.btn-smart{background:#169179;color:#fff;border:0;font-weight:700;border-radius:9px}.btn-smart:hover,.btn-smart:focus{background:#0E6F5B;color:#fff}.btn-home{background:#F28C28;color:#fff;border:0;font-weight:700;border-radius:9px}.btn-home:hover,.btn-home:focus{background:#d87412;color:#fff}.btn-login{background:#3598DB;color:#fff;border:0;font-weight:700;border-radius:9px}.btn-login:hover,.btn-login:focus{background:#237eb9;color:#fff}.portal-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}.portal-actions .btn{margin:0}.file-list{padding-left:20px}.file-list li{margin-bottom:7px}.language-box{text-align:right;margin-bottom:12px}.smart-help-text{font-size:13px;color:#6b7280;margin-top:5px}.form-control{min-height:44px;border-radius:8px;border-color:#cbd5e1;font-size:16px}.form-control:focus{border-color:#3598DB;box-shadow:0 0 0 3px rgba(53,152,219,.16)}textarea.form-control{min-height:88px}.field-error .form-control{border:2px solid #dc2626;background:#fff7f7}.field-error label{color:#991b1b}.field-error-message{display:flex;align-items:flex-start;gap:7px;color:#b91c1c;font-size:13px;font-weight:700;margin-top:6px}.field-error-message:before{content:'↳';font-size:18px;line-height:14px}.form-alert{border-radius:12px;font-weight:700;border-left:5px solid #b91c1c}.recaptcha-error{border:2px solid #dc2626!important}.portal-footer{margin-top:24px;background:#fff;border-radius:14px;padding:20px;text-align:center;box-shadow:0 10px 28px rgba(0,0,0,.10);color:#64748b}.portal-footer a{color:#3598DB;font-weight:700}.footer-links{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:8px}.safe-area{padding-bottom:env(safe-area-inset-bottom)}@media(max-width:767px){body{padding:10px}.portal-header,.portal-card{padding:18px;border-radius:14px}.logo-stage{min-width:0;width:100%;min-height:90px}.portal-logo{max-width:230px;max-height:72px}.portal-title{font-size:24px}.language-box{text-align:center}.portal-actions{display:grid;grid-template-columns:1fr}.portal-actions .btn{width:100%;min-height:44px}.form-group{margin-bottom:18px}.portal-footer{padding:16px}.btn-lg{font-size:17px;min-height:50px}.g-recaptcha{transform-origin:left top;max-width:100%;overflow:hidden}}@media(max-width:360px){.g-recaptcha{transform:scale(.86);width:304px}.portal-logo{max-width:205px}}
    
body{background-color:#e8ecef;background-image:linear-gradient(135deg,rgba(14,111,91,.045) 25%,transparent 25%),linear-gradient(225deg,rgba(53,152,219,.04) 25%,transparent 25%),linear-gradient(45deg,rgba(242,140,40,.035) 25%,transparent 25%),linear-gradient(315deg,rgba(31,41,55,.035) 25%,#e8ecef 25%);background-position:22px 0,22px 0,0 0,0 0;background-size:44px 44px;background-repeat:repeat}.required-star{color:#dc2626;font-size:20px;font-weight:900;line-height:1;vertical-align:-2px}.submit-row{text-align:center;margin:18px 0}.submit-row .btn-smart{display:inline-flex;align-items:center;justify-content:center;width:auto;min-width:280px;max-width:100%;padding:13px 28px}.upload-security-grid{display:block}.upload-security-grid .portal-card{margin:0}.security-block{margin-top:18px;padding-top:18px;border-top:1px solid #e5e7eb}.profile-help-combined{font-size:12px;line-height:1.45;text-align:justify;color:#64748b;margin-top:7px}.address-line{height:44px;min-height:44px;resize:none}.profile-picture-note{display:flex;gap:10px;align-items:flex-start;margin-top:8px;color:#475569}.profile-picture-note i{font-size:22px;color:#3598DB}.address-line{resize:none;height:44px;min-height:44px}.success-burst{position:fixed;inset:0;pointer-events:none;overflow:hidden;z-index:9999}.success-burst i{position:absolute;top:-40px;width:12px;height:18px;border-radius:2px;animation:paperFall 3.2s linear forwards}.success-burst i:nth-child(3n){background:#3598DB}.success-burst i:nth-child(3n+1){background:#F28C28}.success-burst i:nth-child(3n+2){background:#169179}@keyframes paperFall{to{transform:translate(var(--drift),110vh) rotate(780deg);opacity:.15}}@media(max-width:767px){.upload-security-grid{display:block}.submit-row .btn-smart{width:100%;min-width:0}.required-star{font-size:18px}}
.btn-smart{background:var(--ss-success)!important}.btn-login{background:var(--ss-secondary)!important}.portal-header{border-bottom-color:var(--ss-primary)!important}.profile-image{border-color:var(--ss-primary)!important}</style>
</head>
<body>
<div class="portal-wrap safe-area">
    <div class="language-box">
        <strong><?php echo _l('smartsource_language'); ?>:</strong>
        <a href="?lang=en" class="btn btn-xs <?php echo $lang === 'en' ? 'btn-primary' : 'btn-default'; ?>">English</a>
        <a href="?lang=es" class="btn btn-xs <?php echo $lang === 'es' ? 'btn-primary' : 'btn-default'; ?>">Español</a>
    </div>

    <?php if (!empty($field_errors) || $portal_error !== '') { ?>
        <div class="alert alert-danger form-alert" role="alert">
            <div><?php echo html_escape(isset($field_errors['form']) ? $field_errors['form'] : ($portal_error === 'security' ? _l('smartsource_security_verification_failed') : _l('smartsource_form_review_errors'))); ?></div>
            <?php $visibleErrors = array_filter($field_errors, static function($key){ return $key !== 'form'; }, ARRAY_FILTER_USE_KEY); if (!empty($visibleErrors)) { ?><ul style="margin:8px 0 0 18px;padding:0"><?php foreach ($visibleErrors as $fieldMessage) { ?><li><?php echo html_escape($fieldMessage); ?></li><?php } ?></ul><?php } ?>
            <div class="small" style="margin-top:5px"><?php echo _l('smartsource_form_values_preserved'); ?></div>
        </div>
    <?php } ?>

    <div class="portal-header text-center">
        <div class="logo-stage" id="smartLogoStage"><img src="<?php echo html_escape($logo_url); ?>" class="portal-logo" alt="Smart Choice Contractors USA"></div>
        <?php if (!$is_register && $value($subcontractor, 'profile_image', $submitted_data) !== '') { ?>
            <div><img src="<?php echo base_url($value($subcontractor, 'profile_image', $submitted_data)); ?>" class="profile-image" alt="<?php echo html_escape(_l('smartsource_profile_picture')); ?>"></div>
        <?php } ?>
        <h2 class="portal-title"><?php echo html_escape($page_title); ?></h2>
        <p><?php echo html_escape($page_subtitle); ?></p>
        <div class="portal-actions">
            <a href="<?php echo html_escape($home_url); ?>" class="btn btn-home" target="_blank" rel="noopener"><?php echo _l('smartsource_visit_website'); ?></a>
            <a href="<?php echo html_escape($login_url); ?>" class="btn btn-login"><?php echo _l('smartsource_login_account'); ?></a>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" novalidate>
        <input type="text" name="smartsource_website" value="" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1" autocomplete="off">
        <?php $CI =& get_instance(); if (isset($CI->security)) { ?>
            <input type="hidden" name="<?php echo $CI->security->get_csrf_token_name(); ?>" value="<?php echo $CI->security->get_csrf_hash(); ?>">
        <?php } ?>
        <div class="portal-card">
            <h4><?php echo _l('smartsource_company_information'); ?></h4>
            <div class="row">
                <?php $e=$error('company',$field_errors); ?><div class="col-md-6 form-group <?php echo $e!==''?'field-error':''; ?>"><label><?php echo _l('smartsource_company_name'); ?> <span class="required-star">*</span></label><input name="company" class="form-control" value="<?php echo html_escape($value($subcontractor,'company',$submitted_data)); ?>" required><?php if($e!==''){ ?><div class="field-error-message"><?php echo html_escape($e); ?></div><?php } ?></div>
                <?php $e=$error('contact_name',$field_errors); ?><div class="col-md-6 form-group <?php echo $e!==''?'field-error':''; ?>"><label><?php echo _l('smartsource_contact_name'); ?> <span class="required-star">*</span></label><input name="contact_name" class="form-control" value="<?php echo html_escape($value($subcontractor,'contact_name',$submitted_data)); ?>" required><?php if($e!==''){ ?><div class="field-error-message"><?php echo html_escape($e); ?></div><?php } ?></div>
                <?php $e=$error('email',$field_errors); ?><div class="col-md-6 form-group <?php echo $e!==''?'field-error':''; ?>"><label><?php echo _l('smartsource_email'); ?> <span class="required-star">*</span></label><input name="email" type="email" inputmode="email" autocomplete="email" class="form-control" value="<?php echo html_escape($value($subcontractor,'email',$submitted_data)); ?>" required><?php if($e!==''){ ?><div class="field-error-message"><?php echo html_escape($e); ?></div><?php } ?></div>
                <?php $e=$error('phone',$field_errors); ?><div class="col-md-6 form-group <?php echo $e!==''?'field-error':''; ?>"><label><?php echo _l('smartsource_phone'); ?> <span class="required-star">*</span></label><input name="phone" type="tel" inputmode="tel" autocomplete="tel" class="form-control" value="<?php echo html_escape($value($subcontractor,'phone',$submitted_data)); ?>" required><?php if($e!==''){ ?><div class="field-error-message"><?php echo html_escape($e); ?></div><?php } ?></div>
                <div class="col-md-6 form-group"><label><?php echo _l('smartsource_trade'); ?></label><input name="trade" class="form-control" value="<?php echo html_escape($value($subcontractor,'trade',$submitted_data)); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo _l('smartsource_license_number'); ?></label><input name="license_number" class="form-control" value="<?php echo html_escape($value($subcontractor,'license_number',$submitted_data)); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo _l('smartsource_dbpr_verification_link'); ?></label><input name="dbpr_link" type="url" inputmode="url" class="form-control" placeholder="www.myfloridalicense.com/..." value="<?php echo html_escape($value($subcontractor,'dbpr_link',$submitted_data)); ?>"><div class="smart-help-text"><?php echo _l('smartsource_url_help'); ?></div></div>
                <div class="col-md-6 form-group"><label><?php echo _l('smartsource_county_verification_link'); ?></label><input name="county_license_link" type="url" inputmode="url" class="form-control" value="<?php echo html_escape($value($subcontractor,'county_license_link',$submitted_data)); ?>"><div class="smart-help-text"><?php echo _l('smartsource_url_help'); ?></div></div>
                <div class="col-md-6 form-group"><label><?php echo _l('smartsource_insurance_expiration'); ?></label><input name="insurance_expiration" type="date" class="form-control" value="<?php echo html_escape($value($subcontractor,'insurance_expiration',$submitted_data)); ?>"></div>
                <div class="col-md-6 form-group"><label><i class="fa fa-picture-o" aria-hidden="true"></i> <?php echo _l('smartsource_profile_picture'); ?></label><input type="file" name="profile_image_file" class="form-control" accept="image/jpeg,image/png,image/webp" capture="environment"><div class="profile-picture-note"><i class="fa fa-id-badge" aria-hidden="true"></i><span class="profile-help-combined"><?php echo _l('smartsource_profile_picture_combined_help'); ?></span></div></div>
                <div class="col-md-12 form-group"><label><?php echo _l('smartsource_address'); ?></label><input name="address" type="text" maxlength="50" class="form-control address-line" autocomplete="street-address" value="<?php echo html_escape($value($subcontractor,'address',$submitted_data)); ?>"></div>
                <div class="col-md-4 form-group"><label><?php echo _l('smartsource_city'); ?></label><input name="city" autocomplete="address-level2" class="form-control" value="<?php echo html_escape($value($subcontractor,'city',$submitted_data)); ?>"></div>
                <div class="col-md-4 form-group"><label><?php echo _l('smartsource_state'); ?></label><?php $stateValue=$value($subcontractor,'state',$submitted_data); if(trim((string)$stateValue)===''){ $stateValue='Florida'; } ?><input name="state" autocomplete="address-level1" class="form-control" value="<?php echo html_escape($stateValue); ?>"></div>
                <div class="col-md-4 form-group"><label><?php echo _l('smartsource_zip'); ?></label><input name="zip" inputmode="numeric" autocomplete="postal-code" class="form-control" value="<?php echo html_escape($value($subcontractor,'zip',$submitted_data)); ?>"></div>
            </div>
        </div>
        <?php if (!empty($portal_questions)) { ?><div class="portal-card"><h4><i class="fa fa-list-alt"></i> <?php echo _l('smartsource_additional_questions'); ?></h4><div class="row"><?php $postedQuestions=(array)($submitted_data['portal_questions']??[]); foreach($portal_questions as $q){ if(empty($q['active'])){continue;} $key=(string)$q['key']; $lang=(strtolower((string)$this->input->get('lang'))==='es'||strtolower((string)get_option('active_language'))==='spanish')?'es':'en'; $label=(string)($q['label_'.$lang]??$q['label_en']??''); $err=$error('portal_questions_'.$key,$field_errors); ?><div class="col-md-6 form-group <?php echo $err!==''?'field-error':''; ?>"><label><?php echo html_escape($label); ?><?php if(!empty($q['required'])){ ?><span class="required-star">*</span><?php } ?></label><?php if(($q['type']??'text')==='textarea'){ ?><textarea name="portal_questions[<?php echo html_escape($key); ?>]" class="form-control" rows="3"><?php echo html_escape($postedQuestions[$key]??''); ?></textarea><?php } else { ?><input type="<?php echo in_array(($q['type']??'text'),['email','date'],true)?html_escape($q['type']):'text'; ?>" name="portal_questions[<?php echo html_escape($key); ?>]" class="form-control" value="<?php echo html_escape($postedQuestions[$key]??''); ?>"><?php } ?><?php if($err!==''){ ?><div class="field-error-message"><?php echo html_escape($err); ?></div><?php } ?></div><?php } ?></div></div><?php } ?>
        <div class="upload-security-grid">
            <div class="portal-card">
                <h4><?php echo _l('smartsource_upload_documents'); ?></h4>
                <p class="text-muted"><?php echo _l('smartsource_upload_documents_note'); ?></p>
                <input type="file" name="file[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv">
                <div class="smart-help-text"><?php echo _l('smartsource_file_reselect_notice'); ?></div>
                <hr><h5><?php echo _l('smartsource_files_uploaded'); ?></h5>
                <?php if (empty($files)) { ?><p class="text-muted"><?php echo _l('smartsource_no_files'); ?></p><?php } else { ?><ul class="file-list"><?php foreach ($files as $file) { ?><li><?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?> <small class="text-muted"><?php echo html_escape($file['dateadded']); ?></small></li><?php } ?></ul><?php } ?>
                <?php if (function_exists('show_recaptcha') && show_recaptcha()) { $captchaError=$error('recaptcha',$field_errors); ?>
                    <div class="security-block <?php echo $captchaError!==''?'recaptcha-error':''; ?>"><h4><?php echo _l('smartsource_security_verification'); ?></h4><div class="g-recaptcha" data-sitekey="<?php echo html_escape(get_option('recaptcha_site_key')); ?>"></div><?php if($captchaError!==''){ ?><div class="field-error-message"><?php echo html_escape($captchaError); ?></div><?php } ?></div>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <?php } ?>
            </div>
        </div>
        <div class="submit-row"><button class="btn btn-smart btn-lg" type="submit"><?php echo $is_register ? _l('smartsource_create_profile') : _l('smartsource_save_profile'); ?></button></div>
    </form>

    <footer class="portal-footer">
        <strong><?php echo html_escape(get_option('companyname')); ?></strong><br>
        <?php echo _l('smartsource_portal_footer_text'); ?>
        <div class="footer-links"><a href="<?php echo html_escape($home_url); ?>" target="_blank" rel="noopener"><?php echo _l('smartsource_visit_website'); ?></a><a href="<?php echo html_escape($login_url); ?>"><?php echo _l('smartsource_login_account'); ?></a></div>
        <div style="margin-top:8px">&copy; <?php echo date('Y'); ?> <?php echo html_escape(get_option('companyname')); ?></div>
    </footer>
</div>
<script>
(function(){
    var stage=document.getElementById('smartLogoStage');
    if(stage && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){for(var i=0;i<24;i++){var p=document.createElement('i');p.className='logo-particle';p.style.left=(18+Math.random()*64)+'%';p.style.top=(20+Math.random()*58)+'%';p.style.setProperty('--dx',((Math.random()-.5)*150)+'px');p.style.setProperty('--dy',((Math.random()-.5)*100)+'px');p.style.setProperty('--rot',((Math.random()*360)-180)+'deg');p.style.animationDelay=(Math.random()*2.8)+'s';stage.appendChild(p);}}
    var form=document.querySelector('form');if(!form){return;}var submitting=false;form.addEventListener('submit',function(e){if(submitting){e.preventDefault();return false;}submitting=true;var btn=form.querySelector('button[type="submit"]');if(btn){var original=btn.textContent;btn.disabled=true;btn.textContent='<?php echo html_escape(_l('smartsource_saving')); ?>';window.setTimeout(function(){if(document.visibilityState==='visible'){submitting=false;btn.disabled=false;btn.textContent=original;var alertBox=document.querySelector('.form-alert');if(!alertBox){alertBox=document.createElement('div');alertBox.className='alert alert-danger form-alert';form.parentNode.insertBefore(alertBox,form);}alertBox.textContent='<?php echo html_escape(_l('smartsource_registration_failed')); ?>';alertBox.scrollIntoView({behavior:'smooth',block:'center'});}},20000);}});
    var firstError=document.querySelector('.field-error,.recaptcha-error');if(firstError){setTimeout(function(){firstError.scrollIntoView({behavior:'smooth',block:'center'});var input=firstError.querySelector('input,textarea,select');if(input){input.focus({preventScroll:true});}},250);}
})();
</script>
</body>
</html>
