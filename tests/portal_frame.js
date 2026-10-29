const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const source = fs.readFileSync('modules/toast_master/assets/js/portal-frame.js','utf8');
const dom = new JSDOM('<iframe name="sc-mobile-test" src="https://crm.justsmartchoice.com/admin"></iframe>', {
  url:'https://mobile.justsmartchoice.com/preview/',runScripts:'outside-only',pretendToBeVisual:true,
});
const parent = dom.window, child = parent.document.querySelector('iframe').contentWindow;
child.name = 'sc-mobile-test';
child.matchMedia = () => ({matches:true,addEventListener(){}});
child.document.open();
child.document.write('<html><head></head><body><button class="navbar-toggle" aria-expanded="false">Menu</button></body></html>');
child.document.close();
const sent = [];
parent.postMessage = (message, origin) => sent.push({message,origin});
child.eval(source);
child.document.dispatchEvent(new child.Event('DOMContentLoaded'));
function hello(origin, sourceWindow) {
  child.dispatchEvent(new child.MessageEvent('message', {
    origin,source:sourceWindow,data:{type:'sc-mobile-portal-hello'},
  }));
}
hello('https://evil.example',parent);
hello('https://mobile.justsmartchoice.com',child);
assert.equal(sent.length,0,'Untrusted origins/windows cannot obtain location');
hello('https://mobile.justsmartchoice.com',parent);
assert.equal(sent.length,1);
assert.equal(sent[0].origin,'https://mobile.justsmartchoice.com');
assert.equal(sent[0].message.url,'https://crm.justsmartchoice.com/admin');
assert.ok(child.document.querySelector('.navbar-toggle svg'));
assert.equal(child.document.querySelector('.navbar-toggle').getAttribute('aria-label'),'Menu');
assert.equal(child.document.querySelector('.navbar-toggle').getAttribute('aria-expanded'),'false');
const modal = child.document.createElement('div'); modal.className='modal in'; child.document.body.appendChild(modal);
let hidden = 0;
child.jQuery = () => ({modal:action=>{if(action==='hide') hidden++;}});
child.jQuery.fn = {modal(){}};
child.dispatchEvent(new child.MessageEvent('message',{origin:'https://evil.example',source:parent,data:{type:'sc-mobile-portal-close-overlay'}}));
assert.equal(hidden,0);
child.dispatchEvent(new child.MessageEvent('message',{origin:'https://mobile.justsmartchoice.com',source:parent,data:{type:'sc-mobile-portal-close-overlay'}}));
assert.equal(hidden,1,'Back closes the existing modal using its original handler');
child.dispatchEvent(new child.Event('pagehide'));
assert.equal(JSON.parse(child.sessionStorage.getItem('sc-mobile-scroll:sc-mobile-test'))[child.location.href],0);
dom.window.close();
console.log('PASS verified portal frame handshake, origin/source rejection, scroll storage and accessible menu icon');
