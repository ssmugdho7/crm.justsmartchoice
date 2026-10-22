<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Reuse the permission-filtered sidebar; do not expose extra records or query totals for navigation.
$scSalesSections = ['proposals', 'estimates', 'invoices', 'payments', 'credit_notes'];
$scSalesLinks = [];
$scCollectSalesLinks = static function (array $items) use (&$scCollectSalesLinks, &$scSalesLinks, $scSalesSections) {
    foreach ($items as $item) {
        foreach ($scSalesSections as $section) {
            if (rtrim((string) ($item['href'] ?? ''), '/') === rtrim(admin_url($section), '/')) {
                $scSalesLinks[$section] = ['name' => _l($section), 'href' => admin_url($section)];
            }
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            $scCollectSalesLinks($item['children']);
        }
    }
};
$scCollectSalesLinks($this->app_menu->get_sidebar_menu_items());
$scActiveSalesSection = strtolower($this->router->fetch_class());
$scSalesIcons = ['proposals' => 'fa-file-lines', 'estimates' => 'fa-calculator', 'invoices' => 'fa-file-invoice-dollar', 'payments' => 'fa-credit-card', 'credit_notes' => 'fa-receipt'];
?>
<header class="sales-header">
    <p class="sales-eyebrow"><?= e(get_option('companyname')); ?> <span aria-hidden="true">&bull;</span> <?= _l('sc_sales_workspace_subtitle'); ?></p>
    <h1><?= _l('sc_sales_workspace_title'); ?></h1>
</header>
<?php if ($scSalesLinks) { ?>
<nav class="sales-subnav" aria-label="<?= e(_l('als_sales')); ?>">
    <?php foreach ($scSalesSections as $section) {
        if (!isset($scSalesLinks[$section])) continue;
        $scSalesLink = $scSalesLinks[$section]; ?>
    <a href="<?= e($scSalesLink['href']); ?>" class="sales-subnav-btn<?= $section === $scActiveSalesSection ? ' active' : ''; ?>"<?= $section === $scActiveSalesSection ? ' aria-current="page"' : ''; ?>>
        <i class="fa-solid <?= e($scSalesIcons[$section]); ?>" aria-hidden="true"></i>
        <?= e($scSalesLink['name']); ?>
    </a>
    <?php } ?>
</nav>
<?php } ?>
