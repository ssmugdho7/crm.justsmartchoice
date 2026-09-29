<?php defined('BASEPATH') or exit('No direct script access allowed');
$active = isset($goals_nav_active) ? $goals_nav_active : 'dashboard';
$items = [
    'dashboard' => ['url' => admin_url('goals'), 'icon' => 'fa-solid fa-chart-line', 'label' => _l('goals_dashboard')],
    'goals' => ['url' => admin_url('goals'), 'icon' => 'fa-solid fa-bullseye', 'label' => _l('goals')],
    'new' => ['url' => admin_url('goals/goal'), 'icon' => 'fa-solid fa-plus', 'label' => _l('new_goal')],
    'reports' => ['url' => admin_url('goals/reports'), 'icon' => 'fa-solid fa-chart-column', 'label' => _l('goals_reports')],
    'health' => ['url' => admin_url('goals/health'), 'icon' => 'fa-solid fa-heart-pulse', 'label' => _l('goals_health_check')],
    'help' => ['url' => admin_url('goals/help'), 'icon' => 'fa-solid fa-circle-question', 'label' => _l('goals_help_guide')],
];
?>
<nav class="sc-goals-nav" aria-label="<?php echo html_escape(_l('goals')); ?>">
<?php foreach ($items as $key => $item) { if ($key === 'new' && staff_cant('create','goals')) { continue; } ?>
<a href="<?php echo $item['url']; ?>" class="sc-goals-nav-link <?php echo $active === $key ? 'active' : ''; ?>">
<i class="<?php echo $item['icon']; ?>"></i><span><?php echo $item['label']; ?></span>
</a>
<?php } ?>
</nav>
