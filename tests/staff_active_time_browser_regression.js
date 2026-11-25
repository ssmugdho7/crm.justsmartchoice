const vm=require('vm'),fs=require('fs'),assert=require('assert');
let interval,events={},posts=[];const document={hidden:false,addEventListener:(n,f)=>events[n]=f};
const $={post:(url,data)=>{posts.push({url,data});return {fail(){return this;},always(f){f();return this;}};}};
vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/staff-active-time.js','utf8'),{window:{console},document,jQuery:$,admin_url:'/admin/',csrfData:{token_name:'csrf',hash:'fixture'},setInterval:f=>interval=f,Date});
assert.equal(posts[0].data.active,'1');assert.equal(posts[0].data.csrf,'fixture');assert(!('staff_id' in posts[0].data));
document.hidden=true;events.visibilitychange();assert.equal(posts.at(-1).data.active,'0');document.hidden=false;events.pointerdown();interval();assert.equal(posts.at(-1).data.active,'1');
console.log('PASS visible activity, hidden-tab pause, CSRF and server-owned staff identity');
