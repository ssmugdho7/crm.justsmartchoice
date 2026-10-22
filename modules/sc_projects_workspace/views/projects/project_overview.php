<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php // Native financials, permissions, charts, custom fields and vault entries remain intact.
include APPPATH . 'views/admin/projects/project_overview.php'; ?>
<?php if (!empty($scpr_milestones)) { ?>
<section class="panel_s scpr-milestone-tracker"><div class="panel-body">
    <div class="scpr-section-heading"><h2><?= e(_l('scpr_milestones')); ?></h2><a href="<?= admin_url('projects/view/' . (int) $project->id . '?group=project_milestones'); ?>"><?= e(_l('scpr_all_milestones')); ?></a></div>
    <?php foreach ($scpr_milestones as $milestone) { ?>
    <div class="scpr-milestone-row">
        <strong><?= e($milestone['name']); ?></strong><span><?= e(_d($milestone['start_date'])); ?> — <?= e(_d($milestone['due_date'])); ?></span>
        <?php if (isset($milestone['scpr_total'])) { $done = (int) $milestone['scpr_total'] > 0 ? round(100 * (int) $milestone['scpr_done'] / (int) $milestone['scpr_total'], 1) : 0; ?>
        <div class="prj-progress-container"><div class="prj-progress-track" role="progressbar" aria-label="<?= e($milestone['name']); ?>" aria-valuenow="<?= e($done); ?>" aria-valuemin="0" aria-valuemax="100"><div class="prj-progress-bar" style="width:<?= e($done); ?>%"></div></div><span class="prj-progress-text"><?= e($done); ?>%</span></div>
        <?php } ?>
    </div>
    <?php } ?>
</div></section>
<?php } ?>
<?php $this->load->view('../../modules/sc_projects_workspace/views/projects/punchlist'); ?>
