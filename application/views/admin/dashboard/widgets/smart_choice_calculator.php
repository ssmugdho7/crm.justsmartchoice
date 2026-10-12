<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="widget-smart-choice-calculator" class="widget relative">
  <div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa-solid fa-calculator text-info"></i> Calculator</h4>
    <input type="text" id="sc-core-calc-input" class="form-control" placeholder="Example: 1250 * 0.50" aria-label="Calculation" autocomplete="off">
    <div class="mtop10" style="display:flex;gap:6px;flex-wrap:wrap">
      <button type="button" class="btn btn-info btn-sm" id="sc-core-calc-btn">Calculate</button>
      <button type="button" class="btn btn-default btn-sm" id="sc-core-calc-reset">Reset</button>
      <button type="button" class="btn btn-default btn-sm" id="sc-core-calc-copy" disabled><i class="fa-regular fa-copy" aria-hidden="true"></i> Copy</button>
    </div>
    <strong id="sc-core-calc-result" role="status" aria-live="polite" class="tw-block tw-mt-2"></strong>
    <small id="sc-core-calc-feedback" role="status" aria-live="polite" class="text-muted"></small>
  </div></div>
<script>
(function () {
  'use strict';
  // Parse arithmetic without eval/Function, including decimal values and percentages.
  function calculate(text) {
    var source = text.replace(/\b\d{1,3}(?:,\d{3})+(?:\.\d+)?\b/g, function (number) { return number.replace(/,/g, ''); }).replace(/\s+/g, '').replace(/×/g, '*').replace(/÷/g, '/'), at = 0;
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
  var reset = document.getElementById('sc-core-calc-reset');
  var copy = document.getElementById('sc-core-calc-copy');
  var feedback = document.getElementById('sc-core-calc-feedback');
  if (!button || !input || !result || !reset || !copy || !feedback) return;
  var currentResult = null;
  function clearResult() {
    currentResult = null;
    result.textContent = '';
    feedback.textContent = '';
    copy.disabled = true;
  }
  function showResult() {
    clearResult();
    try {
      currentResult = calculate(input.value).toLocaleString('en-US', { maximumFractionDigits: 10 });
      result.textContent = currentResult;
      copy.disabled = false;
    } catch (_) { result.textContent = 'Enter a valid calculation (example: 1250 * 50%).'; }
  }
  function legacyCopy(value) {
    var field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();
    var copied = false;
    try { copied = document.execCommand('copy'); } catch (_) {}
    field.remove();
    copy.focus();
    return copied;
  }
  copy.addEventListener('click', async function () {
    if (currentResult === null) return;
    var value = currentResult, copied = false;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(value);
        copied = true;
      } else { copied = legacyCopy(value); }
    } catch (_) { copied = legacyCopy(value); }
    if (currentResult === value) {
      feedback.textContent = copied ? 'Result copied.' : 'Could not copy automatically. Select and copy the result.';
    }
  });
  reset.addEventListener('click', function () { input.value = ''; clearResult(); input.focus(); });
  input.addEventListener('input', clearResult);
  button.addEventListener('click', showResult);
  input.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); showResult(); } });
})();
</script>
</div>
