(function () {
    'use strict';
    var root = document.querySelector('.sc-help-library');
    if (!root) return;
    var search = root.querySelector('#help-library-search');
    if (!search) return;
    var filter = root.querySelector('#help-library-category');
    var clear = root.querySelector('#help-search-clear');
    var cards = Array.from(root.querySelectorAll('[data-guide]'));
    var sections = Array.from(root.querySelectorAll('.sc-help-category'));
    var featured = root.querySelector('.sc-help-featured');
    function normalize(value) {
        return value.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }
    function update() {
        var terms = normalize(search.value).split(/\s+/).filter(Boolean);
        var category = filter.value;
        var count = 0;
        cards.forEach(function (card) {
            var text = normalize(card.dataset.search);
            var match = (category === 'all' || category === card.dataset.category) && terms.every(function (term) { return text.indexOf(term) !== -1; });
            card.hidden = !match;
            if (match) count++;
        });
        sections.forEach(function (section) {
            section.hidden = !Array.from(section.querySelectorAll('[data-guide]')).some(function (card) { return !card.hidden; });
        });
        if (featured) featured.hidden = terms.length > 0 || category !== 'all';
        clear.hidden = search.value.length === 0;
        root.querySelector('#help-no-results').hidden = count !== 0;
        root.querySelector('#help-result-count').textContent = count + (count === 1 ? ' guide' : ' guides') + (terms.length || category !== 'all' ? ' found' : ' available');
    }
    function reset() { search.value = ''; filter.value = 'all'; update(); search.focus(); }
    search.addEventListener('input', update);
    filter.addEventListener('change', update);
    clear.addEventListener('click', function () { search.value = ''; update(); search.focus(); });
    root.querySelector('#help-reset').addEventListener('click', reset);
    root.querySelector('.sc-help-controls').hidden = false;
    update();
})();
