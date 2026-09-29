<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php get_template_part('head'); ?>
<?php get_template_part('navigation'); ?>
<link rel="stylesheet" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH . '/css/customer_training_library.css?v=130'); ?>">
<div id="wrapper"><div id="content">
<div class="container training-library-container">
    <div class="training-library-heading text-center">
        <span class="training-library-icon"><i class="fa fa-graduation-cap"></i></span>
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
        <div class="row">
        <?php foreach ($books as $book) { ?>
            <div class="col-md-6 col-sm-12">
                <div class="panel_s training-library-book">
                    <div class="panel-body">
                        <div class="training-library-book-title">
                            <span><i class="fa fa-book"></i></span>
                            <div>
                                <h3><?php echo html_escape($book['name']); ?></h3>
                                <p><?php echo html_escape($book['short_description']); ?></p>
                            </div>
                        </div>
                        <div class="list-group training-library-article-list">
                            <?php foreach (($book['articles'] ?? []) as $article) { ?>
                                <a class="list-group-item" href="<?php echo site_url('training_manual/customer-books/article/' . (int) $article['id']); ?>">
                                    <span class="article-icon"><i class="fa fa-file-text-o"></i></span>
                                    <span class="article-copy"><strong><?php echo html_escape($article['title']); ?></strong>
                                    <?php if (!empty($article['description'])) { ?><small><?php echo html_escape(mb_strimwidth(strip_tags($article['description']), 0, 220, '...')); ?></small><?php } ?></span>
                                    <i class="fa fa-chevron-right article-arrow"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    <?php } ?>
</div>
</div></div>
<?php get_template_part('footer'); ?>
<?php get_template_part('scripts'); ?>
</body></html>
