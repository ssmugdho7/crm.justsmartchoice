<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h1>Project closeout summary</h1>
<p>Project #<?= (int) $project->id ?> · <?= html_escape($project->name) ?></p>
<h2>Permit and inspection records</h2>
<?php if (empty($records['permits'])): ?><p>No permit records saved.</p><?php endif; ?>
<?php foreach ($records['permits'] ?? [] as $permit): ?>
<p><?= html_escape(implode(' · ', array_filter([$permit['permit_type'], $permit['permit_number'],
    $permit['jurisdiction'], scaf_permit_stages()[$permit['status']] ?? $permit['status'], $permit['inspection_status']]))) ?></p>
<?php endforeach; ?>
<h2>Project permit and warranty information</h2>
<?php foreach ($fields as $field): if (!preg_match('/permit|warranty|commencement/i', $field['name'])) continue; ?>
<p><strong><?= html_escape($field['name']) ?></strong> <?= nl2br(html_escape($field['value'])) ?></p>
<?php endforeach; ?>
