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

    function decorateModal() {
        $('#contact .modal-dialog').addClass('sccw-contact-dialog');
        $('#contact .modal-content').addClass('sccw-contact-modal');
    }

    addHeader();
    decorateDirectory();
    decorateProfile();
    decorateModal();
    $(document).on('shown.bs.modal', '#contact', decorateModal);
})(window.jQuery);
