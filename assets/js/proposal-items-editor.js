(function (root) {
  'use strict';
  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
    });
  }
  var api = {
    toEditor: function (content, label) {
      return String(content || '').replace(/\{\{\s*proposal_items\s*\}\}|\{\s*proposal_items\s*\}/gi, function () {
        return '<span data-sc-proposal-items="1" contenteditable="false" style="display:inline-block;padding:8px 12px;border:1px solid #99c8c1;border-radius:4px;background:#eef8f6;color:#14554d;">' + escapeHtml(label) + '</span>';
      });
    },
    toTemplate: function (content) {
      return String(content || '').replace(/<span\b(?=[^>]*\bdata-sc-proposal-items=["']1["'])[^>]*>[\s\S]*?<\/span>/gi, '{proposal_items}');
    }
  };
  if (typeof module === 'object' && module.exports) { module.exports = api; }
  else { root.SCProposalItemsEditor = api; }
})(typeof window === 'undefined' ? this : window);
