
<style id="smart-choice-client-portal-v344">
body.customers,body{--sc-green:#00A651;--sc-orange:#F96302;--sc-yellow:#F5B400;--sc-blue:#0077CC;--sc-light-blue:#2CA8FF;}
.navbar,.customers .navbar{background:linear-gradient(90deg,var(--sc-blue),var(--sc-light-blue),var(--sc-orange))!important;border:0!important;box-shadow:0 3px 14px rgba(0,0,0,.12)!important;}
.navbar .navbar-brand img{max-height:44px!important;width:auto!important;margin-top:3px!important;}
.navbar-nav>li>a{color:#fff!important;font-weight:600!important;}
.navbar-nav>li>a:hover{background:rgba(255,255,255,.16)!important;color:#fff!important;}
@media(max-width:767px){.navbar-collapse{background:#fff!important}.navbar-collapse .navbar-nav>li>a{color:#1f2937!important}.navbar-toggle{border-color:#fff!important}.navbar-toggle .icon-bar{background:#fff!important}.container,.container-fluid{width:100%!important;padding-left:14px!important;padding-right:14px!important}.panel_s,.panel-body{width:100%!important}.table-responsive{border:0!important}}
.kb-search,.knowledge-base-search{background:linear-gradient(135deg,#eaf7ff,#fff2e8)!important;border-radius:18px!important;padding:20px!important;}
.kb-category,.knowledge-base .panel_s,.knowledge-base article,.kb-article-single{border-radius:16px!important;border:1px solid #e5eef7!important;box-shadow:0 8px 22px rgba(0,0,0,.06)!important;overflow:hidden!important;}
.kb-category h4,.knowledge-base h4{color:var(--sc-blue)!important;font-weight:800!important;}
.kb-category a,.knowledge-base a{color:#075985!important;font-weight:600!important;}
</style>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="section-knowledge-base">
    <div class="row">
        <div
            class="col-md-<?= count($related_articles) == 0 ? 12 : 8; ?>">
            <div class="panel_s">
                <div class="panel-body">
                    <h1
                        class="tw-mt-0 tw-mb-4 kb-article-single-heading tw-font-semibold tw-text-xl tw-text-neutral-700">
                        <?= e($article->subject); ?>
                    </h1>
                    <div class="tc-content kb-article-content tw-text-neutral-700">
                        <?= $article->description; ?>
                    </div>
                    <h4 class="tw-font-medium tw-text-lg tw-mt-6">
                        <?= _l('clients_knowledge_base_find_useful'); ?>
                    </h4>
                    <div class="answer_response tw-mb-2 tw-text-neutral-500"></div>
                    <div class="btn-group article_useful_buttons" role="group">
                        <button type="button" data-answer="1" class="btn btn-success">
                            <?= _l('clients_knowledge_base_find_useful_yes'); ?>
                        </button>
                        <input type="hidden" name="articleid"
                            value="<?= e($article->articleid); ?>">
                        <button type="button" data-answer="0" class="btn btn-danger">
                            <?= _l('clients_knowledge_base_find_useful_no'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php hooks()->do_action('after_single_knowledge_base_article_customers_area', $article->articleid); ?>
        </div>
        <?php if (count($related_articles) > 0) { ?>
        <div class="col-md-4">
            <h4 class="kb-related-heading tw-font-semibold tw-text-lg tw-text-neutral-700 tw-mt-0 tw-my-0">
                <?= _l('related_knowledgebase_articles'); ?>
            </h4>
            <ul class="articles_list tw-divide-y tw-divide-neutral-200 tw-divide-solid tw-space-y-3">
                <?php foreach ($related_articles as $relatedArticle) { ?>
                <li class="tw-pt-3">
                    <h4 class="article-heading article-related-heading tw-text-normal tw-font-medium tw-my-0">
                        <a href="<?= site_url('knowledge-base/article/' . $relatedArticle['slug']); ?>"
                            class="tw-text-neutral-700 hover:tw-text-neutral-900 active:tw-text-neutral-900">
                            <?= e($relatedArticle['subject']); ?>
                        </a>
                    </h4>
                    <div class="tw-text-neutral-500">
                        <?= mb_substr(strip_tags($relatedArticle['description']), 0, 100); ?>...
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php }	?>
    </div>
</div>