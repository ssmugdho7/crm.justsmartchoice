<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('project_templates/create_project/'), array('id' => 'project-form')); ?>
<div class="modal fade" id="_project_modal" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel"<?php if ($this->input->get('opened_from_lead_id')) {
    echo 'data-lead-id=' . $this->input->get('opened_from_lead_id');
} ?>>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo html_escape($title); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <?php echo render_select('project_template', $project_templates, ['id', 'name'], 'pt_select_project_template', $id); ?>

                        <div class="form-group select-placeholder">
                            <label for="clientid"
                                   class="control-label"><?php echo _l('project_customer'); ?></label>
                            <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                    class="ajax-search"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php $selected = (isset($project) ? $project->clientid : '');
                                if ($selected == '') {
                                    $selected = (isset($customer_id) ? $customer_id: '');
                                }
                                if ($selected != '') {
                                    $rel_data = get_relation_data('customer', $selected);
                                    $rel_val  = get_relation_values($rel_data, 'customer');
                                    echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                } ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?php $value = _d(date('Y-m-d')); ?>
                                <?php echo render_date_input('start_date', 'project_start_date', $value); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
    <script>
        $(function () {
            init_ajax_search("customer", "#clientid.ajax-search");

            appValidateForm($('#project-form'), {
                project_template: 'required',
                clientid: 'required',
                startdate: 'required',
            });

            init_datepicker();
            init_selectpicker();
        });
    </script>
