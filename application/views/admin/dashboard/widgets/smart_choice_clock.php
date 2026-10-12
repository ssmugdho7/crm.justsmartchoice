<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $scClocks = sc_get_team_clocks(); ?>
<div id="widget-smart-choice-world-clock" class="widget relative">
  <style>
    #widget-smart-choice-world-clock h4{font-size:14px}
    #widget-smart-choice-world-clock .sc-world-clock-item{font-size:12px;line-height:1.4;margin-bottom:8px;overflow-wrap:anywhere}
    #widget-smart-choice-world-clock .sc-core-clock{font-size:12px;white-space:nowrap}
  </style>
  <div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa-regular fa-clock text-info"></i> World Clock</h4>
    <div class="row">
      <?php foreach ($scClocks as $scClock) { ?>
      <div class="col-xs-4 text-center sc-world-clock-item"><strong><?= e($scClock['label']); ?></strong><div class="sc-core-clock" data-zone="<?= e($scClock['zone']); ?>">--:--</div></div>
      <?php } ?>
    </div>
  </div></div>
  <script>
  (function () {
    function updateClocks() {
      var now = new Date();
      document.querySelectorAll('#widget-smart-choice-world-clock .sc-core-clock').forEach(function (clock) {
        try {
          clock.textContent = new Intl.DateTimeFormat(undefined, {
            timeZone: clock.getAttribute('data-zone'), hour: 'numeric', minute: '2-digit', hour12: true
          }).format(now);
        } catch (error) {
          clock.textContent = '--:--';
        }
      });
    }
    // Keep this script inside the root: dashboard rendering extracts only that element.
    updateClocks();
    window.setInterval(updateClocks, 30000);
    document.addEventListener('visibilitychange', updateClocks);
  })();
  </script>
</div>
