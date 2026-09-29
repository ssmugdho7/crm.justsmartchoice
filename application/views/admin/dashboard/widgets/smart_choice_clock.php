<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="widget-smart-choice-world-clock" class="widget relative">
  <div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa-regular fa-clock text-info"></i> World Clock</h4>
    <div class="row">
      <div class="col-xs-4 text-center"><strong>India</strong><div class="sc-core-clock" data-zone="Asia/Kolkata">--:--</div></div>
      <div class="col-xs-4 text-center"><strong>Pakistan</strong><div class="sc-core-clock" data-zone="Asia/Karachi">--:--</div></div>
      <div class="col-xs-4 text-center"><strong>Philippines</strong><div class="sc-core-clock" data-zone="Asia/Manila">--:--</div></div>
    </div>
  </div></div>
</div>
<script>(function(){function u(){document.querySelectorAll('.sc-core-clock').forEach(function(e){try{e.textContent=new Intl.DateTimeFormat(undefined,{timeZone:e.getAttribute('data-zone'),weekday:'short',hour:'numeric',minute:'2-digit',hour12:true}).format(new Date())}catch(x){e.textContent='--:--'}})}u();setInterval(u,30000)})();</script>
