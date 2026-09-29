<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="publishx-settings-pane publishx-settings-premium">
    <div class="publishx-settings-hero">
        <div>
            <span class="publishx-eyebrow">SMART CHOICE CONTENT SYSTEM</span>
            <h3><i class="fa-solid fa-blog"></i> <?php echo _l('publishx_settings'); ?></h3>
            <p>Control publishing defaults, brand presentation, artificial intelligence rules, source folders, integrations, media, and calls to action.</p>
        </div>
        <a class="btn btn-default btn-sm" href="<?php echo admin_url('publishx/how_to'); ?>"><i class="fa-regular fa-circle-question"></i> <?php echo _l('publishx_how_to'); ?></a>
    </div>

    <div class="publishx-settings-grid">
        <section class="publishx-setting-card">
            <h4><i class="fa-solid fa-pen-ruler"></i> Brand and Publishing</h4>
            <?php echo render_input('settings[publishx_blog_title]', 'Blog Title', get_option('publishx_blog_title')); ?>
            <?php echo render_textarea('settings[publishx_blog_description]', 'Blog Description', get_option('publishx_blog_description'), ['rows'=>3]); ?>
            <?php echo render_select('settings[publishx_default_language]', [['id'=>'English','name'=>'English'],['id'=>'Spanish','name'=>'Spanish']], ['id','name'], 'Default Language', get_option('publishx_default_language')); ?>
            <?php echo render_input('settings[publishx_default_author_name]', 'Default Author Name', get_option('publishx_default_author_name')); ?>
            <?php echo render_input('settings[publishx_default_cta_text]', 'Default Call-to-Action Text', get_option('publishx_default_cta_text')); ?>
            <?php echo render_input('settings[publishx_appointment_url]', 'Appointment URL', get_option('publishx_appointment_url')); ?>
        </section>

        <section class="publishx-setting-card">
            <h4><i class="fa-solid fa-palette"></i> Appearance</h4>
            <?php echo render_select('settings[publishx_logo_source]', [['id'=>'website','name'=>'Use Website Logo'],['id'=>'crm','name'=>'Use CRM Logo'],['id'=>'custom','name'=>'Custom Logo']], ['id','name'], 'Logo Source', get_option('publishx_logo_source')); ?>
            <?php echo render_select('settings[publishx_favicon_source]', [['id'=>'website','name'=>'Use Website Favicon'],['id'=>'crm','name'=>'Use CRM Favicon'],['id'=>'custom','name'=>'Custom Favicon']], ['id','name'], 'Favicon Source', get_option('publishx_favicon_source')); ?>
            <div class="row">
                <div class="col-md-6"><?php echo render_input('settings[publishx_primary_color]', 'Primary Color', get_option('publishx_primary_color'), 'color'); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_secondary_color]', 'Secondary Color', get_option('publishx_secondary_color'), 'color'); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_accent_color]', 'Accent Color', get_option('publishx_accent_color'), 'color'); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_success_color]', 'Success Color', get_option('publishx_success_color'), 'color'); ?></div>
            </div>
            <?php echo render_select('settings[publishx_card_radius]', [['id'=>'8','name'=>'Compact'],['id'=>'14','name'=>'Professional'],['id'=>'22','name'=>'Soft']], ['id','name'], 'Card Corner Style', get_option('publishx_card_radius')); ?>
        </section>

        <section class="publishx-setting-card publishx-setting-card-wide">
            <h4><i class="fa-solid fa-robot"></i> AI Content and SEO Rules</h4>
            <div class="checkbox checkbox-primary">
                <input type="hidden" name="settings[publishx_use_crm_openai_key]" value="0">
                <input type="checkbox" name="settings[publishx_use_crm_openai_key]" value="1" <?php echo get_option('publishx_use_crm_openai_key')==='1'?'checked':''; ?>>
                <label>Use the AI credentials already configured in the CRM</label>
            </div>
            <?php echo render_input('settings[publishx_openai_key]', 'Module AI Key Fallback', get_option('publishx_openai_key'), 'password'); ?>
            <div class="row">
                <div class="col-md-6"><?php echo render_input('settings[publishx_ai_provider]', 'AI Provider', get_option('publishx_ai_provider')); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_ai_model]', 'AI Model', get_option('publishx_ai_model')); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_ai_base_url]', 'AI Base URL', get_option('publishx_ai_base_url')); ?></div>
                <div class="col-md-6"><?php echo render_input('settings[publishx_ai_organization]', 'AI Organization', get_option('publishx_ai_organization')); ?></div>
            </div>
            <?php echo render_textarea('settings[publishx_ai_global_instructions]', 'Global AI Instructions', get_option('publishx_ai_global_instructions'), ['rows'=>5]); ?>
            <?php echo render_textarea('settings[publishx_ai_disallowed_topics]', 'Topics or Claims AI Must Avoid', get_option('publishx_ai_disallowed_topics'), ['rows'=>3]); ?>
            <?php echo render_textarea('settings[publishx_ai_required_facts]', 'Required Brand Facts and Disclosures', get_option('publishx_ai_required_facts'), ['rows'=>3]); ?>
            <?php echo render_input('settings[publishx_ai_knowledge_folder]', 'AI Knowledge Folder', get_option('publishx_ai_knowledge_folder')); ?>
            <p class="text-muted">Absolute server path or approved website folder used as a reference source. Existing API values are never overwritten during upgrades.</p>
        </section>

        <section class="publishx-setting-card">
            <h4><i class="fa-solid fa-chart-line"></i> Analytics and Tracking</h4>
            <?php echo render_input('settings[publishx_ga_measurement_id]', 'Default GA4 Measurement ID', get_option('publishx_ga_measurement_id')); ?>
            <div class="checkbox checkbox-primary"><input type="hidden" name="settings[publishx_enable_view_tracking]" value="0"><input type="checkbox" name="settings[publishx_enable_view_tracking]" value="1" <?php echo get_option('publishx_enable_view_tracking')==='1'?'checked':''; ?>><label>Enable post view tracking</label></div>
            <?php echo render_input('settings[publishx_default_target_folder]', 'Default Website Publishing Folder', get_option('publishx_default_target_folder')); ?>
        </section>

        <section class="publishx-setting-card">
            <h4><i class="fa-solid fa-video"></i> Media Publishing</h4>
            <div class="checkbox checkbox-primary"><input type="hidden" name="settings[publishx_allow_youtube]" value="0"><input type="checkbox" name="settings[publishx_allow_youtube]" value="1" <?php echo get_option('publishx_allow_youtube')==='1'?'checked':''; ?>><label>Allow YouTube videos</label></div>
            <div class="checkbox checkbox-primary"><input type="hidden" name="settings[publishx_allow_vimeo]" value="0"><input type="checkbox" name="settings[publishx_allow_vimeo]" value="1" <?php echo get_option('publishx_allow_vimeo')==='1'?'checked':''; ?>><label>Allow Vimeo videos</label></div>
            <div class="checkbox checkbox-primary"><input type="hidden" name="settings[publishx_allow_video_upload]" value="0"><input type="checkbox" name="settings[publishx_allow_video_upload]" value="1" <?php echo get_option('publishx_allow_video_upload')==='1'?'checked':''; ?>><label>Allow direct video uploads</label></div>
        </section>
    </div>
</div>
<style>
.publishx-settings-premium{max-width:1200px}.publishx-settings-hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;padding:24px;margin-bottom:20px;border-radius:16px;background:linear-gradient(135deg,#fff 0%,#f5fbfa 58%,#edf6fc 100%);border:1px solid #dce9e7;box-shadow:0 10px 28px rgba(14,111,91,.08)}.publishx-settings-hero h3{margin:5px 0 8px}.publishx-settings-hero p{margin:0;color:#64748b}.publishx-eyebrow{color:#f28c28;font-weight:800;font-size:11px;letter-spacing:.14em}.publishx-settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.publishx-setting-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 7px 20px rgba(15,23,42,.05)}.publishx-setting-card-wide{grid-column:1/-1}.publishx-setting-card h4{margin:0 0 18px;color:#0e6f5b;border-bottom:1px solid #eef2f7;padding-bottom:12px}.publishx-setting-card h4 i{color:#3598db;margin-right:8px}@media(max-width:900px){.publishx-settings-grid{grid-template-columns:1fr}.publishx-setting-card-wide{grid-column:auto}.publishx-settings-hero{display:block}.publishx-settings-hero .btn{margin-top:14px}}
</style>
