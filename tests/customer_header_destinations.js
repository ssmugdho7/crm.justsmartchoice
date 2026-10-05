const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
for (const restricted of [false, true]) {
    const routes = restricted ? ['tickets', 'products/client'] : ['projects', 'estimates', 'products/client', 'tickets', 'invoices'];
    const path = route => route.includes('/') ? route : 'clients/' + route;
    const links = routes.map(route => `<li><a href="/${path(route)}">${route}</a></li>`).join('');
    const sidebar = routes.map(route => `<a href="/${path(route)}">${route}</a>`).join('');
    const dom = new JSDOM(`<nav class="navbar header"><ul class="navbar-nav">${links}</ul></nav><div id="wrapper" class="sc-portal-layout"><button class="sc-portal-menu-button"></button><button class="sc-portal-backdrop"></button><aside class="sc-portal-sidebar"><button class="sc-portal-close"></button><button class="sc-portal-collapse"></button><nav><a href="/clients">Dashboard</a>${sidebar}</nav></aside></div>`, {runScripts:'outside-only', url:'https://portal.example/solar_pro/my'});
    const w = dom.window;
    w.matchMedia = () => ({matches:false, addEventListener(){}});
    w.eval(fs.readFileSync('assets/themes/smartchoice/js/customer-portal.js','utf8'));
    w.document.dispatchEvent(new w.Event('DOMContentLoaded'));
    for (const route of ['projects', 'estimates', 'products/client']) {
        const link = w.document.querySelector(`.navbar a[href="/${path(route)}"]`);
        if (routes.includes(route)) assert(!link.parentElement.classList.contains('sc-portal-nav-moved'), 'Requested shortcut must stay in header: ' + route);
        else assert.equal(link, null, 'Do not create a shortcut that permissions did not grant');
    }
    const invoices = w.document.querySelector('.navbar a[href="/clients/invoices"]');
    if (invoices) assert(invoices.parentElement.classList.contains('sc-portal-nav-moved'), 'Other duplicated destinations stay in the sidebar');
    assert(w.document.body.classList.contains('sc-customer-enhanced'), 'Sticky customer header must remain enabled');
    dom.window.close();
}
console.log('PASS customer header shortcuts across Solar pages and permission-restricted contacts');
