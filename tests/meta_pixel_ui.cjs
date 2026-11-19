const {chromium}=require('playwright');
const fs=require('fs'),assert=require('assert'),path=require('path');
const source=fs.readFileSync(path.join(__dirname,'../modules/zillapage_off/assets/landingpage/js/meta-pixel.js'),'utf8');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE});
 try{
  for(const restricted of [false,true]){
   const page=await browser.newPage();let requests=0;
   await page.addInitScript(restricted=>Object.defineProperty(navigator,'globalPrivacyControl',{value:restricted}),restricted);
   await page.route('https://connect.facebook.net/**',r=>{requests++;return r.fulfill({body:`window.events=[];fbq.callMethod=function(){events.push([...arguments])};fbq.queue.forEach(args=>fbq.callMethod(...args));`,contentType:'application/javascript'});});
   await page.route('https://crm.test/',r=>r.fulfill({body:'<div id="sc-meta-consent" data-pixel-id="1159018933475955" hidden><button data-consent="allow">Allow</button><button data-consent="deny">Decline</button></div><button id="sc-meta-preferences" hidden>Cookie preferences</button>',contentType:'text/html'}));
   await page.goto('https://crm.test/');await page.addScriptTag({content:source});
   assert.equal(requests,0,'No Meta request before consent');
   await page.evaluate(()=>document.dispatchEvent(new CustomEvent('sc:landing-lead-created')));
   assert.equal(requests,0,'Lead without consent sends nothing');
   if(restricted){assert.equal(await page.locator('#sc-meta-consent').isVisible(),false,'Privacy signal suppresses tracking');await page.close();continue;}
   await page.getByText('Decline',{exact:true}).click();assert.equal(requests,0,'Decline loads no tracker');
   await page.getByText('Cookie preferences',{exact:true}).click();await page.getByText('Allow',{exact:true}).click();
   await page.waitForFunction(()=>window.events&&events.some(e=>e[2]==='PageView'));
   assert.equal(requests,1,'One loader after consent');
   await page.evaluate(()=>document.dispatchEvent(new CustomEvent('sc:landing-lead-created')));
   assert.deepEqual(await page.evaluate(()=>events.filter(e=>e[0]==='trackSingle')), [['trackSingle','1159018933475955','PageView'],['trackSingle','1159018933475955','Lead']],'Explicit single-Pixel events with no form data');
   await page.getByText('Cookie preferences',{exact:true}).click();await page.getByText('Decline',{exact:true}).click();
   const before=await page.evaluate(()=>events.length);await page.evaluate(()=>document.dispatchEvent(new CustomEvent('sc:landing-lead-created')));assert.equal(await page.evaluate(()=>events.length),before,'Declining stops lead events');
   await page.getByText('Cookie preferences',{exact:true}).click();await page.getByText('Allow',{exact:true}).click();assert.equal(requests,1,'Reconsent does not reload tracker');
   assert.deepEqual(await page.evaluate(()=>events.at(-1)),['consent','grant']);
   await page.close();
  }
  console.log('PASS Meta Pixel: consent, decline/reconsent, GPC, single initialization, PageView and successful Lead without form data');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
