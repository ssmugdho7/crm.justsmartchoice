const fs=require('fs'),assert=require('assert'),{JSDOM}=require('jsdom');
let dom=new JSDOM('<table class="table-projects"><tr><td><button class="native-project-scope"></button></td></tr></table><div id="native-project-scope-modal"><h4 class="modal-title"></h4><div class="modal-body"></div></div>',{runScripts:'outside-only'}),$=require('jquery')(dom.window);dom.window.$=$;let opened=false;$.fn.modal=function(v){opened=v==='show';return this;};
let source=fs.readFileSync('application/views/admin/projects/manage.php','utf8');source=source.slice(source.lastIndexOf('<script>')+8,source.lastIndexOf('</script>'));
dom.window.eval(source);let text=Array.from({length:50},(_,i)=>'Day '+i+' <script>bad()</script>').join('\n');$('.native-project-scope').attr({'data-scope':text,'data-scope-title':'<Job>'}).trigger('click');
assert(opened);assert.equal($('.modal-body').text(),text);assert.equal($('.modal-body script').length,0);assert.equal($('.modal-title').text(),'<Job>');
console.log('PASS full 50-line scope dialog and title remain inert text');
