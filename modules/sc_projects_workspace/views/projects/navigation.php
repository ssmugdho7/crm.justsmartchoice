<?php defined('BASEPATH') or exit('No direct script access allowed');
$method = strtolower($this->router->fetch_method()); ?>
<nav class="scpr-nav" aria-label="<?= e(_l('projects')); ?>">
    <a href="<?= admin_url('projects'); ?>"<?= $method === 'index' ? ' class="active" aria-current="page"' : ''; ?>><?= e(_l('scpr_directory')); ?></a>
    <?php if (staff_can('create', 'projects')) { ?>
    <a href="<?= admin_url('projects/project'); ?>"<?= $method === 'project' && !isset($project) ? ' class="active" aria-current="page"' : ''; ?>><?= e(_l('new_project')); ?></a>
    <?php } ?>
    <a href="<?= admin_url('projects/gantt'); ?>"<?= $method === 'gantt' ? ' class="active" aria-current="page"' : ''; ?>><?= e(_l('scpr_gantt')); ?></a>
    <?php if (isset($project)) { ?>
    <a href="<?= admin_url('projects/view/' . (int) $project->id); ?>"<?= $method === 'view' ? ' class="active" aria-current="page"' : ''; ?>><?= e(_l('project')); ?> #<?= e($project->id); ?></a>
    <?php if (staff_can('edit', 'projects')) { ?>
    <a href="<?= admin_url('projects/project/' . (int) $project->id); ?>"<?= $method === 'project' ? ' class="active" aria-current="page"' : ''; ?>><?= e(_l('edit_project')); ?></a>
    <?php } ?>
    <?php } ?>
</nav>
