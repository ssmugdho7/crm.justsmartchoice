// Run with NODE_PATH pointing to the isolated jsdom test runtime.
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const {JSDOM} = require('jsdom');
const html = execFileSync('php', ['tests/help_library_render.php'], {
    env: {...process.env, HELP_LIBRARY_RENDER_HTML:'1'}, encoding:'utf8'
});
const dom = new JSDOM(html, {url:'https://portal.example.test/', runScripts:'dangerously'});
try {
    const image = dom.window.document.querySelector('img[src="https://cdn.example.test/broken-cover.png"]');
    assert.ok(image, 'Custom cover is initially preserved');
    image.dispatchEvent(new dom.window.Event('error'));
    assert.equal(image.getAttribute('src'), image.dataset.fallback, 'Broken custom cover uses its topic artwork');
    assert.match(image.src, /client-portal\.svg\?v=2$/);
    assert.equal(image.hidden, false);
    image.dispatchEvent(new dom.window.Event('error'));
    assert.equal(image.getAttribute('src'), image.dataset.genericFallback, 'Broken topic artwork uses generic artwork');
    image.dispatchEvent(new dom.window.Event('error'));
    assert.equal(image.hidden, true, 'Exhausted images stop retrying');
    assert.equal(image.previousElementSibling.textContent.trim(), 'Client Portal', 'Offline category cover remains instead of an empty card');
    const finalSource = image.src;
    image.dispatchEvent(new dom.window.Event('error'));
    assert.equal(image.src, finalSource, 'Further errors cannot loop between fallbacks');
    console.log('PASS rendered card image recovery: custom → topic → generic → offline cover, without retry loops');
} finally { dom.window.close(); }
