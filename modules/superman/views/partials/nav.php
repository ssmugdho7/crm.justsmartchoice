<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$supermanCurrentUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
$supermanNavItems = [
    ['label' => _l('superman_control_center'), 'url' => admin_url('superman'), 'icon' => 'fa fa-bolt', 'match' => '/admin/superman'],
    ['label' => _l('superman_merge_catalog'), 'url' => admin_url('superman/merge_fields'), 'icon' => 'fa fa-search', 'match' => '/admin/superman/merge_fields'],
    ['label' => _l('superman_health_check'), 'url' => admin_url('superman/health'), 'icon' => 'fa fa-heartbeat', 'match' => '/admin/superman/health'],
    ['label' => _l('superman_settings_title'), 'url' => admin_url('superman/settings'), 'icon' => 'fa fa-sliders', 'match' => '/admin/superman/settings'],
];
?>
<div class="superman-module-nav">
    <div class="superman-module-nav-brand"><i class="fa fa-bolt"></i> <?php echo _l('superman_menu_name'); ?></div>
    <?php foreach ($supermanNavItems as $item) { ?>
        <?php
        $isActive = false;
        if ($item['match'] === '/admin/superman') {
            $isActive = (strpos($supermanCurrentUri, '/admin/superman') !== false && strpos($supermanCurrentUri, '/admin/superman/') === false);
        } else {
            $isActive = strpos($supermanCurrentUri, $item['match']) !== false;
        }
        $label = ucwords(strtolower(str_replace(['_', '-'], ' ', (string) $item['label'])));
        ?>
        <a href="<?php echo $item['url']; ?>" class="superman-nav-btn <?php echo $isActive ? 'active' : ''; ?>">
            <i class="<?php echo html_escape($item['icon']); ?>"></i>
            <span><?php echo html_escape($label); ?></span>
        </a>
    <?php } ?>
</div>
