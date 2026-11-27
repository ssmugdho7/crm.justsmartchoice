'use strict';
const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const root = require('node:path').resolve(__dirname, '..');
const helper = fs.readFileSync(root + '/modules/prchat/assets/module_includes/mutual_and_helper_functions.php', 'utf8');
const start = helper.indexOf('  function prchatSmartChoiceMessageType(');
const script = helper.slice(start, helper.indexOf('\n\n  /**\n   * Reply to message functionality', start));
const css = fs.readFileSync(root + '/modules/prchat/assets/css/chat_full_view.css', 'utf8');
const jquery = fs.readFileSync(root + '/assets/plugins/jquery/jquery.min.js', 'utf8');
(async () => {
 const browser = await chromium.launch({ headless: true, executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' });
 try {
  for (const width of [320, 412, 820, 1280]) {
   const page = await browser.newPage({ viewport: { width, height: 900 } });
   const posts = []; let refuseSave = true;
   await page.route('https://fixture.test/**', async route => {
    const url = new URL(route.request().url());
    if (url.pathname === '/picker') {
     const data = new URLSearchParams(route.request().postData()); posts.push({ path: '/picker', q: data.get('q'), method: route.request().method() });
     const q = data.get('q');
     if (q === 'old') await new Promise(r => setTimeout(r, 350));
     await route.fulfill({ contentType: 'application/json', body: JSON.stringify(q === 'none' ? [] : [{ id: 17, text: q === 'old' ? 'Old result' : 'Roof replacement <script>blocked</script>' }]) }); return;
    }
    if (url.pathname === '/save') {
     posts.push({ path: '/save', data: Object.fromEntries(new URLSearchParams(route.request().postData())) });
     await route.fulfill({ contentType: 'application/json', body: JSON.stringify(refuseSave ? { success: false, message: 'Upload permission changed.' } : { success: true, project_url: '/admin/projects/view/17?group=project_files', message: 'Company copy saved.' }) }); return;
    }
    await route.fulfill({ contentType: 'text/html', body: `<!doctype html><html><head><style>${css}</style></head><body class="app admin Prchat_Controller groups-tab"><div id="wrapper" class="desktop_chat"><div id="frame" class="prchat-conversation-open"><div class="content"><div class="contact-profile"><svg class="chat_back_mobile"></svg></div><div class="group_messages" id="10"><div class="modern-message-item" data-message-id="22"><div class="optionsMore" data-id="22" style="display:block;position:static"><button class="_attachChatToProject">Save to project</button></div><a data-chat-file="roof-plan.pdf" href="/uploads/roof-plan.pdf">roof-plan.pdf</a></div></div><div class="chat_group_options active" style="display:block;right:0;width:500px"><div class="modern-group-panel"><div class="modern-group-body"><div class="file-item"><div class="file-details"><div class="file-name">A-very-long-project-document-name-that-must-fit-a-phone-screen-without-truncating-the-important-description.pdf</div></div><div class="file-actions"><a class="file-action-btn" href="/document.pdf" aria-label="Open document">Open</a><a class="file-action-btn" href="/document.pdf" download aria-label="Download document">Save</a></div></div></div></div></div></div></div></div><textarea id="draft">Unsent draft</textarea></body></html>` });
   });
   await page.goto('https://fixture.test/chat');
   await page.addScriptTag({ content: jquery });
   await page.addScriptTag({ content: `window.prchatSettings={smartChoiceProjectPicker:'/picker',attachChatItemToProject:'/save'};window.alert_float=()=>{};` + script });
   if (width <= 1024) {
    assert.equal(await page.locator('.file-actions').evaluate(e => getComputedStyle(e).opacity), '1', 'Document actions visible without hover');
    for (const a of await page.locator('.file-action-btn').all()) {
     const b = await a.boundingBox(); assert(b.width >= 44 && b.height >= 44, 'Document actions have touch targets');
     assert(b.x >= 0 && b.x + b.width <= width, 'Document actions fit viewport');
    }
   }
   await page.evaluate(() => { document.querySelector('.chat_group_options').style.display='none'; });
   await page.getByRole('button', { name: 'Save to project' }).click();
   const dialog = page.getByRole('dialog'); await dialog.waitFor();
   await page.waitForFunction(() => document.querySelector('#prchat-project-results').options.length === 1);
   const db = await dialog.boundingBox(); assert(db.x >= 0 && db.x + db.width <= width, 'Project picker fits viewport');
   assert.equal(await dialog.locator('script').count(), 0, 'Project names render as text');
   assert(await page.getByRole('button', { name: 'Save copy' }).isDisabled(), 'Explicit selection required');
   await page.getByLabel('Find project').fill('none');
   await page.getByRole('status').filter({hasText:'No matching projects'}).waitFor();
   await page.getByLabel('Find project').fill('old'); await page.waitForTimeout(280);
   await page.getByLabel('Find project').fill('roof');
   await page.waitForFunction(() => document.querySelector('#prchat-project-results').options[0]?.textContent.startsWith('Roof replacement'));
   await page.waitForTimeout(400);
   assert.equal(await page.locator('#prchat-project-results option').count(), 1, 'Stale search cannot replace current results');
   await page.getByLabel('Choose project').selectOption('17');
   await page.evaluate(() => { document.querySelector('.group_messages').id='99'; document.querySelector('.optionsMore').setAttribute('data-id','999'); });
   await page.getByRole('button', { name: 'Save copy' }).click();
   await page.getByRole('status').filter({hasText:'Upload permission changed.'}).waitFor();
   assert.equal(await page.getByRole('link', { name: 'Open project documents' }).count(), 0, 'Rejected save cannot show success link');
   refuseSave = false;
   await page.getByRole('button', { name: 'Save copy' }).click();
   const open = page.getByRole('link', { name: 'Open project documents' }); await open.waitFor();
   assert.equal(await open.getAttribute('href'), 'https://fixture.test/admin/projects/view/17?group=project_files');
   const saved = posts.filter(p => p.path === '/save');
   assert(saved.length === 2 && saved.every(p => p.data.project_id === '17' && p.data.message_id === '22' && p.data.group_id === '10' && p.data.file_name === 'roof-plan.pdf'), 'Save remains bound to original message and group');
   assert(posts.filter(p => p.path === '/picker').every(p => p.method === 'POST'), 'Project searches use POST');
   assert.equal(await page.locator('#draft').inputValue(), 'Unsent draft', 'Saving a document leaves draft untouched');
   await page.keyboard.press('Escape'); assert.equal(await page.getByRole('dialog').count(), 0, 'Native dialog dismisses with Escape');
   console.log(`PASS ${width}px: touch document actions, searchable picker, stale requests, denied save, source isolation, verified success link and keyboard close`);
   await page.close();
  }
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode=1; });
