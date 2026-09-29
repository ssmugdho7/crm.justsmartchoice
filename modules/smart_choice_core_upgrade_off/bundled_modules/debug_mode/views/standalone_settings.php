<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('debug_mode/settings')); ?>
        <div class="panel_s debug-mode-card">
            <div class="panel-body">
                <div class="debug-mode-settings-heading">
                    <h4 class="tw-mt-0">Debug Mode Settings</h4>
                    <p class="text-muted">Use this fallback page if your Perfex Settings list shows the title but does not open the settings section.</p>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_enabled" name="debug_mode_enabled" value="1" <?php echo get_option('debug_mode_enabled') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_enabled">Activate Debug Mode</label>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_client_visible" name="debug_mode_client_visible" value="1" <?php echo get_option('debug_mode_client_visible') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_client_visible">Show Debug Tools in Client Area</label>
                </div>

                <div class="form-group">
                    <label for="debug_mode_log_level">Debug Level</label>
                    <select name="debug_mode_log_level" id="debug_mode_log_level" class="form-control">
                        <option value="basic" <?php echo get_option('debug_mode_log_level') == 'basic' ? 'selected' : ''; ?>>Basic</option>
                        <option value="detailed" <?php echo get_option('debug_mode_log_level') == 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                        <option value="developer" <?php echo get_option('debug_mode_log_level') == 'developer' ? 'selected' : ''; ?>>Developer</option>
                    </select>
                </div>

                <?php echo render_input('debug_mode_allowed_roles', 'Allowed Role IDs', get_option('debug_mode_allowed_roles'), 'text', ['placeholder' => 'Example: 1,2,3']); ?>
                <?php echo render_input('debug_mode_allowed_staff', 'Allowed Staff IDs', get_option('debug_mode_allowed_staff'), 'text', ['placeholder' => 'Example: 1,4,7']); ?>

                <button type="submit" class="btn btn-primary">Save Settings</button>
                <a href="<?php echo admin_url('settings?group=debug_mode'); ?>" class="btn btn-default">Back to CRM Settings</a>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
