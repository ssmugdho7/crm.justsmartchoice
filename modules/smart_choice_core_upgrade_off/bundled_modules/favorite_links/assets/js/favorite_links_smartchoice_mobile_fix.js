(function(){
    "use strict";

    function findSearchInput(){
        var selectors = ['#search_input', '#top_search input[type="search"]', 'input[type="search"]', 'input[name="search"]', '.dataTables_filter input'];
        for (var i = 0; i < selectors.length; i++) {
            var el = document.querySelector(selectors[i]);
            if (el) { return el; }
        }
        return null;
    }

    function getFavoriteAction(){
        var icon = document.querySelector('.favorite-links-toggle i.far.fa-star.fa-lg, .favorite-links-nav i.far.fa-star.fa-lg');
        if (!icon) { return null; }
        return icon.closest('a.favorite-links-toggle, a[data-toggle="dropdown"], button') || null;
    }

    function fixStar(){
        var action = getFavoriteAction();
        var input = findSearchInput();

        if (!action || !input) { return; }

        var row = input.closest('.favorite-links-search-row');
        if (!row) {
            row = document.createElement('div');
            row.className = 'favorite-links-search-row';
            input.parentNode.insertBefore(row, input);
            row.appendChild(input);
        }

        var nav = action.closest('.favorite-links-nav, ul.nav');
        if (nav && nav.parentNode !== row) {
            row.appendChild(nav);
        } else if (!nav && action.parentNode !== row) {
            row.appendChild(action);
        }

        action.classList.add('favorite-links-star-action');
        action.style.position = 'relative';
        action.style.inset = 'auto';
        action.style.float = 'none';
        action.style.transform = 'none';
    }

    document.addEventListener('DOMContentLoaded', function(){
        fixStar();
        setTimeout(fixStar, 500);
        setTimeout(fixStar, 1500);
    });

    if (window.jQuery) {
        jQuery(document).ajaxComplete(function(){ setTimeout(fixStar, 250); });
    }
})();
