<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="sconb-help" id="sconb-help" data-sconb-help>
            <section class="sconb-hero sconb-fade-up" aria-labelledby="sconb-help-title">
                <div class="sconb-hero-glow" aria-hidden="true"></div>
                <div class="sconb-hero-main">
                    <p class="sconb-hero-kicker"><?= e(_l('sc_onb_js_help_kicker')); ?></p>
                    <h1 id="sconb-help-title" class="sconb-hero-title"><?= e(_l('sc_onb_help_title')); ?></h1>
                    <p class="sconb-hero-lead"><?= e(_l('sc_onb_js_help_lead')); ?></p>
                    <div class="sconb-hero-actions" data-help-actions></div>
                </div>
                <div class="sconb-hero-progress" data-help-progress aria-live="polite"></div>
            </section>

            <section class="sconb-panel sconb-fade-up" aria-labelledby="sconb-flow-title" style="animation-delay:80ms">
                <h2 id="sconb-flow-title" class="sconb-section-title"><?= e(_l('sc_onb_js_help_flow_title')); ?></h2>
                <ol class="sconb-flow" data-help-flow></ol>
            </section>

            <div class="sconb-toolbar sconb-fade-up" style="animation-delay:140ms">
                <label class="sr-only" for="sconb-search"><?= e(_l('sc_onb_js_help_search')); ?></label>
                <div class="sconb-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="sconb-search" class="form-control" autocomplete="off"
                        placeholder="<?= e(_l('sc_onb_js_help_search')); ?>" data-help-search>
                </div>
                <p class="sconb-shortcuts"><?= e(_l('sc_onb_js_help_shortcuts')); ?></p>
            </div>

            <div class="sconb-groups" data-help-groups>
                <div class="sconb-loading"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
            </div>
            <div data-help-error></div>
            <noscript><div class="alert alert-warning sconb-noscript"><?= e(_l('sc_onb_noscript')); ?></div></noscript>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?= e(sc_onb_asset_url('assets/js/help.js')); ?>"></script>
</body>
</html>
