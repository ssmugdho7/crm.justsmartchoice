"""Run the address helper in Chrome against new/edit/modal form fixtures."""
import re
import subprocess
import tempfile
from pathlib import Path

root = Path(__file__).resolve().parents[1]
helper = (root / 'application/helpers/smart_choice_maps_helper.php').read_text()
script = re.search(r'<script>\n(.*?)\n</script>', helper, re.S)[1]
fixture = '''<form><div class="form-group"><textarea name="address"></textarea></div>
<div class="sc-google-map-field"><input name="google_map_link"><button type="button" class="sc-map-from-address">Create map link</button></div>
<input name="city"><input name="state"><input name="zip"></form>'''
checks = '''
const form = document.querySelector('form');
const field = name => form.querySelector('[name="'+name+'"]');
function set(name, value) {field(name).value=value;field(name).dispatchEvent(new Event('input',{bubbles:true}));}
function check(condition, message) {if(!condition)throw Error(message);}
check(field('address').parentElement.querySelector('.sc-address-format-hint'), 'hint under address');
check(!field('google_map_link').parentElement.querySelector('.sc-address-format-hint'), 'no address hint under map');
set('address','123 Main St, Tampa, FL 33601');
check(field('city').value==='Tampa'&&field('state').value==='FL'&&field('zip').value==='33601','address components');
check(field('address').value==='123 Main St, Tampa, FL 33601','full address retained');
set('address','234 Oak St, Orlando, FL 32801');
check(field('city').value==='Orlando'&&field('zip').value==='32801','correcting auto-filled components');
set('city','Manual city');
set('address','25 First St, Miami, FL 33101-1234');
check(field('city').value==='Manual city'&&field('zip').value==='33101-1234','manual edits preserved');
set('address','50% complete');
check(field('zip').value==='33101-1234','partial and percent input safe');
set('google_map_link','https://maps.app.goo.gl/abc');
check(field('city').value==='Manual city','map url independent');
set('address','123 Main St, Tampa, FL 33601');
form.querySelector('button').click();
check(new URL(field('google_map_link').value).searchParams.get('query')===field('address').value,'one-click map link');
field('city').value='Existing city';
field('zip').value='10001';
delete field('zip').dataset.scAutofilled;
set('address','123 Main St, Tampa, FL 33601');
check(field('city').value==='Existing city'&&field('zip').value==='10001','saved values preserved');
document.body.dataset.result='PASS';
'''
with tempfile.TemporaryDirectory(prefix='lead-address-test-') as temp:
    html=Path(temp)/'index.html'
    html.write_text('<!doctype html>'+fixture+'<script>'+script+'</script><script>try{'+checks+'}catch(e){document.body.dataset.result="FAIL "+e.message}</script>')
    result=subprocess.run(['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome','--headless','--no-first-run','--user-data-dir='+temp+'/profile','--dump-dom',html.as_uri()],capture_output=True,text=True,timeout=30,check=True)
    match=re.search(r'data-result="([^"]*)"',result.stdout)
    assert match and match[1]=='PASS',match[1] if match else result.stderr[-500:]
    print('PASS: hint placement, autofill, corrected addresses, manual/saved fields, ZIP+4, partial input, map URL and map generation')
