<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <div class="panel_s">
            <div class="panel-body">
                <div class="row _buttons">
                    <div class="col-md-8">
                        <?php if(staff_can('create', 'project_templates')){ ?>
                            <a href="<?php echo admin_url("project_templates/template"); ?>" class="btn btn-info pull-left new"><?php echo _l('pt_new_project_template'); ?></a>
                        <?php } ?>
                        <?php if(staff_can('create', 'projects')){ ?>
                            <a href="#" onclick="new_project_from_template(); return false;" class="btn btn-info pull-left new mleft10"><?php echo _l('pt_new_project_from_template'); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading hr-10" />
                <div class="clearfix"></div>
                <?php

                defined('BASEPATH') or exit('No direct script access allowed');

                $table_data = [
                    _l('the_number_sign'),
                    _l('pt_project_template'),
                    _l('pt_project_template_description'),
                ];

                array_unshift($table_data, [
                    'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="projects"><label></label></div>',
                    'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visible')],
                ]);

                $table_data = hooks()->apply_filters('project_template_table_columns', $table_data);

                render_datatable($table_data, 'project-templates');

                ?>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<div id="_project"></div>
</body>
</html>
