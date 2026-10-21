# Module Errors and bulk database upgrade repair

The live `/admin/modules` page listed 150 modules with zero rows marked for Errors
or Needs Update. Errors filtered the table to zero rows without an empty-state
message. The view also swallowed compatibility/database-check exceptions and
therefore excluded those failures from Errors. The latest saved bulk reports
contained zero upgrades, zero errors, and 148 skips: this button has always meant
pending module database migrations, not package installation or downloading module
releases.

Both module-list templates now receive the same server-calculated diagnostics and
share the filter script. Zero results have an explicit message. Failed checks and
failed upgrade attempts are marked as errors, while exception payloads remain
private. Minimum CRM requirements remain enforced.

The empty-state panel deliberately uses a scoped presentation class rather than
the global `.alert` selector: the dev notification module otherwise creates a
spurious “No modules match” toast even while the empty-state panel is hidden.

The bulk action now opens a read-only review showing installed/file versions,
eligible upgrades, and blocked/skipped explanations. Execution requires explicit
confirmation and a framework POST with the existing CSRF field. Each selected
module runs in a separate request, isolating module migration class names and
request timeouts. Only literal `true` is success; a failing return value or
exception produces a visible failure. Unknown HTTP/JSON outcomes stop the batch
without an automatic retry. Version drift is rejected, downgrades are blocked,
and already-current or never-installed modules are not migrated.

Verification:

- `php tests/module_admin_actions.php`: controller and native templates; the
  false-result case failed against the original controller, then passed with the
  repair. Covers review-only GET, eligible POST, stale/invalid requests,
  compatibility/check failures, blocked downgrades, retries, escaped diagnostics,
  non-admin denial, and empty review.
- `node tests/module_admin_ui.js`: real inline scripts; Errors/empty/search
  filters, counts, selected state, sequential POSTs, CSRF preservation,
  duplicate-submit protection, visible mixed results, and stopped uncertain
  requests.
- PHP syntax, Git whitespace checks, and existing module database regressions.

Synthetic tests do not run vendor migrations against customer data. Native module
migration implementations still own their schema changes and require a current
database backup before an administrator starts pending upgrades.
