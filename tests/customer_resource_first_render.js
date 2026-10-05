// The portal must not depend on DOMContentLoaded to remove the legacy green KB hero.
const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const css = ['style.css', 'customer-portal.css'].map(file =>
    fs.readFileSync('assets/themes/smartchoice/css/' + file, 'utf8'));
const dom = new JSDOM(`<head>${css.map(text => `<style>${text}</style>`).join('')}</head>
<body class="customers"><div id="wrapper" class="sc-portal-layout">
<section class="jumbotron kb-search-jumbotron sc-knowledge-search">
<header class="sc-account-heading"><h1>Knowledge Base</h1></header>
<div class="kb-search"><label class="kb-search-heading">Search</label></div>
</section></div></body>`, {runScripts:'outside-only'});
const window = dom.window;
function presentation() {
    return ['.sc-knowledge-search', '.sc-knowledge-search h1', '.kb-search', '.kb-search-heading'].map(selector => {
        const style = window.getComputedStyle(window.document.querySelector(selector));
        return [style.backgroundColor, style.backgroundImage, style.color, style.padding];
    });
}
const initial = presentation();
assert.equal(initial[0][0], 'rgba(0, 0, 0, 0)', 'Heading area is transparent before portal JavaScript');
assert(!initial[0][1].includes('gradient'), 'No legacy hero gradient on first render');
assert.equal(initial[1][2], 'rgb(25, 56, 46)', 'Heading is readable on first render');
assert.equal(initial[2][0], 'rgb(255, 255, 255)', 'Search panel is white on first render');
window.document.body.classList.add('sc-customer-enhanced');
assert.deepEqual(presentation(), initial, 'Portal enhancement does not flash or restyle the KB heading');
// Exercise the actual card error handler with a failed uploaded image and failed topic cover.
const card = fs.readFileSync('modules/training_manual/views/partials/help_guide_card.php', 'utf8');
const handler = card.match(/onerror="([^"]+)"/)[1];
const image = window.document.createElement('img');
image.src = 'https://portal.example/missing-upload.png';
image.dataset.fallback = 'https://portal.example/topic.svg';
image.dataset.genericFallback = 'https://portal.example/generic.svg';
const fail = new window.Function(handler).bind(image);
fail(); assert.equal(image.src, image.dataset.fallback);
fail(); assert.equal(image.src, image.dataset.genericFallback);
assert.equal(image.hidden, false, 'Generic cover remains visible');
fail(); assert.equal(image.hidden, true, 'All failed assets stop retrying');
dom.window.close();
console.log('PASS Knowledge Base first-render styles and bounded Help Library image recovery');
