const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');

(async () => {
  const dom = new JSDOM('<div id="elfinder"><span id="nav-public" class="elfinder-navbar-root elfinder-navbar-collapsed"><span class="elfinder-navbar-arrow"></span><span class="elfinder-navbar-icon"></span>Public</span><span class="elfinder-navbar-dir">Folder</span></div>', {
    url: 'https://crm.example/admin/utilities/media', runScripts: 'outside-only', pretendToBeVisual: true,
  });
  const w = dom.window;
  w.eval(fs.readFileSync('assets/plugins/jquery/jquery.min.js', 'utf8'));
  w.eval(fs.readFileSync('assets/plugins/jquery-ui/jquery-ui.min.js', 'utf8'));
  const $ = w.jQuery;
  let otherMessages = 0;
  $(w).on('message.other-module', () => otherMessages++);
  let starts = 0;
  const calls = [];
  const once = {};
  const manager = {
    draggable: {distance: 4, scroll: false, start() { starts++; }},
    namespace: 'elfinder-test', options: {url: '/admin/utilities/media_connector'},
    uploadURL: '/admin/utilities/media_connector',
    convAbsUrl: url => new URL(url, w.location.href).href,
    one(event, callback) { once[event] = callback; },
  };
  for (const method of ['sync', 'error', 'trigger', 'updateCache', 'remove', 'add', 'change']) {
    manager[method] = (...args) => calls.push([method, ...args]);
  }
  // Exercise the real bundled vendor listener to reproduce the refresh loop.
  const vendor = fs.readFileSync('assets/plugins/elFinder/js/elFinder.js', 'utf8');
  const nativeListener = vendor.slice(vendor.indexOf("$(window).on('message.' + namespace"), vendor.indexOf('// elFinder enable always'));
  w.__testManager = manager;
  w.eval('(function(self, namespace){' + nativeListener + '})(window.__testManager, "elfinder-test");');
  function message(data, origin = 'https://crm.example') {
    w.dispatchEvent(new w.MessageEvent('message', {data, origin}));
  }
  message({source: 'react-devtools-content-script', hello: true});
  assert.equal(calls.filter(c => c[0] === 'sync').length, 1, 'baseline: unrelated object message causes native refresh');
  calls.length = 0;
  $.fn.elfinder = () => manager;
  function drag(selector) {
    const el = $(selector);
    el.trigger($.Event('mousedown', {which: 1, pageX: 10, pageY: 10, target: el[0]}));
    $(w.document).trigger($.Event('mousemove', {which: 1, pageX: 16, pageY: 10, target: el[0]}));
    $(w.document).trigger($.Event('mouseup', {which: 1, pageX: 16, pageY: 10, target: el[0]}));
  }
  $('.elfinder-navbar-root').draggable(manager.draggable);
  drag('.elfinder-navbar-root');
  assert.equal(starts, 1, 'baseline reproduces root drag after only 6px of movement');
  $('.elfinder-navbar-root').draggable('destroy');
  starts = 0;
  w.eval(fs.readFileSync('modules/toast_master/assets/js/utilities-media.js', 'utf8'));
  await new Promise(resolve => w.setTimeout(resolve, 30));
  // Native initialization registers the legacy listener at first opendone,
  // after document-ready when the connector response is slow.
  w.eval('(function(self, namespace){' + nativeListener + '})(window.__testManager, "elfinder-test");');
  once.opendone();
  $('.elfinder-navbar-root, .elfinder-navbar-dir').draggable(manager.draggable);
  drag('.elfinder-navbar-root');
  assert.equal(starts, 0, 'small root pointer movement must not initiate drag and swallow its click');
  drag('.elfinder-navbar-dir');
  assert.equal(starts, 1, 'ordinary subfolders must retain native drag-and-drop');
  let clicks = 0;
  $('.elfinder-navbar-root').on('click', () => clicks++);
  $('.elfinder-navbar-icon').trigger('click');
  assert.equal(clicks, 1, 'clicks on the root icon still reach native folder navigation');
  $('.elfinder-navbar-arrow').on('click', () => $('.elfinder-navbar-root').toggleClass('elfinder-navbar-expanded'));
  $('.elfinder-navbar-arrow').trigger('click').trigger('click');
  for (let i = 0; i < 25; i++) message({source: 'react-devtools-content-script', hello: true});
  for (const data of ['not JSON', 'null', '[]', '{"data":[]}', '{"other":true}']) message(data);
  assert.equal(calls.length, 0, 'unrelated messages never refresh or mutate the manager');
  assert(!$('.elfinder-navbar-root').hasClass('elfinder-navbar-expanded'), 'collapsed folders stay collapsed after extension messages');
  const upload = {bind: 'upload', data: {added: [{hash: 'new-file'}], sync: true}};
  message(JSON.stringify(upload), 'https://untrusted.example');
  message(JSON.stringify(upload), 'https://crm.examp');
  assert.equal(calls.length, 0, 'foreign and prefix-only origins are rejected');
  message(JSON.stringify(upload));
  assert.deepEqual(calls.map(c => c[0]), ['updateCache', 'add', 'trigger', 'trigger', 'sync'], 'valid native upload completion remains functional');
  assert.equal(calls[2][1], 'upload');
  assert.equal(calls[3][1], 'uploaddone');
  calls.length = 0;
  message(JSON.stringify({bind: 'upload', data: {error: ['errUpload']}}));
  assert.deepEqual(calls.map(c => c[0]), ['trigger', 'error'], 'native upload errors remain visible');
  assert.equal(calls[0][1], 'uploadfail');
  calls.length = 0;
  message(JSON.stringify({bind: 'edit', data: {warning: ['warning'], removed: ['old'], changed: [{hash: 'changed'}]}}));
  assert.deepEqual(calls.map(c => c[0]), ['error', 'updateCache', 'remove', 'change', 'trigger', 'trigger'], 'editor changes and warnings retain the native protocol');
  assert(otherMessages >= 30, 'other modules continue receiving window messages');
  dom.window.close();
  console.log('Media root navigation and subfolder drag regression checks passed');
})().catch(error => {console.error(error); process.exitCode = 1;});
