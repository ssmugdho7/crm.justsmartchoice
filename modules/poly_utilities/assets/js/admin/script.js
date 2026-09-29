(function ($) {
    "use strict";
    // ==================== Widget areas ==================== //
    if (poly_utilities_settings.widgets) {
        PolyOperationFunctions.Widgets('admin');
    }
    // ==================== END Widget areas ==================== //

    // ==================== Custom operation actions ==================== //
    //Sticky topbar
    if (poly_utilities_settings.is_sticky == 'true') {
        var offsetTopBarMenu = $('#header');
        $(window).scroll(function () {
            var scroll = $(window).scrollTop();
            if (scroll > poly_utilities_settings.topbar_height) {
                offsetTopBarMenu.addClass('sticky');
                $('body').css({
                    'padding-top': poly_utilities_settings.topbar_height + 'px'
                });
            } else {
                offsetTopBarMenu.removeClass('sticky')
                $('body').css({
                    'padding-top': 'revert'
                });
            }
        });
    }

    // Input file name;
    PolyHandleEvents.InputFriendly(['.poly-resource-name']);

    //Operation buttons
    if (poly_utilities_settings.is_operation_functions === 'true') {
        const polyElementsToProcessHandleCopyContent = [
            ['table a[href^="mailto:"]', 'mailto:'],
            ['table a[href^="tel:"]', 'tel:']
        ];
        const polyElementsToProcessHandleContacts = [['table a[href*="clients/client/"][href*="?contactid"]']];

        PolyHandleRequest.ajaxComplete(processElementsCopyContent, polyElementsToProcessHandleCopyContent);

        PolyHandleRequest.ajaxComplete(processElementsContacts, polyElementsToProcessHandleContacts);

        $('.panel-table-full').on('click change keydown', 'table, button, select, input[type!=checkbox]', function (event) {
            PolyHandleRequest.ajaxComplete(processElementsCopyContent, polyElementsToProcessHandleCopyContent);
            PolyHandleRequest.ajaxComplete(processElementsContacts, polyElementsToProcessHandleContacts);
        });

        function processElementsContacts(selector) {
            var elements = $(selector).map(function () {
                var item = $(this).attr('href').trim();
                if (item) {
                    return $(this);
                }
            }).get();

            $.each(elements, function (index, currentElement) {
                var outerElementHTML = currentElement.prop('outerHTML');
                if (outerElementHTML.indexOf('poly-access') === -1) {
                    currentElement.addClass('poly-access');
                    outerElementHTML = currentElement.prop('outerHTML');
                    var href = currentElement.prop('href').trim();
                    var clientId = PolyCommon.getLastNumberFromUrl(href);
                    if (clientId != null) {
                        var logAsClient = `${admin_url}clients/login_as_client/${clientId}`;
                        var newHtml = `<span class="poly-block-client">${outerElementHTML}&nbsp;${PolyOperationFunctions.AHref(logAsClient, 'clients fa-solid fa-user-lock fa-fw', 'Login')}</span>`;
                        currentElement.replaceWith(newHtml);
                    }
                }
            });
        }

        function processElementsCopyContent(selector, attributePrefix) {

            var elements = $(selector).map(function () {
                var item = $(this).attr('href').replace(attributePrefix, '').trim();
                if (item) {
                    return $(this);
                }
            }).get();

            $.each(elements, function (index, currentElement) {
                var outerElementHTML = currentElement.prop('outerHTML');
                if (outerElementHTML.indexOf('poly-access') === -1) {
                    currentElement.addClass('poly-access');
                    outerElementHTML = currentElement.prop('outerHTML');
                    var newHtml = `<span class="poly-block">${outerElementHTML}&nbsp;${PolyOperationFunctions.Copy(currentElement.prop('innerHTML'), 'fa-regular fa-copy')}</span>`;
                    currentElement.replaceWith(newHtml);
                }
            });

            $('.poly-copy').each(function () {
                var clipboard = new ClipboardJS(this);
                $(this).off('click').on('click', function (event) {
                    event.preventDefault();
                });

                clipboard.on('success', function (e) {
                    PolyMessage.displayNotification('success', 'Copied!');
                    e.clearSelection();
                }).on('error', function (e) {
                    PolyMessage.displayNotification('error', 'Error');
                });
            });
        }
    }
    // ==================== Custom operation actions ==================== //

    // ==================== Quick Access Menu ==================== //
    function handleHotkeyPress(link) {
        window.location.href = link;
    }

    if (poly_quick_access_menu != '' || poly_quick_access_menu != undefined) {
        let alphaKeyPressed = false;
        let lastAlphaKey = '';
        $(document).keydown(function (e) {
            const key = e.key.toUpperCase();
            if ((key >= 'A' && key <= 'Z')) {
                alphaKeyPressed = true;
                lastAlphaKey = key;
            } else if (alphaKeyPressed && (e.key >= '1' && e.key <= '9')) {
                let shortcutKey = lastAlphaKey + '+' + e.key;
                let menuItem = poly_quick_access_menu.find(item => item.shortcut_key === shortcutKey);
                if (menuItem) {
                    handleHotkeyPress(menuItem.link);
                }
                alphaKeyPressed = false;
            } else {
                alphaKeyPressed = false;
                lastAlphaKey = '';
            }
        });
        $(document).keyup(function (e) {
            alphaKeyPressed = false;
            lastAlphaKey = ''
        });

    }

    //Custom quick access menu list
    let top_search = $('#top_search');
    let poly_utilities_quick_access_menu = $('#poly_utilities_quick_access_menu').html();
    $(poly_utilities_quick_access_menu).insertAfter(top_search);
    //Custom quick access menu list

    // ==================== Quick Access Menu ==================== //

    // ==================== Table of content, handle doConfirm() onload ==================== //
    setTimeout(() => {
        if (poly_utilities_settings.is_table_of_content === 'true') {
            //TODO: iframe content
            var iframe = $('#description_ifr')[0];
            if (iframe) {

                var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

                var offsetMCEStickyBar = $('.mce-toolbar-grp');
                var offsetTopIframe = $('#description_ifr').offset().top - offsetMCEStickyBar.height();

                var headingsArray = [];
                var headingsValidIndex = iframeDocument.documentElement.innerHTML.match(/<h\d[^>]*>.*?<\/h\d>/g);

                for (var i = 1; i <= 5; i++) {
                    var heading = $(iframeDocument).find('h' + i);
                    if (heading.length > 0) {
                        headingsArray = headingsArray.concat(heading.get());
                    }
                }

                var headingOfContent = headingsArray.map(function (headingElement) {
                    var heading = $(headingElement);
                    var offset = heading.offset().top + offsetTopIframe;
                    var headingTagName = heading.prop('tagName').toLowerCase();
                    return { head: headingTagName, offset: offset, title: heading.text() };
                });
                if (headingsValidIndex && headingsValidIndex.length > 0) {
                    headingsValidIndex.forEach(function (headingIndex) {
                        var heading = $(headingIndex);
                        var foundObject = headingOfContent.find(function (obj) {
                            return obj.title === heading.text();
                        });

                        if (foundObject) {
                            var offset = foundObject.offset;
                            var headingTagName = foundObject.head;
                            $('.poly-table-of-content').append(`<li class="item ${'h' + headingTagName}" data-offset="${offset}">${heading.text()}</li>`);
                        }
                    });
                } else {
                    $('.poly-table').remove();
                }
            }
        }

        //Confirm
        //Replace note confirm
        if (poly_utilities_settings.is_note_confirm_delete === 'true') {
            $('.todo-body [onclick*="delete_todo_item"], .todo-panel [onclick*="delete_todo_item"]').each(function () {
                let note_id = PolyCommon.extractNumber($(this).attr('onclick'));
                $(this).removeAttr('onclick');
                $(this).data('id', note_id);
                $(this).attr('onclick', `PolyHandleEvents.doConfirm(delete_todo_item,[$(this),${note_id}],poly_utilities_settings.popup_delete)`);
            });
        }
        //Confirm


    }, 500);
    // ==================== Table of content, handle doConfirm() onload ==================== //

    // ==================== Table of content ==================== //
    /**
     * Array of elements handling the display of the table of contents
     * @param {Array<string>} containers An array of strings
     */
    const setupStickyTable = (containers) => {
        containers.forEach(function (containerId) {
            var container = $(containerId);
            var panelBody = container.find('.panel-body');
            var fxTable = $(`<div class="poly-table"><div class="header">${poly_utilities_settings.table_of_content_header}</div><ul class="poly-table-of-content"></ul></div>`).appendTo(panelBody);

            //Scroll
            $(window).scroll(updateTableOfContentPosition);

            //Init
            updateTableOfContentPosition();

            //Function
            function updateTableOfContentPosition() {
                var scroll = $(window).scrollTop();

                var fxTableTop = Math.max(0, ($(window).height() - fxTable.outerHeight()) / 2 - scroll) || 0;

                var topbarHeight = (poly_utilities_settings.is_sticky == 'true') ? poly_utilities_settings.topbar_height : 0;
                var curTop = fxTableTop + topbarHeight;

                fxTable.css({
                    'top': `${((scroll < poly_utilities_settings.topbar_height) ? poly_utilities_settings.topbar_height : curTop)}px`
                });

                $('.poly-table-of-content').css({
                    'padding-bottom': `${((scroll < poly_utilities_settings.topbar_height) ? poly_utilities_settings.topbar_height : 0)}px`,
                });
                $('.poly-table .header').css({
                    'top': fxTable.css('top')
                });
            }
        });
    };

    //TODO: The subsequent updates bring this list into the settings section of the web application.
    if (poly_utilities_settings.is_table_of_content == 'true') {
        setupStickyTable(['#article-form', '#project_form']);
        $(document).on('click', '.poly-table-of-content .item', function () {
            var offsetTop = parseInt($(this).data('offset'));
            $('html, body').animate({
                scrollTop: offsetTop
            }, 800);
        });
    }
    // ==================== Table of content ==================== //

    // ==================== CtrlS: contract, article ==================== //
    setTimeout(function () {
        //Handle for iframe
        var iframe = document.getElementById('description_ifr');
        if (iframe) {
            iframe.contentDocument.addEventListener('keydown', function (e) {
                if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                    PolyHandleEvents.handleCtrlS(e);
                }
            });
        }

        var iframeContent = document.getElementById('content_ifr');
        if (iframeContent) {
            iframeContent.contentDocument.addEventListener('keydown', function (e) {
                if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                    PolyHandleEvents.handleCtrlS(e);
                }
            });
        }
        //Handle without iframe
        if (!iframeContent && !iframe) {
            $(document).keydown(function (e) {
                if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                    PolyHandleEvents.handleCtrlS(e);
                }
            });
        }

    }, 300);
    // ==================== END CtrlS: contract, article ==================== //

    // ==================== Operation buttons ==================== //
    //Project edit
    if (window.location.href.indexOf("admin/projects/view") > -1) {
        var project_block = $('.project-overview-left').find('.panel-body');
        if (!project_block.length) {
            return;
        }
        let _projectId = PolyCommon.extractProjectIdByUrl(window.location.href);
        let _prodjectEdit = `/admin/projects/project/${_projectId}`;
        let _editIcon = `<span class="poly-cursor"><a href="${_prodjectEdit}"><i class="fa-regular fa-pen-to-square poly-icon"></i></a></span>`;
        let _editToolsTop = `<div class="poly-absolute poly-right-0 poly-top-0">${_editIcon}</div>`;
        let _editToolsBottom = `<div class="poly-absolute poly-right-0 poly-bottom-0">${_editIcon}</div>`;
        project_block.append(`${_editToolsTop}${_editToolsBottom}`);
    }
    // ==================== Operation buttons ==================== //


    // ==================== Data Table Filters: functions ==================== //
    var containersDataTableFilters = $('table');
    if (!PolyCommon.countStringOccurrences(window.location.href, '/admin/modules')) {
        dataTableFiltersByUser();
    }
    $(document).on('init.dt', function (e, settings, json) {
        // ==================== Modules: display active, deactive and both ==================== //
        if (PolyCommon.countStringOccurrences(window.location.href, '/admin/modules')) {
            var table = $('table').DataTable();

            var dataRows = table.rows().nodes();
            var totalModules = dataRows.count();
            var totalModulesDeactive = 0;

            dataRows.each(function (data) {
                var trContent = $(data).html();
                if (PolyCommon.countStringOccurrences(trContent, 'modules/activate')) {
                    totalModulesDeactive++;
                }
            });

            let moduleActive = totalModules - totalModulesDeactive;
            let moduleDeactive = totalModulesDeactive;
            let moduleState = `<div class="poly-utilities-modules">
    <div class="poly-item-filter"><label class="poly-label"> <input type="checkbox" class="poly-checkbox-module poly-modules-activate" value="deactivate" data-check-helper="info" checked> <span>Activate (${moduleActive})</span></label></div>
    <div class="poly-item-filter"><label class="poly-label"> <input type="checkbox" class="poly-checkbox-module poly-modules-deactivate" value="activate" data-check-helper="" checked> <span>Deactivate (${moduleDeactive})</span></label></div></div>`;
            $('.app.admin.modules table').before(`<div class="poly-utilities-filter relative">${moduleState}</div>`);

            let storedProActivated = localStorage.getItem('proActivated') === 'true';
            let storedProDeactivated = localStorage.getItem('proDeactivated') === 'true';

            $('.poly-modules-activate').prop('checked', storedProActivated);
            $('.poly-modules-deactivate').prop('checked', storedProDeactivated);

            initModulesStatus(table, storedProActivated, storedProDeactivated)

            $('.poly-checkbox-module').each(function () {
                $(this).off('change').on('change', function () {
                    let proActivated = $('.poly-modules-activate').prop('checked');
                    let proDeactivated = $('.poly-modules-deactivate').prop('checked');

                    localStorage.setItem('proActivated', proActivated);
                    localStorage.setItem('proDeactivated', proDeactivated);

                    initModulesStatus(table, proActivated, proDeactivated);
                });
            });
        }
        function initModulesStatus(table, proActivated, proDeactivated) {
            let keywords = '';
            if (!proActivated && proDeactivated) {
                keywords = 'activate';
            } else if (proActivated && !proDeactivated) {
                keywords = 'deactivate';
            }

            PolyDataTable.dataByKeywords(table, [keywords]);
        }
        // ==================== END Modules: display active, deactive and both ==================== //
    });

    /**
     * 
     * @param {string} tableElement table element
     * @param {Array} dataSaved data table config filters
     * @returns The array of string settings. 
     */
    function getDataItemsFiltersByTable(tableElement, dataSaved) {
        var itemsFilters = [];
        tableElement.find('thead th').each(function (i) {
            var headerText = $(this).text().trim();
            var headerObject = {
                idx: (i + 1) + '',
                id: 'poly-col-' + i,
                active: 'true',
                text: headerText
            };
            itemsFilters.push(headerObject);
        });

        if (dataSaved) { // Sync
            itemsFilters.forEach(function (itemFilter, index) {
                if (dataSaved[index]) {
                    itemFilter.active = dataSaved[index].active || 'true';
                }
            });
        }

        return itemsFilters;
    }

    /**
     * Display the filter list for the data table.
     */

    function dataTableFiltersByUser() {
        'use strict';

        containersDataTableFilters = containersDataTableFilters ? containersDataTableFilters : $('table');
        if (containersDataTableFilters && containersDataTableFilters.length > 0) {

            let dataTab = (poly_utilities_settings.tab) ? '-' + poly_utilities_settings.tab.slug : '';
            let dataArea = poly_utilities_settings.segments[1] ? '-' + poly_utilities_settings.segments[1] : '';
            let dataModel = poly_utilities_settings.segments[2] ? '-' + poly_utilities_settings.segments[2] : '';

            if (dataArea && dataModel) {
                //Deactivate the feature in the homepage data table (widgets).
                let panelBlock = $('body');
                let polyKeyFilters = `u${poly_utilities_settings.uid}${dataArea}${dataModel}${dataTab}`;
                let dataUserFilters = PolyCommon.getValueByKey(poly_utilities_settings.data_filters, polyKeyFilters);

                containersDataTableFilters.each(function (index, container) {

                    var dataTable = $(container);

                    dataTable.on('init.dt', function (e, settings, json) {

                        var dataTableClassIndentity = `poly-table-${index}`;
                        dataTable.addClass(dataTableClassIndentity);
                        //Data table

                        var headerTextObjects = getDataItemsFiltersByTable(dataTable, dataUserFilters);

                        var dataTableHeader = "";

                        if (headerTextObjects) {
                            for (var i = 0; i < headerTextObjects.length; i++) {
                                var obj = headerTextObjects[i];
                                var isCheckedDisplay = (obj.active == 'true') ? 'checked' : '';

                                dataTable.DataTable().column(i).visible((obj.active == 'true'));

                                dataTableHeader += '<div class="poly-item-filter"> <label class="poly-label"> <input type="checkbox" class="poly-checkbox poly-checkbox-' + i + '" value="' + i + '" ' + isCheckedDisplay + ' > <span>' + obj.text + '</span> </label> </div>';
                            }
                        }
                        //Data table

                        if (panelBlock.find(`.poly-utilities-filter.poly-filter-${index}`).length === 0) {
                            dataTable.before(`<div class="poly-utilities-filter hidden-xs poly-filter-${index}">${dataTableHeader}</div>`);
                        }

                        if (panelBlock.find(`.poly-button-tools.poly-tools-${index}`).length === 0) {
                            var buttonTools = `<button class="btn btn-default btn-sm poly-button-tools poly-tools-${index}"><a class="fa-solid fa-list fa-fw"></a></button>`;
                            dataTable.parents('.dataTables_wrapper').find('.dt-buttons').append(buttonTools);
                        }

                        $('.poly-button-tools').on('click', function () {
                            var swalContent = `<div class="poly-utilities-filter-modal">${dataTableHeader}</div>`;
                            let modals_table_filter = poly_utilities_settings.modals.find(item => 'table_filter' in item)['table_filter'];

                            Swal.fire({
                                customClass: {
                                    container: 'poly-utilities-table-filter-modal-content',
                                },
                                title: `<strong>${(modals_table_filter && modals_table_filter.header) ?? ''}</strong>`,
                                html: swalContent,
                                showConfirmButton: false,
                                showCloseButton: true,
                                allowOutsideClick: false,
                                focusConfirm: false,
                                didOpen: function () {
                                    for (var i = 0; i < headerTextObjects.length; i++) {
                                        var obj = headerTextObjects[i];
                                        var isChecked = (obj.active == 'true') ? 'checked' : '';
                                        $('.poly-utilities-filter-modal .poly-checkbox-' + i).prop('checked', isChecked);
                                    }
                                }
                            });

                        });

                        $('body').off('change', '.poly-checkbox').on('change', '.poly-checkbox', function () {
                            let colIndex = parseInt($(this).val());
                            var isChecked = $(this).prop('checked');
                            $('.poly-checkbox-' + colIndex).prop('checked', isChecked);
                            headerTextObjects[colIndex].active = $(this).prop('checked') ? 'true' : 'false';

                            dataTable.DataTable().column(colIndex).visible($(this).prop('checked'));

                            let objectDataTableFilters = {
                                key: polyKeyFilters,
                                value: headerTextObjects,
                            }
                            $.post(admin_url + 'poly_utilities/save_data_filters', { data: objectDataTableFilters }).done(function (response) {
                                try {
                                    if (response) {
                                        const parsedResponse = JSON.parse(response);
                                        if (parsedResponse.code === 200) { }
                                    }
                                } catch (error) {
                                    console.error("Error response:", error);
                                }
                            });
                        });
                    });

                });
            }
        }
    }
    // ==================== END Data Table Filters: functions ==================== //

    // ==================== Menu: add menu item, URL to Quick Access Menu ==================== //

    if (poly_utilities_settings.is_quick_access_menu_icons === 'true') {/* Display the quick add menu icon to be added to the Quick Access Menu if this feature is enabled in settings. */
        initPinQuickAccessMenu('#side-menu');
        initPinQuickAccessMenu('#setup-menu');
    }

    /**
    * Set up the button to quickly add a menu link to the Quick Access Menu
    * @param {string} menuElement class name or ID of the ul element
    */
    function initPinQuickAccessMenu(menuElement) {
        let arrMenu = $(menuElement).children('li');
        arrMenu.each(function (index, item) {
            if (index == 0) return;
            let arrMenuSubs = $(item).find('li');
            if (arrMenuSubs.length > 0) {
                arrMenuSubs.each(function (indexSub, itemSub) {
                    if ($(itemSub).css('display') !== 'none') {
                        let title = itemSub.innerText.trim();
                        let link = $(itemSub).find('a').attr('href');
                        let data_attr = `data-title="${title}" data-link="${link}"`;
                        itemSub.innerHTML = itemSub.innerHTML + `<span ${data_attr} class="poly-quick-pin poly-quick-access-menu-pin-sub"><i class="fa-solid fa-thumbtack pull-right ico-pin"></i></span>`;
                    }
                });
            } else {
                let title = item.innerText.trim();
                let link = $(item).find('a').attr('href');
                let data_attr = `data-title="${title}" data-link="${link}"`;
                item.innerHTML = item.innerHTML + `<span ${data_attr} class="poly-quick-pin poly-quick-access-menu-pin"><i class="fa-solid fa-thumbtack pull-right ico-pin"></i></span>`;
            }

            $('.poly-quick-pin').off('click').on('click', async function () {
                "use strict";
                let title = $(this).data('title');
                let link = $(this).data('link');

                let modals_create_quick_access_menu = poly_utilities_settings.modals.find(item => 'quick_access_menu' in item)['quick_access_menu'];

                const { value: formValues } = await Swal.fire({
                    title: modals_create_quick_access_menu.header,
                    customClass: {
                        popup: 'poly-utilities-access-menu-pin-modal-content'
                    },
                    html: `
                  <div class="row">
                    <div clas="form-group">
                        <div class="col-md-12">
                            <label></label><input id="poly-title" class="form-control" value="${title}">
                        </div>
                    </div>
                    <div clas="form-group">
                        <div class="form-group col-md-12">
                            <label></label><input id="poly-link" class="form-control" value="${link}">
                        </div>
                    </div>
                    <div class="form-group">
                        ${PolyCommon.select('Rels', 'poly_utilities_pin_rel', poly_utilities_settings.rels, 'nofollow', 'form-group col-md-6')}
                        ${PolyCommon.select('Target', 'poly_utilities_pin_target', poly_utilities_settings.targets, '_self', 'form-group col-md-6')}
                    </div>
                    <div class="form-group">
                        ${PolyCommon.select('Hotkeys', 'poly_utilities_pin_hotkey_alphabet', poly_utilities_settings.alphabet, 'N', 'col-md-6')}
                        ${PolyCommon.select('', 'poly_utilities_pin_hotkey_number', poly_utilities_settings.numbers, '8', 'col-md-6')}
                    </div>
                </div>`,
                    focusConfirm: false,
                    allowOutsideClick: false,
                    showCancelButton: true,
                    showCloseButton: true,
                    confirmButtonColor: modals_create_quick_access_menu.confirm_color,
                    cancelButtonColor: modals_create_quick_access_menu.cancel_color,
                    confirmButtonText: modals_create_quick_access_menu.confirm,
                    cancelButtonText: modals_create_quick_access_menu.cancel,
                    preConfirm: () => {
                        const titleValue = $("#poly-title").val();
                        const linkValue = $("#poly-link").val();
                        if (!titleValue || !linkValue) {
                            Swal.showValidationMessage(`${modals_create_quick_access_menu.validation_message}`);
                            return false;
                        }
                        return {
                            icon: ' fa-solid fa-link',
                            title: titleValue,
                            link: linkValue,
                            shortcut_key: `${$('#poly_utilities_pin_hotkey_alphabet').val()}+${$('#poly_utilities_pin_hotkey_number').val()}`,
                            target: $('#poly_utilities_pin_target').val(),
                            rel: $('#poly_utilities_pin_rel').val(),
                        };
                    }
                });
                if (formValues) {
                    $.post(admin_url + 'poly_utilities/save_quick_access', formValues).done(function (response) {
                        let dataResponse = JSON.parse(response);
                        dataResponse.title = "Alert";
                        PolyPopup.popup(dataResponse, true);
                    });
                }
            });
        });
    }
    // ==================== END Menu: add menu item, URL to Quick Access Menu ==================== //

    // ==================== Menu: toogle sidebar ==================== //
    window.addEventListener('DOMContentLoaded', function () {
        if (!$('body').hasClass('page-small')) {
            if (poly_utilities_settings.is_toggle_sidebar_menu === 'true' && !is_mobile()) {
                if (localStorage.getItem('polySidebarCollapsed')) {
                    setTimeout(() => {
                        polySidebarCollapsed(localStorage.getItem('polySidebarCollapsed') === 'poly-show-sidebar');
                    }, 100);
                }

                $('.pinned_project a').each(function () {
                    let _html = $(this).html();
                    if (!$(this).hasClass("wrapper-menu-text")) {
                        $(this).html('<i class="fa fa-file"></i><span class="wrapper-menu-text">' + _html + '</span>');
                    }
                });
                function polySidebarCollapsed(isCollapsed) {
                    if (isCollapsed === true) {
                        $('body').removeClass('hide-sidebar').addClass('show-sidebar');
                        $("body").removeClass("poly-hide-sidebar").addClass("poly-show-sidebar");
                        let menu = $('.poly-show-sidebar #menu');
                        let logo = menu.find('#logo img.img-responsive');
                        if (logo.length !== 0) {
                            logo.addClass('fadeIn');
                        }

                        let logoCollapsed = menu.find('img.favicon-collapsed');
                        if (logoCollapsed.length !== 0) {
                            logoCollapsed.removeClass('fadeIn');
                        }
                    } else {
                        $('body').removeClass('show-sidebar').addClass('hide-sidebar');
                        $("body").removeClass("poly-show-sidebar").addClass("poly-hide-sidebar");
                        if (poly_utilities_settings.favicon_path) {
                            let menu = $('.poly-hide-sidebar #menu');
                            let logo = menu.find('#logo img.img-responsive');
                            if (logo.length !== 0) {
                                logo.removeClass('fadeIn');
                            }
                            let logoCollapsed = menu.find('.favicon-collapsed');
                            if (logoCollapsed.length === 0) {
                                logo.before(`<img class="favicon-collapsed" src="${poly_utilities_settings.favicon_path}"/>`);
                                logoCollapsed = menu.find('.favicon-collapsed');
                            }
                            logoCollapsed.addClass('fadeIn');
                        }
                    }
                    localStorage.setItem('polySidebarCollapsed', (isCollapsed === true) ? 'poly-show-sidebar' : 'poly-hide-sidebar');
                }

                // Overide: Handle minimalize sidebar menu
                $(".hide-menu").removeClass('hide-menu').addClass('poly-toggle-menu');
                $(".poly-toggle-menu").on('click', function (e) {
                    e.preventDefault();
                    if ($("body").hasClass("poly-hide-sidebar")) {
                        polySidebarCollapsed(true);
                    } else {
                        polySidebarCollapsed(false);
                    }
                    if (setup_menu.hasClass("display-block")) {
                        $(".close-customizer").click();
                    }
                });
            }
        }
    });
    // ==================== END Menu: toogle sidebar ==================== //

    // ==================== Menu: Search menu item ==================== //
    if (poly_utilities_settings.is_search_menu === 'true') {
        $('.menu-item-dashboard').before(`<li><input id="poly-sidebar-search" placeholder="Search menu..." class="poly-sidebar-search tw-mt-1" type="text"/></li>`);
        $('#poly-sidebar-search').on('input', function () {
            var searchText = $(this).val().toLowerCase();
            $('#menu li').each(function () {
                var $currentItem = $(this);
                var shouldDisplay = true;

                var currentItemText = $currentItem.html().toLowerCase();
                if (currentItemText.indexOf(searchText) === -1 && currentItemText.indexOf('img-responsive') === -1 && currentItemText.indexOf('dashboard') === -1 && currentItemText.indexOf('poly-sidebar-search') === -1) {
                    shouldDisplay = false;
                }
                if (shouldDisplay) {
                    $currentItem.show();
                } else {
                    $currentItem.hide();
                }
            });
        });
    }
    // ==================== END Menu: Search menu item ==================== //

})(jQuery);