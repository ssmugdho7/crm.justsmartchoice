(function () {
    'use strict';

    const outline = document.querySelector('[data-training-outline]');
    const article = document.querySelector('.training-manual-rendered-article');
    if (!outline || !article) return;

    const headings = [...article.querySelectorAll('h2, h3, h4')].filter(
        heading => heading.textContent.trim() && !heading.closest('.sc-toc')
    );
    if (!headings.length) return;

    const list = document.createElement('ol');
    headings.forEach((heading, index) => {
        if (!heading.id || document.getElementById(heading.id) !== heading) {
            let id = 'training-section-' + (index + 1);
            while (document.getElementById(id)) id += '-section';
            heading.id = id;
        }

        const item = document.createElement('li');
        item.dataset.level = heading.tagName.slice(1);
        const link = document.createElement('a');
        link.href = '#' + encodeURIComponent(heading.id);
        link.textContent = heading.textContent.trim();
        item.append(link);
        list.append(item);
    });

    outline.append(list);
    outline.hidden = false;
})();
