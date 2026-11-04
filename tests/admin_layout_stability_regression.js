/* Run with NODE_PATH pointing to a jsdom installation. No browser/network needed. */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const source = fs.readFileSync(require('node:path').join(__dirname, '../assets/js/sc-layout-stability.js'), 'utf8');
function boot(saved, brokenStorage) {
  const dom = new JSDOM('<body class="admin"><ul id="side-menu"><li class="menu-item-sales"><a href="#">Sales</a><ul class="nav-second-level collapse"><li><a id="navigate" href="/admin/expenses">Expenses</a></li></ul></li><li><a id="external" href="https://example.com">External</a><a id="hash" href="#dialog">Dialog</a></li></ul></body>', { url: 'https://crm.test/admin/', runScripts: 'outside-only' });
  const w = dom.window;
  if (brokenStorage) Object.defineProperty(w, 'sessionStorage', { get() { throw new Error('blocked'); } });
  else w.sessionStorage.setItem('sc_open_menus:crm.test', saved);
  w.eval(source);
  w.document.dispatchEvent(new w.Event('DOMContentLoaded'));
  return dom;
}
let dom = boot('["menu-item-sales"]');
let w = dom.window, d = w.document;
assert(d.querySelector('ul.nav-second-level').classList.contains('in'), 'saved submenu restored before DOM ready');
assert.equal(d.querySelector('.menu-item-sales > a').getAttribute('aria-expanded'), 'true');
function click(id, options) {
  const e = new w.MouseEvent('click', Object.assign({ bubbles: true, cancelable: true, button: 0 }, options));
  // Prevent jsdom navigation after the application's handler observes the click.
  d.addEventListener('click', e => e.preventDefault(), { once: true });
  d.getElementById(id).dispatchEvent(e);
}
click('navigate', { ctrlKey: true });
assert(!d.getElementById('smartchoice-nav-progress').classList.contains('sc-navigating'));
click('external'); click('hash');
assert(!d.getElementById('smartchoice-nav-progress').classList.contains('sc-navigating'));
click('navigate');
assert(d.getElementById('smartchoice-nav-progress').classList.contains('sc-navigating'));
w.dispatchEvent(new w.Event('pageshow'));
assert(!d.getElementById('smartchoice-nav-progress').classList.contains('sc-navigating'), 'back-forward cache reset');
d.querySelector('ul.nav-second-level').classList.remove('in');
w.dispatchEvent(new w.Event('pagehide'));
assert.equal(w.sessionStorage.getItem('sc_open_menus:crm.test'), '[]', 'closed menus remain closed');
w.close();
for (const [saved, broken] of [['bad json', false], ['[]', true]]) {
  dom = boot(saved, broken);
  assert(dom.window.document.getElementById('smartchoice-nav-progress'));
  dom.window.close();
}
console.log('PASS: submenu persistence, collapsed state, navigation exclusions, page restore, unavailable storage');
