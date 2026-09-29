(function () {
    'use strict';

    var boxId = 'sc-appointly-daily-popup';
    var activeStatuses = ['pending', 'in-progress'];
    var pollMs = 20000;
    var manuallyClosedUntil = 0;

    function isActive(item) {
        return item && activeStatuses.indexOf(String(item.status || '').toLowerCase()) !== -1;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (char) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'})[char];
        });
    }

    function makeSound() {
        if (window.SmartChoiceTodaysAppointmentsSound === false) {
            return;
        }
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.value = 0.06;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            setTimeout(function () { osc.stop(); ctx.close(); }, 180);
        } catch (e) {}
    }

    function removePopup() {
        var existing = document.getElementById(boxId);
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }
    }

    function buildList(items) {
        var html = '<ul>';
        items.slice(0, 8).forEach(function (item) {
            html += '<li data-appointment-id="' + escapeHtml(item.id) + '"><a href="' + escapeHtml(item.url) + '">' + escapeHtml(item.time) + ' — ' + escapeHtml(item.subject) + '</a></li>';
        });
        html += '</ul>';
        return html;
    }

    function renderPopup(items, playSound) {
        items = (items || []).filter(isActive);

        if (!items.length) {
            removePopup();
            return;
        }

        if (Date.now() < manuallyClosedUntil) {
            return;
        }

        var existing = document.getElementById(boxId);
        if (!existing) {
            existing = document.createElement('div');
            existing.id = boxId;
            existing.className = 'sc-appointly-toast';
            document.body.appendChild(existing);
            playSound = true;
        }

        existing.innerHTML = '' +
            '<button type="button" class="sc-appointly-close" aria-label="Close">×</button>' +
            '<div class="sc-appointly-title">Today\'s Active Appointments</div>' +
            '<div class="sc-appointly-subtitle">This alert stays only while the appointment is pending or in progress.</div>' +
            buildList(items);

        existing.querySelector('.sc-appointly-close').addEventListener('click', function () {
            manuallyClosedUntil = Date.now() + (5 * 60 * 1000);
            removePopup();
        });

        if (playSound) {
            makeSound();
        }
    }

    function refreshFromServer() {
        var endpoint = window.SmartChoiceTodaysAppointmentsEndpoint;
        if (!endpoint || typeof fetch !== 'function') {
            return;
        }

        fetch(endpoint, {
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (!payload || payload.success !== true) {
                    return;
                }
                renderPopup(payload.items || [], false);
            })
            .catch(function () {});
    }

    function boot() {
        renderPopup(window.SmartChoiceTodaysAppointments || [], true);
        if (window.SmartChoiceTodaysAppointmentsEndpoint) {
            setInterval(refreshFromServer, pollMs);
            setTimeout(refreshFromServer, 3000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
