'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { JSDOM } = require('jsdom');
const root = path.join(__dirname, '..');
const view = fs.readFileSync(path.join(root, 'application/views/admin/includes/modals/sales_attach_file.php'), 'utf8');
const source = view.match(/<script>([\s\S]*?)<\/script>/)[1];
const jquery = fs.readFileSync(path.join(root, 'assets/plugins/jquery/jquery.min.js'), 'utf8');

for (const early of [true, false]) {
    const dom = new JSDOM(`<input name="_attachment_sale_type" value="proposal"><input name="_attachment_sale_id" value="91">
        <div id="sales_attach_file"><div id="sc-sales-links-editor"><div class="sc-sales-link-row">
        <input class="sc-link-title"><input class="sc-link-url"></div><button type="button" id="sc-save-sales-links">Save</button></div></div>`,
        { runScripts: 'outside-only', url: 'https://crm.example/admin/proposals/list_proposals/91' });
    const w = dom.window;
    let ready = early ? 'loading' : 'complete';
    Object.defineProperty(w.document, 'readyState', { get: () => ready });
    const gets = [], posts = [];
    w.requestGetJSON = url => {
        gets.push(url);
        return { done: fn => fn({ links: [{ title: 'Plans', url: 'https://example.test/plans' }] }) };
    };
    w.requestPostJSON = (url, data) => { posts.push({ url, data }); return { done: fn => fn({ success: true }) }; };
    w.alert_float = () => {};
    if (early) {
        assert.doesNotThrow(() => w.eval(source), 'early markup waits without touching jQuery');
        assert.equal(gets.length, 0, 'initialization does not fetch or save document data');
        w.eval(jquery);
        ready = 'complete';
        w.document.dispatchEvent(new w.Event('DOMContentLoaded'));
    } else {
        w.eval(jquery);
        w.eval(source);
    }
    // An AJAX-loaded sales preview can include this shared markup again.
    w.eval(source);
    w.jQuery('#sales_attach_file').trigger('shown.bs.modal');
    assert.deepEqual(gets, ['smart_choice_sales_links/get/proposal/91'], 'one modal open loads links once');
    assert.equal(w.document.querySelector('.sc-link-title').value, 'Plans');
    assert.equal(w.document.querySelector('.sc-link-url').value, 'https://example.test/plans');
    w.document.getElementById('sc-save-sales-links').click();
    assert.equal(posts.length, 1, 'one click saves once after repeated initialization');
    assert.equal(posts[0].url, 'smart_choice_sales_links/save');
    assert.equal(posts[0].data.rel_type, 'proposal');
    assert.equal(posts[0].data.rel_id, 91);
    assert.equal(posts[0].data.link_title[0], 'Plans');
    assert.equal(posts[0].data.link_url[0], 'https://example.test/plans');
    w.close();
}
console.log('PASS sales attachment links before/after jQuery loading and repeated AJAX initialization; no external requests sent');
