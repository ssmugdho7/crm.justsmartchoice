<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (staff_can('create_milestones', 'project_templates')) { ?>
<a href="#" class="btn btn-primary" onclick="new_milestone_template();return false;">
    <i class="fa-regular fa-plus tw-mr-1"></i>
    <?php echo _l('new_milestone'); ?>
</a>
<?php } ?>
<a href="#" class="btn btn-default" onclick="milestones_template_switch_view(); return false;"><i class="fa fa-th-list"></i></a>
<?php if ($milestones_found) { ?>
<div id="kanban-params" class="pull-right">
    <div class="clearfix"></div>
    <?php echo form_hidden('project_template_id', $project->id); ?>
</div>
<div class="clearfix"></div>
<?php } ?>
<?php if ($milestones_found) { ?>
<div class="project-milestones-kanban tw-mt-5">
    <div class="kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
        <div class="row">
            <div class="container-fluid">
                <div id="kan-ban"></div>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="alert alert-info mtop15 no-mbot">
    <?php echo _l('no_milestones_found'); ?>
</div>
<?php } ?>
<div id="milestones-table" class="hide tw-mt-5">
    <div class="panel_s panel-table-full">
        <div class="panel-body">
            <?php render_datatable([
      _l('milestone_name'),
       _l('pt_milestone_start_date'),
       _l('pt_milestone_due_date'),
      _l('milestone_description'),
   ], 'milestones'); ?>
        </div>
    </div>
</div>