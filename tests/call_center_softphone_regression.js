const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync(__dirname + '/../modules/smart_choice_call_center/assets/js/call-center.js', 'utf8');
function fixture(configured) {
  const elements = new Map(), handlers = new Map(), notices = [];
  let startup, device;
  const document = { getElementById: () => null };
  function $(selector) {
    if (typeof selector === 'function') { startup = selector; return; }
    if (selector === document) return { on: (event, target, action) => handlers.set(target, action) };
    if (typeof selector === 'object') return selector;
    if (!elements.has(selector)) elements.set(selector, {
      value: '', props: {}, label: '',
      val(value) { if (value === undefined) return this.value; this.value = value; return this; },
      text(value) { this.label = value; return this; },
      prop(key, value) { this.props[key] = value; return this; },
      removeClass() { return this; }, addClass() { return this; }
    });
    return elements.get(selector);
  }
  class Call {
    constructor() { this.events = {}; this.state = 'pending'; this.parameters = {}; }
    on(event, action) { this.events[event] = action; }
    status() { return this.state; }
    accept() { this.state = 'open'; this.events.accept?.(); }
    reject() { this.state = 'closed'; this.events.reject?.(); }
    disconnect() { this.state = 'closed'; this.events.disconnect?.(); }
  }
  class Device {
    constructor(token) { this.events = {}; device = this; this.token = token; }
    on(event, action) { this.events[event] = action; }
    async register() { this.events.registered(); }
    async connect() { return this.call = new Call(); }
    updateToken(token) { this.token = token; }
  }
  let requests = 0;
  const window = { Twilio: { Device }, scccBrowserEnabled: true, scccTokenUrl: '/token', console: { error() {} }, confirm: () => false };
  vm.runInNewContext(source, { window, document, Twilio: window.Twilio, jQuery: $, alert_float: (level, message) => notices.push([level, message]), fetch: async () => { requests++; return { ok: true, json: async () => ({ success: configured, token: configured ? 'token-' + requests : null }) }; } });
  return { $, handlers, notices, start: () => startup(), device: () => device, Call };
}
(async () => {
  let f = fixture(false); await f.start();
  assert.equal(f.$('.sccc-dialer-title .label').label, 'NOT CONFIGURED');
  f.$('#scccPhone').val('+15557654321');
  await f.handlers.get('#scccCallButton').call(f.$('#scccCallButton'));
  assert.equal(f.notices[0][0], 'warning');
  console.log('PASS missing credentials show NOT CONFIGURED and cannot place a call');
  f = fixture(true); await f.start();
  assert.equal(f.$('.sccc-dialer-title .label').label, 'READY');
  await f.device().events.tokenWillExpire();
  assert.equal(f.device().token, 'token-2');
  console.log('PASS softphone registers and refreshes its expiring token');
  f.$('#scccPhone').val('+15557654321');
  await f.handlers.get('#scccCallButton').call(f.$('#scccCallButton'));
  assert.equal(f.$('#scccCallButton').props.disabled, true);
  assert.equal(f.$('#scccHangup').props.disabled, false);
  f.device().call.accept();
  assert.equal(f.$('.sccc-dialer-title .label').label, 'ON CALL');
  f.handlers.get('#scccHangup')();
  assert.equal(f.$('#scccCallButton').props.disabled, false);
  assert.equal(f.$('#scccHangup').props.disabled, true);
  console.log('PASS outgoing call can be hung up and controls reset');
  const incoming = new f.Call(); f.device().events.incoming(incoming);
  assert.equal(incoming.status(), 'closed');
  assert.equal(f.$('#scccCallButton').props.disabled, false);
  assert.equal(f.$('.sccc-dialer-title .label').label, 'READY');
  console.log('PASS rejecting an incoming call clears active-call state');
  await f.handlers.get('#scccCallButton').call(f.$('#scccCallButton'));
  f.device().call.events.error(new Error('Provider failed'));
  assert.equal(f.$('#scccCallButton').props.disabled, false);
  assert.equal(f.notices.at(-1)[0], 'danger');
  console.log('PASS provider call errors restore controls and display failure');
})().catch(error => { console.error(error); process.exitCode = 1; });
