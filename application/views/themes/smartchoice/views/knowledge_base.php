
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
.sc-kb-category-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:18px}.sc-kb-category-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.08);transition:transform .18s ease,box-shadow .18s ease}.sc-kb-category-card:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(15,23,42,.13)}.sc-kb-category-topline{height:6px;background:var(--sc-kb-accent)}.sc-kb-category-body{padding:20px}.sc-kb-category-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--sc-kb-accent) 14%,white);color:var(--sc-kb-accent);font-size:18px}.sc-kb-category-card h3{font-size:18px;margin:13px 0 5px}.sc-kb-category-card h3 a{color:#0f172a!important}.sc-kb-count{display:inline-block;border:1px solid var(--sc-kb-accent);color:var(--sc-kb-accent);border-radius:999px;padding:3px 9px;font-size:12px;font-weight:700}.sc-kb-category-card p{color:#64748b;min-height:42px;margin:12px 0}.sc-kb-open{display:inline-flex;align-items:center;gap:7px;color:var(--sc-kb-accent)!important;font-weight:800!important}@media(max-width:991px){.sc-kb-category-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.sc-kb-category-grid{grid-template-columns:1fr}.sc-kb-category-body{padding:16px}}
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