<?php defined('BASEPATH') or exit('No direct script access allowed'); $CI=&get_instance(); ?>
<div class="scaf-project-board">
<?php foreach($statuses as $status): ?><section class="scaf-project-column"><h4><?= html_escape($status['name']) ?></h4>
<?php foreach($tasks as $task): if((int)$task['status']!==(int)$status['id'])continue;
$checklist=$CI->tasks_model->get_checklist_items($task['id']);$done=0;foreach($checklist as $item)if(!empty($item['finished']))$done++;
$assignees=$CI->tasks_model->get_task_assignees($task['id']);$tags=get_tags_in($task['id'],'task'); ?>
<article class="scaf-project-task"><div><?= render_tags($tags) ?></div><a href="#" onclick="init_task_modal(<?= (int)$task['id'] ?>);return false;"><strong><?= html_escape($task['name']) ?></strong></a><p><?= html_escape(mb_substr(strip_tags($task['description']??''),0,180)) ?></p><footer><span><?= $done ?>/<?= count($checklist) ?> Checklist</span><span><?php foreach($assignees as $assignee)echo staff_profile_image($assignee['assigneeid'],['staff-profile-image-small'],'small'); ?></span></footer></article>
<?php endforeach; ?></section><?php endforeach; ?>
</div>
