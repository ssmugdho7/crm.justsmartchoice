(function ($) {
    "use strict";

    $(document).on('click', '.item-roles-label', function (e) {
        let $target = $(e.target);
        let _input = $target.find('input');
        if (_input.length != 0) {
            let _tmpId = PolyCommon.generateUniqueID();
            _input.attr('id', _tmpId);
            $target.attr('for', _tmpId);
        }
    });

    $(document).on('click', '.poly-widgets-submit', function (e) {
        update_widgets_poly_utitlities(e);
    });

    $(document).on('change', '.poly-widget-list.active .item-roles-property', function (e) {
        e.preventDefault();
        let $target = $(e.target);
        $target.prop('checked', $target.prop('checked'));
        update_widgets_poly_utitlities(e);
    });

    $(document).on('click', '.poly-widget-list.active .widget-delete', function (e) {
        e.preventDefault();

        //TODO: confirm
        let $target = $(e.target);
        let _parent = $target.closest('li');
        _parent.remove();

        update_widgets_poly_utitlities(e);
    });

    $(document).on('click', '.poly-widgets-area .block .widget', function (e) {
        e.preventDefault();
        let $target = $(e.target);
        let _parent = $target.closest('li');
        let _id = _parent.data('block-id');
        if (_id !== undefined) {
            let $icon = _parent.find('.toggle-widgets i');
            let toggle_content = _parent.find('.widget-block[block-target="' + _id + '"]');
            if (toggle_content) {
                toggle_content.slideToggle();
                if ($icon.hasClass('fa-caret-up')) {
                    $icon.removeClass('fa-caret-up').addClass('fa-caret-down');
                } else {
                    $icon.removeClass('fa-caret-down').addClass('fa-caret-up');
                }
            }
        }
    });

    $(document).on('click', '.poly-widget-list.active .widget-close', function (e) {
        toggle_widget_poly_utitlities(e);
        update_widgets_poly_utitlities(e);
    });

    $(document).on('click', '.poly-widget-list.active .widget', function (e) {
        toggle_widget_poly_utitlities(e);
    });

    function toggle_widget_poly_utitlities(e) {
        e.preventDefault();
        let $target = $(e.target);
        let _parent = $target.closest('li');
        let _id = _parent.data('id');
        let $icon = _parent.find('.toggle-widgets i');
        let toggle_content = _parent.find('.widget-item-block[widget-target="' + _id + '"]');
        if (toggle_content) {
            toggle_content.slideToggle();
            if ($icon.hasClass('fa-caret-up')) {
                $icon.removeClass('fa-caret-up').addClass('fa-caret-down');
            } else {
                $icon.removeClass('fa-caret-down').addClass('fa-caret-up');
            }
        }
    }

    function update_widgets_poly_utitlities(e = null) {
        if (e != null) {
            e.preventDefault();
        }
        let widget_areas = $('.poly-widgets-area').get();

        var widgets_objects_by_current_block = [];

        widget_areas.forEach(widget_area => {
            let _area_block = $(widget_area).find('.block');
            let widget_area_id = _area_block.attr('id');
            let _is_default = _area_block.attr('default');
            let _widget_by_area = _area_block.find('.poly-widget-list.active li').get();
            var current_area_objects = [];
            _widget_by_area.forEach(element => {
                let jqElement = $(element);
                let _id_item = jqElement.data('id');
                let _type = jqElement.data('type');
                let input_elements = jqElement.find('.item-property').get();
                let input_roles_elements = jqElement.find('.item-roles-property').get();
                var _fields = [];
                var _roles = [];
                var _obj = {};
                _obj.id = _id_item;
                _obj.type = _type;
                _obj.name = jqElement.data('name');
                input_elements.forEach(_input => {
                    let input_object = $(_input);
                    let input_type = input_object.attr('type');
                    let input_value = '';
                    switch (input_type) {
                        case 'text': {
                            input_object.val(PolyCommon.PurseContent(input_object.val()));
                            input_value = input_object.val();
                            break;
                        }
                        case "checkbox": {
                            input_value = input_object.prop('checked');
                            break;
                        }
                        case 'textarea': {
                            if (_type === 'text') {
                                input_object.val(PolyCommon.PurseContent(input_object.val()));
                            }
                            if (_type === 'html') {
                                input_object.val(PolyCommon.PurseScripts(input_object.val()));
                            }
                            input_value = input_object.val();
                            break;
                        }
                        default: {
                            input_value = input_object.val();
                        }
                    }

                    _fields.push({
                        name: input_object.attr('field'),
                        type: input_type,
                        label: input_object.attr('label'),
                        value: input_value
                    });
                });
                _obj.fields = _fields;
                //Roles
                input_roles_elements.forEach(_input => {
                    let input_object = $(_input);
                    let input_type = input_object.attr('type');
                    let input_value = '';
                    input_value = input_object.prop('checked');
                    _roles.push({
                        name: input_object.attr('field'),
                        type: input_type,
                        label: input_object.attr('label'),
                        value: input_value
                    });
                });
                _obj.roles = _roles;
                current_area_objects.push(_obj);
            });
            widgets_objects_by_current_block.push({
                id: widget_area_id,
                default: _is_default,
                widgets: current_area_objects,
            });
        });
        $.post(admin_url + 'poly_utilities/update_widget', {
            data: widgets_objects_by_current_block,
        }).done(function (response) {
            let object_rest = JSON.parse(response);
            PolyMessage.displayNotification(object_rest.status, object_rest.message);
        });
    }

    //Drag & drop sortable elements
    $("#poly-widget-list, #poly-widget-list-active").sortable({
        connectWith: ".poly-widget-list",
        placeholder: "ui-state-highlight",
        start: function (event, ui) {
            var targetList = ui.item.parent();
            if (targetList.attr("id") === "poly-widget-list") {
                ui.item.data("fixed", true);
            }
        },
        update: function (event, ui) {
            var targetList = ui.item.parent();
            if (targetList.attr("id") === "poly-widget-list") {
                $(this).sortable("cancel");
            }
        },
        stop: function (event, ui) {
            var targetList = ui.item.parent();
            if (targetList.attr("id") === "poly-widget-list") {
                $(this).sortable("cancel");
            } else {
                if (ui.item.data("fixed")) {
                    let _id = `w${PolyCommon.generateUniqueID()}`;
                    ui.item.attr('data-id', _id);
                    ui.item.find('.widget-item-block').attr('widget-target', _id);
                    var clonedItem = ui.item.clone().removeClass("ui-state-highlight");
                    $("#left-column ul").append(clonedItem);
                    clonedItem.html(clonedItem.html());
                    ui.item.data("fixed", false);
                }
            }
            update_widgets_poly_utitlities();
        }
    }).disableSelection();
})(jQuery);