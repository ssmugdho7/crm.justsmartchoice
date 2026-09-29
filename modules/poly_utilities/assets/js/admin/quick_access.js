(function ($) {
    "use strict";
    PolyOperationFunctions.DisableReload();
    
    //Validation
    var vRules = {};
    vRules = {
        poly_utilities_quick_access_title: 'required',
        poly_utilities_quick_access_shortcut_key: 'required',
        poly_utilities_quick_access_link: 'required',
    }

    appValidateForm($('.quick_access-form'), vRules);

    var iconClasses = PolyCommon.fontAwesome();

    //Bind Shortcut key
    polyBindHotKey(['poly_utilities_quick_access_shortcut_key_pre', 'poly_utilities_quick_access_shortcut_key_last'], 'poly_utilities_quick_access_shortcut_key');

    $('.poly-hotkey').on('change', function () {
        "use strict";
        let shortcutKey = 'poly_utilities_quick_access_shortcut_key';
        let shortcutKeyPre = 'poly_utilities_quick_access_shortcut_key_pre';
        let shortcutKeyLast = 'poly_utilities_quick_access_shortcut_key_last';

        let dataId = $(this).data('id');
        if (dataId) {
            shortcutKey = `${shortcutKey}_${dataId}`;
            shortcutKeyPre = `${shortcutKeyPre}_${dataId}`;
            shortcutKeyLast = `${shortcutKeyLast}_${dataId}`;
        }

        polyBindHotKey([shortcutKeyPre, shortcutKeyLast], shortcutKey);
    });
    //Bind Shortcut key

    //Submit Add Quick Access Menu Item
    $('.btn-submit-poly-utilities').on('click', function () {
        "use strict";
        var form = $('.quick_access-form');
        if (form.valid()) {
            $.post(admin_url + 'poly_utilities/save_quick_access', {
                icon: $('#poly_utilities_quick_access_icon').val(),
                title: $('#poly_utilities_quick_access_title').val(),
                link: $('#poly_utilities_quick_access_link').val(),
                shortcut_key: $('#poly_utilities_quick_access_shortcut_key').val(),
                target: $('#poly_utilities_quick_access_link_target').val(),
                rel: $('#poly_utilities_quick_access_link_rel').val(),
            }).done(function (response) {
                let dataResponse = JSON.parse(response);
                dataResponse.title = "Alert";
                PolyPopup.popup(dataResponse, true);
            });
        }
    });

    //Save manage
    $('.btn-submit-manage-poly-utilities').on('click', function () {
        "use strict";
        initListQuickAccessInformation();

        $.post(admin_url + 'poly_utilities/update_quick_access_menu', {
            data: poly_quick_access_menu,
        }).done(function (response) {
            PolyMessage.displayNotification('success', 'Successfully');
        });
    });

    //Sortable
    if (document.getElementById('myListItem')) {
        Sortable.create(myListItem, {
            handle: '.poly-handle',
            multiDrag: true,
            selectedClass: 'selected',
            filter: '.layer-locked',
            fallbackTolerance: 3,
            animation: 150,
            onEnd: function (evt) {
                initListQuickAccessInformation();
            }
        });
    }
    //Sortable

    //Remove
    $('.poly-quick-access-menu-delete').on('click', function () {
        "use strict";
        var data = {};
        data.link = $(this).data('link');

        Swal.fire({
            title: poly_utilities_settings.popup_delete.header,
            text: poly_utilities_settings.popup_delete.message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: poly_utilities_settings.popup_delete.confirm_color,
            cancelButtonColor: poly_utilities_settings.popup_delete.cancel_color,
            cancelButtonText: poly_utilities_settings.popup_delete.cancel,
            confirmButtonText: poly_utilities_settings.popup_delete.confirm,
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(admin_url + 'poly_utilities/delete_quick_access', data).done(function (response) {
                    window.location.reload();
                });

            }
        });
    });

    //Toggle
    $('.toggle-menu-options').on('click', function (e) {
        "use strict";
        e.preventDefault();
        let menu_id = $(this).parents('li').data('id');
        if ($(this).hasClass('main-item-options')) {
            $(this).parents('li').find('.main-item-options[data-menu-options="' + menu_id + '"]')
                .slideToggle();
        } else {
            $(this).parents('li').find('.sub-item-options[data-menu-options="' + menu_id + '"]')
                .slideToggle();
        }
    });

    // ======== Functions ======== //
    function polyBindHotKey(idsArray, idBindValue) {
        let shortcutKey = '';
        idsArray.forEach(function (id) {
            shortcutKey += $(`#${id}`).val() + '+';
        });
        shortcutKey = shortcutKey.slice(0, -1);
        $('#' + idBindValue).val(shortcutKey);
    }

    function addToListAccessMenu(obj) {
        poly_quick_access_menu.push({
            icon: obj.icon,
            index: obj.index,
            title: obj.title,
            link: obj.link,
            target: obj.target,
            rel: obj.rel,
            shortcut_key: obj.shortcut_key
        });
    }

    function initListQuickAccessInformation() {
        poly_quick_access_menu = [];
        var items = myListItem.querySelectorAll('[data-icon][data-index][data-title][data-link][data-shortcut_key][data-target][data-rel]');
        items.forEach(function (item) {
            let dataId = $(item).data('index');
            item.dataset.shortcut_key = $(`#poly_utilities_quick_access_shortcut_key_${dataId}`).val();
            item.dataset.title = $(`#poly_utilities_quick_access_title_${dataId}`).val();
            item.dataset.link = $(`#poly_utilities_quick_access_link_${dataId}`).val();
            item.dataset.target = $(`#poly_utilities_quick_access_link_target_${dataId}`).val();
            item.dataset.rel = $(`#poly_utilities_quick_access_link_rel_${dataId}`).val();
            item.dataset.icon = $(`#mn_icon-${dataId}`).val();
            addToListAccessMenu(item.dataset);
        });
    }
    // ======== END Functions ======== //

    // ======== Icons ======== //
    var popoverContent = '<div class="row"><div class="text-center"><i class="poly-utilities-preview-icon fa-solid fa-shield-halved fa-fw fa-2x"></i></div><input type="text" data-id="_POLYDATAID_" class="poly-utilities-icon form-control" placeholder="font-awesome" aria-label="font-awesome" aria-describedby="basic-addon1"></div><div class="poly-utilties-list-icons">';
    iconClasses.forEach(function (iconClass) {
        popoverContent += '<div class="col-xs-3 text-center icon"><div class="wrap"><i data-id="_POLYDATAID_" class="fa-2x ' + iconClass + '"></i></div></div>';
    });
    popoverContent += '</div>';

    $(document).on('change keydown', '.poly-utilities-icon', function (event) {
        "use strict";
        var searchValue = $(this).val().toLowerCase();
        var filteredIcons = PolyCommon.fontAwesomeBySearchKeywords(searchValue, iconClasses);

        var filteredContent = '';
        filteredIcons.forEach(function (iconClass) {
            filteredContent += '<div class="col-xs-3 text-center icon"><div class="wrap"><i data-id="_POLYDATAID_" class="fa-2x ' + iconClass + '"></i></div></div>';
        });
        if (filteredContent !== '') {
            let dataId = $(this).attr('data-id');
            filteredContent = filteredContent.replace(/_POLYDATAID_/g, dataId);
            $('.poly-utilties-list-icons').html(filteredContent);
        }
    });

    $(document).on('click', function (e) {
        "use strict";
        if (!$(e.target).closest('.poly-utilities-icon-selected').length && !$(e.target).closest('.poly-popover-icons').length) {
            $('.poly-utilities-icon-selected').popover('hide');
        }
    });

    $('.poly-utilities-icon-selected').popover({
        html: true,
        content: function () {
            let popoverContentBindID = popoverContent.replace(/_POLYDATAID_/g, $(this).attr('data-id'));
            return `<div class="poly-popover-icons" data-id="${$(this).attr('data-id')}">${popoverContentBindID}</div>`;
        },
        placement: 'bottom',
        trigger: 'manual'
    }).on('click', function () {
        $(this).popover('toggle');
    });

    $(document).on('click', '.poly-popover-icons i', function () {
        "use strict";
        var selectedIconClass = $(this).attr('class');
        var dataId = $(this).attr('data-id');
        let iconSelected = selectedIconClass.replace(/\bfa-2x\b/g, '');

        //Create new menu
        $('.poly-utilities-preview-icon').attr('class', `poly-utilities-preview-icon ${selectedIconClass}`);
        $(`.poly-utilities-input-icon`).val(iconSelected);
        $(`.poly-utilities-preview-icon-select`).attr('class', `poly-utilities-preview-icon-select ${iconSelected}`);

        //Edit list menu
        $(`.poly-utilities-preview-icon-${dataId} .poly-utilities-preview-icon-selected`).attr('class', `poly-utilities-preview-icon-selected ${iconSelected}`);
        $(`.poly-utilities-icons.input_${dataId}`).val(iconSelected);
    });
    // ======== END Icons ======== //

})(jQuery);