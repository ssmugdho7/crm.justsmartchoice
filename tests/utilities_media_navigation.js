const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');

(async () => {
  const dom = new JSDOM('<div id="elfinder"><span class="elfinder-navbar-root"><span class="elfinder-navbar-icon"></span>Public</span><span class="elfinder-navbar-dir">Folder</span></div>', {
    url: 'https://crm.example/admin/utilities/media', runScripts: 'outside-only', pretendToBeVisual: true,
  });
  const w = dom.window;
  w.eval(fs.readFileSync('assets/plugins/jquery/jquery.min.js', 'utf8'));
  w.eval(fs.readFileSync('assets/plugins/jquery-ui/jquery-ui.min.js', 'utf8'));
  const $ = w.jQuery;
  let starts = 0;
  const manager = {draggable: {distance: 4, scroll: false, start() { starts++; }}};
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
  $('.elfinder-navbar-root, .elfinder-navbar-dir').draggable(manager.draggable);
  drag('.elfinder-navbar-root');
  assert.equal(starts, 0, 'small root pointer movement must not initiate drag and swallow its click');
  drag('.elfinder-navbar-dir');
  assert.equal(starts, 1, 'ordinary subfolders must retain native drag-and-drop');
  let clicks = 0;
  $('.elfinder-navbar-root').on('click', () => clicks++);
  $('.elfinder-navbar-icon').trigger('click');
  assert.equal(clicks, 1, 'clicks on the root icon still reach native folder navigation');
  dom.window.close();
  console.log('Media root navigation and subfolder drag regression checks passed');
})().catch(error => {console.error(error); process.exitCode = 1;});
