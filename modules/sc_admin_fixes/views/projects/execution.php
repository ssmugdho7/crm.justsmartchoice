<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="scaf-project-execution" data-project="<?= (int)$project_id ?>">
<?php if($group==='project_tasks'): ?>
<button type="button" class="btn sc-btn-secondary" data-scaf-task-view="table">Table</button>
<button type="button" class="btn sc-btn-secondary" data-scaf-task-view="kanban">Kanban</button>
<?php if(staff_can('create','tasks')): ?><button type="button" class="btn sc-btn-primary" data-scaf-scope>Convert Scope to Tasks</button><?php endif; ?>
<p class="text-muted">Converts saved Day / Step headings into dated tasks. Existing matching tasks are retained; nothing is sent to the customer.</p>
<div class="scaf-project-kanban" hidden></div>
<?php else: ?>
<div class="scaf-gantt-zoom" aria-label="Timeline zoom"><?php foreach(['Day','Week','Month'] as $zoom): ?><button type="button" class="btn sc-btn-secondary" data-scaf-gantt="<?= $zoom ?>"><?= $zoom ?></button><?php endforeach; ?></div>
<?php endif; ?>
</div>
