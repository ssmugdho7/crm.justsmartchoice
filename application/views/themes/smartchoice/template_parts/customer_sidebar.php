<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Use the same permission-filtered menu as the header, including enabled modules.
$sections = ['Workspace' => [], 'Finance & Legal' => [], 'Services & Booking' => [], 'Support & Guides' => []];
$sections['Workspace'][] = ['name' => _l('dashboard_string'), 'href' => site_url('clients'), 'icon' => 'fa-solid fa-house'];
$routeGroups = [
    'clients/projects' => ['Workspace', 'fa-solid fa-diagram-project'],
    'solar_pro/' => ['Workspace', 'fa-regular fa-sun'],
    'clients/invoices' => ['Finance & Legal', 'fa-solid fa-file-invoice-dollar'],
    'clients/estimates' => ['Finance & Legal', 'fa-solid fa-calculator'],
    'clients/contracts' => ['Finance & Legal', 'fa-solid fa-file-signature'],
    'clients/proposals' => ['Finance & Legal', 'fa-regular fa-file-lines'],
    'clients/subscriptions' => ['Finance & Legal', 'fa-solid fa-repeat'],
    'appointly/' => ['Services & Booking', 'fa-regular fa-calendar-check'],
    'google_meet/' => ['Services & Booking', 'fa-solid fa-video'],
    'products/' => ['Services & Booking', 'fa-solid fa-cart-shopping'],
    'knowledge-base' => ['Support & Guides', 'fa-solid fa-book-open'],
    'training_manual/' => ['Support & Guides', 'fa-solid fa-book'],
    'clients/tickets' => ['Support & Guides', 'fa-solid fa-headset'],
];
foreach ($menu as $item) {
    $group = 'Services & Booking';
    $icon = 'fa-solid fa-arrow-up-right-from-square';
    foreach ($routeGroups as $route => $mapping) {
        if (strpos($item['href'], site_url($route)) === 0) {
            [$group, $icon] = $mapping;
            break;
        }
    }
    $item['icon'] = !empty($item['icon']) ? $item['icon'] : $icon;
    if (rtrim($item['href'], '/') !== rtrim(site_url('clients'), '/') && rtrim($item['href'], '/') !== rtrim(site_url(), '/')) {
        $sections[$group][] = $item;
    }
}
$sections['Support & Guides'][] = ['name' => _l('customer_profile_files'), 'href' => site_url('clients/files'), 'icon' => 'fa-solid fa-paperclip'];
$sections['Services & Booking'][] = ['name' => _l('calendar'), 'href' => site_url('clients/calendar'), 'icon' => 'fa-regular fa-calendar'];
$sections['Support & Guides'][] = ['name' => _l('clients_nav_profile'), 'href' => site_url('clients/profile'), 'icon' => 'fa-regular fa-user'];
$currentPath = rtrim((string) parse_url(current_full_url(), PHP_URL_PATH), '/');
// Detail routes belong to the existing list destination.
foreach (['project' => 'projects', 'ticket' => 'tickets'] as $detail => $list) {
    $prefix = rtrim((string) parse_url(site_url('clients/' . $detail), PHP_URL_PATH), '/') . '/';
    if (strpos($currentPath, $prefix) === 0) {
        $currentPath = rtrim((string) parse_url(site_url('clients/' . $list), PHP_URL_PATH), '/');
    }
}
?>
<button type="button" class="sc-portal-backdrop" aria-label="Close customer navigation" hidden></button>
<aside id="sc-customer-sidebar" class="sc-portal-sidebar" aria-label="Customer navigation">
    <div class="sc-portal-sidebar-title">
        <strong>Customer Portal</strong>
        <button type="button" class="sc-portal-collapse" aria-label="Collapse customer navigation" title="Collapse customer navigation" aria-expanded="true"><i class="fa-solid fa-angles-left" aria-hidden="true"></i></button>
        <button type="button" class="sc-portal-close" aria-label="Close customer navigation" title="Close customer navigation"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <nav>
        <?php foreach ($sections as $heading => $items) { if (!$items) { continue; } ?>
        <div class="sc-portal-nav-group" data-portal-group="<?= e($heading); ?>">
            <h2><?= e($heading); ?></h2>
            <?php foreach ($items as $item) {
                $path = rtrim((string) parse_url($item['href'], PHP_URL_PATH), '/');
                $active = $currentPath === $path || ($path !== rtrim((string) parse_url(site_url('clients'), PHP_URL_PATH), '/') && strpos($currentPath, $path . '/') === 0);
                if ($item['href'] === site_url('clients') && $currentPath === rtrim((string) parse_url(site_url(), PHP_URL_PATH), '/')) { $active = true; }
            ?>
            <a href="<?= e($item['href']); ?>" title="<?= e($item['name']); ?>" aria-label="<?= e($item['name']); ?>"<?= $active ? ' aria-current="page"' : ''; ?> <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                <i class="<?= e($item['icon']); ?>" aria-hidden="true"></i><span><?= e($item['name']); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php } ?>
    </nav>
</aside>

<nav class="sc-portal-mobile-nav" aria-label="Customer shortcuts">
    <a href="<?= site_url('clients'); ?>" aria-label="Home"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span></a>
    <?php if (has_contact_permission('projects')) { ?><a href="<?= site_url('clients/projects'); ?>" aria-label="Work"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span>Work</span></a><?php } ?>
    <?php if (has_contact_permission('invoices')) { ?><a href="<?= site_url('clients/invoices'); ?>" aria-label="Money"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i><span>Money</span></a><?php } ?>
    <?php if (has_contact_permission('support')) { ?><a href="<?= site_url('clients/tickets'); ?>" aria-label="Support"><i class="fa-solid fa-headset" aria-hidden="true"></i><span>Support</span></a><?php } ?>
    <button type="button" class="sc-portal-more" aria-controls="sc-customer-sidebar" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i><span>More</span></button>
</nav>
