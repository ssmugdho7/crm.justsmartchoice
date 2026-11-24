(function () {
    'use strict';

    // Lead Manager is a legacy jQuery module. Some browser extensions define a
    // read-only `$`, which prevents jQuery from booting and leaves its inline
    // SMS handlers undefined. Keep this fallback dependency-free.
    if (typeof window.leadManagerMessage === 'function') return;

    function modal() {
        return document.getElementById('lead-manager-sms-modal');
    }

    function showModal() {
        var node = modal();
        if (!node) return;
        node.style.display = 'block';
        node.classList.add('show');
        node.setAttribute('aria-hidden', 'false');
    }

    function hideModal() {
        var node = modal();
        if (!node) return;
        node.style.display = 'none';
        node.classList.remove('show');
        node.setAttribute('aria-hidden', 'true');
    }

    function sendForm(form) {
        if (!form || form.dataset.submitting === 'true') return false;
        var message = form.querySelector('[name="message"]');
        if (!message || !message.value.trim()) {
            message && message.focus();
            return false;
        }
        form.dataset.submitting = 'true';
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.text().then(function (text) {
                var data;
                try { data = JSON.parse(text); } catch (e) { data = { error: text }; }
                if (!response.ok || data.error || !data.success) throw new Error(data.error || 'Message could not be sent.');
                hideModal();
                window.alert('Message Sent!');
            });
        }).catch(function (error) {
            window.alert(error.message || 'Message could not be sent.');
        }).finally(function () {
            delete form.dataset.submitting;
        });
        return false;
    }

    window.leadManagerMessage = function (leadId) {
        fetch((window.admin_url || '/admin/') + 'lead_manager/send_sms_modal?id=' + encodeURIComponent(leadId), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) throw new Error('Unable to open SMS composer.');
            return response.text();
        }).then(function (html) {
            var node = modal();
            if (!node) throw new Error('SMS composer is unavailable.');
            var content = node.querySelector('.modal-content') || node;
            content.innerHTML = html;
            showModal();
            var form = content.querySelector('form');
            var send = content.querySelector('.send_sms_btn_lm');
            if (send) send.addEventListener('click', function () { sendForm(form); });
            if (form) form.addEventListener('submit', function (event) { event.preventDefault(); sendForm(form); });
        }).catch(function (error) {
            window.alert(error.message || 'Unable to open SMS composer.');
        });
    };

    window.validate_sms_form = function (leadId) {
        var form = document.querySelector('#sms-form-' + leadId) || document.querySelector('#lead-manager-sms-modal form');
        return sendForm(form);
    };

    document.addEventListener('click', function (event) {
        var close = event.target.closest && event.target.closest('#lead-manager-sms-modal [data-dismiss="modal"], #lead-manager-sms-modal .close');
        if (close) { event.preventDefault(); hideModal(); }
    });
}());
