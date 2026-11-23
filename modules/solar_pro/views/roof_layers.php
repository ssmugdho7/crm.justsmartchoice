<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="sp-roof" data-roof-url="<?php echo html_escape($roof_url); ?>">
<h2><?php echo _l('solar_pro_roof_title'); ?></h2>
<p><?php echo _l('solar_pro_roof_description'); ?></p>
<button type="button" class="btn btn-default sp-roof-load"><?php echo _l('solar_pro_roof_load'); ?></button>
<p class="sp-roof-status" role="status" aria-live="polite"></p>
<div class="sp-roof-controls" hidden>
<label><?php echo _l('solar_pro_roof_layer'); ?> <select class="sp-roof-mode"><option value="annual"><?php echo _l('solar_pro_roof_sunlight'); ?></option><option value="shade"><?php echo _l('solar_pro_roof_shade'); ?></option></select></label>
<span class="sp-roof-time" hidden>
<label><?php echo _l('solar_pro_roof_month'); ?> <select class="sp-roof-month"><?php foreach(solar_pro_month_names() as $n=>$month): ?><option value="<?php echo (int)$n; ?>"><?php echo html_escape($month); ?></option><?php endforeach; ?></select></label>
<label><?php echo _l('solar_pro_roof_day'); ?> <input class="sp-roof-day" type="number" value="21" min="1" max="31"></label>
<label><?php echo _l('solar_pro_roof_hour'); ?> <input class="sp-roof-hour" type="range" value="12" min="0" max="23"><output>12:00</output></label>
</span>
</div>
<canvas class="sp-roof-canvas" hidden role="img" aria-label="<?php echo _l('solar_pro_roof_title'); ?>"></canvas>
<p class="sp-roof-legend" hidden><span class="sp-roof-gradient"></span> <span></span></p>
<p class="sp-roof-credit" hidden><strong>Google Maps</strong> · <span></span></p>
<p class="sp-roof-note"><?php echo _l('solar_pro_roof_note'); ?></p>
</section>
<link rel="stylesheet" href="<?php echo module_dir_url('solar_pro', 'assets/css/solar-roof.css'); ?>">
<script src="<?php echo module_dir_url('solar_pro', 'assets/vendor/geotiff/geotiff.js'); ?>"></script>
<script src="<?php echo module_dir_url('solar_pro', 'assets/js/solar-roof.js'); ?>" defer></script>
