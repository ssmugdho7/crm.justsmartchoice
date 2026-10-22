<?php defined('BASEPATH') or exit('No direct script access allowed');
$progress = max(0, min(100, (float) $percent)); ?>
<section class="scpr-project-summary">
    <div class="scpr-project-identity">
        <h1><span>#<?= e($project->id); ?></span> <?= e($project->name); ?></h1>
        <p><?= e($project->client_data->company); ?></p>
        <div class="prj-progress-container">
            <div class="prj-progress-track" role="progressbar" aria-label="<?= e(_l('project_progress')); ?>" aria-valuenow="<?= e($progress); ?>" aria-valuemin="0" aria-valuemax="100"><div class="prj-progress-bar" style="width:<?= e($progress); ?>%"></div></div>
            <span class="prj-progress-text"><?= e($progress); ?>%</span>
        </div>
    </div>
    <div class="scpr-project-metrics">
        <?php if (staff_can('create', 'projects') && (int) $project->billing_type === 1) { ?>
        <div><span><?= e(_l('scpr_contract_value')); ?></span><strong><?= e(app_format_money($project->project_cost, $currency)); ?></strong></div>
        <?php } ?>
        <div><span><?= e(_l('scpr_crew_hours')); ?></span><strong><?= e(seconds_to_time_format($project_total_logged_time ?? 0)); ?></strong></div>
        <div><span><?= e(_l('project_deadline')); ?></span><strong><?= $project->deadline ? e(_d($project->deadline)) : e(_l('scpr_no_deadline')); ?></strong></div>
    </div>
</section>
