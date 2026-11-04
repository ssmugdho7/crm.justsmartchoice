# Issue #25 — layout stability

Source: https://github.com/Smart-Choice-Constraction/justsmartchoice/issues/25

Implemented in this checkout:
- Admin viewport and table space reservation without assigning 380px to every form panel.
- Loading shimmer limited to empty totals containers; reduced-motion support.
- Existing resizable sidebar width/position retained.
- Submenus restored from session storage immediately after sidebar markup, before footer initialization; actual menu-item slug classes are used (native parent items have no ID).
- Common dark navy submenu chevron, replacing duplicate module glyphs.
- Native navigation progress indicator with modifier/external/hash/download exclusions, timeout and pageshow reset.
- Progressive cross-document view transitions. No content opacity lock, previous-page full-height carryover, or permanently hidden content if JavaScript fails.

Verification:
- PHP lint for aside.php: passed.
- node --check for sc-layout-stability.js: passed.
- tests/admin_layout_stability_regression.js with jsdom: passed (persistence, closed state, navigation exclusions, Back/pageshow reset, malformed/blocked storage).
- git diff --check: passed.
- Not deployed; live navigation, visual layout and CLS measurement are not yet verified.

Referenced module upgrades are not completed by this change:
Authenticated GitHub downloads for #17, #21, #23 and #24 succeeded. All four ZIPs contain HTML/CSS design references and screenshots, with zero PHP files. #22 contains a filename but no download link. Replacing production templates blindly with these static mockups would discard native permissions, filtering and interactions. These are separate module implementation requirements, not drop-in PHP upgrades. The source packages are retained under /tmp/crm-issue25 for inspection during this session.

## Browser inspection — 2026-11-04

Live authenticated browser inspection covered Dashboard, Expenses, Invoices and Contracts. Confirmed that opening Sales, navigating to Invoices, then navigating to Expenses closes Sales. Computed min-height on live content, totals and DataTables wrappers is 0px. At the observed narrow content width, expenses totals occupy about 233px and invoice totals about 155px. Added container-aware reservations so a resized sidebar also triggers the wrapped-totals layout.

An isolated localhost preview (synthetic records only) confirmed native page navigation restores the submenu and common chevrons appear. The table stayed at 382px including its border before/after delayed data. Narrow 900px viewport activated the 240px totals reservation. The synthetic totals grew beyond that reservation because the fixture is not the native template; this is not evidence of zero CLS in the complete app. Production was not modified. Temporary browser viewport override was reset.
