<?php defined('BASEPATH') or exit('No direct script access allowed');
// Reuse the directory's aggregate; do not run one query per status.
$summary = $scpr_portfolio ?? $this->load->get_var('scpr_portfolio') ?? scpr_portfolio();
$selected = (int) $this->input->get('status'); ?>
<div class="prj-kpi-grid _filters _hidden_inputs">
<?php foreach ($statuses as $status) { $id = (int) $status['id']; ?>
    <a href="<?= admin_url('projects?status=' . $id); ?>" class="prj-kpi-card scpr-status-<?= e($id); ?><?= $selected === $id ? ' is-active' : ''; ?>"
        role="button" aria-pressed="<?= $selected === $id ? 'true' : 'false'; ?>"
        @click.prevent="extra.projectsRules = <?= app\services\utilities\Js::from($table->findRule('status')->setValue([$id])); ?>">
        <span class="prj-kpi-label"><?= e($status['name']); ?></span>
        <strong class="prj-kpi-count"><?= e($summary['counts'][$id] ?? 0); ?></strong>
    </a>
<?php } ?>
</div>
