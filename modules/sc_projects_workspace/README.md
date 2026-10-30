# Emerald Projects Workspace

Project directory, permission-aware project workspace, task punch list, milestone tracker and native Gantt presentation for the Smart Choice CRM admin web application.

## Delivered changes

The supplied `smartchoice-perfex-crm-project.zip` guides the emerald/white palette, cards, portfolio ribbon, row action pills, progress bars, workspace sidebar and builder spacing. The application uses live records; screenshot counts, staff initials, names and dollar figures are examples, never seeded into the CRM.

- Directory: five native statuses with live accessible-project counts; active-project progress, logged hours and fixed-price contract values grouped by currency. Saved filters, search, exports, native columns and custom fields remain. Table filters apply to the table; the ribbon explicitly describes its broader accessible-project scope.
- Workspace: native permission/feature-filtered tabs, including all ten reference destinations when enabled, plus any installed extensions. Existing member photos/tooltips, project selector, actions, warnings, modals and native financial/operational overview remain. Summary includes contract value when permitted, logged hours, deadline and dynamic progress.
- Tasks: up to 12 visible tasks in native order, completion checkboxes, translated priorities and statuses. The complete native task table remains for filters, assignees, timers and bulk actions. Completion/reopen requests use native authorized endpoints and recover visibly on failure.
- Milestones: up to eight milestones with task completion when the staff member can see all project tasks. Full milestone destination remains available; the summary does not disclose restricted task counts.
- Gantt: native task/milestone/member/status controls, drag dates, task modals and Day/Week/Month/Year zoom remain. The directory Gantt's inherited malformed zoom selector is repaired in the module-owned view.
- Create/edit: all native form fields, settings, validation, CSRF helpers, custom fields, recurring/billing choices and member assignments remain.
- English and Spanish: all new visible strings have paired language keys. Native strings continue to use Perfex's installed translations.

Perfex's actual status IDs are **4 = Finished, 5 = Cancelled**, unlike the accompanying reference plan. No status remapping occurs. Fixed-price values use SQL DECIMAL aggregation; hourly rates are not misrepresented as contract totals. No database migration is required.

## Permissions and licensing

This is an extension of native Projects, not a second project data store. Perfex already registers Projects View Own, View, Create, Edit and Delete plus milestone capabilities. View Own is natively marked not applicable: project membership establishes own access even without a global View grant. This extension preserves that existing policy, role assignments and backend checks. Create/Edit/Delete remain separate native grants; no rights are automatically granted. Financial summaries follow native Projects Create visibility. Task mutation rights follow the native task editor/assignee/staff-creator policy. No new controller, public API, upload path or mutation endpoint is introduced.

As requested for future modules, activation uses the existing owner-controlled **Smart Choice Module Licensing** system and product identifier `sc_projects_workspace`. Install/configure that module first, activate a valid company license under Module Licensing, then activate this workspace. Missing/expired licenses disable this presentation extension and return Projects to its native views; they never disable native Projects or delete data. Issuer setup, private signing keys and company license issuance are separate existing licensing configuration, not fabricated by this package.

## Installation / upgrade / rollback

1. Back up source, database and uploads using the normal site process. Do not replace the CRM database or upload folders with a module ZIP.
2. Upload the install ZIP through Setup → Modules, or place its `sc_projects_workspace/` directory in `modules/`. The final bootstrap path must be `modules/sc_projects_workspace/sc_projects_workspace.php`.
3. Ensure `sc_module_licensing` is installed, active and privately configured. Activate the `sc_projects_workspace` key, then activate **Smart Choice Projects Workspace** as an administrator.
4. Open `/admin/projects`, a project overview/tasks/milestones/files tab, `/admin/projects/gantt`, and create/edit pages. Check with both a global-view account and an assigned-only account. Existing native permissions apply without reassignment.
5. For later source upgrades, deactivate only this workspace, replace its directory, preserve the licensing system/configuration, reactivate and perform the same checks. There are no schema migrations or data conversions.
6. Rollback: deactivate this workspace to restore native Projects immediately. Removing its folder after deactivation removes only presentation source. Project records, uploads, assignments, settings and licenses are not deleted.

