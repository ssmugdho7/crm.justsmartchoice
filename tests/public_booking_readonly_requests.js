const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const { JSDOM } = require('jsdom');
const dom = new JSDOM('<div id="providers-container"></div><div id="time-slots-section"></div><div id="time-slots-container"></div><div id="slot-loading"></div><input id="timezone" value="America/New_York">', { url: 'https://crm.example.test/' });
// Use the CRM's real jQuery so its global ajaxSetup merge behavior is exercised.
const browser = new JSDOM(dom.serialize(), { url: 'https://crm.example.test/', runScripts: 'outside-only' });
browser.window.eval(fs.readFileSync('assets/plugins/jquery/jquery.min.js', 'utf8'));
const $ = browser.window.jQuery;
$.ajaxSetup({ data: { csrf_token_name: 'SYNTHETIC_DO_NOT_PUT_IN_URL' } });
const requests = [];
$.ajaxTransport('+json', options => ({
    send(headers, complete) {
        requests.push(options);
        complete(200, 'OK', { text: JSON.stringify({ success: true, data: [], time_slots: [] }) }, 'Content-Type: application/json');
    }, abort() {}
}));
const context = vm.createContext({ $, console: { log() {}, error() {} }, Intl,
    site_url: 'https://crm.example.test/', appointlyLang: {}, providersCache: {}, timeSlotsCache: {},
    isLoadingProviders: false, isLoadingTimeSlots: false, staffSchedules: {}, renderProviders() {}, renderTimeSlots() {}
});
const source = fs.readFileSync('modules/appointly/assets/js/appointments_external_form_js.php', 'utf8');
for (const [name, next] of [['loadServiceProviders', 'providerHasWorkingHours'], ['loadTimeSlots', 'renderTimeSlots']]) {
    const start = source.indexOf('        function ' + name + '(');
    const end = source.indexOf('        function ' + next + '(', start);
    vm.runInContext(source.slice(start, end), context);
}
context.loadServiceProviders(1);
context.loadTimeSlots(1, 2, '2026-10-15');
assert.strictEqual(requests.length, 2);
for (const request of requests) {
    assert.strictEqual(request.type, 'GET');
    const url = new URL(request.url);
    assert.strictEqual(url.searchParams.get('service_id'), '1');
    assert.strictEqual(url.searchParams.has('csrf_token_name'), false);
    assert(!request.url.includes('SYNTHETIC_DO_NOT_PUT_IN_URL'));
}
assert.strictEqual(new URL(requests[1].url).searchParams.get('provider_id'), '2');
console.log('PASS booking provider and availability GET requests work without CSRF cookies and do not leak global tokens');
