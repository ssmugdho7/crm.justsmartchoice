<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="widget-smart-choice-calculator" class="widget relative">
  <div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa-solid fa-calculator text-info"></i> Calculator</h4>
    <input type="text" id="sc-core-calc-input" class="form-control" placeholder="Example: 1250 * 0.50">
    <button type="button" class="btn btn-info btn-sm mtop10" id="sc-core-calc-btn">Calculate</button>
    <strong id="sc-core-calc-result" role="status" aria-live="polite" class="tw-block tw-mt-2"></strong>
  </div></div>
</div>
<script>
(function () {
  'use strict';
  // Parse arithmetic without eval/Function, including decimal values and percentages.
  function calculate(text) {
    var source = text.replace(/,/g, '').replace(/\s+/g, ''), at = 0;
    function factor() {
      var sign = 1;
      while (source[at] === '+' || source[at] === '-') { if (source[at++] === '-') sign *= -1; }
      var value;
      if (source[at] === '(') {
        at++; value = expression();
        if (source[at++] !== ')') throw new Error();
      } else {
        var match = source.slice(at).match(/^(?:\d+(?:\.\d*)?|\.\d+)/);
        if (!match) throw new Error();
        at += match[0].length; value = Number(match[0]);
      }
      while (source[at] === '%') { at++; value /= 100; }
      return sign * value;
    }
    function term() {
      var value = factor();
      while (source[at] === '*' || source[at] === '/') {
        var op = source[at++], right = factor();
        value = op === '*' ? value * right : value / right;
      }
      return value;
    }
    function expression() {
      var value = term();
      while (source[at] === '+' || source[at] === '-') {
        var op = source[at++], right = term();
        value = op === '+' ? value + right : value - right;
      }
      return value;
    }
    if (!source || source.length > 500) throw new Error();
    var value = expression();
    if (at !== source.length || !Number.isFinite(value)) throw new Error();
    return Number(value.toPrecision(12));
  }
  var input = document.getElementById('sc-core-calc-input');
  var button = document.getElementById('sc-core-calc-btn');
  var result = document.getElementById('sc-core-calc-result');
  if (!button || !input || !result) return;
  function showResult() {
    try { result.textContent = calculate(input.value).toLocaleString('en-US', { maximumFractionDigits: 10 }); }
    catch (_) { result.textContent = 'Enter a valid calculation (example: 1250 * 50%).'; }
  }
  button.addEventListener('click', showResult);
  input.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); showResult(); } });
})();
</script>
