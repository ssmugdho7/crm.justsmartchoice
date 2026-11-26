'use strict';

const assert = require('node:assert/strict');
const fs = require('fs');
const {JSDOM} = require('jsdom');

const source = fs.readFileSync(__dirname + '/../assets/js/lead_manager_voice_refresh.js', 'utf8');

function fixture() {
    const dom = new JSDOM('', {
        url: 'https://crm.example/admin/appointly/appointments/view',
        runScripts: 'outside-only'
    });
    const {window} = dom;
    const $ = require('jquery')(window);
    const requests = [];
    const updates = [];
    const notices = [];
    window.jQuery = $;
    window.admin_url = '/admin/';
    window.updateCallStatus = () => {};
    window.alert_float = (type, message) => notices.push([type, message]);
    window.refreshTwilioToken = () => { throw new Error('Legacy refresh handler ran'); };
    window.device = {updateToken: token => updates.push(token)};
    window.twilioToken = null;
    $.ajax = settings => {
        const response = $.Deferred();
        requests.push({settings, response});
        return response.promise();
    };
    window.eval(source);
    return {dom, window, requests, updates, notices};
}

async function verifyRefresh() {
    const {dom, window, requests, updates, notices} = fixture();
    const refresh = window.refreshTwilioToken();
    assert.equal(window.refreshTwilioToken(), refresh, 'concurrent refreshes share the request');
    assert.equal(requests.length, 1);
    assert.equal(requests[0].settings.dataType, 'json');
    assert.equal(requests[0].settings.type, 'POST');
    requests[0].response.resolve({token: 'synthetic-voice-token'});
    await refresh;
    assert.deepEqual(updates, ['synthetic-voice-token']);
    assert.equal(window.twilioToken, 'synthetic-voice-token');
    assert.equal(notices.length, 0);

    const expired = window.refreshTwilioToken();
    requests[1].response.reject({status: 401});
    await expired;
    window.refreshTwilioToken();
    window.refreshTwilioToken();
    assert.equal(requests.length, 2, 'expired tabs stop polling until reload');
    assert.equal(notices.length, 1);
    assert.match(notices[0][1], /session expired.*Sign in again/);
    dom.window.close();

    const malformed = fixture();
    const first = malformed.window.refreshTwilioToken();
    malformed.requests[0].response.reject({status: 200, responseText: '<html>Sign in</html>'});
    await first;
    const second = malformed.window.refreshTwilioToken();
    malformed.requests[1].response.resolve({token: null});
    await second;
    assert.equal(malformed.updates.length, 0);
    assert.equal(malformed.notices.length, 1, 'identical errors show just one notice');
    assert.doesNotMatch(malformed.notices[0][1], /<html>|SyntaxError/);
    malformed.dom.window.close();
}

verifyRefresh().then(() => {
    console.log('PASS Lead Manager refresh coalesces requests, updates the voice device, and handles session expiry and malformed responses.');
}).catch(error => {
    console.error(error);
    process.exitCode = 1;
});
