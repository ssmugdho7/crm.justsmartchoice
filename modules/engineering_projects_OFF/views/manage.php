<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('engineering_projects', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="<?php echo admin_url('engineering_projects/engineering_project'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_engineering_project'); ?></a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <div class="row _hidden_inputs" style="margin-bottom: 2em;">
                                <div class="col-md-2">
                                    <?php echo render_select('site_survey_schedule', get_engeniering_status(), array('id', 'name'), 'engineering_project_site_survey_schedule','',[],[],'_filters') ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('contractor_id', $contractors, array('id', 'contractor'), 'engg_contractor_select','',[],[],'_filters'); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('install_schedule', get_engeniering_status(), array('id', 'name'), 'engg_install_schedule','',[],[],'_filters') ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('drawings_id', $drawings, array('id', 'name'), 'engg_drawings','',[],[],'_filters'); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('final_inspection', get_engeniering_status(), array('id', 'name'), 'engg_final_inspection','',[],[],'_filters') ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('tpo_status', get_engeniering_tpo_status(), array('id', 'name'), 'engg_tpo_status','',[],[],'_filters') ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        <?php } ?>
                        <?php render_datatable(array(
                            _l('engg_project_id'),
                            _l('engineering_job_name'),
                            _l('crm_project'),
                            _l('customer'),
                            _l('engg_site_survey_schedule'),
                            _l('engg_drawings'),
                            _l('engg_project_documents'),
                            _l('shared_crm_files'),
                            _l('engg_tpo_status'),
                        ), 'engineering_projects'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var ProjectsServerParams = {};
        initDataTable('.table-engineering_projects', window.location.href, [3], [3],ProjectsServerParams);
        $('select[name="final_inspection"],select[name="site_survey_schedule"],select[name="contractor_id"],select[name="install_schedule"],select[name="drawings_id"],select[name="final_inspection"],select[name="tpo_status"]').on('change', function() {
            if ($.fn.DataTable.isDataTable('.table-engineering_projects')) {
            $('.table-engineering_projects').DataTable().destroy();
            }
            $.each($('._hidden_inputs ._filters select'),function(){
                ProjectsServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
            });
            console.log(ProjectsServerParams);
            initDataTable('.table-engineering_projects', window.location.href, [3], [3],ProjectsServerParams);
        });

        $('.table-engineering_projects').DataTable().on('draw', function() {
            var rows = $('.table-engineering_projects').find('tr');
            $.each(rows, function() {
                var td = $(this).find('td').eq(6);
                var percent = $(td).find('input[name="percent"]').val();
                $(td).find('.engineering_project-progress').circleProgress({
                    value: percent,
                    size: 45,
                    animation: false,
                    fill: {
                        gradient: ["#28b8da", "#059DC1"]
                    }
                })
            })
        })
    });
</script>
</body>

</html>
<div class="modal fade" id="addEngineeringProjectModal" tabindex="-1" role="dialog" aria-labelledby="addEngineeringProjectModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open(admin_url('engineering_projects/engineering_project')); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="addEngineeringProjectModalLabel"><?php echo _l('add_engineering_project'); ?></h4>
      </div>
      <div class="modal-body">
        <?php
          $this->load->model('clients_model');
          $customers = $this->clients_model->get();
          echo render_select('customer_id', $customers, ['userid','company'], _l('customer'));
          echo render_date_input('start_date', _l('project_start_date'));
          echo render_date_input('end_date', _l('project_end_date'));
          echo render_input('name', _l('project_name'));
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
