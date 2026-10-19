const assert = require('node:assert/strict');
const editor = require('../assets/js/proposal-items-editor.js');

for (const token of ['{proposal_items}', '{{proposal_items}}', '{{ proposal_items }}', '{PROPOSAL_ITEMS}']) {
  const before = '<p>Scope {proposal_subject}</p>';
  const after = '<p>Terms &amp; conditions</p>';
  for (const label of ['Proposal items <automatic>', 'Artículos de la propuesta']) {
    const display = editor.toEditor(before + token + after, label);
    assert(!display.includes(token));
    assert(display.includes('contenteditable="false"'));
    assert.equal(editor.toTemplate(display), before + '{proposal_items}' + after);
  }
}
assert(editor.toEditor('{proposal_items}', '<script>bad</script>').includes('&lt;script&gt;'));
assert.equal(editor.toTemplate('<span>Preserve ordinary spans</span>'), '<span>Preserve ordinary spans</span>');
assert.equal(editor.toTemplate(editor.toEditor('<p>Scope without items</p>', 'Items')), '<p>Scope without items</p>');
assert.equal(editor.toTemplate(editor.toEditor('{proposal_items}<p>Middle</p>{proposal_items}', 'Items')), '{proposal_items}<p>Middle</p>{proposal_items}');
console.log('PASS proposal editor: readable markers, English/Spanish labels, template round trips and escaped content');
