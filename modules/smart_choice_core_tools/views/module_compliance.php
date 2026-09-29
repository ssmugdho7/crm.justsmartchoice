<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4 class="tw-font-semibold tw-mt-0">Module Compliance</h4>
<p class="text-muted">Verifies the mandatory Smart Choice module structure without modifying third-party module business logic.</p>
<div class="table-responsive"><table class="table table-striped dt-table"><thead><tr>
<th>Module</th><th>Main File</th><th>English</th><th>Spanish</th><th>Migrations</th><th>Invalid Migration Config</th>
</tr></thead><tbody>
<?php foreach ($rows as $row) { ?>
<tr><td><?php echo html_escape(ucwords(str_replace('_',' ',$row['slug']))); ?></td>
<td><?php echo $row['main'] ? 'OK' : 'Missing'; ?></td>
<td><?php echo $row['english'] ? 'OK' : 'Missing'; ?></td>
<td><?php echo $row['spanish'] ? 'OK' : 'Missing'; ?></td>
<td><?php echo $row['migrations'] ? 'OK' : 'Missing'; ?></td>
<td><?php echo $row['invalid_migration_config'] ? '<span class="text-danger">Remove</span>' : 'OK'; ?></td></tr>
<?php } ?>
</tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>
