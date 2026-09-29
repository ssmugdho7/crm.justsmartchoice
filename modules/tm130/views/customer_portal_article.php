<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php get_template_part('head'); ?>
<?php get_template_part('navigation'); ?>
<link rel="stylesheet" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH . '/css/customer_training_library.css?v=130'); ?>">
<div id="wrapper"><div id="content">
<div class="container training-customer-article-container">
    <a class="btn btn-default btn-sm mtop20" href="<?php echo site_url('training_manual/customer-books'); ?>"><i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?></a>
    <article class="panel_s training-customer-article mtop20">
        <div class="panel-body">
            <div class="training-customer-article-header">
                <span><i class="fa fa-book-open"></i></span>
                <div><p><?php echo _l('training_manual_training_library'); ?></p><h1><?php echo html_escape($article['title']); ?></h1></div>
            </div>
            <?php if (!empty($article['description'])) { ?><p class="training-customer-article-summary"><?php echo html_escape($article['description']); ?></p><?php } ?>
            <div class="training-manual-rendered-article"><?php echo $article['content']; ?></div>
        </div>
    </article>
</div>
</div></div>
<?php get_template_part('footer'); ?>
<?php get_template_part('scripts'); ?>
</body></html>
