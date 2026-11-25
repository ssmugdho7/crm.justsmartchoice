const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../modules/smart_square_payments/views/checkout.php', 'utf8');
const script = source.match(/<script>\s*([\s\S]*?)<\/script>/)[1]
    .replace(/<\?= json_encode\(\$billing,[\s\S]*?\?>/g, JSON.stringify({address_line_1:'10 Test Street',locality:'Tampa',administrative_district_level_1:'FL',postal_code:'33601',country:'US'}))
    .replace(/<\?= json_encode\(site_url\('invoice\/'[\s\S]*?\)\); \?>/g, JSON.stringify('https://crm.test/invoice/122/hash'))
    .replace(/<\?=[\s\S]*?\?>/g, JSON.stringify('fixture'));

async function run(responses, valid = true, subscription = false) {
    let click, tokens = 0;
    const requests = [], redirects = [], enrollments = [];
    const button = { disabled: true };
    const status = { textContent: '' };
    const elements = {'smart-square-pay':button,'smart-square-status':status};
    for (const id of ['square-street','square-city','square-state','square-postal','square-same-address','square-terms','smart-square-edit','smart-square-billing']) {
        elements[id] = {value:'',checked:false,disabled:false,handlers:{},addEventListener(event,fn){this.handlers[event]=fn;},scrollIntoView(){},focus(){}};
    }
    if (subscription) {
        delete elements['square-same-address'];
        delete elements['smart-square-edit'];
        elements['smart-square-subscription-source'] = { value: '' };
    }
    elements['smart-square-checkout-form'] = {
        reportValidity: () => valid,
        addEventListener: (_, fn) => { click = fn; },
        submit: () => enrollments.push(elements['smart-square-subscription-source'].value),
    };
    const context = {
        document: { getElementById: id => elements[id] },
        URLSearchParams,
        window: {
            Square: { payments: () => ({ card: async () => ({
                attach: async () => {}, tokenize: async () => { tokens++; return { status: 'OK', token: 'one-card-token' }; }
            }) }) },
            location: { assign: url => redirects.push(url) },
        },
        fetch: async (_, options) => {
            requests.push(options.body);
            const response = responses.shift();
            if (response instanceof Error) throw response;
            return response;
        },
    };
    vm.runInNewContext(script, context);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(button.disabled, false, 'Card attachment enables Pay');
    if (!subscription) {
        elements['square-same-address'].checked = true;
        elements['square-same-address'].handlers.change();
        assert.equal(elements['square-city'].value,'Tampa','Same-address fills invoice city');
    }
    for (let i = 0; i < 2 && responses.length; i++) {
        await click({preventDefault(){}});
        if (i === 0 && requests.length) elements['square-street'].value = 'Changed after request';
    }
    return { button, tokens, requests, redirects, enrollments, status, elements };
}

(async () => {
    const success = await run([{ ok: true, json: async () => ({ success: true, message: 'Paid' }) }]);
    assert.deepEqual(success.redirects, ['https://crm.test/invoice/122/hash']);
    assert.equal(success.button.disabled, true, 'Success cannot be submitted again');
    const sent = new URLSearchParams(success.requests[0]);
    assert.equal(sent.get('billing_address[address_line_1]'),'10 Test Street','Billing address accompanies the payment');
    assert.equal(success.elements['smart-square-billing'].disabled,true,'Submitted address is locked for safe retries');
    const invalid = await run([{ok:true}],false);
    assert.equal(invalid.tokens,0,'Incomplete address or unaccepted terms never tokenize a card');
    assert.equal(invalid.requests.length,0,'Invalid form never posts a payment');
    const retry = await run([new Error('connection lost'), { ok: true, json: async () => ({ success: true }) }]);
    assert.equal(retry.tokens, 1, 'Interrupted response reuses card token');
    assert.equal(retry.requests[0], retry.requests[1], 'Retry preserves the exact request');
    const invalidJson = await run([{ ok: true, json: async () => { throw new SyntaxError('HTML response'); } }]);
    assert.equal(invalidJson.redirects.length, 0, 'Invalid response never reports success');
    assert.equal(invalidJson.button.disabled, false, 'Interrupted request can be retried');
    const expired = await run([{ ok: false, status: 419 }]);
    assert.equal(expired.redirects.length, 0, 'Expired session never reports success');
    const subscription = await run([{ok:true}], true, true);
    assert.deepEqual(subscription.enrollments, ['one-card-token'], 'Subscription submits its token once through its own form');
    assert.equal(subscription.requests.length, 0, 'Subscription never calls the invoice charge endpoint');
    assert.equal(subscription.button.disabled, true, 'Enrollment remains disabled while the form navigates');
    const unaccepted = await run([{ok:true}], false, true);
    assert.equal(unaccepted.tokens, 0, 'Unaccepted terms prevent subscription tokenization');
    console.log('Square invoice payment, retry, expiry, and subscription checkout checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
