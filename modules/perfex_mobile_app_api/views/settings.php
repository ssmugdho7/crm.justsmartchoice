<?php defined('BASEPATH') or exit('No direct script access allowed');

$module_enabled = get_option(PERFEX_MOBILE_APP_API . '_enabled');
$require_auth_key = get_option(PERFEX_MOBILE_APP_API . '_require_auth_key');
$log_responses = get_option(PERFEX_MOBILE_APP_API . '_log_responses');
?>
<div class="form-group">
    <label class="control-label clearfix"><?php echo _l('module_enable_option'); ?></label>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_enabled_yes" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_enabled]" value="1" <?php echo ($module_enabled == '1') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_enabled_yes"><?php echo _l('settings_yes'); ?></label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_enabled_no" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_enabled]" value="0" <?php echo ($module_enabled == '0') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_enabled_no"><?php echo _l('settings_no'); ?></label>
    </div>
</div>

<div class="form-group">
    <label class="control-label clearfix">Require Auth-Key header</label>
    <p class="text-muted">Leave disabled unless your mobile app is configured to send the module Auth-Key header.</p>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_authkey_yes" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_require_auth_key]" value="1" <?php echo ($require_auth_key == '1') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_authkey_yes"><?php echo _l('settings_yes'); ?></label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_authkey_no" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_require_auth_key]" value="0" <?php echo ($require_auth_key != '1') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_authkey_no"><?php echo _l('settings_no'); ?></label>
    </div>
</div>

<div class="form-group">
    <label class="control-label clearfix">API request logging</label>
    <p class="text-muted">Enable only for troubleshooting. It can store request/response payloads in the api_logs table.</p>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_logs_yes" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_log_responses]" value="1" <?php echo ($log_responses == '1') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_logs_yes"><?php echo _l('settings_yes'); ?></label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="perfex_mobile_logs_no" name="settings[<?php echo PERFEX_MOBILE_APP_API; ?>_log_responses]" value="0" <?php echo ($log_responses != '1') ? 'checked' : ''; ?>>
        <label for="perfex_mobile_logs_no"><?php echo _l('settings_no'); ?></label>
    </div>
</div>

<hr>
<p><strong>API base URL:</strong> <code><?php echo site_url('perfex_mobile_app_api/perfex_mobile_app_api'); ?></code></p>
<p><strong>Health check:</strong> <code><?php echo site_url('perfex_mobile_app_api/perfex_mobile_app_api/health'); ?></code></p>
