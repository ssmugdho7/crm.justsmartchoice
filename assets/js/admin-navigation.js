(function () {
    'use strict';
    function init() {
        var header = document.getElementById('header');
        if (!header) return;
        function update() {
            document.documentElement.style.setProperty('--sc-admin-header-height', header.getBoundingClientRect().height + 'px');
        }
        update();
        if (window.ResizeObserver) new ResizeObserver(update).observe(header);
        else window.addEventListener('resize', update);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
