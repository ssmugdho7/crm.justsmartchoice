<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $scColors=['#169179','#3598db','#f59e0b','#7c3aed','#ef4444','#0ea5e9']; ?>
<div class="sc-kb-portal-shell">
  <div class="sc-kb-portal-heading">
    <span class="sc-kb-eyebrow">SMART CHOICE CUSTOMER SUPPORT</span>
    <h2>Knowledge Base</h2>
    <p>Choose a category to find clear answers, project guidance, payment information, permitting help, and construction advice.</p>
  </div>
  <div class="sc-kb-category-grid" role="list">
  <?php foreach ($articles as $i=>$category) { $c=$scColors[$i%count($scColors)]; ?>
    <article class="sc-kb-category-card" role="listitem" style="--sc-kb-accent:<?= e($c); ?>">
      <div class="sc-kb-category-topline"></div>
      <div class="sc-kb-category-body">
        <div class="sc-kb-category-icon"><i class="fa-solid fa-book-open-reader"></i></div>
        <div class="sc-kb-category-title-row"><h3><a href="<?= site_url('knowledge-base/category/' . e($category['group_slug'])); ?>"><?= e($category['name']); ?></a></h3><span class="sc-kb-count"><?= e(count($category['articles'])); ?></span></div>
        <p><?= e($category['description']); ?></p>
        <div class="sc-kb-card-rule"></div>
        <a class="sc-kb-open" href="<?= site_url('knowledge-base/category/' . e($category['group_slug'])); ?>">View Questions <i class="fa-solid fa-arrow-right"></i></a>
      </div>
    </article>
  <?php } ?>
  </div>
</div>
