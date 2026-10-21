const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../application/views/admin/includes/sidebar_resize.php', 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
function element(classes = []) {
  const set = new Set(classes);
  return {className: classes.join(' '), handlers: {}, attributes: {}, style: {setProperty(key, value) { this[key] = value; }},
    classList: {contains: key => set.has(key), add: key => set.add(key), remove: key => set.delete(key)},
    addEventListener(key, fn) { this.handlers[key] = fn; }, setAttribute(key, value) { this.attributes[key] = value; },
    appendChild(child) { this.child = child; }, setPointerCapture(id) { this.pointer = id; },
    hasPointerCapture(id) { return this.pointer === id; }, releasePointerCapture() { this.pointer = null; }};
}
function fixture({saved, blockedStorage, rtl, mobile, nonAdmin} = {}) {
  const body = element([nonAdmin ? 'customers' : 'admin', 'user-id-18', ...(rtl ? ['rtl'] : [])]);
  const storage = new Map(saved === undefined ? [] : [['sc.admin.sidebar.width.18', String(saved)]]);
  const menu = element();
  menu.getBoundingClientRect = () => { const width = parseFloat(body.style['--sc-admin-sidebar-width']) || 230; return {width, left: rtl ? 1920 - width : 0, right: rtl ? 1920 : width, top: 64, bottom: 900}; };
  let observer;
  const window = {innerWidth: mobile ? 500 : 1920, innerHeight: 900, handlers: {},
    addEventListener(name, fn) { this.handlers[name] = fn; },
    localStorage: {getItem(key) { if (blockedStorage) throw new Error('blocked'); return storage.get(key) ?? null; }, setItem(key, value) { if (blockedStorage) throw new Error('blocked'); storage.set(key, value); }}};
  const document = {body, readyState: 'complete', getElementById(id) { return id === 'menu' ? menu : body.child || null; }, createElement() { return element(); }};
  vm.runInNewContext(source, {window, document, MutationObserver: class { constructor(fn) { observer = fn; } observe() {} }});
  const handle = body.child;
  const event = extra => Object.assign({button: 0, pointerId: 1, clientX: 230, preventDefault() {}}, extra);
  const width = () => parseFloat(body.style['--sc-admin-sidebar-width']);
  return {body, window, storage, handle, width, sync: () => observer(),
    down: extra => handle.handlers.pointerdown(event(extra)), move: extra => handle.handlers.pointermove(event(extra)),
    up: () => handle.handlers.pointerup(event()), key: key => handle.handlers.keydown(event({key}))};
}
let f = fixture();
assert.equal(f.width(), 230); assert.equal(f.handle.attributes.role, 'separator');
f.down(); f.move({clientX: 310}); assert.equal(f.width(), 310);
f.move({clientX: 1400}); assert.equal(f.width(), 380);
f.move({clientX: -500}); assert.equal(f.width(), 200); f.up();
assert.equal(f.storage.get('sc.admin.sidebar.width.18'), '200');
assert.equal(f.body.classList.contains('sc-sidebar-dragging'), false);
console.log('PASS pointer resizing clamps both limits, saves on release, and cleans drag state');
f.down(); f.move({clientX: 330}); f.key('Escape'); assert.equal(f.width(), 200);
assert.equal(f.storage.get('sc.admin.sidebar.width.18'), '200');
f.down(); f.move({clientX: 330}); f.handle.handlers.pointercancel(); assert.equal(f.width(), 200);
f.down(); f.move({clientX: 330}); f.window.handlers.blur(); assert.equal(f.width(), 200);
console.log('PASS Escape, cancelled pointer and window blur restore the original width');
f.key('End'); assert.equal(f.width(), 380); f.key('Home'); assert.equal(f.width(), 200);
f.key('ArrowRight'); assert.equal(f.width(), 210); f.handle.handlers.dblclick(); assert.equal(f.width(), 230);
f = fixture({saved: 350}); assert.equal(f.width(), 350);
f.window.innerWidth = 900; f.window.handlers.resize(); assert.equal(f.width(), 350);
f.window.innerWidth = 800; f.window.handlers.resize(); assert.equal(f.width(), 320);
f.window.innerWidth = 1920; f.window.handlers.resize(); assert.equal(f.width(), 350);
assert.equal(f.storage.get('sc.admin.sidebar.width.18'), '350');
console.log('PASS keyboard, reset, persistence, and responsive cap without overwriting the preferred width');
f.body.classList.add('hide-sidebar'); f.sync(); assert.equal(f.handle.hidden, true);
f.body.classList.remove('hide-sidebar'); f.sync(); assert.equal(f.handle.hidden, false);
f.body.classList.add('modal-open'); f.sync(); assert.equal(f.handle.hidden, true);
f = fixture({mobile: true}); assert.equal(f.handle.hidden, true);
f = fixture({nonAdmin: true}); assert.equal(f.handle, undefined);
console.log('PASS collapsed, modal, mobile and customer pages do not expose an active resize handle');
f = fixture({saved: 'invalid'}); assert.equal(f.width(), 230);
f = fixture({blockedStorage: true}); f.key('ArrowRight'); assert.equal(f.width(), 240);
f = fixture({rtl: true}); f.down({clientX: 1690}); f.move({clientX: 1600}); assert.equal(f.width(), 320); f.up();
console.log('PASS invalid/blocked storage and reversed RTL pointer direction');
