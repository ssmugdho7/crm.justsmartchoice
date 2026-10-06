<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.contact-profile-change-password-section .sc-password-field { position: relative; }
.contact-profile-change-password-section .sc-password-field .form-control { padding-inline-end: 48px; }
.contact-profile-change-password-section .sc-password-toggle {
    position: absolute; inset-inline-end: 1px; top: 1px; bottom: 1px;
    width: 42px; border: 0; border-radius: 6px; background: transparent;
    color: #526865; display: flex; align-items: center; justify-content: center;
}
.contact-profile-change-password-section .sc-password-toggle:hover { color: #17685d; background: #edf6f2; }
.contact-profile-change-password-section .sc-password-toggle:focus-visible { outline: 2px solid #17685d; outline-offset: 2px; }
</style>
<script>
(function () {
    'use strict';
    var section = document.querySelector('.contact-profile-change-password-section');
    if (!section) return;
    var buttons = section.querySelectorAll('.sc-password-toggle');
    function setVisibility(button, visible) {
        var input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? button.dataset.hideLabel : button.dataset.showLabel);
        button.querySelector('i').className = visible ? 'fa fa-eye-slash' : 'fa fa-eye';
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            setVisibility(button, button.getAttribute('aria-pressed') !== 'true');
        });
    });
    var form = section.querySelector('form');
    if (form) {
        ['submit', 'reset'].forEach(function (eventName) {
            form.addEventListener(eventName, function () {
                buttons.forEach(function (button) { setVisibility(button, false); });
            });
        });
    }
})();
</script>
