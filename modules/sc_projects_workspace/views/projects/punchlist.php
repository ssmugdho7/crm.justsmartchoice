<?php defined('BASEPATH') or exit('No direct script access allowed');
if (isset($project->settings->available_features['project_tasks']) && !$project->settings->available_features['project_tasks']) { return; } ?>
<section class="scpr-punchlist panel_s" data-error="<?= e(_l('scpr_task_error')); ?>">
    <div class="panel-body">
        <div class="scpr-section-heading"><h2><?= e(_l('scpr_punchlist')); ?></h2><a href="<?= admin_url('projects/view/' . (int) $project->id . '?group=project_tasks'); ?>"><?= e(_l('scpr_all_tasks')); ?></a></div>
        <p class="scpr-scope-note"><?= e(_l('scpr_punchlist_scope')); ?></p>
        <?php foreach (($scpr_tasks ?? []) as $task) { $complete = (int) $task['status'] === 5; ?>
        <div class="prj-task-row<?= $complete ? ' completed' : ''; ?>">
            <input class="scpr-task-toggle" type="checkbox" data-task-id="<?= e((int) $task['id']); ?>"<?= $complete ? ' checked' : ''; ?><?= !scpr_can_change_task($task) ? ' disabled' : ''; ?> aria-label="<?= e(sprintf(_l('scpr_toggle_task'), $task['name'])); ?>">
            <div class="scpr-task-detail"><a class="prj-task-title" href="<?= admin_url('tasks/view/' . (int) $task['id']); ?>" onclick="init_task_modal(<?= (int) $task['id']; ?>); return false;"><?= e($task['name']); ?></a>
                <span><?= e($task['milestone_name'] ?? ''); ?><?= !empty($task['duedate']) ? ' · ' . e(_d($task['duedate'])) : ''; ?></span>
            </div>
            <span class="scpr-task-priority scpr-priority-<?= e((int) $task['priority']); ?>"><?= e(task_priority($task['priority'])); ?></span>
            <span class="label scpr-task-status"><?= e(get_task_status_by_id($task['status'])['name']); ?></span>
        </div>
        <?php } ?>
        <?php if (empty($scpr_tasks)) { ?><p class="scpr-empty"><?= e(_l('no_tasks_found')); ?></p><?php } ?>
    </div>
</section>
