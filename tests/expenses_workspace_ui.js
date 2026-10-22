/* Executable UI interaction checks; fixture DOM, no CRM records or credentials. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const jquery = require('jquery');
const code = fs.readFileSync('modules/sc_expenses_workspace/assets/js/expenses.js', 'utf8');
const dom = new JSDOM(`<!doctype html><body class="scct-expenses"><div id="expenses_total"><button data-scct-expense-status="all"></button><button data-scct-expense-status="billed"></button></div><select id="scct-expense-status"><option value="all">All</option><option value="billed">Billed</option></select></body>`, { runScripts:'outside-only', url:'https://fixture.invalid/admin/expenses/list_expenses' });
const $ = jquery(dom.window); dom.window.jQuery = $; dom.window.admin_url = 'https://fixture.invalid/admin/';
let prefilter, refreshes=0, changes=0;
$.ajaxPrefilter = fn => { prefilter=fn; };
$.fn.selectpicker = function (action) { assert.equal(action, 'refresh'); refreshes++; return this; };
$('#scct-expense-status').on('change', () => changes++);
dom.window.eval(code);
$('[data-scct-expense-status="billed"]').trigger('click');
assert.equal($('#scct-expense-status').val(), 'billed');
assert.equal(changes, 1); assert.equal(refreshes, 1);
assert.equal($('[data-scct-expense-status="billed"]').attr('aria-pressed'), 'true');
assert.equal($('[data-scct-expense-status="all"]').attr('aria-pressed'), 'false');
$('#expenses_total').html('<button data-scct-expense-status="billed"></button>');
$(dom.window.document).trigger('ajaxComplete', [{}, {url:dom.window.admin_url+'expenses/get_expenses_total'}]);
assert.equal($('[data-scct-expense-status="billed"]').attr('aria-pressed'), 'true');
const total = {url:dom.window.admin_url+'expenses/get_expenses_total',data:'csrf=fixture&years%5B%5D=2026'};
prefilter(total);assert.equal(total.data,'csrf=fixture&years%5B%5D=2026&scct_expenses_workspace=1');
const other={url:dom.window.admin_url+'expenses/table',data:'csrf=fixture'};prefilter(other);assert.equal(other.data,'csrf=fixture');
$('#scct-expense-status').remove();$('[data-scct-expense-status="billed"]').trigger('click');assert.equal(changes,1);
const untouched=new JSDOM('<body class="clients"></body>',{runScripts:'outside-only'});
untouched.window.jQuery=jquery(untouched.window);untouched.window.jQuery.ajaxPrefilter=()=>{throw Error('Must not change client pages');};untouched.window.eval(code);
// All native form controls, recurring choices, CSRF helper and endpoints stay present in the new form.
for(const file of ['expense.php','manage.php','expense_preview_template.php','manage_categories.php']) {
 const original=fs.readFileSync('application/views/admin/expenses/'+file,'utf8');
 const updated=fs.readFileSync('modules/sc_expenses_workspace/views/expenses/'+file,'utf8');
 const names=[...original.matchAll(/(?:name|id)="([^"<]+)"/g)].map(m=>m[0]);
 for(const name of names) assert.ok(updated.includes(name), file+' retains native control '+name);
 const controls=[...original.matchAll(/(?:render_input|render_select|render_date_input|render_textarea|form_hidden|form_open_multipart|form_open)\(\s*['"]([^'"]+)/g)].map(m=>m[0]);
 for(const control of controls) assert.ok(updated.includes(control),file+' retains '+control);
}
console.log('PASS: expense status actions, AJAX refresh, native filters/CSRF payload, scope isolation and form control preservation');
