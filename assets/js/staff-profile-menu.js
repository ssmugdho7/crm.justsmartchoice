(function () {
    'use strict';
    var profile = document.querySelector('#menu .sidebar-user-profile');
    if (!profile) return;
    var button = profile.querySelector('.sc-staff-language-toggle');
    if (!button) return;
    var item = button.parentElement;
    function expand(open) {
        item.classList.toggle('sc-language-open', open);
        button.setAttribute('aria-expanded', String(open));
    }
    button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation(); // A submenu toggle must not close Bootstrap's parent dropdown.
        expand(button.getAttribute('aria-expanded') !== 'true');
    });
    item.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
            event.preventDefault(); event.stopPropagation(); expand(false); button.focus();
        }
    });
    if (window.jQuery) window.jQuery(profile).on('hidden.bs.dropdown', function () { expand(false); });
})();
