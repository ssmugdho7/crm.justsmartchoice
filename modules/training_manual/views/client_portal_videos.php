<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php get_template_part('head'); ?>
<style>.training-manual-client-video-card{min-height:180px;border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,.08)}.training-manual-client-video-card .panel-body{min-height:180px;display:flex;flex-direction:column}.training-manual-client-video-card .btn{margin-top:auto}</style>
<div id="wrapper">
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="mbot20"><i class="fa fa-video-camera"></i> <?php echo _l('training_manual_customer_videos'); ?></h3>
                    <?php if (empty($articles)) { ?>
                        <div class="alert alert-info"><?php echo _l('training_manual_no_customer_videos'); ?></div>
                    <?php } else { ?>
                        <div class="row training-manual-client-video-grid">
                            <?php foreach ($articles as $article) { ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="panel_s training-manual-client-video-card">
                                        <div class="panel-body">
                                            <h4><?php echo html_escape($article['title'] ?? ''); ?></h4>
                                            <p><?php echo html_escape(mb_strimwidth(strip_tags($article['description'] ?? ''), 0, 120, '...')); ?></p>
                                            <a class="btn btn-primary btn-sm" href="<?php echo site_url('training_manual/' . (!empty($article['short_code']) ? $article['short_code'] : $article['slug'])); ?>" target="_blank">
                                                <i class="fa fa-play-circle"></i> <?php echo _l('view'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_template_part('scripts'); ?>
</body>
</html>
