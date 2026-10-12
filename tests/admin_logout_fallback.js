// Exercise the actual view handlers with loaded, missing and failing admin JS.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
for (const name of ['header', 'aside']) {
  const view = fs.readFileSync(path.join(__dirname, '../application/views/admin/includes', name + '.php'), 'utf8');
  const block = view.match(/<li class="header-logout">([\s\S]*?)<\/li>/)[1];
  assert(block.includes('href="<?= admin_url(\'authentication/logout\'); ?>"'), name + ' uses the server-generated logout route');
  const handler = block.match(/onclick="([^"]+)"/)[1];
  const run = context => vm.runInNewContext('(function(){' + handler + '})()', context);
  assert.equal(run({}), undefined, name + ' allows normal link navigation when main.js is missing');
  let calls = 0;
  assert.equal(run({logout() { calls++; }}), false, name + ' delegates to the loaded logout function');
  assert.equal(calls, 1);
  assert.equal(run({logout() { return false; }}), false, name + ' preserves timer-warning interception');
  assert.equal(run({logout: null}), undefined, name + ' handles a non-function global');
  console.log('PASS ' + name + ' logout navigation and timer warning fallback');
}
