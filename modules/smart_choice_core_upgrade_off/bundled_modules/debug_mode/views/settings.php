<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s debug-mode-card">
    <div class="panel-body">
        <div class="debug-mode-settings-heading">
            <h4 class="tw-mt-0">CRM Utilities & Debug Tools</h4>
            <p class="text-muted">
                Control debugging, safe maintenance tools, client portal visibility, phpMyAdmin access, cache cleanup, and diagnostic reports from one place.
            </p>
        </div>

        <div class="debug-mode-actions-grid">
            <a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-primary debug-mode-btn">
                <i class="fa fa-wrench"></i> Open CRM Utilities
            </a>
            <a href="<?php echo admin_url('debug_mode/health'); ?>" class="btn btn-info debug-mode-btn" target="_blank">
                <i class="fa fa-heartbeat"></i> Health Check
            </a>
            <a href="<?php echo html_escape(get_option('debug_mode_phpmyadmin_url')); ?>" class="btn btn-success debug-mode-btn" target="_blank" rel="noopener noreferrer">
                <i class="fa fa-database"></i> Open phpMyAdmin
            </a>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_enabled" name="settings[debug_mode_enabled]" value="1" <?php echo get_option('debug_mode_enabled') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_enabled">Activate Debug Mode</label>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_show_admin_banner" name="settings[debug_mode_show_admin_banner]" value="1" <?php echo get_option('debug_mode_show_admin_banner') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_show_admin_banner">Show Debug Mode Banner/Notice</label>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" id="debug_mode_client_portal_enabled" name="settings[debug_mode_client_portal_enabled]" value="1" <?php echo get_option('debug_mode_client_portal_enabled') == '1' ? 'checked' : ''; ?>>
                    <label for="debug_mode_client_portal_enabled">Client Portal Enabled</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="debug_mode_log_level">Debug Level</label>
                    <select name="settings[debug_mode_log_level]" id="debug_mode_log_level" class="form-control">
                        <option value="basic" <?php echo get_option('debug_mode_log_level') == 'basic' ? 'selected' : ''; ?>>Basic</option>
                        <option value="detailed" <?php echo get_option('debug_mode_log_level') == 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                        <option value="developer" <?php echo get_option('debug_mode_log_level') == 'developer' ? 'selected' : ''; ?>>Developer</option>
                    </select>
                </div>
                <?php echo render_input('settings[debug_mode_phpmyadmin_url]', 'phpMyAdmin / Bluehost Link', get_option('debug_mode_phpmyadmin_url'), 'url'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?php echo render_input('settings[debug_mode_allowed_roles]', 'Allowed Role IDs', get_option('debug_mode_allowed_roles'), 'text', ['placeholder' => 'Example: 1,2,3']); ?>
            </div>
            <div class="col-md-6">
                <?php echo render_input('settings[debug_mode_allowed_staff]', 'Allowed Staff IDs', get_option('debug_mode_allowed_staff'), 'text', ['placeholder' => 'Example: 1,4,7']); ?>
            </div>
        </div>

        <div class="alert alert-warning">
            <strong>Important:</strong> These tools are for troubleshooting and maintenance. Cache cleanup preserves index.html files. Database actions are limited to CHECK, ANALYZE, OPTIMIZE, and REPAIR tables only.
        </div>
    </div>
</div>
