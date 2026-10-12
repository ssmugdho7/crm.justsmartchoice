const assert = require('node:assert/strict');
const vm = require('node:vm');
const {execFileSync} = require('node:child_process');
const fixture = JSON.parse(execFileSync('php',[__dirname + '/team_clocks_regression.php','--fixture'],{encoding:'utf8'}));
let time = Date.parse('2026-01-15T10:00:00Z');
class ClockDate extends Date { constructor(){super(time);} }
const elements = fixture.zones.map(zone => ({textContent:'--:--',getAttribute:()=>zone}));
let tick, visible;
const document = {
 querySelectorAll(selector) {assert.equal(selector,'#widget-smart-choice-world-clock .sc-core-clock');return elements;},
 addEventListener(name,fn){assert.equal(name,'visibilitychange');visible=fn;}
};
vm.runInNewContext(fixture.script,{document,Date:ClockDate,Intl,window:{setInterval(fn,delay){assert.equal(delay,30000);tick=fn;}}});
assert.deepEqual(elements.map(e=>e.textContent),['3:30 PM','3:00 PM','6:00 PM','5:00 AM']);
time += 60000;
tick();
assert.deepEqual(elements.map(e=>e.textContent),['3:31 PM','3:01 PM','6:01 PM','5:01 AM']);
time += 3600000;
visible();
assert.deepEqual(elements.map(e=>e.textContent),['4:31 PM','4:01 PM','7:01 PM','6:01 AM']);
console.log('PASS retained widget script initializes correct country times, updates periodically and refreshes after returning to the tab');
