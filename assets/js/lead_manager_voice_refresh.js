(function ($) {
    'use strict';

    if (!$ || typeof window.refreshTwilioToken !== 'function') {
        return;
    }

    let pendingRefresh;
    let sessionExpired = false;
    let lastFailure;

    function reportFailure(message, type) {
        updateCallStatus(message);
        if (lastFailure !== message) {
            lastFailure = message;
            alert_float(type, message);
        }
    }

    // Both tokenWillExpire and the SDK's expired-token error reach this function.
    // Reuse the legacy device and identity, with one request per refresh.
    window.refreshTwilioToken = function () {
        if (sessionExpired || pendingRefresh) {
            return pendingRefresh;
        }

        pendingRefresh = $.ajax({
            url: admin_url + 'lead_manager/generateClientToken',
            type: 'POST',
            dataType: 'json',
            data: {forPage: window.location.pathname},
            timeout: 15000
        }).then(function (data) {
            if (!data || typeof data.token !== 'string' || !data.token) {
                throw new Error('Missing browser calling token');
            }
            if (device) {
                device.updateToken(data.token);
            }
            twilioToken = data.token;
            lastFailure = undefined;
            updateCallStatus('Twilio token refreshed');
        }).catch(function (error) {
            if (error.status === 401) {
                sessionExpired = true;
                reportFailure('Your CRM session expired. Sign in again to resume browser calling.', 'warning');
            } else if (error.status === 419) {
                sessionExpired = true;
                reportFailure('Refresh this CRM page to resume browser calling.', 'warning');
            } else {
                reportFailure('Browser calling could not renew its connection to Twilio.', 'danger');
            }
        }).always(function () {
            pendingRefresh = undefined;
        });

        return pendingRefresh;
    };
})(window.jQuery);
