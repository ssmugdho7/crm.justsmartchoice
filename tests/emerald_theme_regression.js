/* Run with NODE_PATH pointing to an existing jsdom/jquery installation. */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const jquery = require('jquery');
const script = fs.readFileSync('assets/js/custom.js', 'utf8');
const main = fs.readFileSync('assets/js/main.js', 'utf8');
const toggle = main.slice(main.indexOf('  $(".hide-menu").click(function (e) {'), main.indexOf('  // Hide sidebar on content click on mobile'));
async function page({mobile = false, saved, blocked = false, nativeClock = true, admin = true} = {}) {
  const dom = new JSDOM(`<body class="${admin ? 'admin' : 'customers'} ${mobile ? 'page-small mobile' : ''}">
    <div id="header"><button class="hide-menu"></button><nav><div class="tw-flex tw-justify-between"><input id="search_input" type="search">${nativeClock ? '<div id="sc-header-datetime">Native clock</div>' : ''}<ul></ul></div></nav></div>
    <input type="search" name="search"><div id="setup-menu"></div></body>`, {url:'https://dev.justsmartchoice.com',runScripts:'outside-only'});
  const w = dom.window;
  Object.defineProperty(w, 'innerWidth', {value: mobile ? 390 : 1440, writable:true});
  w.document.getElementById('search_input').getClientRects = () => [{width:350,height:38}];
  if (saved !== undefined) w.localStorage.setItem('smartchoice_sidebar_collapsed', saved);
  if (blocked) Object.defineProperty(w, 'localStorage', {get(){throw Error('Storage disabled');}});
  const $ = jquery(w);
  w.jQuery = w.$ = $;
  w.setup_menu = $('#setup-menu');
  w.eval(toggle);
  let intervals = 0;
  w.setInterval = () => {intervals++;};
  w.eval(script);
  await new Promise(resolve => w.setTimeout(resolve, 30));
  return {w,$,dom,intervals:()=>intervals};
}
(async () => {
  let p = await page({saved:'true'});
  assert(p.w.document.body.classList.contains('hide-sidebar'));
  assert.equal(p.intervals(), 0, 'reuse existing clock without another timer');
  p.$('.hide-menu').trigger('click');
  assert.equal(p.w.localStorage.getItem('smartchoice_sidebar_collapsed'), 'false');
  p.$('.hide-menu').trigger('click');
  assert.equal(p.w.localStorage.getItem('smartchoice_sidebar_collapsed'), 'true');
  const event = new p.w.KeyboardEvent('keydown', {key:'K',ctrlKey:true,bubbles:true,cancelable:true});
  p.w.document.dispatchEvent(event);
  assert(event.defaultPrevented);
  assert.equal(p.w.document.activeElement.id, 'search_input', 'shortcut selects header search');
  p.w.eval(script);
  await new Promise(resolve => p.w.setTimeout(resolve, 30));
  p.$('.hide-menu').trigger('click');
  assert.equal(p.w.localStorage.getItem('smartchoice_sidebar_collapsed'), 'false', 'reinitialization does not duplicate handlers');
  p.dom.window.close();
  p = await page({mobile:true,saved:'true'});
  assert(!p.w.document.body.classList.contains('hide-sidebar'));
  p.$('.hide-menu').trigger('click');
  assert(p.w.document.body.classList.contains('show-sidebar'));
  assert.equal(p.w.localStorage.getItem('smartchoice_sidebar_collapsed'), 'true', 'mobile toggle preserves desktop preference');
  p.dom.window.close();
  p = await page({blocked:true,nativeClock:false});
  assert.equal(p.w.document.querySelectorAll('#sc-header-datetime').length,1);
  assert.equal(p.intervals(),1);
  assert(!/Invalid|Syncing/.test(p.w.document.getElementById('sc-header-datetime').textContent));
  p.$('.hide-menu').trigger('click');
  p.dom.window.close();
  p = await page({admin:false,nativeClock:false});
  assert.equal(p.w.document.querySelectorAll('#sc-header-datetime').length,0);
  assert.equal(p.intervals(),0);
  p.dom.window.close();
  console.log('PASS: header search, clock reuse/fallback, sidebar persistence, mobile, blocked storage, repeated initialization, customer isolation');
})().catch(error => {console.error(error); process.exitCode=1;});