No deployment to dev or production is included in this implementation delivery. Real activation with a valid license and end-to-end live project writes have not been performed.

## Source locations

- `modules/sc_projects_workspace/sc_projects_workspace.php`: native module/language/activation registration and licensing dependency.
- `modules/sc_projects_workspace/sc_license.php`: product identifier.
- `modules/sc_projects_workspace/includes/projects.php`: scoped hooks, native view mapping, accessible aggregates, row progress/actions and task visibility helpers.
- `modules/sc_projects_workspace/assets/css/projects.css`: scoped emerald components and responsive integration.
- `modules/sc_projects_workspace/assets/js/projects.js`: KPI selection and native task completion/reopen interaction.
- `modules/sc_projects_workspace/views/projects/`: `manage.php`, `stats.php`, `portfolio.php`, `header.php`, `navigation.php`, `project_tabs.php`, `view.php`, `project.php`, `gantt.php`, `project_overview.php`, `project_tasks.php`, `punchlist.php`.
- `modules/sc_projects_workspace/language/{english,spanish}/sc_projects_workspace_lang.php`: paired new strings.
- `modules/sc_projects_workspace/smart_choice_module_manifest.json`: affected paths, hooks, tables and permission policy.
- Repository `tests/projects_workspace_regression.php`, `tests/projects_workspace_ui.js`, `tests/projects_workspace_visual.js`, `tests/fixtures/projects_workspace.php`: executable verification sources.

## Actual verification — 23 October 2026

- PHP syntax checks: all module PHP files and SQL fixture runner pass; JavaScript syntax and Git whitespace checks pass.
- SQL tests executed against Bluehost's MySQL through the native CI database driver, using exclusively connection-local `TEMPORARY` fixture tables with an isolated `audit_scpr_` prefix: 32 checks pass. Includes membership/global scope, exact decimal totals, currency separation, timer scope, native status/progress behavior, task edit eligibility, language parity and unlicensed fallback. No live project rows read or changed by these tests.
- DOM tests pass for KPI selection, native completion/reopen routes, rejection/network rollback, native form/CSRF/control preservation, unrelated-page isolation and the actual Gantt zoom handler.
- Headless Chrome renders actual PHP presentation components at 1440, 768 and 390 pixels: six layouts pass page-overflow checks, ten tabs render, hidden features and financial/actions permission conditions work, and Spanish output renders. Screenshots are synthetic fixtures, not production screenshots. Visual inspection caught and corrected low-contrast task status labels.
- These tests do not claim real Perfex activation/upgrade, full browser CRUD, every third-party project tab, live upload recovery, license issuance or production behavior. Perform the installation smoke checks above before promoting. Full native task/Gantt/backend code remains responsible for those existing flows.

Reproduce from repository root (install dev-only tools in a temporary directory, not the production module):

```sh
find modules/sc_projects_workspace -name '*.php' -exec php -l {} \;
node --check modules/sc_projects_workspace/assets/js/projects.js
npm install --prefix /tmp/scpr-tools jsdom jquery@3.7.1 playwright --no-audit --no-fund
NODE_PATH=/tmp/scpr-tools/node_modules node tests/projects_workspace_ui.js
NODE_PATH=/tmp/scpr-tools/node_modules node tests/projects_workspace_visual.js
CRM_SITE_ROOT=/private/site/checkout php tests/projects_workspace_regression.php
```

The SQL runner needs a private site config and MySQL TEMPORARY TABLE privileges. The visual runner uses Mac Chrome at its normal application path and writes synthetic screenshots to `/tmp/sc-project-evidence` (override with `SC_PROJECT_EVIDENCE`). Runtime JavaScript uses the CRM's existing jQuery; no npm packages are needed in production.
