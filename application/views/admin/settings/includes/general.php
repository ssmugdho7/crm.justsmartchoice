<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.sc-logo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-bottom:18px}.sc-logo-card{border:1px solid #dfe8ef;border-radius:8px;background:#fff;padding:12px;box-shadow:0 1px 2px rgba(15,23,42,.04)}.sc-logo-card h5{margin:0 0 8px;color:#1f3c88;font-size:13px;font-weight:700}.sc-logo-preview{height:112px;display:flex;align-items:center;justify-content:center;border:1px dashed #cfdce7;border-radius:6px;background:#f8fbfd;margin-bottom:9px;overflow:hidden}.sc-logo-preview img{max-height:96px!important;max-width:100%!important;width:auto!important;object-fit:contain!important}.sc-logo-preview.favicon{height:82px}.sc-logo-preview.favicon img{max-height:64px!important}.sc-logo-spec{margin:7px 0 9px;padding:6px 8px;border-left:3px solid #168dde;background:#f8fbfd;border-radius:4px;font-size:11px;line-height:1.4;color:#52606d}.sc-logo-actions{display:flex;align-items:center;justify-content:space-between;gap:8px}.sc-logo-file{font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:210px}.sc-logo-status{font-size:11px;margin-top:6px;min-height:16px}.sc-logo-status.ok{color:#08783e}.sc-logo-status.error{color:#b42318}
</style>
<?php
$company_logo_light = get_option('company_logo');
$company_logo_dark  = get_option('company_logo_dark');
$favicon             = get_option('favicon');
$company_logo_contract = get_option('company_logo_contract');
$companyPath         = get_upload_path_by_type('company');
function sc_logo_file_meta($filename, $basePath) {
    $meta = ['size' => '', 'dimensions' => ''];
    if (!$filename) { return $meta; }
    $file = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . basename($filename);
    if (!is_file($file)) { return $meta; }
    $meta['size'] = round(filesize($file) / 1024, 1) . ' KB';
    $info = @getimagesize($file);
    if ($info) { $meta['dimensions'] = $info[0] . ' × ' . $info[1] . ' px'; }
    return $meta;
}
$lightMeta = sc_logo_file_meta($company_logo_light, $companyPath);
$darkMeta  = sc_logo_file_meta($company_logo_dark, $companyPath);
$favMeta   = sc_logo_file_meta($favicon, $companyPath);
$contractMeta = sc_logo_file_meta($company_logo_contract, $companyPath);
?>
<div class="row"><div class="col-md-12">
<div class="sc-logo-grid">
    <div class="sc-logo-card" data-logo-card>
        <h5><?= _l('company_logo_light'); ?></h5>
        <div class="sc-logo-preview">
            <?php if ($company_logo_light) { ?><img data-logo-preview src="<?= base_url('uploads/company/' . $company_logo_light); ?>?v=<?= time(); ?>" alt="Light Logo"><?php } else { ?><span data-logo-empty>No logo uploaded</span><?php } ?>
        </div>
        <div class="sc-logo-spec">Transparent PNG or SVG preferred. Maximum 1600 × 600 pixels and 2 MB. Crop empty transparent margins.</div>
        <input type="file" name="company_logo" class="form-control input-sm sc-logo-input" accept=".jpg,.jpeg,.png,.gif,.svg" data-max-bytes="2097152" data-max-width="1600" data-max-height="600">
        <div class="sc-logo-status" data-logo-status><?php if ($company_logo_light) { echo e(trim($lightMeta['dimensions'] . ' ' . $lightMeta['size'])); } ?></div>
        <?php if ($company_logo_light && staff_can('delete', 'settings')) { ?><div class="text-right"><a href="<?= admin_url('settings/remove_company_logo'); ?>" class="_delete text-danger text-xs"><i class="fa fa-trash"></i> Remove</a></div><?php } ?>
    </div>
    <div class="sc-logo-card" data-logo-card>
        <h5><?= _l('company_logo_dark'); ?></h5>
        <div class="sc-logo-preview">
            <?php if ($company_logo_dark) { ?><img data-logo-preview src="<?= base_url('uploads/company/' . $company_logo_dark); ?>?v=<?= time(); ?>" alt="Dark Logo"><?php } else { ?><span data-logo-empty>No logo uploaded</span><?php } ?>
        </div>
        <div class="sc-logo-spec">White/light artwork on a transparent background. Maximum 1600 × 600 pixels and 2 MB.</div>
        <input type="file" name="company_logo_dark" class="form-control input-sm sc-logo-input" accept=".jpg,.jpeg,.png,.gif,.svg" data-max-bytes="2097152" data-max-width="1600" data-max-height="600">
        <div class="sc-logo-status" data-logo-status><?php if ($company_logo_dark) { echo e(trim($darkMeta['dimensions'] . ' ' . $darkMeta['size'])); } ?></div>
        <?php if ($company_logo_dark && staff_can('delete', 'settings')) { ?><div class="text-right"><a href="<?= admin_url('settings/remove_company_logo/dark'); ?>" class="_delete text-danger text-xs"><i class="fa fa-trash"></i> Remove</a></div><?php } ?>
    </div>

    <div class="sc-logo-card" data-logo-card>
        <h5>Contract Logo</h5>
        <div class="sc-logo-preview">
            <?php if ($company_logo_contract) { ?><img data-logo-preview src="<?= base_url('uploads/company/' . $company_logo_contract); ?>?v=<?= time(); ?>" alt="Contract Logo"><?php } else { ?><span data-logo-empty>No contract logo uploaded</span><?php } ?>
        </div>
        <div class="sc-logo-spec">Used by the <code>{contract_logo}</code> merge field in contracts and contract email templates. Transparent PNG or SVG preferred; maximum 1600 × 600 pixels and 2 MB.</div>
        <input type="file" name="company_logo_contract" class="form-control input-sm sc-logo-input" accept=".jpg,.jpeg,.png,.gif,.svg" data-max-bytes="2097152" data-max-width="1600" data-max-height="600">
        <div class="sc-logo-status" data-logo-status><?php if ($company_logo_contract) { echo e(trim($contractMeta['dimensions'] . ' ' . $contractMeta['size'])); } ?></div>
    </div>
    <div class="sc-logo-card" data-logo-card>
        <h5><?= _l('settings_general_favicon'); ?></h5>
        <div class="sc-logo-preview favicon">
            <?php if ($favicon) { ?><img data-logo-preview src="<?= base_url('uploads/company/' . $favicon); ?>?v=<?= time(); ?>" alt="Favicon"><?php } else { ?><span data-logo-empty>No favicon uploaded</span><?php } ?>
        </div>
        <div class="sc-logo-spec">Square PNG or ICO. Recommended 256 × 256 pixels; maximum 512 × 512 pixels and 512 KB.</div>
        <input type="file" name="favicon" class="form-control input-sm sc-logo-input" accept=".png,.ico,.jpg,.jpeg" data-max-bytes="524288" data-max-width="512" data-max-height="512">
        <div class="sc-logo-status" data-logo-status><?php if ($favicon) { echo e(trim($favMeta['dimensions'] . ' ' . $favMeta['size'])); } ?></div>
        <?php if ($favicon && staff_can('delete', 'settings')) { ?><div class="text-right"><a href="<?= admin_url('settings/remove_fv'); ?>" class="_delete text-danger text-xs"><i class="fa fa-trash"></i> Remove</a></div><?php } ?>
    </div>
</div>
<script>
(function(){
 document.querySelectorAll('.sc-logo-input').forEach(function(input){
   input.addEventListener('change', function(){
     var card=input.closest('[data-logo-card]'), status=card.querySelector('[data-logo-status]'), file=input.files&&input.files[0];
     if(!file){return;}
     status.className='sc-logo-status';
     if(file.size>Number(input.dataset.maxBytes||0)){status.textContent='File is too large.';status.classList.add('error');input.value='';return;}
     var isSvg=/svg/i.test(file.type)||/\.svg$/i.test(file.name);
     var reader=new FileReader();
     reader.onload=function(e){
       var applyPreview=function(){var img=card.querySelector('[data-logo-preview]');if(!img){img=document.createElement('img');img.setAttribute('data-logo-preview','');card.querySelector('.sc-logo-preview').innerHTML='';card.querySelector('.sc-logo-preview').appendChild(img);}img.src=e.target.result;status.textContent='Ready to upload: '+file.name+' ('+Math.round(file.size/1024)+' KB). Click Save Settings.';status.classList.add('ok');};
       if(isSvg){applyPreview();return;}
       var probe=new Image(); probe.onload=function(){if(probe.width>Number(input.dataset.maxWidth)||probe.height>Number(input.dataset.maxHeight)){status.textContent='Image dimensions exceed the allowed maximum.';status.classList.add('error');input.value='';return;}applyPreview();}; probe.onerror=function(){status.textContent='Invalid image file.';status.classList.add('error');input.value='';}; probe.src=e.target.result;
     };
     reader.readAsDataURL(file);
   });
 });
})();
</script>
		<?php $attrs = (get_option('companyname') != '' ? [] : ['autofocus' => true]); ?>
		<?= render_input('settings[companyname]', 'settings_general_company_name', get_option('companyname'), 'text', $attrs); ?>
		<hr />
		<?= render_input('settings[main_domain]', 'settings_general_company_main_domain', get_option('main_domain')); ?>
		<div class="mTop5"></div>
        <div class="form-group">
            <label for="sc_menu_icon_color" class="control-label"><?= _l('sc_menu_icon_color'); ?></label>
            <div class="input-group" style="max-width:240px">
                <span class="input-group-addon"><i class="fa fa-palette"></i></span>
                <input type="color" id="sc_menu_icon_color" name="settings[sc_menu_icon_color]" class="form-control" value="<?= e(get_option('sc_menu_icon_color') ?: '#169179'); ?>" style="height:38px;padding:4px">
            </div>
            <p class="text-muted mtop5"><?= _l('sc_menu_icon_color_help'); ?></p>
        </div>
        <hr />
		<?= render_input('settings[company_crm_domain]', 'Company CRM Domain', get_option('company_crm_domain') ?: 'https://crm.justsmartchoice.com/', 'url', ['placeholder'=>'https://crm.justsmartchoice.com/']); ?>

        <hr />
        <div class="panel panel-default">
          <div class="panel-heading"><strong>CRM API Settings</strong></div>
          <div class="panel-body">
            <p class="text-muted">This key is stored in the CRM database. Saving other settings does not replace or regenerate it.</p>
            <?= render_input('settings[crm_api_key]', 'CRM API Key', get_option('crm_api_key'), 'text', ['autocomplete'=>'off','placeholder'=>'Existing CRM API key']); ?>
            <?= render_input('settings[crm_api_base_url]', 'CRM API Base URL', get_option('crm_api_base_url') ?: site_url('api'), 'url'); ?>
            <?php render_yes_no_option('crm_api_enabled', 'Enable CRM API'); ?>
          </div>
        </div>
        <hr />
        <div class="panel panel-default sc-ai-settings-panel">
          <div class="panel-heading"><strong>AI Integration Settings</strong></div>
          <div class="panel-body">
            <p class="text-muted">Existing AI credentials are loaded from the CRM database. This upgrade does not generate, replace, or erase an existing key.</p>
            <?php render_yes_no_option('sc_ai_enabled', 'Enable AI Integration'); ?>
            <?= render_select('settings[sc_ai_provider]', [
                ['id'=>'openai','name'=>'OpenAI'],
                ['id'=>'anthropic','name'=>'Anthropic Claude'],
                ['id'=>'google','name'=>'Google Gemini'],
                ['id'=>'azure_openai','name'=>'Azure OpenAI'],
                ['id'=>'custom','name'=>'Custom/OpenAI-Compatible'],
            ], ['id','name'], 'AI Provider', get_option('sc_ai_provider') ?: 'openai'); ?>
            <?= render_input('settings[sc_ai_api_key]', 'AI API Key', get_option('sc_ai_api_key'), 'password', ['autocomplete'=>'new-password','placeholder'=>'Existing saved AI API key']); ?>
            <?= render_input('settings[sc_ai_model]', 'AI Model', get_option('sc_ai_model') ?: 'gpt-4.1-mini', 'text', ['placeholder'=>'Model name']); ?>
            <?= render_input('settings[sc_ai_base_url]', 'AI API Base URL', get_option('sc_ai_base_url'), 'url', ['placeholder'=>'Optional custom provider endpoint']); ?>
            <?= render_input('settings[sc_ai_organization]', 'AI Organization / Project ID', get_option('sc_ai_organization'), 'text', ['placeholder'=>'Optional']); ?>
          </div>
        </div>
		<hr />
		<?php render_yes_no_option('rtl_support_admin', 'settings_rtl_support_admin'); ?>
		<hr />
		<?php render_yes_no_option('rtl_support_client', 'settings_rtl_support_client'); ?>
		<hr />
		<?= render_input('settings[allowed_files]', 'settings_allowed_upload_file_types', get_option('allowed_files')); ?>
	</div>
</div>