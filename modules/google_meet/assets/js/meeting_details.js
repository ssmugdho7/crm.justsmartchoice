(function (document, window) {
    'use strict';
    function init() {
        var nav = document.querySelector('.gm-detail-breadcrumb');
        var header = document.getElementById('header');
        if (!nav || !header) return;
        // Keep navigation accessible where the compact header has invisible overflow.
        // Raise only the meeting breadcrumb below it; keep the sticky header on top during scroll.
        function update() {
            var belowHeader = window.innerWidth <= 767 && nav.getBoundingClientRect().top >= header.getBoundingClientRect().bottom;
            var layer = belowHeader ? String((parseInt(window.getComputedStyle(header).zIndex, 10) || 1000) + 1) : 'auto';
            if (nav.style.zIndex !== layer) nav.style.zIndex = layer;
        }
        update();
        var pending = false;
        function schedule() {
            if (pending) return;
            pending = true;
            window.requestAnimationFrame(function () { pending = false; update(); });
        }
        window.addEventListener('resize', schedule);
        window.addEventListener('scroll', schedule, { passive: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})(document, window);
