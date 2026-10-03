// Native DOM events plus jQuery; no CRM bootstrap, requests, accounts or writes.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const code = fs.readFileSync('modules/smart_choice_links/assets/js/smart_choice_links.js', 'utf8');
function star(side, title) {
    return `<li class="smart-choice-links-nav smart-choice-links-${side}" data-scl-label="${title}" data-scl-side="${side}"><a href="#" class="smart-choice-links-trigger" aria-label="${title}"><i class="fa fa-star"></i></a><ul class="smart-choice-links-dropdown dropdown-menu"><li><a href="/fallback-${side}">Fallback</a></li></ul></li>`;
}
async function run() {
    const dom = new JSDOM(`<html><head></head><body><nav><ul id="header-nav"><li id="top_search_button"><button>Search</button></li></ul></nav><button id="outside">Outside</button><ul id="side-menu"><li><a href="/projects">Projects</a></li></ul><div id="smart-choice-links-source" hidden><ul>${star('left','Admin Links')}${star('right','Setup Links')}</ul></div><div id="smart-choice-links-panel-templates" hidden><ul id="smart-choice-links-left-panel-template"><li><a href="/admin/projects" data-scl-open-behavior="_self">Projects</a></li><li><a href="/docs" data-scl-open-behavior="_blank">Docs</a></li></ul><ul id="smart-choice-links-right-panel-template"><li><a href="/admin/settings" data-scl-open-behavior="_self">Settings</a></li></ul></div></body></html>`, { runScripts: 'outside-only', url: 'https://crm.example/' });
    const w = dom.window, d = w.document;
    w.eval(fs.readFileSync(require.resolve('jquery'), 'utf8'));
    const $ = w.jQuery;
    // jsdom has no layout; expose its existing search anchor as visible.
    $.expr.pseudos.visible = () => true;
    $.fn.tooltip = function () { return this; };
    w.smartChoiceLinksEnableMenuSearch = '1';
    w.admin_url = '/admin/';
    let requests = 0, opens = [];
    $.post = () => { requests++; };
    w.open = (...args) => opens.push(args);
    let legacyClicks = 0;
    d.addEventListener('click', e => { if (e.target.closest('.smart-choice-links-trigger')) legacyClicks++; });
    w.eval(code);
    d.dispatchEvent(new w.Event('DOMContentLoaded'));
    await delay(25);
    const left = d.querySelector('#smart-choice-links-left-live'), right = d.querySelector('#smart-choice-links-right-live');
    const trigger = nav => nav.querySelector('.smart-choice-links-trigger');
    const panel = d.querySelector('#smart-choice-links-panel-portal');
    const click = target => target.dispatchEvent(new w.MouseEvent('click', { bubbles: true, cancelable: true, button: 0, detail: 1 }));
    const key = (target, value, repeat = false) => target.dispatchEvent(new w.KeyboardEvent('keydown', { bubbles: true, cancelable: true, key: value, repeat }));
    const isOpen = () => panel.classList.contains('is-open');
    const assertOpen = owner => {
        assert.equal(isOpen(), true);
        assert.equal(trigger(owner).getAttribute('aria-expanded'), 'true');
        assert.equal(panel.getAttribute('aria-hidden'), 'false');
        assert.equal(panel.hasAttribute('inert'), false);
    };
    assert.ok(left && right, 'Both configured menus are placed');
    assert.equal(d.querySelector('#top_search_button').previousElementSibling, left);
    assert.equal(d.querySelector('#top_search_button').nextElementSibling, right);
    assert.equal(trigger(left).getAttribute('role'), 'button');
    assert.equal(trigger(left).getAttribute('aria-controls'), panel.id);
    trigger(left).dispatchEvent(new w.MouseEvent('mousedown', { bubbles: true, cancelable: true }));
    assert.equal(isOpen(), false, 'Pressing/holding alone does not toggle');
    trigger(left).dispatchEvent(new w.MouseEvent('mouseup', { bubbles: true, cancelable: true }));
    click(trigger(left).querySelector('i'));
    assertOpen(left);
    assert.equal(legacyClicks, 0, 'One click does not also trigger a Bootstrap handler');
    left.dispatchEvent(new w.MouseEvent('mouseout', { bubbles: true, relatedTarget: panel }));
    assertOpen(left); // Pointer can cross the gap to the menu without holding the trigger.
    const oldContent = panel.firstElementChild;
    const mutations = [];
    const tracker = new w.MutationObserver(records => mutations.push(...records));
    tracker.observe(d.body, { childList: true, subtree: true });
    const unrelated = d.createElement('div'); unrelated.textContent = 'Updated dashboard'; d.body.appendChild(unrelated);
    await delay(420); // Includes the initial 300ms repair and the mutation-driven repair.
    assertOpen(left);
    assert.equal(panel.firstElementChild, oldContent, 'Repairs do not recreate visible menu content');
    assert.equal(d.querySelector('#smart-choice-links-left-live'), left, 'Repairs retain the original trigger node');
    assert.equal(mutations.filter(m => [...m.removedNodes].includes(left) || [...m.removedNodes].includes(right)).length, 0, 'Repairs do not detach buttons or cause observer churn');
    tracker.disconnect();
    click(trigger(right)); assertOpen(right);
    assert.equal(trigger(left).getAttribute('aria-expanded'), 'false');
    assert.equal(panel.textContent.trim(), 'Settings', 'Switching menus replaces the correct configured links');
    click(trigger(right)); assert.equal(isOpen(), false, 'Same trigger closes the menu');
    assert.equal(panel.hasAttribute('inert'), true, 'Closing immediately removes links from keyboard interaction');
    assert.ok(panel.firstElementChild, 'Content remains through the closing animation');
    click(trigger(left)); click(panel.querySelector('li')); assertOpen(left);
    click(d.querySelector('#outside')); assert.equal(isOpen(), false, 'Outside click closes');
    key(trigger(right), 'Enter'); assertOpen(right);
    assert.equal(d.activeElement, panel.querySelector('a'), 'Keyboard opening focuses a usable link');
    key(d.activeElement, 'Escape'); assert.equal(isOpen(), false); assert.equal(d.activeElement, trigger(right), 'Escape restores focus');
    key(trigger(left), ' '); assertOpen(left);
    key(trigger(left), ' ', true); assertOpen(left); // Held key must not repeatedly toggle.
    key(d.activeElement, 'Escape');
    key(trigger(right), 'ArrowDown'); assertOpen(right);
    d.querySelector('#outside').focus(); assert.equal(isOpen(), false, 'Tab/focus leaving the menu dismisses it');
    trigger(left).dispatchEvent(new w.MouseEvent('click', { bubbles: true, cancelable: true, detail: 0 }));
    assertOpen(left); assert.equal(d.activeElement, panel.querySelector('a'), 'Keyboard-generated click works without mouse-down');
    click(panel.querySelector('a[data-scl-open-behavior="_blank"]'));
    assert.equal(isOpen(), false); assert.deepEqual(opens[0], ['https://crm.example/docs', '_blank', 'noopener']);
    d.querySelector('#smart-choice-links-right-panel-template').remove();
    click(trigger(right)); assertOpen(right);
    assert.equal(panel.querySelector('ul').hasAttribute('aria-hidden'), false, 'Fallback content is accessible when opened');
    assert.equal(panel.querySelector('a').getAttribute('href'), '/fallback-right', 'Fallback keeps its destination');
    // A second script include must not add a second toggle listener.
    w.eval(code); click(trigger(right)); assert.equal(isOpen(), false, 'Duplicate script include toggles only once');
    click(trigger(left)); assertOpen(left);
    // A later header update must repair placement without closing an existing menu.
    d.querySelector('#header-nav').appendChild(left);
    await delay(140); assertOpen(left);
    assert.equal(d.querySelector('#top_search_button').previousElementSibling, left);
    const anchor = d.querySelector('#top_search_button');
    anchor.getBoundingClientRect = () => ({ left: 300, width: 100, bottom: 40 });
    // Wait through every delayed initialization; none may reset open state.
    await delay(2000); assertOpen(left);
    assert.equal(d.querySelectorAll('#smart-choice-links-panel-portal').length, 1);
    assert.equal(d.querySelectorAll('#smart-choice-links-left-live').length, 1);
    assert.equal(requests, 0, 'Menu interactions never write CRM data');
    const input = d.querySelector('.smart-choice-links-menu-search');
    input.value = 'missing'; input.dispatchEvent(new w.Event('input', { bubbles: true }));
    await delay(110);
    assert.equal(d.querySelector('#side-menu li.scl-filtered-out a').getAttribute('href'), '/projects', 'Sidebar search retains its routes');
    dom.window.close();
    const css = fs.readFileSync('modules/smart_choice_links/assets/css/smart_choice_links.css', 'utf8');
    assert.match(css, /transition: opacity var\(--scl-animation-speed/);
    assert.match(css, /visibility 0s linear var\(--scl-animation-speed/);
    assert.match(css, /prefers-reduced-motion: reduce/);
    console.log('PASS: completed clicks, touch-style activation, keyboard/focus, no repair churn, late initialization, outside/Escape dismissal, route preservation, duplicate includes and transition rules');
}
run().catch(e => { console.error(e); process.exitCode = 1; });
