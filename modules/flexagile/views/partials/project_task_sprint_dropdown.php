<?php if($active_sprint || $sprints) : ?>
<?php $name = ($active_sprint) ? $active_sprint['name'] : flexagile_lang('none-selected'); ?>
<h5 id="flexagile-task-selected"> <i class="fa fa-bolt"></i>
    <?php echo flexagile_lang('sprint') ?>  - <span><?php echo $name; ?></span> &nbsp;
    <a href="#" class="font-medium-xs" onclick="return flexagile_cta()" id="flexsprints-cta">
        <i class="fa-regular fa-pen-to-square"></i>
    </a>
</h5>
<div class="form-group" id="flexsprints-dropdown-container" style="display:none"
     data-message="<?php echo flexagile_lang('sprint-changed-successfully') ?>"
     data-id="<?php echo $task_id; ?>"
     data-url="<?php echo admin_url('flexagile/ajax') ?>" data-status="<?php echo $task_status ?>">
    <?php $attr = ['data-id'=>$project_id,'id'=>"flexagile-task-dropdown"]; ?>
    <?php echo render_select('sprint', $sprints, ['id', 'name'], '',0,$attr); ?>
    <small class="text-info"><?php echo flexagile_lang('only-showing-sprints-for-this-project') ?></small>
</div>
<hr/>
<?php endif; ?>
