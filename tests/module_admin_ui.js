const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.join(__dirname, '..');
function script(view) { return fs.readFileSync(path.join(root, 'application/views/admin/modules', view), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1]; }
function element(extra = {}) {
  const classes = new Set();
  return Object.assign({style: {}, textContent: '', dataset: {}, handlers: {}, attrs: {},
    classList: {toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); }, add(name) { classes.add(name); }},
    addEventListener(name, handler) { this.handlers[name] = handler; }, setAttribute(name, value) { this.attrs[name] = value; }}, extra);
}
function filterFixture(errorCount) {
  const rows = [element({dataset: {active: '1', update: '0', error: errorCount ? '1' : '0'}, textContent: 'Alpha module'}),
    element({dataset: {active: '0', update: '1', error: '0'}, textContent: 'Beta module'})];
  const filters = ['all', 'active', 'inactive', 'update', 'error'].map(name => element({dataset: {scFilter: name}}));
  const counts = Object.fromEntries(filters.map(f => ['sc_count_' + f.dataset.scFilter, element()]));
  const input = element({value: ''}), empty = element();
  const els = {...counts, wrapper: element({querySelectorAll() { return filters; }}), sc_module_search: input,
    sc_modules_table: element({querySelectorAll() { return rows; }}), sc_module_empty: empty};
  vm.runInNewContext(script('filters.php'), {document: {readyState: 'complete', getElementById(id) { return els[id]; }}});
  function click(name) { filters.find(f => f.dataset.scFilter === name).handlers.click({preventDefault() {}}); }
  return {rows, filters, counts, input, empty, click};
}
let f = filterFixture(0);
f.click('error');
assert.equal(f.empty.style.display, '');
assert.match(f.empty.textContent, /No module compatibility or database upgrade errors/);
assert.equal(f.counts.sc_count_error.textContent, 0);
assert(f.rows.every(r => r.style.display === 'none'));
console.log('PASS zero-error filter shows an explicit empty state and accurate count');
f = filterFixture(1); f.click('error');
assert.equal(f.rows[0].style.display, ''); assert.equal(f.rows[1].style.display, 'none');
assert.equal(f.filters[4].attrs['aria-pressed'], 'true');
f.input.value = 'beta'; f.input.handlers.input();
assert.match(f.empty.textContent, /No modules match your search/);
f.input.value = ''; f.input.handlers.input(); f.click('update');
assert.equal(f.rows[1].style.display, ''); assert.equal(f.rows[0].style.display, 'none');
f.click('all'); assert(f.rows.every(r => r.style.display === ''));
console.log('PASS Errors, search, Needs Update, All, and selected-filter accessibility');

function upgradeFixture(responses) {
  const form = element({action: '/admin/modules/upgrade_all_database'}), start = element(), summary = element();
  const results = [element(), element()];
  const rows = results.map((result, i) => element({dataset: {module: 'module_' + i, version: '1.0.1', installed: '1.0.0'}, querySelector() { return result; }}));
  const calls = [];
  class FixtureFormData { *[Symbol.iterator]() { yield ['csrf_fixture', 'qa-token']; } }
  vm.runInNewContext(script('upgrades.php'), {URLSearchParams, FormData: FixtureFormData, window: {confirm() { return true; }},
    document: {getElementById(id) { return {sc_upgrade_form: form, sc_upgrade_start: start, sc_upgrade_summary: summary}[id]; }, querySelectorAll() { return rows; }},
    fetch: async (url, options) => { calls.push({url, options}); const response = responses.shift(); return typeof response === 'function' ? response() : response; }});
  return {form, start, summary, results, calls, submit: () => form.handlers.submit({preventDefault() {}})};
}
const response = (status, message) => ({ok: true, json: async () => ({status, message})});
(async () => {
  let resolveFirst;
  const u = upgradeFixture([() => new Promise(resolve => { resolveFirst = resolve; }), response('error', '<b>Migration missing</b>')]);
  const pending = u.submit(); await u.submit();
  assert.equal(u.calls.length, 1); assert.equal(u.start.disabled, true);
  resolveFirst(response('upgraded', 'Upgraded')); await pending;
  assert.equal(u.calls.length, 2);
  assert.equal(u.calls[0].options.method, 'POST');
  assert.equal(u.calls[0].options.body.get('csrf_fixture'), 'qa-token');
  assert.equal(u.calls[0].options.body.get('module'), 'module_0');
  assert.equal(u.calls[1].options.body.get('module'), 'module_1');
  assert.match(u.summary.textContent, /1 upgraded, 1 failed, 0 skipped/);
  assert.equal(u.results[1].textContent, '<b>Migration missing</b>');
  console.log('PASS sequential per-module POSTs, CSRF fields, duplicate-submit protection, visible outcomes, and text-only messages');
  const denied = upgradeFixture([{ok: false, status: 403}, response('upgraded', 'Must not run')]);
  await denied.submit(); assert.equal(denied.calls.length, 1); assert.match(denied.results[0].textContent, /HTTP 403/);
  assert.match(denied.summary.textContent, /Stopped/);
  const invalid = upgradeFixture([{ok: true, json: async () => { throw new Error('Invalid JSON'); }}, response('upgraded', 'Must not run')]);
  await invalid.submit(); assert.equal(invalid.calls.length, 1); assert.match(invalid.results[0].textContent, /result is unknown/);
  console.log('PASS denied, malformed, or uncertain responses stop the batch without automatic retry');
})().catch(error => { console.error(error); process.exitCode = 1; });
