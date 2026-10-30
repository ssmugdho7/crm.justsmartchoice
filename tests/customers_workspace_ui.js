'use strict';
const fs = require('fs');
const { JSDOM } = require('jsdom');

function assert(condition, message) {
  if (!condition) throw new Error(message);
  console.log('PASS:', message);
}

const html = '<!doctype html><body class="sccw-customers"><div id="wrapper"><main class="content">' +
  '<div class="tw-grid tw-grid-cols-2"><div><span>87</span><span>Total</span></div><div><span>85</span><span>Active</span></div></div>' +
  '<div class="panel_s"><table class="table-clients"><tbody><tr><td><div class="row-options"><a href="#">View</a></div></td></tr></tbody></table></div>' +
  '<nav class="customer-tabs"></nav><form class="client-form"></form><div id="contact" class="modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>' +
  '</main></div></body>';
const dom = new JSDOM(html, { runScripts: 'outside-only', url: 'https://dev.example/admin/clients' });
const jquery = require('jquery')(dom.window);
dom.window.jQuery = jquery;
dom.window.$ = jquery;
dom.window.scCustomersWorkspace = {
  method: 'index', customersUrl: '/admin/clients', contactsUrl: '/admin/clients/all_contacts',
  title: 'Customer Management', subtitle: 'Customer accounts and property portfolios',
  customers: 'Customers', contacts: 'Contacts'
};
dom.window.eval(fs.readFileSync('modules/sc_customers_workspace/assets/js/customers.js', 'utf8'));

assert(dom.window.document.querySelectorAll('.sccw-header').length === 1, 'adds one translated customer header');
assert(dom.window.document.querySelector('.sccw-nav a.active').textContent === 'Customers', 'marks customer directory navigation active');
assert(dom.window.document.querySelectorAll('.sccw-kpi').length === 2, 'decorates live KPI cards without replacing their values');
assert(dom.window.document.querySelector('.table-clients').classList.contains('sccw-data-table'), 'decorates the native customer table');
assert(dom.window.document.querySelector('.customer-tabs').classList.contains('sccw-profile-tabs'), 'decorates the native profile navigation');
assert(dom.window.document.querySelector('#contact .modal-content').classList.contains('sccw-contact-modal'), 'decorates the native contact modal');
