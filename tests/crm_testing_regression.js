'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const read = rel => fs.readFileSync(path.join(root, rel), 'utf8');
(async () => {
  const listeners = {}, input = {value: ''}, result = {textContent: ''};
  input.addEventListener = (name, fn) => { listeners[name] = fn; };
  const button = {addEventListener: (name, fn) => { listeners[name] = fn; }};
  const script = read('application/views/admin/dashboard/widgets/smart_choice_calculator.php').match(/<script>([\s\S]*?)<\/script>/)[1];
  vm.runInNewContext(script, {document: {getElementById: id => ({'sc-core-calc-input': input, 'sc-core-calc-result': result, 'sc-core-calc-btn': button}[id])}, Function: () => { throw Error('dynamic evaluation disabled'); }});
  for (const [expression, expected] of [['1250 * 0.50','625'],['1,250 * 50%','625'],['(2+3)*4','20'],['-.5 * 8','-4'],['0.1 + 0.2','0.3']]) {
    input.value = expression; listeners.click(); assert.equal(result.textContent, expected);
  }
  for (const expression of ['1/0','alert(1)','2..3','2+','']) { input.value = expression; listeners.click(); assert.match(result.textContent,/valid calculation/); }
  input.value = '25+5'; listeners.keydown({key:'Enter',preventDefault(){}}); assert.equal(result.textContent,'30');
  const storage = new Map([['prchat_audio_input','mic-2'],['prchat_audio_output','speaker-2']]);
  const sinks = [];
  const media = {setSinkId: async id => { sinks.push(id); }};
  const context = {window: {localStorage: {getItem:key => storage.get(key),setItem:(key,value)=>storage.set(key,value)}}, document:{getElementById:()=>media}, console, Promise, setTimeout};
  vm.runInNewContext(read('modules/prchat/assets/js/calls/call-manager.js'), context);
  const Manager = context.window.ChatCallManager, manager = new Manager({});
  assert.equal(manager.audioInputId,'mic-2'); assert.equal(manager.audioOutputId,'speaker-2');
  await manager.setAudioInput(''); await manager.setAudioOutput('');
  assert.equal(new Manager({}).audioInputId,null); assert.equal(new Manager({}).audioOutputId,null); assert.deepEqual(sinks,['','','']);
  await manager.setAudioInput('mic-3'); await manager.setAudioOutput('speaker-3');
  assert.equal(new Manager({}).audioInputId,'mic-3'); assert.equal(new Manager({}).audioOutputId,'speaker-3');
  media.setSinkId = async () => {throw Error('device missing');};
  await assert.rejects(manager.setAudioOutput('missing')); assert.equal(storage.get('prchat_audio_output'),'speaker-3');
  console.log('PASS: calculator arithmetic, invalid input, Enter, disabled eval, audio persistence/default reset and device failures');
})().catch(error => {console.error(error);process.exit(1);});
