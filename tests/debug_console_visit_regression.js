'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../modules/debug_mode/assets/js/debug_mode_console.js'), 'utf8');

class Element {
  constructor() {
    this.children = []; this.handlers = {}; this.attributes = {}; this.hidden = false;
    this.classes = new Set();
    this.classList = {toggle: (name, on) => on ? this.classes.add(name) : this.classes.delete(name), add: name => this.classes.add(name)};
  }
  set textContent(value) { this.text = value; this.children = []; }
  get textContent() { return (this.text || '') + this.children.map(child => child.textContent).join(' '); }
  appendChild(child) { this.children.push(child); }
  setAttribute(name, value) { this.attributes[name] = value; }
  getAttribute(name) { return this.attributes[name]; }
  addEventListener(name, callback) { this.handlers[name] = callback; }
  querySelector(selector) { return this.parts[selector]; }
}

function harness(visit, serverEvents = []) {
  const root = new Element(), toggle = new Element(), status = new Element(), events = new Element();
  toggle.parts = {'[data-console-toggle-label]': new Element(), '.fa': new Element()};
  root.parts = {'[data-console-toggle]': toggle, '[data-console-status]': status, '[data-console-events]': events,
    '[data-console-refresh]': new Element(), '[data-console-count]': new Element(), '[data-console-body]': new Element()};
  const handlers = {}, windowHandlers = {}, requests = [];
  const attributes = {'data-scope': 'expenses', 'data-route': '/admin/expenses', 'data-endpoint': '/admin/debug_mode/module_console',
    'data-token': 'session-token', 'data-visit': visit, 'data-visit-proof': 'signed-visit'};
  const document = {readyState: 'loading', hidden: false, currentScript: {getAttribute: key => attributes[key]},
    getElementById: () => root, createElement: () => new Element(), addEventListener: (name, callback) => handlers[name] = callback};
  const window = {scDebugCaptureStatus: {pending: 0, failed: false}, addEventListener: (name, callback) => windowHandlers[name] = callback,
    setInterval(callback) { this.poll = callback; }};
  const sandbox = {window, document, sessionStorage: {getItem: () => null, setItem() {}}, URL, URLSearchParams,
    location: {href: 'https://crm.test/admin/expenses'}, fetch: async (url, options) => {
      requests.push(new URLSearchParams(options.body));
      return {ok: true, status: 200, redirected: false, json: async () => ({success: true, scope: 'expenses', route: '/admin/expenses', visit, events: serverEvents})};
    }};
  vm.runInNewContext(source, sandbox);
  return {root, status, events, requests, window, load() { document.readyState = 'complete'; windowHandlers.load(); }, recorded: () => handlers['sc-debug-recorded']()};
}

const tick = () => new Promise(resolve => setImmediate(resolve));
const event = {fingerprint: 'error-a', severity: 'warning', summary: 'PHP warning <script>must stay text</script>', occurrences: 1,
  first_seen_unix: 100, last_seen_unix: 100, source: 'CRM log', trace: [], location: null};
(async () => {
  const broken = harness('visit-a', [event]);
  assert.equal(broken.requests.length, 0, 'Do not claim a clean load before the document finishes');
  broken.window.scDebugCaptureStatus.pending = 1;
  broken.load(); await tick();
  assert.equal(broken.requests.length, 0, 'Wait for buffered browser errors to be recorded');
  broken.window.scDebugCaptureStatus.pending = 0;
  broken.recorded(); await tick();
  assert.equal(broken.status.textContent, '1 issue on this page load');
  assert.match(broken.events.textContent, /PHP warning/);
  assert.doesNotMatch(broken.events.textContent, /Mark resolved/);
  assert.equal(broken.requests[0].get('visit'), 'visit-a');
  assert.equal(broken.requests[0].get('visit_proof'), 'signed-visit');

  const fixed = harness('visit-b'); fixed.load(); await tick();
  assert.equal(fixed.status.textContent, 'No errors on this page load', 'A clean reload clears the prior issue automatically');
  assert.doesNotMatch(fixed.events.textContent, /PHP warning/);

  const added = [event];
  const next = harness('visit-c', added); next.load(); await tick();
  added.push({...event, fingerprint: 'error-b', summary: 'New background failure'});
  next.recorded(); await tick();
  assert.equal(next.status.textContent, '2 issues on this page load', 'New errors appear without user action');

  const incomplete = harness('visit-d'); incomplete.window.scDebugCaptureStatus.failed = true;
  incomplete.load(); await tick();
  assert.equal(incomplete.status.textContent, 'Browser capture incomplete', 'Capture failure must never be reported as a clean page');
  console.log('PASS automatic drawer: load completion, buffered capture, clean reload, new issues, signed visit isolation and incomplete-capture state');
})().catch(error => { console.error(error); process.exitCode = 1; });
