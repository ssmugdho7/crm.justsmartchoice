const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../modules/toast_master/assets/js/toast_master.js', 'utf8');
function element() {
  return {children: [], style: {}, handlers: {}, classList: {add() {}},
    appendChild(child) { child.parentNode = this; this.children.push(child); },
    removeChild(child) { this.children = this.children.filter(c => c !== child); },
    addEventListener(name, callback) { this.handlers[name] = callback; }, focus() {},
    querySelector(selector) { if (selector === '.sc-toast-progress' && !this.innerHTML.includes('sc-toast-progress')) return null; return this.parts[selector] ||= element(); }, parts: {}};
}
function fixture(channels = {}, path = '/admin/toast_master/settings', enabled = true) {
  const body = element(), handlers = {}, logs = [], native = [], tones = [];
  const document = {body, addEventListener() {}, createElement: element,
    getElementById(id) { return body.children.find(e => e.id === id); }};
  const jq = arg => {
    if (typeof arg === 'function') { arg(); return; }
    return {each() {}, on(name, callback) { handlers[name] = callback; }};
  };
  jq.post = (url, data) => logs.push({url, data});
  const window = {SMART_CHOICE_TOAST_MASTER: {enabled, historyEnabled: true, soundEnabled: false, style: '2', position: 'top-left', channels},
    location: {pathname: path, origin: 'https://example.test'}, admin_url: '/admin/', jQuery: jq,
    alert_float(...args) { native.push(['float', ...args]); }, alert(...args) { native.push(['alert', ...args]); },
    AudioContext: class { constructor() { this.currentTime = 0; this.destination = {}; }
      createOscillator() { tones.push(true); return {frequency: {setValueAtTime() {}}, connect() {}, start() {}, stop() {}}; }
      createGain() { return {gain: {setValueAtTime() {}, exponentialRampToValueAtTime() {}}, connect() {}}; }} };
  vm.runInNewContext(source, {window, document, setTimeout() { return 1; }, clearTimeout() {}, Date});
  return {window, body, handlers, logs, native, tones, count: () => body.children.reduce((n, c) => n + c.children.length, 0)};
}
let f = fixture();
let toast = f.window.smartChoiceNotify('success', '<b>Saved</b>', null, {preview: true, style: '12', position: 'bottom-center', progressBar: false});
assert.match(toast.className, /style-12/); assert.equal(f.body.children[0].id, 'sc-toast-container-bottom-center');
assert.equal(toast.parts['.sc-toast-message'].textContent, 'Saved'); assert.equal(f.logs.length, 0);
assert.equal(f.window.SMART_CHOICE_TOAST_MASTER.style, '2');
console.log('PASS preview uses current appearance without changing runtime configuration or history');
for (const channel of ['alertFloat', 'ajax', 'validation', 'messages', 'announcements', 'moduleUpdates', 'cron']) {
  f = fixture({[channel]: false});
  assert.equal(f.window.smartChoiceNotify('info', 'QA', null, {source: channel}), null);
  f.window.smartChoiceToastConfigure({channels: {[channel]: true}});
  assert.ok(f.window.smartChoiceNotify('info', 'QA', null, {source: channel}));
  assert.equal(f.logs[0].data.source, channel);
}
console.log('PASS every supported source honors disable/enable and records the actual source');
f = fixture({alertFloat: false, browserAlert: false});
f.window.alert_float('success', 'native', 9000); f.window.alert('native prompt');
assert.deepEqual(f.native, [['float', 'success', 'native', 9000], ['alert', 'native prompt']]); assert.equal(f.count(), 0);
for (const [path, channel] of [['prchat/Prchat_Controller/chat_full_view', 'messages'], ['announcements', 'announcements'], ['modules', 'moduleUpdates'], ['cron', 'cron']]) {
  f = fixture({[channel]: false}, '/admin/' + path); f.window.alert_float('info', 'native');
  assert.equal(f.count(), 0); assert.equal(f.native.length, 1);
}
console.log('PASS disabled CRM/browser/category interception preserves original native behavior');
f = fixture({}, undefined, false);
assert.equal(f.window.smartChoiceNotify('info', 'hidden'), null);
assert.ok(f.window.smartChoiceNotify('info', 'preview', null, {preview: true}));
f.window.smartChoiceTestSound('success', {soundEnabled: true, soundName: 'bell', volume: 0}); assert.equal(f.tones.length, 0);
console.log('PASS global disable allows settings preview only, and zero volume stays silent');
f = fixture({ajax: true, validation: true});
f.handlers['ajaxError.smartChoiceToast']({}, {status: 404}, {url: '/admin/example'});
assert.equal(f.count(), 1); assert.equal(f.logs[0].data.source, 'ajax');
f = fixture({ajax: false, validation: false});
f.handlers['ajaxError.smartChoiceToast']({}, {status: 404}, {url: '/admin/example'});
f.handlers['invalid-form.validate.smartChoiceToast']({}, {errorList: [{message: 'Required'}]}); assert.equal(f.count(), 0);
f = fixture(); f.handlers['invalid-form.validate.smartChoiceToast']({}, {errorList: [{message: 'Required'}]}); assert.equal(f.logs[0].data.source, 'validation');
for (const url of ['/admin/toast_master/log', '/admin/misc/notifications_check', '/admin/prchat/poll']) {
  f = fixture(); f.handlers['ajaxError.smartChoiceToast']({}, {status: 500}, {url}); assert.equal(f.count(), 0);
}
console.log('PASS AJAX/validation event integration obeys switches and excludes logging/polling loops');

const view = fs.readFileSync(__dirname + '/../modules/toast_master/views/settings.php', 'utf8');
assert.ok(view.indexOf('init_tail();') < view.indexOf('<script>'));
const script = view.match(/<script>([\s\S]*?)<\/script>/)[1];
let preview;
const values = {'#toaster_position': 'bottom-left', '#toast_master_sound_name': 'bell', '#toast_master_animation': 'flip'};
const jq = selector => ({val() {
  if (values[selector]) return values[selector];
  if (selector.includes('toaster_style')) return '12';
  if (selector.includes('volume')) return '0';
  if (selector.includes('duration')) return '3500';
  return '0';
}, on() { return this; }});
const page = {smartChoiceNotify(type, message, title, settings) { preview = settings; }};
vm.runInNewContext(script, {window: page, document: {}, jQuery: jq}); page.smartChoiceToastPreview('info', 'QA');
assert.equal(preview.soundEnabled, false); assert.equal(preview.progressBar, false); assert.equal(preview.pauseOnHover, false); assert.equal(preview.volume, 0);
console.log('PASS settings initialization follows jQuery and reads No radios correctly');
