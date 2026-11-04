<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content ga-module"><div class="panel_s"><div class="panel-body">
<h4>Internal CRM reports</h4>
<p>Operational counts from CRM records. This report sends no staff, customer, or sales data to Google Analytics.</p>
<a class="btn btn-default" href="<?= admin_url('google_analytics') ?>">Website traffic reports</a><hr>
<form method="get" class="form-inline">
<label for="internal-start">From</label> <input type="date" id="internal-start" name="start" class="form-control" value="<?= html_escape($start) ?>" required>
<label for="internal-end">To</label> <input type="date" id="internal-end" name="end" class="form-control" value="<?= html_escape($end) ?>" required>
<button class="btn btn-info" type="submit">Apply</button></form><hr>
<table class="table table-striped"><thead><tr><th>CRM metric</th><th>Count</th></tr></thead><tbody>
<?php foreach ($report as $label => $value) { ?><tr><td><?= html_escape($label) ?></td><td><?= $value === null ? 'Unavailable in this installation' : (int) $value ?></td></tr><?php } ?>
</tbody></table>
<h4>Sales performance by currency</h4>
<p>Document totals are grouped by current status and original currency. Payments are recorded receipts, not a separate revenue forecast. Amounts in different currencies are never added together.</p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Metric</th><th>Currency</th><th>Current status</th><th>Count</th><th>Total</th></tr></thead><tbody>
<?php foreach ($sales as $row) { $status = $row['status'] === null ? 'Recorded' : ($row['metric'] === 'Estimates created' ? strip_tags(format_estimate_status($row['status'])) : strip_tags(format_invoice_status($row['status']))); ?>
<tr><td><?= html_escape($row['metric']) ?></td><td><?= html_escape($row['currency_name']) ?></td><td><?= html_escape($status) ?></td><td><?= (int) $row['document_count'] ?></td><td><?= html_escape(number_format((float) $row['amount'], 2)) ?></td></tr>
<?php } ?></tbody></table></div>
<p class="text-muted">Staff and customer counts use the latest stored activity/login timestamp, not historical sessions. Appointment counts use scheduled dates. Document counts use creation dates. Payment counts use recorded dates. These are CRM operational records, separate from public website traffic and GA4 conversions.</p>
</div></div></div></div><?php init_tail(); ?></body></html>
