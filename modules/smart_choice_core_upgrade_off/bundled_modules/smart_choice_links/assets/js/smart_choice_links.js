(function($) {
    "use strict";




    function initSmartChoiceLinksDragSort() {
        var tbody = document.getElementById('smart-choice-links-sortable');
        if (!tbody) {
            return;
        }

        var dragged = null;

        $(tbody).find('tr').each(function() {
            this.setAttribute('draggable', 'true');
        });

        tbody.addEventListener('dragstart', function(e) {
            var row = e.target.closest('tr[data-id]');
            if (!row) {
                return;
            }
            dragged = row;
            row.classList.add('scl-dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        tbody.addEventListener('dragend', function() {
            if (dragged) {
                dragged.classList.remove('scl-dragging');
            }
            dragged = null;
        });

        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
            var target = e.target.closest('tr[data-id]');
            if (!target || target === dragged) {
                return;
            }

            var rect = target.getBoundingClientRect();
            var next = (e.clientY - rect.top) / (rect.bottom - rect.top) > .5;
            tbody.insertBefore(dragged, next ? target.nextSibling : target);
        });

        tbody.addEventListener('drop', function(e) {
            e.preventDefault();
            saveSmartChoiceLinksOrder();
        });
    }

    function saveSmartChoiceLinksOrder() {
        var order = [];
        $('#smart-choice-links-sortable tr[data-id]').each(function(index) {
            order.push($(this).data('id'));
            $(this).find('.scl-position-value').text(index + 1);
        });

        if (!order.length) {
            return;
        }

        if (typeof requestPost === 'function') {
            requestPost(window.smartChoiceLinksReorderEndpoint || 'smart_choice_links/reorder', { order: order }).done(function() {
                if (typeof alert_float === 'function') {
                    alert_float('success', 'Link order updated.');
                }
            });
        } else {
            $.post(admin_url + 'smart_choice_links/reorder', { order: order });
        }
    }

    function cleanSmartChoiceLinkUrl(value) {
        value = (value || '').toString().trim().replace(/\s+/g, '');

        if (/^\/?admin\/modules\/?$/i.test(value)) {
            return 'admin/modules';
        }

        value = value.replace(/h{2,}ttps:\/\//ig, 'https://');
        value = value.replace(/h{2,}ttp:\/\//ig, 'http://');
        value = value.replace(/ttps:\/\//ig, 'https://').replace(/ttp:\/\//ig, 'http://');
        value = value.replace(/^(https?):\/{1,3}/i, '$1://');

        var protocols = value.match(/https?:\/\//ig);
        if (protocols && protocols.length > 1) {
            var lastHttps = Math.max(value.lastIndexOf('https://'), value.lastIndexOf('http://'));
            if (lastHttps > -1) {
                value = value.substring(lastHttps);
            }
        }

        if (/^https?:\/\/crm\.justsmartchoice\.com\/admin\/(moduless|modeless)\/?$/i.test(value) ||
            /^\/?admin\/(moduless|modeless)\/?$/i.test(value) ||
            /^\/?adminmodules\/?$/i.test(value) ||
            /^\/\/adminmodules\/?$/i.test(value)) {
            return 'admin/modules';
        }

        if (/^https?:\/\/crm\.justsmartchoice\.com\/admin\//i.test(value)) {
            value = value.replace(/^https?:\/\/crm\.justsmartchoice\.com\/admin\//i, 'admin/');
        }

        if (/^\/?modules\/?$/i.test(value) || /^https?:\/\/crm\.justsmartchoice\.com\/modules\/?$/i.test(value)) {
            return 'admin/modules';
        }

        return value;
    }

    function ensureSmartChoiceModalWrapper() {
        var existing = $('#smart-choice-links-modal-wrapper');

        if (existing.length > 1) {
            existing.not(':last').remove();
            existing = $('#smart-choice-links-modal-wrapper');
        }

        if (!existing.length) {
            $('body').append('<div class="modal fade" id="smart-choice-links-modal-wrapper" tabindex="-1" role="dialog" aria-hidden="true"></div>');
            existing = $('#smart-choice-links-modal-wrapper');
        }

        if (!existing.parent().is('body')) {
            existing.appendTo('body');
        }

        return existing;
    }

    function cleanupSmartChoiceModal() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }

    function mountSmartChoiceLinks() {
        var source = $('#smart-choice-links-source');
        if (!source.length) {
            ensureSmartChoiceModalWrapper();
            return;
        }

        var html = source.html();
        source.remove();

        if ($('.smart-choice-links-nav').length) {
            ensureSmartChoiceModalWrapper();
            return;
        }

        var topSearch = $('#top_search');
        if (topSearch.length) {
            topSearch.before(html);
            ensureSmartChoiceModalWrapper();
            return;
        }

        var headerNav = $('#header ul.nav.navbar-nav').first();
        if (headerNav.length) {
            headerNav.append(html);
        }

        ensureSmartChoiceModalWrapper();
    }

    window.smartChoiceLinksModal = function(id) {
        var wrapper = ensureSmartChoiceModalWrapper();
        wrapper.html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading Smart Choice Link form...</div></div></div>');
        wrapper.modal({ show: true, backdrop: true, keyboard: true });

        requestGet('smart_choice_links/modal/' + id).done(function(response) {
            wrapper.html(response);
            wrapper.modal('show');

            if (typeof init_selectpicker === 'function') {
                init_selectpicker();
            }
            if (typeof init_datepicker === 'function') {
                init_datepicker();
            }
            if (typeof appValidateForm === 'function') {
                appValidateForm(wrapper.find('form'), { title: 'required', url: 'required' });
            }


            wrapper.find('[data-scl-url-input]').off('blur.scl').on('blur.scl', function() {
                $(this).val(cleanSmartChoiceLinkUrl($(this).val()));
            });

            wrapper.find('form').off('submit.scl').on('submit.scl', function() {
                var urlField = $(this).find('[data-scl-url-input]');
                urlField.val(cleanSmartChoiceLinkUrl(urlField.val()));
            });
        }).fail(function(data) {
            wrapper.modal('hide');
            cleanupSmartChoiceModal();
            var message = 'Unable to open Smart Choice Link form.';
            if (data && data.responseText) {
                message = data.responseText.replace(/<[^>]*>?/gm, '').substring(0, 250);
            }
            if (typeof alert_float === 'function') {
                alert_float('danger', message);
            } else {
                alert(message);
            }
        });
    };

    $(document).on('hidden.bs.modal', '#smart-choice-links-modal-wrapper', function() {
        $(this).html('');
        cleanupSmartChoiceModal();
    });

    $(document).ready(function() {
        mountSmartChoiceLinks();
        initSmartChoiceLinksDragSort();
    });

})(jQuery);
