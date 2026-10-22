<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content scpr-directory">
        <?php $this->load->view('../../modules/sc_projects_workspace/views/projects/navigation'); ?>
        <div id="vueApp">
            <h1 class="scpr-page-title"><?= e(_l('scpr_title')); ?></h1>
            <p class="scpr-page-description"><?= e(_l('scpr_subtitle')); ?></p>
            <?php $this->load->view('admin/projects/stats'); ?>
            <?php $this->load->view('../../modules/sc_projects_workspace/views/projects/portfolio'); ?>
            <div class="_buttons scpr-toolbar">
                <?php if (staff_can('create', 'projects')) { ?>
                <a href="<?= admin_url('projects/project'); ?>" class="btn btn-primary"><i class="fa-regular fa-plus tw-mr-1"></i><?= _l('new_project'); ?></a>
                <?php } ?>
                <a href="<?= admin_url('projects/gantt'); ?>" class="btn btn-default"><i class="fa fa-align-left tw-mr-1" aria-hidden="true"></i><?= e(_l('scpr_gantt')); ?></a>
                <div class="scpr-filters">
                    <app-filters id="<?= $table->id(); ?>" view="<?= $table->viewName(); ?>"
                        :rules="extra.projectsRules || <?= app\services\utilities\Js::from($this->input->get('status') ? $table->findRule('status')->setValue([(int) $this->input->get('status')]) : []); ?>"
                        :saved-filters="<?= $table->filtersJs(); ?>" :available-rules="<?= $table->rulesJs(); ?>">
                    </app-filters>
                </div>
            </div>
            <p class="scpr-scope-note"><?= e(_l('scpr_scope')); ?></p>
            <div class="panel_s prj-table-panel">
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
</body>
</html>
