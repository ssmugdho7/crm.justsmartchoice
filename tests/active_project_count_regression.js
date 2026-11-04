const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
(async function () {
  const events = {}, text = {textContent:'17'}, widget = {dataset:{countUrl:'/admin/smart_choice_ui/active_projects_count'},querySelector:()=>text};
  let next = {count:17}, calls=0;
  const context = {Number, String, Error, document:{hidden:false,getElementById:()=>widget,addEventListener:(k,v)=>events[k]=v},window:{addEventListener:(k,v)=>events[k]=v,setInterval:(fn)=>events.interval=fn},fetch:async()=>{calls++;return {ok:true,json:async()=>next}}};
  const source = fs.readFileSync(__dirname+'/../assets/js/sc-active-project-count.js','utf8');
  vm.runInNewContext(source,context);
  const flush=()=>new Promise(resolve=>setImmediate(resolve));
  await flush(); assert.equal(text.textContent,'17');
  next={count:15}; events.focus(); await flush(); assert.equal(text.textContent,'15');
  next={count:-1}; events.interval(); await flush(); assert.equal(text.textContent,'15');
  context.fetch=async()=>{throw new Error('Expired session/network');};events.pageshow();await flush();assert.equal(text.textContent,'15');
  context.document.hidden=true;const before=calls;events.interval();await flush();assert.equal(calls,before);
  vm.runInNewContext(source,context);assert.equal(calls,before);
  console.log('PASS 17-to-15 refresh, invalid/error retention, hidden-page suppression and duplicate initialization guard');
})().catch(e=>{console.error(e);process.exit(1)});
