<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="debug-mode-page">
            <div class="debug-mode-hero debug-mode-hero-compact">
                <div>
                    <h1>CRM Utilities & Debug Tools</h1>
                    <p>Troubleshooting center for modules, errors, folders, backups, staff data, links, files, cache, and database checks.</p>
                </div>
                <div class="debug-mode-status-pill <?php echo debug_mode_is_enabled() ? 'active' : ''; ?>">
                    <?php echo debug_mode_is_enabled() ? 'Debug Mode Active' : 'Production Mode'; ?>
                </div>
            </div>

            <div class="debug-mode-bottom-nav debug-mode-top-nav">
                <a class="active" href="<?php echo admin_url('debug_mode'); ?>">Utilities</a>
                <a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a>
                <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a>
                <a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a>
                <a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
            </div>

            <div class="debug-mode-grid debug-mode-utility-grid">
                <div class="debug-mode-tile"><h3><i class="fa fa-bug"></i> Debug Mode</h3><p>Switch between troubleshooting and production behavior.</p><a class="btn btn-warning btn-sm" href="<?php echo admin_url('debug_mode/toggle_debug/on'); ?>">Activate</a> <a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/toggle_debug/off'); ?>">Deactivate</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-trash"></i> Cache Cleanup</h3><p>Clean cache safely while keeping index.html and .htaccess.</p><a class="btn btn-primary btn-sm _delete" href="<?php echo admin_url('debug_mode/clear_cache'); ?>">Clean Cache</a><small>Last: <?php echo html_escape(get_option('debug_mode_cache_last_cleared')); ?></small></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-database"></i> Database Backup</h3><p>Create a SQL backup file before maintenance.</p><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/database_backup'); ?>">Create Backup</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-users"></i> Staff Import / Export</h3><p>Export staff and open safe import guidance.</p><a class="btn btn-info btn-sm" href="<?php echo admin_url('debug_mode/export_staff'); ?>">Export Staff</a> <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/staff_import_help'); ?>">Import Guide</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-link"></i> Broken Links</h3><p>Search tables for common wrong URL patterns.</p><a class="btn btn-warning btn-sm" href="<?php echo admin_url('debug_mode/broken_links'); ?>">Scan Links</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-folder-open"></i> Orphan Files</h3><p>Find folders that may not connect to installed modules.</p><a class="btn btn-danger btn-sm" href="<?php echo admin_url('debug_mode/orphan_files'); ?>">Scan Orphans</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-exclamation-triangle"></i> CRM Errors</h3><p>Open searchable/copyable CRM log report.</p><a class="btn btn-warning btn-sm" target="_blank" href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Open Errors</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-sitemap"></i> File Structure</h3><p>Folder-only CRM map with HTML export.</p><a class="btn btn-default btn-sm" target="_blank" href="<?php echo admin_url('debug_mode/file_structure'); ?>">Open Structure</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-users"></i> Client Portal</h3><p>Enable or disable client portal login access.</p><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/toggle_client_portal/on'); ?>">Enable</a> <a class="btn btn-danger btn-sm" href="<?php echo admin_url('debug_mode/toggle_client_portal/off'); ?>">Disable</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-external-link"></i> cPanel / phpMyAdmin</h3><p>Open Bluehost server login. After login, open phpMyAdmin from cPanel.</p><a class="btn btn-info btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo html_escape($phpmyadmin_url ?: 'https://s111.bluehost.com:2083/'); ?>">Open Login</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-signal"></i> Network Tools</h3><p>Internet check, browser-to-CRM speed test, DNS flush commands, IP and route information.</p><a class="btn btn-primary btn-sm" href="<?php echo admin_url('debug_mode/network_tools'); ?>">Open Network Tools</a></div>
                <div class="debug-mode-tile"><h3><i class="fa fa-magic"></i> Safe Auto Repair</h3><p>Creates missing safe folders/options and adds required index.html files without deleting business data.</p><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/auto_repair'); ?>">Run Safe Repair</a></div>
            </div>

            <div class="panel_s debug-mode-card"><div class="panel-body">
                <h3>Safe Database Tools</h3>
                <p class="text-muted">Use these before and after module installs. Back up first before Optimize or Repair.</p>
                <div class="debug-mode-inline-actions">
                    <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/database_action/check'); ?>">Check Tables</a>
                    <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/database_action/analyze'); ?>">Analyze Tables</a>
                    <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/database_action/optimize'); ?>">Optimize Tables</a>
                    <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/database_action/repair'); ?>">Repair Tables</a>
                </div>
                <div class="table-responsive debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Table</th><th>Operation</th><th>Message</th></tr></thead><tbody>
                <?php foreach ((array)$database_report as $row) { ?><tr><td><?php echo html_escape($row['table']); ?></td><td><?php echo html_escape($row['operation']); ?></td><td><code><?php echo html_escape($row['message']); ?></code></td></tr><?php } ?>
                </tbody></table></div>
            </div></div>

            <?php if (!empty($broken_links)) { ?>
            <div class="panel_s debug-mode-card"><div class="panel-body"><h3>Broken Links Found</h3><div class="table-responsive debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Table</th><th>Field</th><th>Issue</th><th>Value</th></tr></thead><tbody><?php foreach ($broken_links as $row) { ?><tr><td><?php echo html_escape($row['table']); ?></td><td><?php echo html_escape($row['field']); ?></td><td><?php echo html_escape($row['issue']); ?></td><td><code><?php echo html_escape($row['value']); ?></code></td></tr><?php } ?></tbody></table></div></div></div>
            <?php } ?>

            <?php if (!empty($orphan_files)) { ?>
            <div class="panel_s debug-mode-card"><div class="panel-body"><h3>Possible Orphan Folders</h3><div class="alert alert-warning">Review carefully before deleting. This page does not auto-delete files.</div><div class="table-responsive debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Path</th><th>Status</th><th>Reason</th></tr></thead><tbody><?php foreach ($orphan_files as $row) { ?><tr><td><code><?php echo html_escape($row['path']); ?></code></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['reason']); ?></td></tr><?php } ?></tbody></table></div></div></div>
            <?php } ?>

            <div class="panel_s debug-mode-card"><div class="panel-body">
                <h3>Recent CRM Error Report</h3>
                <div class="debug-mode-inline-actions"><a class="btn btn-default btn-sm" target="_blank" href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Open Full Error Tool</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/export_errors'); ?>">Export Errors</a></div>
                <div class="table-responsive debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Log File</th><th>Error Line</th></tr></thead><tbody><?php if (empty($error_report)) { ?><tr><td colspan="2">No recent error lines found.</td></tr><?php } ?><?php foreach ((array)$error_report as $row) { ?><tr><td><?php echo html_escape($row['file']); ?></td><td><code><?php echo html_escape($row['line']); ?></code></td></tr><?php } ?></tbody></table></div>
            </div></div>

            <div class="panel_s debug-mode-card"><div class="panel-body">
                <h3>Expanded Help Guide</h3>
                <div class="debug-mode-help-grid">
                    <div><strong>ACT / UPG / QTY ★5</strong><p>Compact module page filters. ACT finds inactive modules, UPG finds visible Upgrade Database actions, and QTY ★5 finds modules marked with five-star/quality wording.</p></div>
                    <div><strong>CRM Errors</strong><p>Reads recent log files and gives copy, export, filter, and search tools so you can send errors for repair.</p></div>
                    <div><strong>File Structure</strong><p>Shows folders only and can export an HTML map. This helps decide where controllers, models, views, assets, and language files belong.</p></div>
                    <div><strong>Database Backup</strong><p>Creates a SQL file under uploads/debug_mode_backups. Use this before migration, repair, optimize, or cleanup.</p></div>
                    <div><strong>Staff Export</strong><p>Exports the staff table to CSV. Import is handled by guide first to prevent corrupting passwords, roles, and permissions.</p></div>
                    <div><strong>Broken Links</strong><p>Scans URL/link fields for common mistakes such as ttps://, double slashes, and adminmodules.</p></div>
                    <div><strong>Orphan Folders</strong><p>Lists folders under uploads/modules that may be disconnected from CRM records. Never delete until verified.</p></div>
                    <div><strong>Database Tools</strong><p>CHECK and ANALYZE are safer. OPTIMIZE and REPAIR should be used after backup.</p></div>
                </div>
            </div></div>
            <div class="debug-mode-bottom-nav">
                <a class="active" href="<?php echo admin_url('debug_mode'); ?>">Utilities</a>
                <a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a>
                <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a>
                <a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a>
                <a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
