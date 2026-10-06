(function () {
    'use strict';
    if (window.scJitsiMeetingInitialized) { return; }
    window.scJitsiMeetingInitialized = true;
    function text(id, value) { var element = document.getElementById(id); if (element) element.textContent = value; }
    function init() {
        var share = document.getElementById('shareMeetingModal');
        if (share) {
            share.querySelectorAll('[data-gm-copy]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var field = document.getElementById(button.getAttribute('data-gm-copy'));
                    if (!field) { return; }
                    function fallback() {
                        var focused = document.activeElement;
                        field.focus(); field.select();
                        try { if (!document.execCommand('copy')) throw new Error('copy'); text('gm-share-feedback', 'Copied.'); }
                        catch (error) { text('gm-share-feedback', 'Select the text and copy it with your keyboard.'); }
                        if (focused && focused.focus) focused.focus();
                    }
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(field.value).then(function () { text('gm-share-feedback', 'Copied.'); }, fallback);
                    } else { fallback(); }
                });
            });
            var device = document.getElementById('gm-device-share');
            if (device && navigator.share) {
                device.hidden = false;
                device.addEventListener('click', function () {
                    navigator.share({text: document.getElementById('gm-share-invitation').value, url: document.getElementById('gm-share-link').value})
                        .catch(function (error) { if (error.name !== 'AbortError') text('gm-share-feedback', 'Device sharing is unavailable. Use a copy button instead.'); });
                });
            }
        }
        var configElement = document.getElementById('gm-room-config');
        if (!configElement) { return; }
        var config;
        try { config = JSON.parse(configElement.textContent); } catch (error) { text('gm-room-feedback', 'Room configuration is unavailable. Open the meeting in a new window.'); return; }
        var api, localId = null, moderator = false, joined = false, ending = false, chain = Promise.resolve(), csrf = config.csrf;
        function post(url, payload) {
            var task = chain.then(function () {
                var data = Object.assign({}, payload); data[csrf.token_name] = csrf.hash;
                return new Promise(function (resolve, reject) {
                    window.jQuery.ajax({url: url, type: 'POST', data: data, dataType: 'json'})
                        .done(function (response) { if (response.csrf) csrf = response.csrf; if (response.success) resolve(response); else reject(new Error(response.message || 'Unable to save.')); })
                        .fail(function (xhr) { if (xhr.responseJSON && xhr.responseJSON.csrf) csrf = xhr.responseJSON.csrf; reject(new Error('Request failed.')); });
                });
            });
            chain = task.catch(function () {}); return task;
        }
        function lifecycle(event) {
            return post(config.lifecycleUrl, {meeting_id: config.meetingId, event: event});
        }
        var field = document.getElementById('gm-room-note'), saveButton = document.getElementById('gm-save-note');
        var savedText = '', noteTimer, saving = false, saveAgain = false;
        function saveNote() {
            if (!field || !config.noteUrl) return;
            if (saving) { saveAgain = true; return; }
            var value = field.value.trim();
            if (!value) { text('gm-note-feedback', 'Enter a note to save.'); return; }
            if (value === savedText) { text('gm-note-feedback', 'Saved to timeline.'); return; }
            saving = true; if (saveButton) saveButton.disabled = true; text('gm-note-feedback', 'Saving…');
            post(config.noteUrl, {comment: value, note_key: config.noteKey}).then(function (response) {
                savedText = value;
                var timeline = document.getElementById('gm-note-timeline'), id = parseInt(response.comment_id, 10);
                if (timeline && id > 0) {
                    var article = timeline.querySelector('[data-comment-id="' + id + '"]');
                    if (!article) { article = document.createElement('article'); article.setAttribute('data-comment-id', id); var label = document.createElement('strong'); label.textContent = 'Your meeting note'; article.appendChild(label); article.appendChild(document.createElement('p')); timeline.prepend(article); }
                    article.querySelector('p').textContent = response.comment;
                }
                text('gm-note-feedback', 'Saved to timeline.');
            }).catch(function () { text('gm-note-feedback', 'Not saved. Your text is still here. Try Save to timeline.'); })
                .then(function () { saving = false; if (saveButton) saveButton.disabled = false; if (saveAgain) { saveAgain = false; saveNote(); } });
        }
        window.addEventListener('beforeunload', function (event) {
            if (field && field.value.trim() !== savedText && field.value.trim() !== '') { event.preventDefault(); event.returnValue = ''; }
        });
        if (field) field.addEventListener('input', function () { clearTimeout(noteTimer); noteTimer = setTimeout(saveNote, 1200); });
        if (saveButton) saveButton.addEventListener('click', function () { clearTimeout(noteTimer); saveNote(); });
        var finish = document.getElementById('gm-finish-meeting');
        if (finish && config.isHost) finish.addEventListener('click', function () {
            if (ending || !window.confirm('Finish this meeting in the CRM and leave the video room?')) return;
            ending = true; finish.disabled = true;
            lifecycle('finish').then(function () { joined = false; if (api) api.executeCommand('hangup'); text('gm-room-feedback', 'Meeting completed.'); })
                .catch(function () { ending = false; finish.disabled = false; text('gm-room-feedback', 'Meeting could not be completed. Please try again.'); });
        });
        document.querySelectorAll('[data-gm-fullscreen]').forEach(function (button) {
            var viewport = document.getElementById('jitsi-meet-viewport');
            if (!viewport.requestFullscreen) { button.hidden = true; return; }
            button.addEventListener('click', function () {
                var request = document.fullscreenElement ? document.exitFullscreen() : viewport.requestFullscreen();
                if (request && request.catch) request.catch(function () { text('gm-room-feedback', 'Full screen is unavailable. Use Open in new window.'); });
            });
        });
        function initializeVideo() {
            var viewport = document.getElementById('jitsi-meet-viewport');
            try {
                viewport.textContent = '';
                api = new window.JitsiMeetExternalAPI(config.domain, {roomName: config.roomName, parentNode: viewport, width: '100%', height: '100%', userInfo: config.userInfo,
                    configOverwrite: {prejoinConfig: {enabled: true}, startWithAudioMuted: true, startWithVideoMuted: true}});
                var iframe = api.getIFrame();
                iframe.setAttribute('allow', 'camera; microphone; fullscreen; display-capture; autoplay; clipboard-write');
                iframe.setAttribute('title', 'Video meeting');
                function applyPin() { if (config.isHost && config.pin && moderator) api.executeCommand('password', config.pin); }
                api.addEventListener('videoConferenceJoined', function (event) {
                    localId = event.id; joined = true; applyPin(); text('gm-room-feedback', 'Connected to your meeting.');
                    api.executeCommand('subject', config.subject);
                    lifecycle('joined').catch(function () { text('gm-room-feedback', 'Video connected, but CRM attendance could not be saved.'); });
                });
                api.addEventListener('participantRoleChanged', function (event) {
                    if (localId && event.id !== localId) return;
                    moderator = event.role === 'moderator'; applyPin();
                });
                api.addEventListener('passwordRequired', function () { if (config.pin) api.executeCommand('password', config.pin); });
                api.addEventListener('videoConferenceLeft', function () {
                    if (!joined) return; joined = false;
                    lifecycle('left').then(function () { text('gm-room-feedback', config.isHost ? 'Meeting completed.' : 'You left the meeting.'); })
                        .catch(function () { text('gm-room-feedback', 'You left the video room. CRM status could not be updated.'); });
                });
            } catch (error) { text('gm-room-feedback', 'Video could not be loaded. Open the meeting in a new window.'); }
        }
        if (typeof window.JitsiMeetExternalAPI === 'function') initializeVideo();
        else {
            var script = document.createElement('script'); script.src = 'https://' + config.domain + '/external_api.js'; script.async = true;
            script.onload = initializeVideo; script.onerror = function () { text('gm-room-feedback', 'The Jitsi service could not be reached. Open the meeting in a new window.'); };
            document.head.appendChild(script);
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());
