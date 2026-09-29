<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/customer_training_library.css?v=135'); ?>">
<div class="container training-library-container">
    <div class="training-library-heading text-center">
        <h1><?php echo _l('training_manual_training_library'); ?></h1>
        <p><?php echo _l('training_manual_training_library_intro'); ?></p>
    </div>
    <?php if (empty($books)) { ?>
        <div class="training-library-empty text-center">
            <i class="fa fa-book"></i>
            <h2><?php echo _l('training_manual_no_customer_books_title'); ?></h2>
            <p><?php echo _l('training_manual_no_customer_books'); ?></p>
        </div>
    <?php } else { ?>
        <div class="training-library-grid">
            <?php foreach ($books as $index => $book) {
                $cover = training_manual_customer_image_url($book['cover_image'] ?? '', $book['name'] ?? ''); ?>
                <div class="panel_s training-library-book accent-<?php echo ($index % 4) + 1; ?>">
                    <div class="training-library-cover">
                        <img src="<?php echo html_escape($cover); ?>"
                             data-fallback="<?php echo html_escape(training_manual_customer_image_url('', $book['name'] ?? '')); ?>"
                             onerror="if(this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
                             alt="<?php echo html_escape($book['name']); ?>" loading="lazy">
                    </div>
                    <div class="panel-body">
                        <div class="training-library-book-title">
                            <span><i class="fa fa-book"></i></span>
                            <div>
                                <h3><?php echo html_escape($book['name']); ?></h3>
                                <p><?php echo html_escape($book['short_description']); ?></p>
                            </div>
                        </div>
                        <div class="list-group training-library-article-list">
                            <?php foreach (($book['articles'] ?? []) as $article) {
                                $thumb = training_manual_customer_image_url($article['thumbnail'] ?? '', ($article['title'] ?? '') . ' ' . ($book['name'] ?? '')); ?>
                                <a class="list-group-item" href="<?php echo site_url('training_manual/customer-books/article/' . (int) $article['id']); ?>">
                                    <img class="article-thumb" src="<?php echo html_escape($thumb); ?>"
                                         data-fallback="<?php echo html_escape(training_manual_customer_image_url('', ($article['title'] ?? '') . ' ' . ($book['name'] ?? ''))); ?>"
                                         onerror="if(this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
                                         alt="" loading="lazy">
                                    <span class="article-copy">
                                        <strong><?php echo html_escape($article['title']); ?></strong>
                                        <?php if (!empty($article['description'])) { ?><small><?php echo html_escape(mb_strimwidth(strip_tags($article['description']), 0, 180, '...')); ?></small><?php } ?>
                                    </span>
                                    <i class="fa fa-chevron-right article-arrow"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<script>
(function () {
    document.body.classList.add('training-manual-library-page');
    window.addEventListener('load', function () {
        var pageTitle = <?php echo json_encode(_l('training_manual_training_library')); ?>;
        document.querySelectorAll('footer *').forEach(function (node) {
            if (node.children.length === 0 && node.textContent.trim() === pageTitle) {
                var block = node.closest('.panel, .panel_s, .footer-title, .page-title, .section-heading, div');
                if (block) { block.style.display = 'none'; }
            }
        });
    });
})();
</script>
