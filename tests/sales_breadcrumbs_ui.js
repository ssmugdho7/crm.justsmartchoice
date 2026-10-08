'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { JSDOM } = require('jsdom');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/sc-sales-breadcrumbs.js'), 'utf8');
let checks = 0;
function check(value, label) { assert.ok(value, label); checks++; }
const catalog = [
    ['proposals', 'Proposals'], ['estimates', 'Estimates'], ['invoices', 'Invoices'],
    ['contracts', 'Contracts'], ['payments', 'Payments'], ['credit_notes', 'Credit Notes'],
    ['invoice_items', 'Items'],
];
function page(route, options = {}) {
    const root = options.root || '/admin/';
    const entries = options.entries || catalog;
    const group = options.group || 'sales';
    const config = { adminUrl: root, dashboard: 'Dashboard', sales: 'Sales', title: options.title || '', view: 'View' };
    const menu = entries.map(([uri, label]) => `<li><a href="${root}${uri}"><i class="fa fa-file"></i><span class="sub-menu-text">${label}</span></a></li>`).join('');
    const dom = new JSDOM(`<html><head><script type="application/json" id="sc-sales-breadcrumbs-config">${JSON.stringify(config)}</script></head><body>
        <ul id="side-menu"><li class="menu-item-${group}"><a href="#">Sales</a><ul class="nav-second-level collapse">${menu}</ul></li></ul>
        <div id="wrapper"><div class="content"><h4>Original heading</h4><form id="original-form" method="post" action="/save"><input name="csrf" value="original"><input name="amount" value="123"><button type="submit">Save</button></form></div></div>
        <button id="outside">Other action</button></body></html>`, { url: 'https://crm.example' + root + route, runScripts: 'outside-only' });
    const w = dom.window;
    Object.defineProperty(w.document, 'readyState', { value: 'complete' });
    w.eval(source);
    return { w, d: w.document, close: () => w.close() };
}
for (const [route, label] of catalog) {
    const h = page(route + '?status=open');
    const trail = h.d.querySelector('.sc-sales-breadcrumbs__trail');
    check(trail && trail.children.length === 3 && trail.lastChild.textContent === label, route + ' list has Dashboard / Sales / current page');
    check(h.d.querySelector('[aria-current="page"]').textContent === label, route + ' current page is announced');
    check(h.d.querySelector('.content').firstElementChild === h.d.getElementById('sc-sales-breadcrumbs'), route + ' breadcrumb is above existing content');
    h.close();
}
for (const [route, label] of catalog) {
    const h = page(route + '/edit/31', { title: 'Edit document #31' });
    const trail = h.d.querySelector('.sc-sales-breadcrumbs__trail');
    check(trail.children.length === 4 && trail.children[2].querySelector('a').pathname === '/admin/' + route, route + ' detail returns directly to its list');
    check(trail.lastChild.textContent === 'Edit document #31', route + ' displays current title');
    h.close();
}
for (const route of ['index', 'manage', 'list_invoices']) {
    const h = page('invoices/' + route + '/');
    check(h.d.querySelector('.sc-sales-breadcrumbs__trail').children.length === 3, 'List alias ' + route + ' avoids a duplicate breadcrumb');
    h.close();
}
for (const route of ['invoices/recurring', 'invoice_items/import', 'proposals/proposal', 'contracts/contract']) {
    const h = page(route, { title: 'New / special screen' });
    check(h.d.querySelector('.sc-sales-breadcrumbs__trail').children.length === 4, route + ' supports secondary and new-document screens');
    h.close();
}
for (const route of ['clients', 'reports/sales', 'invoices_not_real', 'authentication']) {
    const h = page(route);
    check(!h.d.getElementById('sc-sales-breadcrumbs'), route + ' does not receive Sales breadcrumbs');
    h.close();
}
const restricted = page('payments/payment/31', { entries: [['payments', 'Payments']] });
check(restricted.d.querySelectorAll('.sc-sales-breadcrumbs__links a').length === 1 && !restricted.d.querySelector('.sc-sales-breadcrumbs__links a[href*="invoices"]'), 'Restricted staff see only their rendered menu entries');
const toggle = restricted.d.querySelector('.sc-sales-breadcrumbs__toggle');
const links = restricted.d.querySelector('.sc-sales-breadcrumbs__links');
check(links.hidden && toggle.getAttribute('aria-expanded') === 'false', 'Sales shortcuts are closed initially');
toggle.click();
check(!links.hidden && toggle.getAttribute('aria-expanded') === 'true', 'Click opens Sales shortcuts and keeps them open');
check(restricted.w.location.pathname === '/admin/payments/payment/31', 'Sales button does not use browser history or change the page');
toggle.dispatchEvent(new restricted.w.KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true, cancelable: true }));
check(restricted.d.activeElement === links.querySelector('a'), 'ArrowDown focuses the first shortcut');
links.querySelector('a').dispatchEvent(new restricted.w.KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
check(links.hidden && restricted.d.activeElement === toggle, 'Escape closes shortcuts and restores focus');
toggle.click();
restricted.d.getElementById('outside').click();
check(links.hidden, 'Outside click closes shortcuts');
check(toggle.type === 'button' && !toggle.closest('form'), 'Breadcrumb controls cannot submit a sales form');
check(restricted.d.querySelector('input[name="csrf"]').value === 'original' && restricted.d.querySelector('input[name="amount"]').value === '123' && restricted.d.getElementById('original-form').getAttribute('action') === '/save', 'CSRF, field values and existing form action preserved');
restricted.w.eval(source);
check(restricted.d.querySelectorAll('#sc-sales-breadcrumbs').length === 1, 'Repeated initialization cannot duplicate breadcrumbs');
restricted.close();
const title = page('invoices/invoice/31', { title: '<img src=x onerror=alert(1)>' });
check(!title.d.querySelector('.sc-sales-breadcrumbs img') && title.d.querySelector('[aria-current="page"]').textContent.includes('<img'), 'Document titles are text, never HTML');
title.close();
const fallback = page('payments/payment/31');
check(fallback.d.querySelector('[aria-current="page"]').textContent === 'Payments #31', 'Deep links have a useful detail fallback');
fallback.close();
const customized = page('invoices/invoice/31', { root: '/crm/staff/', group: 'sales-center-main' });
check(customized.d.querySelector('.sc-sales-breadcrumbs__trail a').pathname === '/crm/staff/' && customized.d.querySelector('.sc-sales-breadcrumbs__trail').children[2].querySelector('a').pathname === '/crm/staff/invoices', 'Sales Hub and custom admin prefixes supported');
customized.close();
const absent = page('invoices', { entries: [['payments', 'Payments']] });
check(!absent.d.getElementById('sc-sales-breadcrumbs'), 'No inferred access to links absent from the staff menu');
absent.close();
const unsafe = page('invoices', { entries: [['invoices', 'Invoices'], ['https://evil.example/admin/payments', 'External']] });
// Replace the external URL before reinitializing: fixture prepends its admin root by default.
unsafe.d.getElementById('sc-sales-breadcrumbs').remove();
unsafe.d.querySelector('.nav-second-level li:last-child a').href = 'https://evil.example/admin/payments';
unsafe.w.eval(source);
check(unsafe.d.querySelectorAll('.sc-sales-breadcrumbs__links a').length === 1, 'External menu links excluded');
unsafe.close();
const nested = page('invoices/recurring/31', { entries: [['invoices', 'Invoices'], ['invoices/recurring', 'Recurring invoices']] });
check(nested.d.querySelector('.sc-sales-breadcrumbs__trail').children[2].textContent === 'Recurring invoices', 'Most specific Sales child route wins');
nested.close();
const separate = page('invoices');
separate.d.getElementById('sc-sales-breadcrumbs').remove();
separate.d.getElementById('side-menu').insertAdjacentHTML('beforeend', '<li class="menu-item-sales-center-main"><a><span class="menu-text">Sales Hub</span></a><ul class="nav-second-level"><li><a href="/admin/sales_center"><span class="sub-menu-text">Sales Representatives</span></a></li></ul></li>');
separate.w.eval(source);
check(separate.d.querySelectorAll('.sc-sales-breadcrumbs__links a').length === 7 && !separate.d.querySelector('.sc-sales-breadcrumbs__links a[href*="sales_center"]'), 'Native Sales shortcuts do not mix in the separate Sales Hub');
separate.close();
const mobile = page('invoices');
mobile.d.getElementById('sc-sales-breadcrumbs').remove();
const header = mobile.d.createElement('header');
header.id = 'header';
header.style.zIndex = '1050';
header.getBoundingClientRect = () => ({ bottom: 62 });
mobile.d.body.prepend(header);
Object.defineProperty(mobile.w, 'innerWidth', { value: 390, configurable: true });
const originalRect = mobile.w.HTMLElement.prototype.getBoundingClientRect;
mobile.w.HTMLElement.prototype.getBoundingClientRect = function () { return this.id === 'sc-sales-breadcrumbs' ? { top: 80 } : originalRect.call(this); };
mobile.w.eval(source);
check(mobile.d.getElementById('sc-sales-breadcrumbs').style.zIndex === '1051', 'Mobile breadcrumbs remain clickable over invisible header overflow');
mobile.d.querySelector('.sc-sales-breadcrumbs__toggle').click();
mobile.w.dispatchEvent(new mobile.w.Event('scroll'));
check(mobile.d.querySelector('.sc-sales-breadcrumbs__links').hidden, 'Scrolling closes shortcuts before they overlap sticky navigation');
mobile.d.getElementById('sc-sales-breadcrumbs').remove();
mobile.w.HTMLElement.prototype.getBoundingClientRect = function () { return this.id === 'sc-sales-breadcrumbs' ? { top: 40 } : originalRect.call(this); };
mobile.w.eval(source);
check(mobile.d.getElementById('sc-sales-breadcrumbs').style.zIndex === 'auto', 'Sticky header stays above breadcrumbs when the page scrolls beneath it');
mobile.close();
console.log(`PASS: ${checks} Sales list/detail, permissions, keyboard, deep-link and form-preservation checks`);
