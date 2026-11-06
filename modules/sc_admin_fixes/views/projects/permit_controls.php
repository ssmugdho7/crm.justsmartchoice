<?php defined('BASEPATH') or exit('No direct script access allowed');
$edit = scaf_permit_controls_allowed($project->id, true);
$stages = scaf_permit_stages();
$currency = $this->projects_model->get_currency($project->id) ?: get_base_currency();
$save = admin_url('sc_admin_fixes/scaf_permit_controls/save/'.$project->id);
init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h2>Permits &amp; Change Orders</h2>
<p><a href="<?= admin_url('projects/view/'.$project->id) ?>"><?= html_escape($project->name) ?></a></p>
<a class="btn btn-default" href="?download=closeout">Download closeout summary PDF</a>
<h3>Permitting lifecycle</h3>
<?php if (empty($records['permits'])): ?><p>No structured permit records saved.</p><?php endif; ?>
<?php foreach ($records['permits'] ?? [] as $permit): ?>
<h4><?= html_escape(trim($permit['permit_type'].' '.$permit['permit_number'])) ?></h4>
<p><?= html_escape($permit['jurisdiction']) ?> · <?= html_escape($permit['inspection_status'] ?? '') ?></p>
<ol class="list-inline">
<?php foreach ($stages as $key => $label): ?>
<li><span class="label label-<?= $permit['status'] === $key ? 'success' : 'default' ?>"
<?= $permit['status'] === $key ? 'aria-current="step"' : '' ?>><?= html_escape($label) ?></span></li>
<?php endforeach; ?>
</ol>
<?php endforeach; ?>
<?php if ($edit && $records['permits'] !== null): ?>
<?= form_open($save) ?>
<input type="hidden" name="kind" value="permit">
<label>Record <select name="record_id" class="form-control"><option value="0">New permit</option>
<?php foreach ($records['permits'] as $permit): ?>
<option value="<?= (int) $permit['id'] ?>"><?= html_escape(trim($permit['permit_type'].' '.$permit['permit_number'])) ?></option>
<?php endforeach; ?></select></label>
<label>Trade / permit type <input class="form-control" required name="permit_type" maxlength="100"></label>
<label>Permit number <input class="form-control" name="permit_number" maxlength="100"></label>
<label>Jurisdiction <input class="form-control" name="jurisdiction" maxlength="191"></label>
<label>Stage <select class="form-control" name="status">
<?php foreach ($stages as $key => $label): ?><option value="<?= html_escape($key) ?>"><?= html_escape($label) ?></option><?php endforeach; ?>
</select></label>
<button type="submit" class="btn btn-primary">Save permit</button>
<?= form_close() ?>
<?php endif; ?>
<h3>Saved permitting details and inspectors</h3>
<dl>
<?php foreach ($fields as $field): if (trim((string) $field['value']) === '') continue; $phone = preg_match('/inspector/i', $field['name']) ? scaf_permit_contact_link($field['value']) : ''; ?>
<dt><?= html_escape($field['name']) ?></dt>
<dd><?= html_escape($field['value']) ?> <?php if ($phone): ?>
<a href="tel:<?= html_escape($phone) ?>">Call</a> · <a href="sms:<?= html_escape($phone) ?>">SMS</a>
<?php endif; ?></dd>
<?php endforeach; ?>
</dl>
<h3>Change order ledger</h3>
<?php if (empty($records['change_orders'])): ?><p>No change orders saved.</p><?php endif; ?>
<div class="table-responsive"><table class="table">
<thead><tr><th>Change order</th><th>Price change</th><th>Schedule impact</th><th>Status and approval reference</th></tr></thead>
<tbody>
<?php foreach ($records['change_orders'] ?? [] as $order): ?>
<tr><td><?= html_escape($order['change_number'].' · '.$order['title']) ?></td>
<td><?= html_escape(app_format_money($order['amount'], $currency)) ?></td>
<td><?= (int) $order['days_impact'] ?> days</td>
<td><?= html_escape($order['status']) ?> <?= html_escape($order['approved_at'] ?? '') ?>
<?php if (str_contains((string) $order['description'], 'Customer approval reference: ')): ?>
<p><?= html_escape($order['description']) ?></p>
<?php endif; ?>
<?php if ($edit && $order['status'] === 'draft'): ?>
<?= form_open($save) ?>
<input type="hidden" name="kind" value="approve"><input type="hidden" name="record_id" value="<?= (int) $order['id'] ?>">
<label>Customer approval reference <input class="form-control" name="approval_reference" required maxlength="1000"></label>
<button type="submit" class="btn btn-default">Record approval</button>
<?= form_close() ?>
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php if ($edit && $records['change_orders'] !== null): ?>
<h4>New draft change order</h4>
<?= form_open($save) ?>
<input type="hidden" name="kind" value="change">
<label>Reference <input class="form-control" name="change_number" required maxlength="100"></label>
<label>Title <input class="form-control" name="title" required maxlength="191"></label>
<label>Price change <input class="form-control" name="amount" type="number" required step="0.01"></label>
<label>Delay in days <input class="form-control" name="days_impact" type="number" required min="0" max="9999" value="0"></label>
<button type="submit" class="btn btn-primary">Save draft</button>
<?= form_close() ?>
<?php endif; ?>
</div></div></div></div>
<?php init_tail(); ?>
