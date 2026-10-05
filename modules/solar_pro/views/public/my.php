<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-12 sc-dashboard">
    <div class="sc-dashboard-welcome"><div><h1><?= e(_l('solar_pro_my_solar')); ?></h1><p><?= e(_l('solar_pro_my_solar_subtitle')); ?></p></div></div>
    <div class="sc-dashboard-resource-grid">
        <?php foreach ($analyses as $a) { ?>
        <article class="sc-dashboard-project">
            <h3><?= e($a['address']); ?></h3>
            <p><?= e(number_format($a['system_kw'], 3)); ?> kW / <?= (int) $a['panel_count']; ?> <?= e(_l('solar_pro_panels')); ?> / <?= e(number_format($a['annual_production_kwh'])); ?> kWh</p>
            <a class="btn btn-default" href="<?= site_url('solar_pro/portal/' . rawurlencode($a['public_token'])); ?>"><?= e(_l('solar_pro_view_report')); ?></a>
        </article>
        <?php } ?>
    </div>
    <?php if (!$analyses) { ?><div class="sc-dashboard-empty"><i class="fa-regular fa-sun" aria-hidden="true"></i><?= e(_l('solar_pro_no_customer_reports')); ?></div><?php } ?>
</div>
