<?php defined('BASEPATH') or exit('No direct script access allowed');
$selectedGroup = $this->input->get('group') ?: 'project_overview';
$labels = ['project_files' => 'scpr_tab_files', 'project_gantt' => 'scpr_gantt', 'project_activity' => 'scpr_tab_activity', 'project_notes' => 'scpr_tab_notes']; ?>
<nav aria-label="<?= e(_l('scpr_workspace')); ?>">
<ul class="nav nav-pills nav-stacked project-tabs scpr-tabs">
<?php foreach (filter_project_visible_tabs($tabs, $project->settings->available_features) as $key => $projectTab) { ?>
    <?php if (isset($projectTab['collapse'])) { ?>
    <li class="project_tab_<?= e($key); ?> scpr-tab-group">
        <span class="scpr-nav-group"><i class="<?= e($projectTab['icon']); ?>" aria-hidden="true"></i> <?= e($projectTab['name']); ?></span>
        <ul>
        <?php foreach ($projectTab['children'] as $child) { ?>
            <li class="<?= $selectedGroup === $child['slug'] ? 'active' : ''; ?>">
                <a data-group="<?= e($child['slug']); ?>" href="<?= e(admin_url('projects/view/' . (int) $project->id . '?group=' . $child['slug'])); ?>"<?= $selectedGroup === $child['slug'] ? ' aria-current="page"' : ''; ?>>
                    <?= e($child['name']); ?>
                    <?php if (!empty($child['badge']['value'])) { ?><span class="badge"><?= e($child['badge']['value']); ?></span><?php } ?>
                </a>
            </li>
        <?php } ?>
        </ul>
    </li>
    <?php } else { ?>
    <li class="project_tab_<?= e($key); ?><?= $selectedGroup === $key ? ' active' : ''; ?>">
        <a data-group="<?= e($key); ?>" href="<?= e(admin_url('projects/view/' . (int) $project->id . '?group=' . $key)); ?>"<?= $selectedGroup === $key ? ' aria-current="page"' : ''; ?>>
            <i class="<?= e($projectTab['icon']); ?>" aria-hidden="true"></i>
            <?= e(isset($labels[$key]) ? _l($labels[$key]) : $projectTab['name']); ?>
            <?php if (!empty($projectTab['badge']['value'])) { ?><span class="badge"><?= e($projectTab['badge']['value']); ?></span><?php } ?>
        </a>
    </li>
    <?php } ?>
<?php } ?>
</ul>
</nav>
