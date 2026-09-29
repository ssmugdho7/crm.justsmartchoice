<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <?php echo form_hidden('project_id', $project->id) ?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">
                    <div class="row">
                        <div class="col-md-7 project-heading">
                            <div class="tw-flex tw-flex-wrap tw-items-center">
                                <h3 class="project-name"><?php echo _l('pt_project_template').": ".$project->name; ?></h3>
                                <div class="visible-xs">
                                    <div class="clearfix"></div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-5 text-right">
                            <?php if (module_is_active('task_templates') && staff_can('create', 'task_templates')) { ?>
                            <a href="#"
                                onclick="return false;"
                                class="btn btn-primary new-task-template-to-milestone">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?php echo _l('tt_new_task_template'); ?>
                            </a>
                            <?php } ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <?php echo _l('more'); ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right width200 project-actions">
                                    <?php if (staff_can('create', 'projects')) { ?>
                                    <li>
                                        <a href="#" onclick="new_project_from_template('<?php echo $project->id; ?>'); return false;">
                                            <?php echo _l('pt_new_project_from_template'); ?>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if (staff_can('edit', 'project_templates')) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('project_templates/template/' . $project->id); ?>">
                                            <?php echo _l('pt_edit_project_template'); ?>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if (staff_can('create', 'project_templates')) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('project_templates/copy/' . $project->id); ?>">
                                            <?php echo _l('pt_copy_project_template'); ?>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li class="divider"></li>
                                    <?php if (staff_can('delete', 'project_templates')) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('project_templates/delete/' . $project->id); ?>"
                                            class="_delete">
                                            <span class="text-danger"><?php echo _l('pt_delete_project_template'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="project-menu-panel tw-my-5">
                    <?php hooks()->do_action('before_render_project_view', $project->id); ?>
                    <?php $this->load->view('project_tabs'); ?>
                </div>

                <?php $this->load->view(($tab ? $tab['view'] : 'project_overview')); ?>

            </div>
        </div>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="add-edit-members" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('project_templates/add_edit_members/' . $project->id)); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('project_members'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
            $selected = [];
            foreach ($members as $member) {
                array_push($selected, $member['staff_id']);
            }
           echo render_select('project_members[]', $staff, ['staffid', ['firstname', 'lastname']], 'project_members', $selected, ['multiple' => true, 'data-actions-box' => true], [], '', '', false);
           ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary" autocomplete="off"
                    data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php $this->load->view('milestone'); ?>
<?php init_tail(); ?>
<!-- For invoices table -->
<script>
taskid = '<?php echo $this->input->get('taskid'); ?>';
</script>
<script>
    Dropzone.autoDiscover = false;
var current_user_is_admin = $('input[name="current_user_is_admin"]').val();
var project_id = $('input[name="project_id"]').val();
</script>
</body>

</html>