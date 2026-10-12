const assert=require('node:assert/strict');
const vm=require('node:vm');
const {execFileSync}=require('node:child_process');
const fixture=JSON.parse(execFileSync('php',[__dirname+'/dashboard_calculator_regression.php','--fixture'],{encoding:'utf8'}));
const ids=['input','btn','result','reset','copy','feedback'];
const nodes=Object.fromEntries(ids.map(id=>['sc-core-calc-'+id,{value:'',textContent:'',disabled:id==='copy',events:{},addEventListener(event,fn){this.events[event]=fn;},focus(){this.focused=true;}}]));
const input=nodes['sc-core-calc-input'],result=nodes['sc-core-calc-result'],copy=nodes['sc-core-calc-copy'],feedback=nodes['sc-core-calc-feedback'];
let clipboard='',fallback='';
const navigator={clipboard:{async writeText(value){clipboard=value;}}};
const document={getElementById:id=>nodes[id],body:{appendChild(field){fallback=field.value;}},createElement(){return {style:{},setAttribute(){},select(){},remove(){}};},execCommand(command){assert.equal(command,'copy');return true;}};
vm.runInNewContext(fixture.script,{document,navigator,Number});
function calculate(value){input.value=value;nodes['sc-core-calc-btn'].events.click();return result.textContent;}
(async()=>{
 for(const [expression,expected] of [['12*10','120'],['(100+25)/5','25'],['200*10%','20'],['1,250 * .5','625'],['.1+.2','0.3'],['-2*-3','6'],['2×3÷2','3'],['0','0']]) assert.equal(calculate(expression),expected,expression);
 await copy.events.click();assert.equal(clipboard,'0');assert.equal(feedback.textContent,'Result copied.');
 calculate('1,250');await copy.events.click();assert.equal(clipboard,'1,250');
 navigator.clipboard.writeText=async()=>{throw new Error('denied');};
 await copy.events.click();assert.equal(fallback,'1,250');assert.equal(feedback.textContent,'Result copied.');
 document.execCommand=()=>false;await copy.events.click();assert.match(feedback.textContent,/Could not copy/);
 input.events.input();assert.equal(result.textContent,'');assert.equal(copy.disabled,true);
 for(const expression of ['','1/0','(2+3','1,2','2**3','alert(1)','1;2']){assert.match(calculate(expression),/valid calculation/);assert.equal(copy.disabled,true);}
 input.value='5+7';let prevented=false;input.events.keydown({key:'Enter',preventDefault(){prevented=true;}});assert.equal(result.textContent,'12');assert.equal(prevented,true);
 nodes['sc-core-calc-reset'].events.click();assert.equal(input.value,'');assert.equal(result.textContent,'');assert.equal(feedback.textContent,'');assert.equal(copy.disabled,true);assert.equal(input.focused,true);
 console.log('PASS arithmetic, percentages, invalid inputs, Enter, Reset, Copy, clipboard rejection fallback and stale-result clearing');
})().catch(error=>{console.error(error);process.exitCode=1;});
