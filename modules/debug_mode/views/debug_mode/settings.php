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
            <button type="button" class="btn btn-warning debug-mode-btn" id="debugModeOpenErrorModal">
                <i class="fa fa-exclamation-triangle"></i> CRM Errors
            </button>
            <a href="<?php echo admin_url('debug_mode/file_structure'); ?>" class="btn btn-default debug-mode-btn" target="_blank">
                <i class="fa fa-sitemap"></i> File Structure
            </a>
        </div>

        <div class="modal fade" id="debugModeErrorsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> CRM Error Report</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Recent CRM errors from today's and yesterday's log files.</p>
                        <textarea id="debugModeErrorText" class="form-control debug-mode-error-editor" rows="18" readonly>Loading error report...</textarea>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>" target="_blank" class="btn btn-info">Open Full Page</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <div class="checkbox checkbox-primary">
                    <input type="hidden" name="settings[debug_mode_enabled]" value="0">
                    <input type="checkbox" id="debug_mode_enabled" name="settings[debug_mode_enabled]" value="1" <?php echo debug_mode_is_enabled() ? 'checked' : ''; ?>>
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

<script>
(function(){
  var btn = document.getElementById('debugModeOpenErrorModal');
  if (!btn) return;
  btn.addEventListener('click', function(){
    if (window.jQuery) { $('#debugModeErrorsModal').modal('show'); }
    var target = document.getElementById('debugModeErrorText');
    if (target) target.value = 'Loading CRM error report...';
    $.getJSON(admin_url + 'debug_mode/crm_errors_json', function(resp){
      if (target) target.value = resp && resp.text ? resp.text : 'No recent CRM errors found.';
    }).fail(function(){
      if (target) target.value = 'Unable to load CRM error report. Open the full page instead.';
    });
  });
})();
</script>
