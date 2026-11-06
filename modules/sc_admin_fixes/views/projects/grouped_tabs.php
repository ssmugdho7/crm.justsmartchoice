<?php defined('BASEPATH') or exit('No direct script access allowed');
$groups=scaf_project_tab_groups(filter_project_visible_tabs($tabs,$project->settings->available_features));
$labels=['overview'=>'Overview & Scope','execution'=>'Execution & Timeline','permits'=>'Permits & Inspections','financials'=>'Financials','collaboration'=>'Files & Notes'];
$active=$this->input->get('group') ?: 'project_overview'; ?>
<nav class="scaf-project-navigation" aria-label="Project navigation">
<?php foreach($labels as $group=>$label){$items=$groups[$group];if($group==='permits'&&(is_admin()||staff_can('view_project_controls','smart_choice_enterprise_core')))$items[]=['slug'=>'scaf-permits','name'=>'Project permits & inspections'];if(!$items)continue;$selected=in_array($active,array_column($items,'slug'),true); ?>
<div class="dropdown <?= $selected?'active':''; ?>"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= e($label); ?> <span class="caret"></span></button><ul class="dropdown-menu">
<?php foreach($items as $item){$slug=$item['slug'];?><li class="<?= $active===$slug?'active':''; ?>"><a href="<?= $slug==='scaf-permits'?e(admin_url('sc_admin_fixes/scaf_permit_controls/index/'.(int)$project->id)):e(admin_url('projects/view/'.(int)$project->id.'?group='.rawurlencode($slug))); ?>"><?= e($item['name']); ?></a></li><?php } ?></ul></div>
<?php } ?></nav>
