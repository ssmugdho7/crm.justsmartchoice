<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo flexagile_lang('sprint-planning'); ?>
                    </h4>
                    <div>
                        <a href="#" id="flexagile-create-sprint"
                           data-title="<?php echo flexagile_lang('create-sprint'); ?>"
                            data-button-text="<?php echo flexagile_lang('create-sprint'); ?>"
                            class="btn btn-primary mright5">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo flexagile_lang('create-sprint'); ?>
                        </a>
                        <a href="<?php echo admin_url('flexagile/settings') ?>"
                           class="btn btn-secondary mright5">
                            <i class="fa-solid fa-gear tw-mr-1"></i>
                            <?php echo _l('settings'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="3" data-order-type="desc">
                            <thead>
                                <th><?php echo flexagile_lang('sprint-name')?></th>
                                <th><?php echo flexagile_lang('project')?></th>
                                <th><?php echo flexagile_lang('tasks-count'); ?></th>
                                <th><?php echo flexagile_lang('start-date')?></th>
                                <th><?php echo flexagile_lang('end-date')?></th>
                                <th><?php echo flexagile_lang('status')?></th>
                                <th><?php echo flexagile_lang('actions')?></th>
                            </thead>
                            <tbody>
                                <?php foreach ($sprints as $sprint): ?>
                                <tr>
                                    <td>
                                        <?php echo $sprint['name']; ?>
                                        <div class="row-options">
                                            <a href="<?php echo admin_url('flexagile/view/' . $sprint['id']) ?>"><?php echo flexagile_lang('sprint-details'); ?></a>
                                            | <a
                                                    href="<?php echo "" ?>"
                                                    data-title="<?php echo flexagile_lang('edit-sprint'); ?>"
                                                    data-id="<?php echo $sprint['id']; ?>"
                                                    data-name="<?php echo $sprint['name']; ?>"
                                                    data-description="<?php echo $sprint['description']; ?>"
                                                    data-start-date="<?php echo $sprint['start_date']; ?>"
                                                    data-end-date="<?php echo $sprint['end_date']; ?>"
                                                    data-project-id="<?php echo $sprint['project_id']; ?>"
                                                    data-button-text="<?php echo flexagile_lang('update-sprint'); ?>"
                                                    class="flexagile-edit-sprint"
                                            ><?php echo flexagile_lang('edit-sprint'); ?></a>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('projects/view/' . $sprint['project_id']); ?>">
                                            <?php echo $sprint['project_name']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $sprint['tasks_count']; ?>
                                    </td>
                                    <td>
                                        <?php echo flexagile_format_human_date($sprint['start_date']); ?>
                                    </td>
                                    <td>
                                        <?php echo ($sprint['status'] == FLEXAGILE_STATUS_ONGOING) ?  flexagile_format_days($sprint['end_date']) : flexagile_format_human_date($sprint['end_date']); ?>
                                    </td>
                                    <td>
                                        <?php echo flexagile_status_label($sprint); ?>
                                    </td>
                                    <td data-order="<?php echo $sprint['id'] ?>">
                                        <?php if($sprint['status'] == FLEXAGILE_STATUS_PLANNING): ?>
                                            <a href="<?php echo admin_url('flexagile/start/' . $sprint['id']); ?>"
                                               class="btn btn-default btn-sm btn-primary">
                                                <?php echo flexagile_lang('start-sprint'); ?>
                                            </a>
                                        <?php endif ?>
                                        <?php if($sprint['status'] == FLEXAGILE_STATUS_ONGOING): ?>
                                        <a href="<?php echo admin_url('flexagile/complete/' . $sprint['id']); ?>"
                                               class="btn btn-default btn-sm btn-success">
                                                <?php echo flexagile_lang('complete-sprint'); ?>
                                            </a>
                                        <?php endif ?>
                                            <a href="<?php echo admin_url('flexagile/delete/' . $sprint['id']); ?>"
                                                class="btn btn-danger btn-sm btn-danger _delete">
                                                <?php echo flexagile_lang('delete-sprint'); ?>
                                            </a>
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
<div class="modal fade" id="flexagile-create-edit-sprint-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('flexagile/create_edit_sprint')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo flexagile_lang('create-sprint'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('name', 'flexagile_sprint-name'); ?>
                <?php echo render_select('project_id', $projects, ['id', 'name'], 'flexagile_project'); ?>
                <?php echo render_textarea('description', 'flexagile_description'); ?>
                <?php echo render_datetime_input('start_date', 'flexagile_start-date'); ?>
                <?php echo render_datetime_input('end_date', 'flexagile_end-date'); ?>
                <input type="hidden" name="id" id="sprint_id" value="0">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo flexagile_lang('create-sprint'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
</body>

</html>