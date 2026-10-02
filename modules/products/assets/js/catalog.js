(function ($) {
    'use strict';
    var root = document.getElementById('sc-catalog');
    if (!root) return;
    var search = root.querySelector('#sc-catalog-search');
    var sort = root.querySelector('#sc-catalog-sort');
    var grid = root.querySelector('#filter_html');
    function update() {
        var terms = search.value.toLocaleLowerCase().trim().split(/\s+/).filter(Boolean);
        var cards = Array.from(grid.querySelectorAll('.product-row'));
        var visible = 0;
        cards.forEach(function (card) {
            card.hidden = !terms.every(function (term) { return card.dataset.catalogSearch.toLocaleLowerCase().indexOf(term) !== -1; });
            if (!card.hidden) visible++;
        });
        cards.sort(function (a, b) {
            if (sort.value === 'default') return Number(a.dataset.catalogOrder) - Number(b.dataset.catalogOrder);
            var result = a.dataset.catalogName.localeCompare(b.dataset.catalogName);
            return sort.value === 'name-desc' ? -result : result;
        }).forEach(function (card) { grid.appendChild(card); });
        root.querySelector('.no_product').classList.toggle('hidden', visible > 0 || grid.getAttribute('aria-busy') === 'true');
        root.querySelector('#sc-catalog-count').textContent = visible + (visible === 1 ? ' service' : ' services') + (terms.length ? ' found' : ' available');
    }
    function countCart(items) {
        var count = (Array.isArray(items) ? items : []).reduce(function (sum, item) { return sum + Math.max(0, Number(item.quantity) || 0); }, 0);
        var badge = root.querySelector('#sc-cart-count');
        badge.textContent = count;
        badge.setAttribute('aria-label', count + ' items in cart');
        badge.hidden = false;
    }
    $(document).on('scCatalogRendered', update);
    $(document).on('scCartUpdated', function (_, items) { countCart(items); });
    search.addEventListener('input', update);
    // Listen through the portal's existing selectpicker enhancement as well as native selects.
    $(sort).on('change', update);
    root.querySelector('#sc-catalog-reset').addEventListener('click', function () {
        search.value = ''; sort.value = 'default';
        if ($(sort).data('selectpicker')) $(sort).selectpicker('refresh');
        $('#product_categories').selectpicker('val', []).trigger('change');
        search.focus();
    });
    // Capture failures too: broken URLs become readable covers, never repeated fallback image requests.
    grid.addEventListener('error', function (event) {
        var image = event.target;
        if (!image.classList || !image.classList.contains('sc-product-image')) return;
        image.hidden = true;
        image.closest('.sc-product-slider').querySelector('.sc-product-cover').hidden = false;
    }, true);
    grid.addEventListener('load', function (event) {
        var image = event.target;
        if (!image.classList || !image.classList.contains('sc-product-image')) return;
        image.hidden = false;
        image.closest('.sc-product-slider').querySelector('.sc-product-cover').hidden = true;
    }, true);
    $(function () {
        // Move only links already granted/rendered by the shared customer navigation.
        var utility = root.querySelector('.sc-shop-utilities');
        document.querySelectorAll('#content .customers-top-submenu-files > a, #content .customers-top-submenu-calendar > a').forEach(function (link) {
            utility.appendChild(link);
        });
        if (grid.querySelector('.product-row')) update();
        $.getJSON(site_url + 'products/client/get_my_cart').done(countCart);
    });
})(jQuery);
