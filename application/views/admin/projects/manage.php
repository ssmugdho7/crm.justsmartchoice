<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div id="vueApp">
            <div class="row">
                <div class="col-md-12">
                    <div class="tw-block md:tw-hidden">
                        <?php $this->load->view('admin/projects/stats'); ?>
                    </div>
                    <div class="_buttons">
                        <div class="md:tw-flex md:tw-items-center">
                            <?php if (staff_can('create', 'projects')) { ?>
                            <a href="<?= admin_url('projects/project'); ?>"
                                class="btn btn-primary pull-left display-block mright5">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?= _l('new_project'); ?>
                            </a>
                            <?php } ?>
                            <a href="<?= admin_url('projects/gantt'); ?>"
                                data-toggle="tooltip"
                                data-title="<?= _l('project_gant'); ?>"
                                class="btn btn-default btn-with-tooltip sm:!tw-px-3">
                                <i class="fa fa-align-left" aria-hidden="true"></i>
                            </a>
                            <div class="tw-hidden md:tw-block md:tw-ml-6 rtl:md:tw-mr-6">
                                <?php $this->load->view('admin/projects/stats'); ?>
                            </div>
                            <div class="ltr:tw-ml-auto rtl:tw-mr-auto">
                                <app-filters
                                    id="<?= $table->id(); ?>"
                                    view="<?= $table->viewName(); ?>"
                                    :rules="extra.projectsRules || <?= app\services\utilities\Js::from($this->input->get('status') ? $table->findRule('status')->setValue([(int) $this->input->get('status')]) : []); ?>"
                                    :saved-filters="<?= $table->filtersJs(); ?>"
                                    :available-rules="<?= $table->rulesJs(); ?>">
                                </app-filters>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="panel_s tw-mt-2">
                        <div class="panel-body">
                            <div class="panel-table-full">
                                <?= form_hidden('custom_view'); ?>
                                <?php $this->load->view('admin/projects/table_html'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/projects/copy_settings'); ?>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-projects', admin_url + 'projects/table', undefined, undefined, {},
            <?= hooks()->apply_filters('projects_table_default_order', json_encode([5, 'asc'])); ?>
        );

        init_ajax_search('customer', '#clientid_copy_project.ajax-search');
    });
</script>

<style>
.table-projects .native-project-scope{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;white-space:pre-line;overflow:hidden;max-height:48px;width:280px;max-width:100%;padding:0;border:0;background:transparent;text-align:left;line-height:24px;color:inherit;cursor:pointer}
.table-projects .native-project-scope:focus-visible{outline:2px solid #059669;outline-offset:2px}
#native-project-scope-modal .modal-body{white-space:pre-wrap;overflow-wrap:anywhere;max-height:70vh;overflow:auto}
</style>
<div class="modal fade" id="native-project-scope-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="native-project-scope-heading"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="<?= e(_l('close')); ?>">&times;</button><h4 class="modal-title" id="native-project-scope-heading"></h4></div><div class="modal-body"></div></div></div></div>
<script>
$(document).on('click', '.table-projects .native-project-scope', function () {
    var modal = $('#native-project-scope-modal');
    modal.find('.modal-title').text($(this).attr('data-scope-title'));
    modal.find('.modal-body').text($(this).attr('data-scope'));
    modal.modal('show');
});
</script>

</body>
</html>
