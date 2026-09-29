<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="debug-mode-header">
            <div>
                <h1>Debug Mode</h1>
                <p>Control CRM debugging, access roles, visibility, and troubleshooting options safely from Perfex CRM.</p>
            </div>
            <a href="<?php echo admin_url('debug_mode/health'); ?>" class="btn btn-default">Health Check</a>
        </div>

        <?php echo form_open(admin_url('debug_mode/settings')); ?>

        <div class="panel_s debug-mode-card">
            <div class="panel-body">
                <h4>Debug Mode Settings</h4>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_enabled" name="debug_mode_enabled" value="1" <?php echo get_option('debug_mode_enabled') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_enabled">Activate Debug Mode</label>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_client_visible" name="debug_mode_client_visible" value="1" <?php echo get_option('debug_mode_client_visible') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_client_visible">Show Debug Tools in Client Area</label>
                </div>

                <?php echo render_select('debug_mode_log_level', [
                    ['id' => 'basic', 'name' => 'Basic'],
                    ['id' => 'detailed', 'name' => 'Detailed'],
                    ['id' => 'developer', 'name' => 'Developer']
                ], ['id', 'name'], 'Debug Level', get_option('debug_mode_log_level') ?: 'basic'); ?>

                <?php echo render_input('debug_mode_allowed_roles', 'Allowed Role IDs', get_option('debug_mode_allowed_roles'), 'text', ['placeholder' => 'Example: 1,2,3']); ?>
                <?php echo render_input('debug_mode_allowed_staff', 'Allowed Staff IDs', get_option('debug_mode_allowed_staff'), 'text', ['placeholder' => 'Example: 1,4,7']); ?>

                <p class="text-muted">
                    Use this module only for troubleshooting. Keep it inactive when not needed.
                </p>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
