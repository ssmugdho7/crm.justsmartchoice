var pickers = $('.colorpicker-component');

function resetDefaultTable(table) {
    $.ajax({
        url: `${admin_url}customtables/resetDefaultTable/${table}`,
        type: 'post',
    })
    .done(function (response) {
        if (response) {
            alert_float('success', 'Reset Default Table Successfully');
        }
        setTimeout(function () {
            window.location.reload();
        }, 1000);
    });
}

function saveTableStyle() {
    var data = [];
    $.each(pickers, function () {
        var color = $(this).find('input').val();
        if (color != '') {
            var _data = {};
            _data.id = $(this).find('input').data('id');
            _data.color = color;
            data.push(_data);
        }
    });
    $.post(admin_url + 'customtables/saveTableStyle', {
        data: JSON.stringify(data),
        table_custom_css: $('#custom_css_for_table').val(),
    }).done(function () {
        window.location.reload();
    });
}

// Set custom css preview
function setCustomPreview() {
    $("#table_custom_css").remove();
    var data = $('#custom_css_for_table').val();
    var appendData = '<style id="table_custom_css">' + data + '</style>';
    $(appendData).appendTo("head");
}