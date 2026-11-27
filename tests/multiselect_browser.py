"""Exercise the real Bootstrap Select widget and CRM tick styling in Chrome."""
from pathlib import Path
import re, subprocess, tempfile
root=Path(__file__).resolve().parents[1]
scripts=['assets/plugins/jquery/jquery.min.js','assets/plugins/bootstrap/js/bootstrap.min.js','assets/builds/bootstrap-select.min.js','assets/js/sc-multiselect.js']
html='<!doctype html><meta charset="utf-8">'
for path in ['assets/plugins/bootstrap/css/bootstrap.min.css','assets/plugins/bootstrap-select/css/bootstrap-select.css','assets/css/sc-multiselect.css']:
    html+='<style>'+(root/path).read_text()+'</style>'
html+='''<body class="admin"><form><select class="selectpicker" id="team" name="assigned_staff[]" multiple><option value="1">Alex</option><option value="2">Jamie</option><option value="3" disabled>Disabled</option></select><select class="selectpicker" id="status"><option>New</option><option>Contacted</option></select></form>'''
for path in scripts: html+='<script>'+(root/path).read_text()+'</script>'
html+='''<script>$(function(){ $('.selectpicker').selectpicker(); setTimeout(function(){try{
function check(v,label){if(!v)throw Error(label);}
$('.selectpicker').selectpicker();
const picker=$('#team').data('selectpicker');
picker.$button.click();
const row = text => Array.from(picker.$menu[0].querySelectorAll('li a')).find(a=>a.textContent.includes(text));
row('Alex').querySelector('.check-mark').click();
row('Jamie').querySelector('.check-mark').click();
check(JSON.stringify($('#team').val())==='["1","2"]','two options selected');
check(getComputedStyle(row('Alex').querySelector('.check-mark'),'::after').content!=='none','visible tick');
check(JSON.stringify(new FormData(document.querySelector('form')).getAll('assigned_staff[]'))==='["1","2"]','form posts multiple values');
row('Alex').querySelector('.check-mark').click();
check(JSON.stringify($('#team').val())==='["2"]','tick deselection');
check(!$('#status').prop('multiple'),'single status preserved');
$('#team').selectpicker('refresh');
check(picker.$newElement.hasClass('sc-multiselect'),'refresh keeps ticks');
$('#team').selectpicker('destroy').selectpicker();
setTimeout(function(){document.body.dataset.result=$('#team').data('selectpicker').$newElement.hasClass('sc-multiselect')?'PASS':'FAIL reinitialize';},100);
}catch(e){document.body.dataset.result='FAIL '+e.stack;}},100);});</script>'''
with tempfile.TemporaryDirectory() as tmp:
    file=Path(tmp)/'test.html';file.write_text(html)
    r=subprocess.run(['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome','--headless','--no-first-run','--user-data-dir='+tmp+'/profile','--virtual-time-budget=2000','--dump-dom',file.as_uri()],capture_output=True,text=True,timeout=30)
    match=re.search(r'data-result="([^"]*)"',r.stdout)
    assert match and match[1]=='PASS',match[1] if match else r.stderr[-500:]
    print('PASS: click ticks, select two, deselect, serialize form, refresh, reinitialize, single-select status')
