'use strict';

const fs = require('fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');

const source = fs.readFileSync(__dirname + '/../assets/js/sc-delete-confirmation.js', 'utf8');

async function create(promptAnswers) {
    const dom = new JSDOM(
        '<body><a class="_delete" href="/admin/proposals/delete/7">Delete proposal</a></body>',
        {url: 'https://crm.example/admin/proposals', runScripts: 'outside-only', pretendToBeVisual: true}
    );
    const {window} = dom;
    const prompts = [];
    window.prompt = (message, value) => {
        prompts.push({message, value});
        return promptAnswers.shift();
    };
    window.HTMLFormElement.prototype.submit = function () {
        this.dataset.submitted = 'true';
    };
    window.document.body.insertAdjacentHTML('afterbegin',
        '<script id="sc-delete-config" type="application/json">' +
        JSON.stringify({
            basePath: '/admin/',
            actionPattern: '(?:^|_)(?:delete|remove)(?:_|$)',
            title: 'Delete',
            cancel: 'Cancel',
            instruction: 'Type DELETE to confirm deletion.'
        }) +
        '</script>'
    );
    window.eval(source);
    return {dom, window, prompts};
}

(async () => {
    let test = await create(['delete']);
    assert.equal(await test.window.SCDelete.confirm('Remove this record?'), false, 'lowercase DELETE is rejected');
    assert.equal(test.prompts.length, 1);

    test = await create(['DELETE']);
    assert.equal(await test.window.SCDelete.confirm('Remove this record?'), true, 'exact DELETE is accepted');

    test = await create(['DELETE']);
    test.dom.window.document.querySelector('a._delete').click();
    await new Promise(resolve => setTimeout(resolve, 0));
    const form = test.dom.window.document.querySelector('form');
    assert(form, 'confirmed links are converted to a POST form');
    assert.equal(form.querySelector('[name="delete_confirmation"]').value, 'DELETE');
    assert.equal(form.dataset.submitted, 'true');

    test = await create(['']);
    test.dom.window.document.querySelector('a._delete').click();
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(test.dom.window.document.querySelector('form'), null, 'cancelled links do not submit');
    assert.equal(test.prompts.length, 1);

    console.log('PASS DELETE confirmation accepts only exact text, blocks cancellation, and submits authorized links as POST');
})().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
