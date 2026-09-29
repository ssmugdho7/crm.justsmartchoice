
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
<div class="panel_s">
	<div class="panel-body">
		<?php if (count($articles) == 0) { ?>
		<p class="no-margin">
			<?= _l('clients_knowledge_base_articles_not_found'); ?>
		</p>
		<?php } ?>
		<?php if (isset($category)) {
		    // Category articles list
		    get_template_part('knowledge_base/category_articles_list', ['articles' => $articles]);
		} elseif (isset($search_results)) {
		    // Search results
		    get_template_part('knowledge_base/search_results', ['articles' => $articles]);
		} else {
		    // Default page
		    get_template_part('knowledge_base/categories', ['articles' => $articles]);
		}
hooks()->do_action('after_kb_groups_customers_area');
?>
	</div>
</div>