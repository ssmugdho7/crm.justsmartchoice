<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<li data-task-id="<?php echo $task['id']; ?>" class="task<?php if (staff_can('create','task_templates') || staff_can('edit', 'task_templates')) {
    echo ' sortable';
} ?>">
    <div class="panel-body">
        <div class="sm:tw-flex">
            <?php
$assignees = !empty($task['assignees_ids']) ? explode(',', $task['assignees_ids']) : [];
if (count($assignees) > 0 && $assignees[0] != '') { ?>
            <div
                class="tw-mb-4 tw-flex-shrink-0 sm:tw-mb-0 sm:tw-mr-2 tw-flex tw-flex-col tw-items-center tw-space-y-0.5">
                <?php if ($task['current_user_is_assigned']) {
    echo staff_profile_image(get_staff_user_id(), ['staff-profile-image-small'], 'small', ['data-toggle' => 'tooltip', 'data-title' => _l('project_task_assigned_to_user')]);
}
                foreach ($assignees as $assigned) {
                    $assigned = trim($assigned);
                    if ($assigned != get_staff_user_id()) {
                        echo staff_profile_image($assigned, ['staff-profile-image-xs sub-staff-assigned-milestone'], 'small', ['data-toggle' => 'tooltip', 'data-title' => get_staff_full_name($assigned)]);
                    }
                }
            ?>
            </div>
            <?php } ?>
            <div>
                <h4 class="tw-text-base tw-my-0">
                    <a href="<?php echo admin_url('tasks/view/' . $task['id']); ?>"
                        class="task_milestone tw-text-neutral-600 hover:tw-text-neutral-700 active:tw-text-neutral-700"
                        onclick="edit_task_template(<?php echo $task['id']; ?>); return false;">
                        <?php echo $task['name']; ?>
                    </a>
                </h4>
            </div>
        </div>
    </div>
</li>