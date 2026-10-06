// Isolated UI check: dummy values only; no authentication or form submission.
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const {JSDOM} = require('jsdom');
for (const theme of ['smartchoice', 'perfex']) {
    const html = execFileSync('php', ['tests/customer_profile_render.php'], {
        env: {...process.env, PROFILE_RENDER_HTML:'1', PROFILE_TEST_THEME:theme}, encoding:'utf8'
    });
    const dom = new JSDOM(html, {runScripts:'dangerously', url:'https://portal.example/clients/profile'});
    try {
        const doc = dom.window.document;
        const names = ['oldpassword', 'newpassword', 'newpasswordr'];
        const inputs = names.map(name => doc.getElementById(name));
        const buttons = names.map(name => doc.querySelector('button[aria-controls="'+name+'"]'));
        const form = inputs[0].form;
        let submissions = 0;
        form.addEventListener('submit', event => { event.preventDefault(); submissions++; });
        inputs.forEach((input,index) => { input.value='Dummy-value-'+index; });
        const payload = Array.from(new dom.window.FormData(form).entries());
        buttons.forEach((button,index) => {
            assert.equal(button.type,'button');
            assert.equal(inputs[index].type,'password');
            button.click();
            assert.equal(inputs[index].type,'text');
            assert.equal(button.getAttribute('aria-pressed'),'true');
            assert.match(button.getAttribute('aria-label'),/^Hide /);
            assert.ok(button.querySelector('.fa-eye-slash'));
            inputs.forEach((input,other) => { if(other!==index) assert.equal(input.type,'password','Other fields remain masked'); });
            assert.deepEqual(Array.from(new dom.window.FormData(form).entries()),payload,'Visibility does not change submitted values');
            button.click();
            assert.equal(inputs[index].type,'password');
            assert.equal(button.getAttribute('aria-pressed'),'false');
            assert.match(button.getAttribute('aria-label'),/^Show /);
        });
        assert.equal(submissions,0,'Eye icons cannot submit the password form');
        buttons.forEach(button=>button.click());
        form.dispatchEvent(new dom.window.Event('submit',{cancelable:true}));
        assert.ok(inputs.every(input=>input.type==='password'),'Submission remasks all fields');
        assert.deepEqual(Array.from(new dom.window.FormData(form).entries()),payload);
        buttons.forEach(button=>button.click());
        form.reset();
        assert.ok(inputs.every(input=>input.type==='password'),'Reset remasks all fields');
        console.log('PASS '+theme+': independent password eyes, labels, masking, unchanged payloads and no accidental submission');
    } finally { dom.window.close(); }
}
