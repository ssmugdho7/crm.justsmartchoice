<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="widget-smart-choice-calculator" class="widget relative">
  <div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa-solid fa-calculator text-info"></i> Calculator</h4>
    <input type="text" id="sc-core-calc-input" class="form-control" placeholder="Example: 1250 * 0.50">
    <button type="button" class="btn btn-info btn-sm mtop10" id="sc-core-calc-btn">Calculate</button>
    <strong id="sc-core-calc-result" class="tw-block tw-mt-2"></strong>
  </div></div>
</div>
<script>(function(){var b=document.getElementById('sc-core-calc-btn');if(!b)return;b.addEventListener('click',function(){var i=document.getElementById('sc-core-calc-input'),r=document.getElementById('sc-core-calc-result');try{if(!/^[0-9+\-*/().% ]+$/.test(i.value))throw new Error();r.textContent=Function('return ('+i.value.replace(/%/g,'/100')+')')()}catch(e){r.textContent='Invalid calculation'}})})();</script>
