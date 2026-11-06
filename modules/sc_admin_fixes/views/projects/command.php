<?php defined('BASEPATH') or exit('No direct script access allowed');$p=$command_project;$c=$command;
$status=get_project_status_by_id($p->status);$overdue=!empty($p->deadline)&&$p->deadline<date('Y-m-d')&&(int)$p->status===2;
$money=static function($value)use($c){return $value===null?'—':app_format_money($value,$c['currency']);}; ?>
<section class="scaf-project-command" data-scaf-command="<?= (int)$p->id; ?>">
<header><div><span class="scaf-project-eyebrow">PROJECT #<?= (int)$p->id; ?></span><h1><?= e($p->name); ?></h1><p><?= e(get_company_name($p->clientid)); ?> · <span class="label label-default"><?= e($status['name']??'Status #'.$p->status); ?></span></p></div>
<div class="scaf-project-quick">
<?php if(staff_can('edit','projects')){?><a class="btn btn-default" href="<?= e(admin_url('projects/project/'.(int)$p->id)); ?>">Adjust timeline</a><?php } ?>
<?php if(staff_can('create','tasks')){?><button class="btn btn-primary" type="button" data-scaf-quick-task="<?= (int)$p->id; ?>">+ New Task</button><?php } ?>
<?php if(staff_can('create','invoices')){?><a class="btn btn-default" href="<?= e(admin_url('invoices/invoice?customer_id='.(int)$p->clientid.'&project_id='.(int)$p->id)); ?>">New Invoice</a><?php } ?>
<a class="btn btn-default" href="<?= e(admin_url('projects/view/'.(int)$p->id.'?group=project_notes')); ?>">Log delay note</a>
</div></header>
<?php if($overdue){?><div class="scaf-project-delay" role="note"><strong>Timeline needs attention.</strong> The saved deadline is <?= e(_d($p->deadline)); ?>. <?php if(staff_can('edit','projects')){?><a href="<?= e(admin_url('projects/project/'.(int)$p->id)); ?>">Adjust the timeline</a> · <?php } ?><a href="<?= e(admin_url('projects/view/'.(int)$p->id.'?group=project_notes')); ?>">Log a delay reason</a></div><?php } ?>
<div class="scaf-project-financials" aria-label="Project financial summary">
<article><span>Contract Value</span><strong><?= e($money($c['contract'])); ?></strong><small>Original fixed-price scope</small></article>
<article><span>Approved Change Orders</span><strong><?= e($money($c['changes'])); ?></strong><small>Approved project controls only</small></article>
<article><span>Invoiced to Date</span><strong><?php if(!$c['billed'])echo '—';foreach($c['billed'] as $code=>$amount)echo e(app_format_money($amount,$code)).'<br>'; ?></strong><small>Issued invoices · currencies separate</small></article>
<article><span>Estimated Gross Margin</span><strong><?= e($money($c['margin'])); ?><?= $c['margin_percent']!==null?' · '.e($c['margin_percent']).'%':''; ?></strong><small>Contract + approved changes − recorded expenses. Unrecorded labor is excluded.</small></article>
</div>
<?php if(!$this->input->get('group')||$this->input->get('group')==='project_overview'){?><section class="scaf-project-stepper"><div class="scaf-project-stepper-title"><h2>Execution schedule</h2><a class="btn btn-default btn-sm" href="<?= e(admin_url('projects/view/'.(int)$p->id.'?group=project_tasks')); ?>">Open tasks & scope conversion</a></div>
<?php if($c['steps']){foreach($c['steps'] as $step){$task=$step['task'];?><article><span class="scaf-step-number">Day <?= (int)$step['day']; ?></span><div><h3><?php if($task){?><button type="button" data-scaf-open-task="<?= (int)$task['id']; ?>"><?= e($step['name']); ?></button><?php }else echo e($step['name']); ?></h3><p><?= nl2br(e($step['body'])); ?></p><small><?= e(_d($step['date'])); ?></small></div><span class="label label-default"><?php if($task)echo e(format_task_status($task['status'],true,true));else echo 'Not scheduled'; ?></span></article><?php }}else{?><p><?= $c['scope_text']!==''?nl2br(e($c['scope_text'])):'No saved scope yet. Add Day / Step headings to build the execution schedule.'; ?></p><?php } ?></section><?php } ?>
</section>
