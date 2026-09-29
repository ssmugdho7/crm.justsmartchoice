<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="debug-mode-page">
            <div class="debug-mode-hero">
                <div>
                    <h1>CRM Utilities & Debug Tools</h1>
                    <p>Safe maintenance, diagnostics, cache cleanup, client portal control, database checks, and system reports.</p>
                </div>
                <div class="debug-mode-status-pill <?php echo get_option('debug_mode_enabled') === '1' ? 'active' : ''; ?>">
                    <?php echo get_option('debug_mode_enabled') === '1' ? 'Debug Mode Active' : 'Production Mode'; ?>
                </div>
            </div>

            <div class="debug-mode-grid">
                <div class="debug-mode-tile">
                    <h3><i class="fa fa-bug"></i> Debug Mode</h3>
                    <p>Enable or disable CRM debug mode. Use only while troubleshooting.</p>
                    <a class="btn btn-warning btn-sm" href="<?php echo admin_url('debug_mode/toggle_debug/on'); ?>">Activate</a>
                    <a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/toggle_debug/off'); ?>">Deactivate</a>
                </div>

                <div class="debug-mode-tile">
                    <h3><i class="fa fa-trash"></i> Cache Cleanup</h3>
                    <p>Deletes safe cache files while preserving index.html and .htaccess.</p>
                    <a class="btn btn-primary btn-sm _delete" href="<?php echo admin_url('debug_mode/clear_cache'); ?>">Clean Cache</a>
                    <small>Last cleared: <?php echo html_escape(get_option('debug_mode_cache_last_cleared')); ?></small>
                </div>

                <div class="debug-mode-tile">
                    <h3><i class="fa fa-users"></i> Client Portal</h3>
                    <p>Quickly enable or disable client portal login access.</p>
                    <a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/toggle_client_portal/on'); ?>">Enable Portal</a>
                    <a class="btn btn-danger btn-sm" href="<?php echo admin_url('debug_mode/toggle_client_portal/off'); ?>">Disable Portal</a>
                </div>

                <div class="debug-mode-tile">
                    <h3><i class="fa fa-database"></i> Bluehost phpMyAdmin</h3>
                    <p>Open the saved Bluehost/phpMyAdmin link in a new tab.</p>
                    <a class="btn btn-info btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo html_escape($phpmyadmin_url); ?>">Open phpMyAdmin</a>
                </div>
            </div>

            <div class="panel_s debug-mode-card">
                <div class="panel-body">
                    <h3>Safe Database Tools</h3>
                    <p class="text-muted">These actions do not delete business records. They check and maintain table health.</p>
                    <a class="btn btn-default" href="<?php echo admin_url('debug_mode/database_action/check'); ?>">Check Tables</a>
                    <a class="btn btn-default" href="<?php echo admin_url('debug_mode/database_action/analyze'); ?>">Analyze Tables</a>
                    <a class="btn btn-default" href="<?php echo admin_url('debug_mode/database_action/optimize'); ?>">Optimize Tables</a>
                    <a class="btn btn-default" href="<?php echo admin_url('debug_mode/database_action/repair'); ?>">Repair Tables</a>

                    <div class="table-responsive mtop20">
                        <table class="table table-striped">
                            <thead><tr><th>Table</th><th>Operation</th><th>Message</th></tr></thead>
                            <tbody>
                            <?php foreach ((array)$database_report as $row) { ?>
                                <tr>
                                    <td><?php echo html_escape($row['table']); ?></td>
                                    <td><?php echo html_escape($row['operation']); ?></td>
                                    <td><code><?php echo html_escape($row['message']); ?></code></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel_s debug-mode-card">
                <div class="panel-body">
                    <h3>Recent CRM Error Report</h3>
                    <p class="text-muted">Shows recent CodeIgniter/PHP log lines from today and yesterday.</p>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Log File</th><th>Error Line</th></tr></thead>
                            <tbody>
                            <?php if (empty($error_report)) { ?>
                                <tr><td colspan="2">No recent error lines found.</td></tr>
                            <?php } ?>
                            <?php foreach ((array)$error_report as $row) { ?>
                                <tr>
                                    <td><?php echo html_escape($row['file']); ?></td>
                                    <td><code><?php echo html_escape($row['line']); ?></code></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel_s debug-mode-card">
                <div class="panel-body">
                    <h3>Help Guide</h3>
                    <div class="debug-mode-help-grid">
                        <div><strong>Clean Cache</strong><p>Removes temporary CRM cache files. It keeps index.html and .htaccess safe.</p></div>
                        <div><strong>Check Tables</strong><p>Runs MySQL CHECK TABLE to identify table issues without changing records.</p></div>
                        <div><strong>Analyze Tables</strong><p>Updates table statistics so MySQL can make better query decisions.</p></div>
                        <div><strong>Optimize Tables</strong><p>Reorganizes table storage. Useful after large updates or imports.</p></div>
                        <div><strong>Repair Tables</strong><p>Attempts safe table repair. Use only after a backup if CHECK TABLE reports problems.</p></div>
                        <div><strong>Client Portal Toggle</strong><p>Controls the common Perfex disable_client_login option from this screen.</p></div>
                        <div><strong>Error Report</strong><p>Reads recent application logs so you can identify CRM errors faster.</p></div>
                        <div><strong>phpMyAdmin Link</strong><p>Opens your saved Bluehost phpMyAdmin link. Edit the link in Settings.</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
