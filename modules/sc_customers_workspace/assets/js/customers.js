(function ($) {
    'use strict';
    if (!$ || !document.body.classList.contains('sccw-customers')) return;

    var cfg = window.scCustomersWorkspace || {};
    var content = document.querySelector('#wrapper > .content');
    if (!content) return;

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function addHeader() {
        if (content.querySelector('.sccw-header')) return;
        var contactsActive = cfg.method === 'all_contacts';
        var header = document.createElement('section');
        header.className = 'sccw-header';
        header.innerHTML = '<div class="sccw-eyebrow">Smart Choice Contractors USA</div>' +
            '<h1>' + escapeHtml(cfg.title) + '</h1><p>' + escapeHtml(cfg.subtitle) + '</p>' +
            '<nav class="sccw-nav" aria-label="' + escapeHtml(cfg.title) + '">' +
            '<a class="' + (contactsActive ? '' : 'active') + '" href="' + escapeHtml(cfg.customersUrl) + '">' + escapeHtml(cfg.customers) + '</a>' +
            '<a class="' + (contactsActive ? 'active' : '') + '" href="' + escapeHtml(cfg.contactsUrl) + '">' + escapeHtml(cfg.contacts) + '</a></nav>';
        content.insertBefore(header, content.firstChild);
    }

    function decorateDirectory() {
        var summary = content.querySelector('.tw-grid.tw-grid-cols-2');
        if (summary) {
            summary.classList.add('sccw-kpis');
            Array.prototype.forEach.call(summary.children, function (card, index) {
                card.classList.add('sccw-kpi', 'sccw-kpi-' + index);
            });
        }
        $('.table-clients, .table-all-contacts').closest('.panel_s').addClass('sccw-table-card');
        $('.table-clients, .table-all-contacts').addClass('sccw-data-table');
    }

    function decorateProfile() {
        $('.customer-tabs').addClass('sccw-profile-tabs');
        $('.customer_profile .md\\:tw-flex').first().addClass('sccw-profile-layout');
        $('.customer-profile-group-heading').first().addClass('sccw-section-title');
        $('.client-form').addClass('sccw-client-form');
        $('.customer-profile-tabs').addClass('sccw-form-tabs');
    }

    function decorateDetails() {
        content.querySelectorAll('a[href^="mailto:"],a[href^="tel:"]').forEach(function (link) {
            if (!link.textContent.trim() || link.nextElementSibling?.classList.contains('sccw-copy')) return;
            var copy = document.createElement('button'); copy.type = 'button'; copy.className = 'sccw-copy';
            copy.textContent = cfg.copy || 'Copy'; copy.setAttribute('aria-label', (cfg.copy || 'Copy') + ': ' + link.textContent.trim());
            copy.addEventListener('click', async function () {
                try {
                    if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                    await navigator.clipboard.writeText(link.textContent.trim());
                    notify(cfg.copied || 'Copied to clipboard.', false);
                } catch (_) { notify(cfg.copyFailed || 'Unable to copy. Select and copy the value manually.', true); }
            });
            link.after(copy);
        });
        content.querySelectorAll('.customer-tabs > ul:not([data-sccw-grouped])').forEach(function (list) {
            list.dataset.sccwGrouped = 'true';
            var categories = [['profile','contacts'], ['invoices','payments','estimates','proposals','statement','credit_notes'], ['projects','tasks','subscriptions','attachments','vault'], ['reminders','notes','appointments']];
            var items = Array.from(list.children);
            categories.forEach(function (keys, index) {
                var members = items.filter(function (li) { var a = li.querySelector('a[data-group]'); return a && keys.includes(a.dataset.group); });
                if (!members.length) return;
                var heading = document.createElement('li'); heading.className = 'sccw-tab-section';
                heading.textContent = (cfg.sections || [])[index] || ['Account & identity','Financials','Delivery & operations','Communications & logs'][index];
                list.appendChild(heading); members.forEach(function (li) { list.appendChild(li); });
            });
            // Extension tabs remain accessible after native categories.
            items.filter(function (li) { var a = li.querySelector('a[data-group]'); return !a || !categories.some(function (keys) { return keys.includes(a.dataset.group); }); }).forEach(function (li) { list.appendChild(li); });
        });
        content.querySelectorAll('.statement-account-summary').forEach(function (summary) {
            summary.classList.add('sccw-statement-summary');
        });
        content.querySelectorAll('.table a[href]').forEach(function (link) {
            var path;
            try { path = new URL(link.href).pathname; } catch (_) { return; }
            var row = link.closest('tr');
            if (!row || !content.querySelector('.statement-account-summary')) return;
            if (/\/payments\/payment\//.test(path)) row.classList.add('sccw-ledger-payment');
            if (/\/credit_notes\/list_credit_notes\//.test(path)) row.classList.add('sccw-ledger-credit');
        });
    }

    function notify(message, error) {
        var stack = document.getElementById('sccw-toast-stack');
        if (!stack) { stack = document.createElement('div'); stack.id = 'sccw-toast-stack'; stack.setAttribute('role','status'); stack.setAttribute('aria-live','polite'); document.body.appendChild(stack); }
        var toast = document.createElement('div'); toast.className = 'sccw-toast' + (error ? ' is-error' : ''); toast.textContent = message; stack.appendChild(toast);
        window.setTimeout(function () { toast.classList.add('is-dismissing'); window.setTimeout(function () { toast.remove(); }, 250); }, 4000);
    }

    function decorateModal() {
        $('#contact .modal-dialog').addClass('sccw-contact-dialog');
        $('#contact .modal-content').addClass('sccw-contact-modal');
    }

    addHeader();
    decorateDirectory();
    decorateProfile();
    decorateModal();
    decorateDetails();
    $(document).on('shown.bs.modal', '#contact', decorateModal);
    // Contacts and statements are rendered asynchronously; enhance the actual returned nodes.
    new MutationObserver(function () { decorateDirectory(); decorateDetails(); }).observe(content, {childList:true, subtree:true});
})(window.jQuery);
