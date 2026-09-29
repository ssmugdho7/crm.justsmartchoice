<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php $this->load->config('whatsbot/openai'); ?>
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#whatsapp_auto_lead" aria-controls="whatsapp_auto_lead" role="tab" data-toggle="tab">
                    <?php echo _l('whatsapp_auto_lead'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#webhooks" aria-controls="webhooks" role="tab" data-toggle="tab">
                    <?php echo _l('webhooks'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#supportagent" aria-controls="supportagent" role="tab" data-toggle="tab">
                    <?php echo _l('supportagent'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#notification_sound" aria-controls="notification_sound" role="tab" data-toggle="tab">
                    <?php echo _l('notification_sound'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#ai_integration" aria-controls="ai_integration" role="tab" data-toggle="tab">
                    <?php echo _l('ai_integration'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#ai_assistent" aria-controls="ai_assistent" role="tab" data-toggle="tab">
                    <?php echo _l('ai_assistant'); ?>
                </a>
            </li>
            <li role="presentation" class="">
                <a href="#clear_chat_history" aria-controls="clear_chat_history" role="tab" data-toggle="tab">
                    <?php echo _l('auto_clear_chat_history'); ?>
                </a>
            </li>

        </ul>
    </div>
</div>
<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="whatsapp_auto_lead">
        <div class="mbot15">
            <label for="whatsapp_auto_lead_settings"><?php echo _l('convert_whatsapp_message_to_lead'); ?></label>
            <div class="onoffswitch">
                <input type="checkbox" value="1" class="onoffswitch-checkbox" id="whatsapp_auto_lead_settings" name="settings[whatsapp_auto_lead_settings]" <?php echo ('1' == get_option('whatsapp_auto_lead_settings')) ? 'checked' : ''; ?>>
                <label class="onoffswitch-label" for="whatsapp_auto_lead_settings"></label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?php echo render_select('settings[whatsapp_auto_leads_status]', $leads_statuses, ['id', 'name'], 'leads_status', get_option('whatsapp_auto_leads_status'), [], [], '', '', false); ?>
            </div>
            <div class="col-md-4">
                <?php echo render_select('settings[whatsapp_auto_leads_source]', $leads_sources, ['id', 'name'], 'leads_source', get_option('whatsapp_auto_leads_source'), [], [], '', '', false); ?>
            </div>
            <div class="col-md-4">
                <?php echo render_select('settings[whatsapp_auto_leads_assigned]', wb_get_all_staff(), ['staffid', ['firstname', 'lastname']], 'leads_assigned', get_option('whatsapp_auto_leads_assigned'), [], [], '', '', false); ?>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="webhooks">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_webhooks"><?php echo _l('enable_webhooks'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_webhooks" name="settings[enable_webhooks]" <?php echo ('1' == get_option('enable_webhooks')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_webhooks"></label>
                </div>
            </div>
            <?php $methods = [
                ['key' => 'GET', 'value' => 'GET'],
                ['key' => 'POST', 'value' => 'POST']
            ]; ?>
            <?= render_select('settings[webhook_resend_method]', $methods, ['key', 'value'], 'webhook_resend_method', get_option('webhook_resend_method'), [], [], 'col-md-4', '', false); ?>
            <div class="form-group col-md-12">
                <label for="settings[webhooks_url]" class="control-label"><?php echo _l('webhooks_label'); ?></label>
                <input type="text" id="settings[webhooks_url]" name="settings[webhooks_url]" class="form-control" value="<?php echo get_option('webhooks_url'); ?>">
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="supportagent">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_supportagnet"><?php echo _l('assign_chat_permission_to_support_agent'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_supportagent" name="settings[enable_supportagent]" <?php echo ('1' == get_option('enable_supportagent')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_supportagent"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <?= _l('support_agent_note'); ?>
                </div>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="notification_sound">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_notification_sound"><?php echo _l('enable_whatsapp_notification_sound'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_wtc_notification_sound" name="settings[enable_wtc_notification_sound]" <?php echo ('1' == get_option('enable_wtc_notification_sound')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_wtc_notification_sound"></label>
                </div>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="ai_integration">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_wb_openai"><?php echo _l('enable_wb_openai'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_wb_openai" name="settings[enable_wb_openai]" <?php echo ('1' == get_option('enable_wb_openai')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_wb_openai"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo render_input('settings[wb_open_ai_key]', 'open_ai_secret_key', get_option('wb_open_ai_key')); ?>
            </div>
        </div>
        <div class="row openai_model">
            <div class="col-md-6">
                <?php echo render_select('settings[wb_openai_model]', config_item('openai_models'), ['key', 'value'], 'chat_model', get_option('wb_openai_model'), [], [], '', '', false); ?>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="clear_chat_history">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_clear_chat_history"><?php echo _l('enable_auto_clear_chat_history'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_clear_chat_history" name="settings[enable_clear_chat_history]" <?php echo ('1' == get_option('enable_clear_chat_history')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_clear_chat_history"></label>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label for="settings[wb_auto_clear_time]" class="control-label"><?php echo _l('auto_clear_time'); ?></label>
                <div class="input-group">
                    <input type="number" id="settings[wb_auto_clear_time]" name="settings[wb_auto_clear_time]" class="form-control" min='1' value="<?php echo get_option('wb_auto_clear_time'); ?>">
                    <span class="input-group-addon"><?= _l('days'); ?></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <?= _l('clear_chat_history_note'); ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="alert alert-danger">
                    This feature requires a properly configured cron job. Before activating the feature, make sure that the <a
                        href="<?php echo admin_url('settings?group=cronjob'); ?>">cron job</a> is configured as explanation in
                    the documentation.
                </div>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="ai_assistent">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="enable_ai_assistant"><?php echo _l('enable_ai_assistant'); ?></label>
                <div class="onoffswitch">
                    <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_ai_assistant" name="settings[enable_ai_assistant]" <?php echo ('1' == get_option('enable_ai_assistant')) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="enable_ai_assistant"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo render_input('settings[stop_ai_assistant]', 'stop_ai_assistant', get_option('stop_ai_assistant')); ?>
            </div>
            <div class="col-md-6">
                <div class="range-container tw-flex tw-gap-6 tw-items-center tw-justify-between">
                    <div class="tw-flex tw-flex-col tw-justify-center width400">
                        <label for="temperature" class="form-label"><i class="fa-regular fa-circle-question tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('temperature_note'); ?>" data-placement="top"></i><?php echo _l('temperature'); ?></label>
                        <input type="range" name="settings[pa_temperature]" id="temperature" min="0.1" max="2.0" step="0.1" value="<?= get_option('pa_temperature') ?>" oninput="updateValue('temperature', this.value)">
                    </div>
                    <div class="tw-border tw-border-neutral-300/80 tw-border-solid tw-px-4 tw-py-1 tw-rounded">
                        <span class="range-value" id="temperature-value"><?= get_option('pa_temperature') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo render_select('settings[wb_pa_model]', config_item('openai_models'), ['key', 'value'], 'ai_model', get_option('wb_pa_model'), [], [], '', '', false); ?>
            </div>
            <div class="col-md-6">
                <div class="range-container tw-flex tw-gap-6 tw-items-center tw-justify-between">
                    <div class="tw-flex tw-flex-col tw-justify-center width400">
                        <label for="max-token" class="form-label"><i class="fa-regular fa-circle-question tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('max_tokens_note'); ?>" data-placement="top"></i><?php echo _l('max_token'); ?></label>
                        <input type="range" name="settings[pa_max_token]" id="max-token" min="1" max="4096" step="1" value="<?= get_option('pa_max_token') ?>" oninput="updateValue('max-token', this.value)">
                    </div>
                    <div class="tw-border tw-border-neutral-300/80 tw-border-solid tw-px-4 tw-py-1 tw-rounded">
                        <span class="range-value" id="max-token-value"><?= get_option('pa_max_token') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function updateValue(id, value) {
        document.getElementById(id + '-value').innerText = value;
    }
</script>
