# Proposal PDF rendering

StyleFlow registers its proposal renderer at priority 100, after Custom PDF. Previously that renderer skipped Custom PDF's cover/closing settings and wrote raw proposal content after a separate item table. Customer language could also leak Spanish labels into the exported PDF.

All three proposal renderers now share `Proposal_document_pdf`: a separate cover, body pages, signatures/attachments and a separate closing page. Existing Custom PDF artwork and configured text are respected; designed English defaults are used when assets/settings are absent. Proposal labels use the core and StyleFlow English language packs, and the previous UI language is restored after export. Invoice and estimate rendering paths remain unchanged.

Item placeholders accept `{proposal_items}` and `{{proposal_items}}`, including whitespace/case variations. Items and totals render once at the placeholder position, or are appended when no placeholder exists. Customer/company details use predictable two-column layout. The proposal date label, quantity/hours headings, fractional adjustments, notes and terms are preserved.

## Regression checks

Run `php tests/proposal_pdf_regression.php <engine> <scenario> [output.pdf]` with:

- Engines: `native`, `custom`, `styleflow`.
- Scenarios: `short`, `long`, `configured`, `image`.

These use the real TCPDF classes/views with synthetic CRM fixtures, without database access. They check item-token variants, single item insertion, English labels from a Spanish starting language, restoration of the UI language, source-record preservation and separate presentation pages. Short examples render three pages; long examples render eight. Render PDFs with Poppler and inspect cover/body/closing pages before release.

For visual QA with copied production artwork, set `SC_PROPOSAL_QA_ASSET_DIR` to a private folder containing `crm-existing-cover.png` and `crm-existing-closing.png` and use the `image` scenario.
