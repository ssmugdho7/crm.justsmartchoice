<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <h3><i class="fa fa-cog"></i> <?php echo _l('sc_ai_repair_settings'); ?></h3>
                        <p class="text-muted"><?php echo _l('sc_ai_repair_settings_help'); ?></p>
                        <hr>
                        <?php echo form_open(admin_url('sc_ai_repair/settings')); ?>

                        <?php echo render_input('crm_root', _l('sc_ai_repair_crm_root'), get_option('sc_ai_repair_crm_root'), 'text', [
                            'placeholder' => '/home/account/public_html/crm.justsmartchoice',
                        ]); ?>
                        <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_crm_root_help'); ?></p>

                        <?php echo render_input('crm_url', _l('sc_ai_repair_crm_url'), get_option('sc_ai_repair_crm_url'), 'url', [
                            'placeholder' => 'https://crm.smartchoice.com',
                        ]); ?>
                        <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_crm_url_help'); ?></p>

                        <hr>
                        <?php echo render_select('provider', [
                            ['id' => 'openai', 'name' => 'OpenAI'],
                        ], ['id', 'name'], _l('sc_ai_repair_provider'), get_option('sc_ai_repair_provider')); ?>
                        <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_provider_help'); ?></p>

                        <?php echo render_input('model', _l('sc_ai_repair_model'), get_option('sc_ai_repair_model') ?: 'gpt-5-mini'); ?>
                        <?php echo render_input('base_url', _l('sc_ai_repair_base_url'), get_option('sc_ai_repair_base_url') ?: 'https://api.openai.com/v1', 'url'); ?>
                        <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_base_url_help'); ?></p>
                        <?php echo render_input('organization', _l('sc_ai_repair_organization'), get_option('sc_ai_repair_organization')); ?>

                        <div class="form-group">
                            <label for="api_key"><?php echo _l('sc_ai_repair_api_key'); ?></label>
                            <div class="input-group">
                                <input type="password" id="api_key" name="api_key" class="form-control" autocomplete="new-password" value="<?php echo html_escape(get_option('sc_ai_repair_api_key')); ?>">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="scair-key-view" title="<?php echo _l('sc_ai_repair_view_key'); ?>"><i class="fa fa-eye"></i></button>
                                    <button type="button" class="btn btn-default" id="scair-key-copy" title="<?php echo _l('sc_ai_repair_copy_key'); ?>"><i class="fa fa-copy"></i></button>
                                </span>
                            </div>
                            <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_api_key_help'); ?> The module also checks existing CRM OpenAI/ChatGPT key options automatically when this field is empty.</p>
                        </div>

                        <?php echo render_input('max_file_bytes', _l('sc_ai_repair_max_file_bytes'), get_option('sc_ai_repair_max_file_bytes'), 'number', [
                            'min' => 65536,
                            'max' => 2097152,
                            'step' => 1024,
                        ]); ?>
                        <p class="text-muted mtop-5"><?php echo _l('sc_ai_repair_max_file_bytes_help'); ?></p>

                        <?php echo render_yes_no_option('sc_ai_repair_auto_backup', _l('sc_ai_repair_auto_backup')); ?>
                        <?php echo render_yes_no_option('sc_ai_repair_require_approval', _l('sc_ai_repair_require_approval')); ?>
                        <?php echo render_yes_no_option('sc_ai_repair_scan_vendor', _l('sc_ai_repair_scan_vendor')); ?>
                        <div class="alert alert-info">
                            <strong><i class="fa fa-info-circle"></i> <?php echo _l('sc_ai_repair_scan_vendor'); ?>:</strong>
                            <?php echo _l('sc_ai_repair_scan_vendor_help'); ?>
                        </div>

                        <button class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function($){
    $('#scair-key-view').on('click', function(){
        var input = $('#api_key');
        var reveal = input.attr('type') === 'password';
        input.attr('type', reveal ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye', !reveal).toggleClass('fa-eye-slash', reveal);
    });
    $('#scair-key-copy').on('click', function(){
        var input = document.getElementById('api_key');
        var value = input.value || '';
        if (!value) { alert_float('warning', '<?php echo addslashes(_l('sc_ai_repair_api_key_empty')); ?>'); return; }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(function(){ alert_float('success', '<?php echo addslashes(_l('sc_ai_repair_key_copied')); ?>'); });
        } else {
            var previous = input.type; input.type = 'text'; input.select(); document.execCommand('copy'); input.type = previous;
            alert_float('success', '<?php echo addslashes(_l('sc_ai_repair_key_copied')); ?>');
        }
    });
})(jQuery);
</script>
</body></html>
