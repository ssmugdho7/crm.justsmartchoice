<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); $pending = array_filter($modules, function ($module) { return $module['maintenance']['can_upgrade']; }); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s"><div class="panel-body">
            <h4 class="no-mtop">Module Database Upgrades</h4>
            <p>This updates installed modules with pending database migrations. It does not download new module releases or update Composer/npm packages.</p>
            <div class="alert alert-<?= $pending ? 'warning' : 'info'; ?>" id="sc_upgrade_summary" role="status" aria-live="polite">
                <?php if ($pending) { ?>
                    <?= count($pending); ?> module(s) are ready to upgrade. Keep a current database backup before starting.
                <?php } else { ?>
                    No eligible module database upgrades are pending. Checked <?= count($modules); ?> modules. Any blocked modules are explained below.
                <?php } ?>
            </div>
            <?= form_open(admin_url('modules/upgrade_all_database'), ['id' => 'sc_upgrade_form']); ?>
                <button type="submit" id="sc_upgrade_start" class="btn btn-success" <?= !$pending ? 'disabled' : ''; ?>>Upgrade Pending Modules (<?= count($pending); ?>)</button>
                <a href="<?= admin_url('modules'); ?>" class="btn btn-default">Back to Modules</a>
            <?= form_close(); ?>
            <noscript><div class="alert alert-info mtop15">Enable JavaScript to run and track these upgrades.</div></noscript>
            <div class="table-responsive mtop20">
                <table class="table table-hover" id="sc_upgrade_table">
                    <thead><tr><th>Module</th><th>Installed</th><th>Module files</th><th>Result</th></tr></thead>
                    <tbody>
                    <?php foreach ($modules as $module) { $status = $module['maintenance']; ?>
                        <tr data-ready="<?= $status['can_upgrade'] ? '1' : '0'; ?>" data-module="<?= e($module['system_name']); ?>" data-version="<?= e($module['headers']['version']); ?>" data-installed="<?= e((string) $module['installed_version']); ?>">
                            <td><?= e($module['headers']['module_name']); ?></td>
                            <td><?= e($module['installed_version'] === false ? 'Not installed' : $module['installed_version']); ?></td>
                            <td><?= e($module['headers']['version']); ?></td>
                            <td class="sc-upgrade-result <?= $status['errors'] ? 'text-danger' : ''; ?>"><?= e($status['errors'] ? implode(' ', $status['errors']) : $status['note']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
<script>
(function () {
    var form = document.getElementById('sc_upgrade_form'), start = document.getElementById('sc_upgrade_start');
    var summary = document.getElementById('sc_upgrade_summary');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#sc_upgrade_table tr[data-ready="1"]'));
    var running = false;
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (running || !rows.length) return;
        if (!window.confirm('Run the pending module database migrations? Confirm that a current database backup is available.')) return;
        running = true;
        start.disabled = true;
        var upgraded = 0, failed = 0, skipped = 0, checked = 0;
        summary.className = 'alert alert-info';
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i], result = row.querySelector('.sc-upgrade-result');
            result.textContent = 'Upgrading…';
            summary.textContent = 'Processing ' + (i + 1) + ' of ' + rows.length + ' pending modules…';
            try {
                // Keep the framework-generated CSRF field on every individual POST.
                var body = new URLSearchParams(new FormData(form));
                body.set('module', row.dataset.module);
                body.set('version', row.dataset.version);
                body.set('installed_version', row.dataset.installed);
                var response = await fetch(form.action, {method: 'POST', credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}, body: body});
                if (!response.ok) throw new Error('Request failed (HTTP ' + response.status + ').');
                var data = await response.json();
                if (!['upgraded', 'skipped', 'error'].includes(data.status) || typeof data.message !== 'string') throw new Error('Invalid upgrade response.');
                result.textContent = data.message;
                result.classList.toggle('text-danger', data.status === 'error');
                result.classList.toggle('text-success', data.status === 'upgraded');
                checked++;
                if (data.status === 'upgraded') upgraded++;
                else if (data.status === 'error') failed++;
                else skipped++;
            } catch (error) {
                result.textContent = error.message + ' The result is unknown. Check this module before retrying.';
                result.classList.add('text-danger');
                summary.className = 'alert alert-danger';
                summary.textContent = 'Stopped: ' + upgraded + ' upgraded, ' + failed + ' failed, ' + skipped +
                    ' skipped; ' + (rows.length - checked) + ' unconfirmed or not started. Reload the review after checking the failed request.';
                return; // Never automatically repeat a migration with an unknown outcome.
            }
        }
        summary.className = 'alert alert-' + (failed ? 'warning' : 'success');
        summary.textContent = 'Finished: ' + upgraded + ' upgraded, ' + failed + ' failed, ' + skipped + ' skipped.';
    });
})();
</script>
<?php init_tail(); ?>
