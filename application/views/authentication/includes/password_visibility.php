<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.sc-auth-password { position:relative; }
.sc-auth-password .form-control { padding-inline-end:48px; }
.sc-auth-password button { position:absolute; inset-inline-end:2px; top:2px; bottom:2px; width:42px; border:0; border-radius:5px; background:transparent; color:#526865; display:flex; align-items:center; justify-content:center; cursor:pointer; }
.sc-auth-password button:hover { background:#edf6f2; color:#17685d; }
.sc-auth-password button:focus-visible { outline:2px solid #17685d; outline-offset:2px; }
.sc-auth-password svg { width:20px; height:20px; }
</style>
<script>
(function () {
    'use strict';
    var eye = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var hiddenEye = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10 5a12 12 0 0 1 12 7 17 17 0 0 1-3 4M6 6a17 17 0 0 0-4 6s3.5 7 10 7a13 13 0 0 0 5-1M10 10a3 3 0 0 0 4 4"/></svg>';
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.closest('.sc-auth-password,.sc-password-field,.input-group')) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'sc-auth-password';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        if (!input.autocomplete) input.autocomplete = <?= json_encode($password_autocomplete ?? 'current-password'); ?>;
        var button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-controls', input.id);
        function mask(visible) {
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', String(visible));
            button.innerHTML = visible ? hiddenEye : eye;
        }
        mask(false);
        button.addEventListener('click', function () { mask(input.type === 'password'); });
        wrapper.appendChild(button);
        if (input.form) ['submit', 'reset'].forEach(function (event) {
            input.form.addEventListener(event, function () { mask(false); });
        });
    });
})();
</script>
