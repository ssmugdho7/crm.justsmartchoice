// Run with node; open the printed loopback URL in a browser. No application session or packages required.
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '..');
const modules = fs.readdirSync(path.join(root, 'modules')).filter(name => {
 const file = path.join(root, 'modules', name, 'assets/js/smart_choice_module_standard.js');
 return fs.existsSync(file) && /querySelectorAll\('a\[(?:target)?\]'\)/.test(fs.readFileSync(file, 'utf8'));
}).sort();
const server = http.createServer((req, res) => {
 const url = new URL(req.url, 'http://127.0.0.1');
 res.setHeader('Cache-Control', 'no-store');
 if (url.pathname === '/script') {
  const name = url.searchParams.get('module');
  if (!modules.includes(name)) { res.writeHead(404); res.end(); return; }
  const relative = `modules/${name}/assets/js/smart_choice_module_standard.js`;
  const content = url.searchParams.get('baseline') === '1'
    ? execFileSync('git', ['show', `61a5fd9d:${relative}`], {cwd: root})
    : fs.readFileSync(path.join(root, relative));
  res.setHeader('Content-Type', 'text/javascript');res.end(content);return;
 }
 if (url.pathname !== '/') { res.writeHead(404);res.end();return; }
 res.setHeader('Content-Type', 'text/html; charset=utf-8');
 res.end(`<!doctype html><html><title>Repair 46 selector regression</title><body>
 <h1>Repair 46: module selectors</h1><pre id="test-results">Running...</pre>
 <div id="fixtures"><div class="favorite-links"><a id="favorite" href="#" target="_blank">Favorite</a></div>
 <div class="favorite_links"><a id="legacy" href="#" target="_blank">Legacy favorite</a></div>
 <div data-smart-choice-same-tab><a id="scoped" href="#" target="reports">Scoped link</a></div>
 <a id="outside" href="#" target="_blank">Unrelated link</a><button class="btn" style="background-image:linear-gradient(red,blue)">Button</button></div>
 <script>
 (async function(){
  var modules=${JSON.stringify(modules)}, results=[], errors=[];
  window.addEventListener('error',function(e){errors.push(e.message);});
  for(var name of modules){
   document.querySelectorAll('#fixtures a').forEach(function(a){a.setAttribute('target','_blank');});
   document.querySelector('.btn').style.backgroundImage='linear-gradient(red,blue)';
   var before=errors.length;
   await new Promise(function(resolve){var script=document.createElement('script');script.src='/script?module='+encodeURIComponent(name)+'&baseline='+new URLSearchParams(location.search).get('baseline');script.onload=resolve;script.onerror=resolve;document.body.appendChild(script);});
   var ok=errors.length===before && ['favorite','legacy','scoped'].every(function(id){return !document.getElementById(id).hasAttribute('target');}) && document.getElementById('outside').getAttribute('target')==='_blank' && document.querySelector('.btn').style.backgroundImage==='none';
   results.push((ok?'PASS ':'FAIL ')+name);
  }
  var passed=results.filter(function(row){return row.startsWith('PASS');}).length;
  document.getElementById('test-results').textContent=passed+'/'+modules.length+' passed; '+errors.length+' uncaught errors\\n'+results.join('\\n');
  document.title=passed===modules.length?'PASS repair 46 selectors':'FAIL repair 46 selectors';
 })();
 </script></body></html>`);
});
server.listen(0, '127.0.0.1', () => console.log(`Selector browser test: http://127.0.0.1:${server.address().port}/ (add ?baseline=1 to reproduce the original errors)`));
