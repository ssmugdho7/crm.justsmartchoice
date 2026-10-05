<?php defined('BASEPATH') or exit('No direct script access allowed');
// Presentation categories only: all records still come from the existing permission-filtered controller.
$categories = ['service' => 'Service Experience', 'portal' => 'Client Portal', 'projects' => 'Project & Construction', 'company' => 'Company & Standards', 'training' => 'Training Library'];
$guides = [];
foreach ($books as $book) {
    $name = (string) ($book['name'] ?? '');
    $category = 'service';
    if (preg_match('/portal|support|communication|estimates|invoices|payments|financing/i', $name)) { $category = 'portal'; }
    elseif (preg_match('/vision|values|quality standards/i', $name)) { $category = 'company'; }
    elseif (preg_match('/pre-construction|permit history|scheduling|inspections|quality control|preparing|safety|maintenance/i', $name)) { $category = 'projects'; }
    foreach (($book['articles'] ?? []) as $article) {
        $description = trim(strip_tags((string) ($article['description'] ?? '')));
        $articleCategory = preg_match('/training|educational|trains employees|trains staff/i', $name . ' ' . $description) ? 'training' : $category;
        $guides[] = ['article' => $article, 'book' => $name, 'cover' => $book['cover_image'] ?? '', 'category' => $articleCategory, 'description' => $description ?: trim(strip_tags((string) ($book['short_description'] ?? '')))];
    }
}
$featured = [];
foreach (['How to Use the Client Portal', 'Our Project Process', 'What to Expect From Our Services', 'Support Tickets and Communication'] as $featuredBook) {
    foreach ($guides as $guide) {
        if ($guide['book'] === $featuredBook && $guide['category'] !== 'training') { $featured[] = $guide; break; }
    }
}
?>
<link rel="stylesheet" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH . '/css/help_library.css?v=4'); ?>">
<section class="sc-help-library" aria-labelledby="help-library-title">
    <header class="sc-help-header">
        <span class="sc-help-eyebrow"><i class="fa fa-book-open" aria-hidden="true"></i> CUSTOMER RESOURCES</span>
        <h1 id="help-library-title"><?php echo _l('training_manual_training_library'); ?></h1>
        <p>Everything you need to understand our services, your project, and how to use your customer portal.</p>
    </header>
    <?php if ($guides) { ?>
    <div class="sc-help-controls" hidden>
        <div class="sc-help-search">
            <label for="help-library-search">Search documentation</label>
            <div class="sc-help-input-wrap"><i class="fa fa-search" aria-hidden="true"></i><input id="help-library-search" type="search" placeholder="Search titles and descriptions…" autocomplete="off" aria-controls="help-guide-sections"><button type="button" id="help-search-clear" aria-label="Clear search" hidden><i class="fa fa-times" aria-hidden="true"></i></button></div>
        </div>
        <div class="sc-help-filter"><label for="help-library-category">Category</label><select id="help-library-category" aria-controls="help-guide-sections"><option value="all">All categories</option><?php foreach ($categories as $key => $label) { ?><option value="<?php echo $key; ?>"><?php echo $label; ?></option><?php } ?></select></div>
    </div>
    <?php if ($featured) { ?>
    <section class="sc-help-featured" aria-labelledby="help-featured-title"><div class="sc-help-section-heading"><div><h2 id="help-featured-title">Start here</h2><p>Featured guides for a smoother project experience.</p></div></div><div class="sc-help-grid">
    <?php foreach ($featured as $guide) { $isFeatured = true; require __DIR__ . '/partials/help_guide_card.php'; } ?>
    </div></section>
    <?php } ?>
    <div class="sc-help-results-heading"><h2>Browse all guides</h2><p id="help-result-count" role="status" aria-live="polite"><?php echo count($guides); ?> guides available</p></div>
    <div id="help-guide-sections">
    <?php foreach ($categories as $key => $label) {
        $categoryGuides = array_values(array_filter($guides, static function ($guide) use ($key) { return $guide['category'] === $key; }));
        if (!$categoryGuides) { continue; } ?>
        <section class="sc-help-category" data-category="<?php echo $key; ?>" aria-labelledby="help-category-<?php echo $key; ?>"><div class="sc-help-section-heading"><h3 id="help-category-<?php echo $key; ?>"><?php echo $label; ?></h3><span><?php echo count($categoryGuides) . (count($categoryGuides) === 1 ? ' guide' : ' guides'); ?></span></div>
        <?php if ($key === 'training') { ?><p class="sc-help-category-intro">Educational resources and professional training, separate from customer help guides.</p><?php } ?>
        <div class="sc-help-grid"><?php foreach ($categoryGuides as $guide) { $isFeatured = false; require __DIR__ . '/partials/help_guide_card.php'; } ?></div></section>
    <?php } ?>
    </div>
    <div class="sc-help-empty" id="help-no-results" hidden><i class="fa fa-search" aria-hidden="true"></i><h3>No guides found</h3><p>Try a different keyword or clear your search and category filter.</p><button type="button" id="help-reset">Show all guides</button></div>
    <?php } else { ?>
    <div class="sc-help-empty"><i class="fa fa-book" aria-hidden="true"></i><h2><?php echo _l('training_manual_no_customer_books_title'); ?></h2><p><?php echo _l('training_manual_no_customer_books'); ?></p></div>
    <?php } ?>
</section>
<script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH . '/js/help_library.js?v=2'); ?>" defer></script>
