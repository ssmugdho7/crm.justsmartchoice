"use strict";
var diagramUrl='';
function loadGridView() {
    var formData = {
        search: $("input#search").val(),
        start: 0,
        length: _lnth,
        draw: 1
    }
    gridViewDataCall(formData, function (resposne) {
        $('div#grid-tab').html(resposne);  
    })
}
function gridViewDataCall(formData, successFn, errorFn) {
    $.ajax({
        url:  admin_url + 'dmn/grid/'+(formData.start+1),
        method: 'POST',
        data: formData,
        async: false,
        error: function (res, st, err) {
            console.log("error API", err)
        },
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (response) {
            if ($.isFunction(successFn)) {
                successFn.call(this, response);
            }
        }
    });
    setTimeout(__renderGridViewdmn, 900)
}
function __renderGridViewdmn() {
    $('div[id^="map_"]').each(function(index) {    
        setTimeout('', 200)
        var mId= $(this).attr('id');
        var filename = $('textarea#m_'+mId).val();
        $('#ifrm_'+mId).attr('src', site_url+'modules/dmn/views/iframe.php?filename='+filename);
    });
}
$("button.dmn-btn").on('click', function (e) {
    if($('#title').val() == '' || $('#dmn_group_id').val() == '' || $('#description').val() == ''){
        validate_dmn_form();
    }else{
        $('#dmn-form').submit();
    }
})