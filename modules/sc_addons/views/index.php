<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= e(module_dir_url(SC_ADDONS_MODULE, 'assets/css/sc_addons.css')); ?>?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/sc_addons.css'); ?>">
<div id="wrapper">
  <div class="content">
    <div class="scao" id="scao-app">
      <div class="scao-head">
        <div>
          <h1 class="scao-title"><i class="fa-solid fa-puzzle-piece" aria-hidden="true"></i> <?= _l('sc_addons_title'); ?></h1>
          <p class="scao-sub"><?= _l('sc_addons_subtitle'); ?></p>
        </div>
        <button type="button" class="btn btn-default" id="scao-refresh"><i class="fa-solid fa-rotate" aria-hidden="true"></i> <span class="scao-refresh-label"><?= _l('sc_addons_refresh'); ?></span></button>
      </div>

      <ul class="nav nav-tabs scao-tabs" role="tablist">
        <li class="active" role="presentation"><a href="#scao-tab-integrations" id="scao-tabbtn-integrations" data-toggle="tab" role="tab" aria-controls="scao-tab-integrations" aria-selected="true"><?= _l('sc_addons_tab_integrations'); ?></a></li>
        <li role="presentation"><a href="#scao-tab-modules" id="scao-tabbtn-modules" data-toggle="tab" role="tab" aria-controls="scao-tab-modules" aria-selected="false" tabindex="-1"><?= _l('sc_addons_tab_modules'); ?> <span class="badge" id="scao-mod-count"></span></a></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane active" id="scao-tab-integrations" role="tabpanel" aria-labelledby="scao-tabbtn-integrations">
          <div class="scao-stats" id="scao-stats" role="group" aria-label="<?= e(_l('sc_addons_filter_status')); ?>">
            <?php foreach (['connected', 'needs_attention', 'not_configured', 'disabled', 'not_installed'] as $st) { ?>
              <button type="button" class="scao-stat scao-stat-<?= $st; ?>" data-filter-status="<?= $st; ?>" aria-pressed="false"><b data-count="<?= $st; ?>">0</b><span><?= _l('sc_addons_st_' . $st); ?></span></button>
            <?php } ?>
          </div>
          <div class="scao-toolbar">
            <input type="search" class="form-control" id="scao-search" placeholder="<?= e(_l('sc_addons_search')); ?>" aria-label="<?= e(_l('sc_addons_search')); ?>">
            <div class="scao-chips" id="scao-chips">
              <button type="button" class="scao-chip active" data-cat=""><?= _l('sc_addons_all'); ?></button>
              <?php foreach ($categories as $cat) { ?>
                <button type="button" class="scao-chip" data-cat="<?= e($cat); ?>"><?= e(_l('sc_addons_cat_' . strtolower(preg_replace('/[^a-z]+/i', '_', $cat)))); ?></button>
              <?php } ?>
            </div>
          </div>
          <div id="scao-sections"></div>
          <div class="scao-empty" id="scao-empty" hidden>
            <p><?= _l('sc_addons_none_found'); ?></p>
            <button type="button" class="btn btn-default btn-sm" id="scao-clear-filters"><?= _l('sc_addons_clear_filters'); ?></button>
          </div>
        </div>

        <div class="tab-pane" id="scao-tab-modules" role="tabpanel" aria-labelledby="scao-tabbtn-modules">
          <div class="scao-toolbar">
            <input type="search" class="form-control" id="scao-mod-search" placeholder="<?= e(_l('sc_addons_search_modules')); ?>" aria-label="<?= e(_l('sc_addons_search_modules')); ?>">
            <div class="scao-chips" id="scao-mod-chips" role="group" aria-label="<?= e(_l('sc_addons_filter_status')); ?>">
              <?php foreach (['' => 'sc_addons_js_mod_all', 'active' => 'sc_addons_js_mod_active', 'inactive' => 'sc_addons_js_mod_inactive', 'available' => 'sc_addons_js_mod_not_installed'] as $k => $lk) { ?>
                <button type="button" class="scao-chip<?= $k === '' ? ' active' : ''; ?>" data-mst="<?= $k; ?>" aria-pressed="<?= $k === '' ? 'true' : 'false'; ?>"><?= _l($lk); ?> <span class="scao-chip-n" data-mcount="<?= $k; ?>"></span></button>
              <?php } ?>
            </div>
            <label class="scao-sort"><span><?= _l('sc_addons_js_sort_label'); ?></span>
              <select class="form-control input-sm" id="scao-mod-sort">
                <option value="name"><?= _l('sc_addons_js_sort_name'); ?></option>
                <option value="status"><?= _l('sc_addons_js_sort_status'); ?></option>
              </select>
            </label>
          </div>
          <div class="scao-modlist" id="scao-modlist"></div>
          <div class="scao-more" id="scao-mod-more" hidden>
            <span class="text-muted" id="scao-mod-showing" aria-live="polite"></span>
            <button type="button" class="btn btn-default btn-sm" id="scao-mod-more-btn"><?= _l('sc_addons_js_show_more'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="scao-config-modal" tabindex="-1" role="dialog" aria-labelledby="scao-config-title">
  <div class="modal-dialog" role="document">
    <form class="modal-content" id="scao-config-form" autocomplete="off" novalidate>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="<?= e(_l('sc_addons_cancel')); ?>"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="scao-config-title"></h4>
      </div>
      <div class="modal-body">
        <p class="text-muted" id="scao-config-desc"></p>
        <div id="scao-config-status"></div>
        <div class="alert alert-warning scao-exclusive" id="scao-config-exclusive" hidden></div>
        <section class="scao-guide" id="scao-config-guide" hidden aria-labelledby="scao-guide-title">
          <h5 class="scao-guide-title" id="scao-guide-title"><i class="fa-solid fa-list-check" aria-hidden="true"></i> <?= _l('sc_addons_js_guide_title'); ?></h5>
          <ol class="scao-guide-steps" id="scao-guide-steps"></ol>
          <div class="scao-guide-urls" id="scao-guide-urls"></div>
          <a href="#" class="scao-guide-docs" id="scao-guide-docs" target="_blank" rel="noopener noreferrer" hidden></a>
        </section>
        <div id="scao-config-fields"></div>
        <p class="scao-link"><a href="#" id="scao-config-settings" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> <?= _l('sc_addons_open_settings'); ?><span class="sr-only"> (<?= _l('sc_addons_js_opens_new_tab'); ?>)</span></a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('sc_addons_cancel'); ?></button>
        <button type="submit" class="btn btn-default" data-mode="save" id="scao-config-save"><?= _l('sc_addons_save'); ?></button>
        <button type="submit" class="btn btn-primary" data-mode="connect" id="scao-config-connect"><?= _l('sc_addons_save_connect'); ?></button>
        <button type="submit" class="btn btn-primary" data-mode="oauth" id="scao-config-oauth" hidden></button>
      </div>
    </form>
  </div>
</div>

<script>
window.SCAO = {
  base: <?= json_encode(admin_url('sc_addons/'), JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  csrfName: <?= json_encode($this->security->get_csrf_token_name(), JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  cards: <?= json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  modules: <?= json_encode($modules, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  catLabels: <?= json_encode(array_combine($categories, array_map(fn ($c) => _l('sc_addons_cat_' . strtolower(preg_replace('/[^a-z]+/i', '_', $c))), $categories)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  t: <?= json_encode(sc_addons_js_strings(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>
};
</script>
<?php init_tail(); ?>
<script src="<?= e(module_dir_url(SC_ADDONS_MODULE, 'assets/js/sc_addons.js')); ?>?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/sc_addons.js'); ?>"></script>
</body>
</html>
