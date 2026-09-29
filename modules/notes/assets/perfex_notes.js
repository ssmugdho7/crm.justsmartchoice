function perfex_note_get_tab_is_note()
{
    var param_name = 'tab';
    var regex = new RegExp('[?&]' + param_name + '(=([^&#]*)|&|#|$)');
    var results = regex.exec(window.location.href);

    if (!results) {
        return null;
    }

    if (!results[2]) {
        return '';
    }

    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function perfex_note_set_note_tab()
{
    if (perfex_note_get_tab_is_note() === 'note') {
        $('a[href="#tab_notes"]').tab('show');
    }
}


function notes_share_one(noteId)
{
    requestGetJSON(admin_url + 'notes/share_link/' + noteId).done(function(response) {
        if (!response || !response.success || !response.url) {
            alert_float('danger', response && response.message ? response.message : 'Unable to create share link.');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(response.url).then(function() {
                alert_float('success', response.message);
            });
            return;
        }

        var textarea = document.createElement('textarea');
        textarea.value = response.url;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert_float('success', response.message);
    }).fail(function() {
        alert_float('danger', 'Unable to create share link.');
    });
}
