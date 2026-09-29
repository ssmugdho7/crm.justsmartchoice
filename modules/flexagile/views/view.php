<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $sprint['name']; ?>
                    </h4>

                    <div>
                        <a href="<?php echo admin_url('flexagile'); ?>" class="btn btn-secondary mright5">
                            <i class="fa-solid fa-less-than tw-mr-1"></i>
                            <?php echo flexagile_lang('back-to-sprints'); ?>
                        </a>
                        <?php if($sprint['status'] == FLEXAGILE_STATUS_PLANNING): ?>
                            <a href="<?php echo admin_url('flexagile/start/' . $sprint['id']); ?>"
                               class="btn btn-default btn-primary">
                                <?php echo flexagile_lang('start-sprint'); ?>
                            </a>
                        <?php endif ?>
                        <?php if($sprint['status'] == FLEXAGILE_STATUS_ONGOING): ?>
                            <a href="<?php echo admin_url('flexagile/complete/' . $sprint['id']); ?>"
                               class="btn btn-default btn-success">
                                <?php echo flexagile_lang('complete-sprint'); ?>
                            </a>
                        <?php endif ?>
                        <?php if($sprint['status'] == FLEXAGILE_STATUS_COMPLETE): ?>
                            <a href="<?php echo admin_url('flexagile/create_invoice/' . $sprint['id']); ?>"
                               class="btn btn-default btn-primary">
                                <i class="fa-solid fa-file-invoice-dollar tw-mr-1"></i>
                                <?php echo flexagile_lang('create-sprint-invoice'); ?>
                            </a>
                        <?php endif ?>
                        <a href="<?php echo admin_url('flexagile/delete/' . $sprint['id']); ?>"
                           class="btn btn-danger btn-danger _delete">
                            <?php echo flexagile_lang('delete-sprint'); ?>
                        </a>

                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <div>
                            <p><b><?php echo flexagile_lang('status') . ': </b>' . flexagile_status_label($sprint); ?></p>
                            <h5><b><?php echo flexagile_lang('project') . ': </b><a href="'.$project_link.'">#' .$project_name.'</a>' ?></h5>
                            <p><b><?php echo flexagile_lang('start-date') . ': </b>' . flexagile_format_human_date($sprint['start_date']); ?></p>
                            <p><b><?php echo flexagile_lang('end-date') . ': </b>' . flexagile_format_human_date($sprint['end_date']); ?></p>
                            <p><b><?php echo flexagile_lang('description') . ': </b>' . $sprint['description']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <h4><?php echo flexagile_lang('backlogs'); ?></h4>
                        <table class="table dt-table" data-order-col="2" data-order-type="desc">
                            <thead>
                            <th><?php echo _l('the_number_sign'); ?></th>
                            <th><?php echo _l('tasks_dt_name'); ?></th>
                            <th><?php echo _l('task_status'); ?></th>
                            <th><?php echo _l('tasks_list_priority'); ?></th>
                            <th><?php echo _l('task_assigned'); ?></th>
                            <th style="width: 20%"><?php echo flexagile_lang('move_to'); ?></th>
                            </thead>
                            <tbody>
                            <?php foreach ($tasks as $task):
                                $task = $task['taskdata'];
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $task['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $task['name']; ?>
                                        <div class="row-options">
                                            <a href="<?php echo admin_url('tasks/view/' . $task['id']) ?>"><?php echo flexagile_lang('view-task'); ?></a>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo format_task_status($task['status']); ?>
                                    </td>
                                    <td>
                                        <?php echo task_priority($task['priority']); ?>
                                    </td>
                                    <td>
                                        <?php echo format_members_by_ids_and_names($task['assignees_ids'], $task['assignees']); ?>
                                    </td>
                                    <td>
                                        <a href="#" onclick="flexagile_select_move_to_toggle('<?php echo $task['id'] ?>');return false;" class="">
                                            <i class="fa-regular fa-circle fa-xs"></i>
                                            <i class="fa-regular fa-circle fa-xs"></i>
                                            <i class="fa-regular fa-circle fa-xs"></i>
                                        </a>
                                        <div class="flexassign-moveto"
                                             style="display: none"
                                             data-message="<?php echo flexagile_lang('sprint-changed-successfully') ?>"
                                                data-url="<?php echo admin_url('flexagile/ajax') ?>"
                                             id="flexiselect-moveto-<?php echo $task['id'] ?>">
                                            <select id="sprint_id" class="form-control" data-width="100%" onchange="flexagile_select_move_to(this,'<?php echo $task['id'] ?>','<?php echo $task['status']; ?>')">
                                                <option value=""><?php echo flexagile_lang('none-selected'); ?></option>
                                                <?php foreach ($sprints as $sp):
                                                    if($sp['id'] == $sprint['id']){
                                                        continue;
                                                    }
                                                    ?>
                                                    <option value="<?php echo $sp['id']; ?>"><?php echo $sp['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>