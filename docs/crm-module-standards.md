# Mandatory CRM Module Standards

**Smart Choice Contractors USA — Requirements for every new or modified module**

Platform compatibility target: **Perfex CRM 3.4.1**.

These requirements apply to every future CRM module development, repair, and upgrade. They are mandatory, including when modifying an existing module. Review compliance within the affected module before delivery; do not silently omit unmet requirements or describe an incomplete implementation as complete.

## 1. Review before implementation

- Review the affected module, its routes, controllers, API endpoints, models, views, permission registration, language files, activation/install hooks, and migrations.
- Review working reference modules in this repository and follow their verified Perfex conventions. Do not assume an existing module is compliant merely because it is installed.
- Verify compatibility against Perfex CRM 3.4.1. If the available runtime differs, report that distinction and the compatibility checks actually performed.
- Preserve existing records, settings, assigned permissions, uploads, integrations, and functionality.
- Avoid modifying CRM core files; use module hooks and native extension mechanisms.
- Follow Perfex's native activation, installation, and migration structure. Schema changes must use upgrade-safe migrations and preserve existing data.

## 2. Top navigation

Any module with **three or more pages** must provide a navigation menu at the top of its pages so users can move between main sub-pages without returning to the CRM sidebar.

- Include all main sub-pages that the current user is permitted to access.
- Highlight the active page, including its detail/edit routes where applicable.
- Use the existing Perfex CRM design and working navigation patterns.
- Keep navigation usable on mobile widths and accessible by keyboard.
- Localize every navigation label.
- Enforce access independently in the destination controller or API endpoint; navigation filtering alone does not authorize a request.

Examples:

- Accounting: Dashboard | Expenses | Transactions | Reports | Settings
- Subcontractors: Dashboard | Subcontractors | Projects | Contracts | Documents | Reports

## 3. Permission registration and enforcement

Every module must register these five basic permissions using the native Perfex permission structure:

| Permission | Required behavior |
| --- | --- |
| View Own | View only records assigned to or owned by the user, according to the module's documented ownership rules. |
| View (Global Permission) | View all records authorized by the module's access boundaries. |
| Create | Create new records. |
| Edit | Modify existing records within the user's authorized record scope. |
| Delete | Delete records within the user's authorized record scope. |

Register additional permissions when the module supports the corresponding feature:

- View All Templates
- Import
- Export
- AI Estimate
- Settings
- Reports
- Approve
- Share with Customers
- Manufacturing and Nesting
- Manage Settings

Permission names and descriptions must use English and Spanish language keys. Preserve existing capability identifiers and role assignments during upgrades; do not grant new capabilities to all roles automatically.

Enforcement requirements:

- Check permissions in the UI and backend, including controllers, API endpoints, AJAX requests, downloads, exports, and state-changing actions.
- Apply ownership/assignment restrictions to list queries, search, counts, individual records, and mutation paths. Document which fields establish ownership or assignment.
- A global view capability does not implicitly grant Create, Edit, Delete, or other action capabilities.
- Enforce action capability and authorized record scope together. Guessing or changing a record ID must not expose or modify another user's records.
- Preserve native administrator behavior and applicable customer/public access rules. Public links must retain their own authentication/token and authorization checks.
- Hiding a button or navigation item is never a substitute for backend authorization.

## 4. English and Spanish language files

Create both language folders when a module is initially created. Existing modified modules must meet the same bilingual requirement.

```text
modules/
  module_name/
    language/
      english/
        module_name_lang.php
      spanish/
        module_name_lang.php
```

- Follow the existing working Perfex language registration and loading structure.
- All visible text must use language keys: menus, buttons, headings, messages, validation errors, permissions, settings, empty states, and confirmation dialogs.
- Do not hardcode user-facing text in controllers, models, views, JavaScript, or API responses shown to users. Use the established translation mechanism in each layer.
- Every new key must exist in both language files, with an actual English and Spanish translation.
- Make text changes through language files rather than hardcoded source replacements.
- Preserve existing keys used by other code, settings, or templates; check for missing keys and untranslated UI in both languages.

## 5. Complete source and delivery record

Every delivery must include a clear **title and subtitle** describing what was developed or repaired, plus:

- Complete source code for every new or modified file, with exact filenames and folder locations. A repository delivery must contain the actual complete files, not only snippets.
- Required database migrations, or an explicit statement that none are required.
- English and Spanish language files and language registration changes where required.
- Permission registration and backend/UI enforcement.
- Top navigation when the module has three or more pages.
- Installation or upgrade instructions, including prerequisites, dependencies, activation/migration steps, and data-preservation or rollback considerations.
- A summary of changes and actual testing results, including limitations and unperformed checks.

Do not deliver partial source, missing dependencies, placeholder functions, or incomplete ZIP packages. If a ZIP is supplied, include the complete module and correct folder structure, ready for installation or upgrade. Exclude credentials, private runtime configuration, and customer data from source delivery.

Use this delivery-record structure:

```text
Title: <specific module change>
Subtitle: <user-visible result and purpose>
Compatibility: <Perfex target, runtime used, and checks performed>
Changed files: <exact repository-relative paths>
Database migrations: <files and upgrade steps, or none required>
Navigation: <main pages, active state, and permission filtering>
Permissions: <capabilities, ownership rules, and enforcement locations>
Languages: <English/Spanish files and key changes>
Installation/upgrade: <complete steps and dependencies>
Data preservation/rollback: <backup and recovery instructions>
Testing: <actual commands/scenarios and observed outcomes>
Limitations: <unperformed checks or unresolved requirements>
```

## 6. Required verification before delivery

Perform meaningful checks appropriate to the changed module; use isolated test data and avoid sending real notifications or changing customer records during testing.

- **Activation/install:** verify native registration, dependencies, and fresh installation when adding a module or changing installation behavior.
- **Upgrades:** test affected migrations and upgrade paths against existing data; verify settings and role assignments survive. Check that repeated upgrade execution does not duplicate or destroy data.
- **Navigation:** verify each authorized main page, active-page highlighting, direct links, restricted-page filtering, keyboard access, and mobile layout.
- **Permissions:** test administrator, global-view, own-only, action-specific, and denied-access roles as applicable. Test assigned/owned and other-user records. Check direct controller/API requests as well as visible buttons, including read, create, edit, delete, and applicable extra actions.
- **Translations:** verify matching English/Spanish keys and render both languages. Check menus, permissions, settings, validation, success/error messages, and empty states.
- **Regression:** verify affected existing workflows, data, settings, and functionality remain intact. Run relevant syntax checks and project tests.
- **Evidence:** record what was actually run and its outcome. A syntax check does not establish runtime compatibility, successful activation, or authorization correctness. Mark unavailable checks as not performed and explain the limitation.

Never claim a feature, installation, upgrade, language, or permission path was tested unless that check was actually performed. Do not mark a delivery fully compliant while mandatory requirements remain unresolved.

## 7. First-party module licenses

Future first-party modules must use the [owner-controlled licensing mechanism](module-licensing-and-social-signin.md). Bind purchased activation to company, module, exact CRM URL, and expiration. Require a valid signed confirmation before native activation and every protected feature action; suppress unavailable feature navigation accordingly. Keep the issuer private signing key on the separate owner-controlled server. Do not distribute it to customer installations or commit it. Preserve native password/recovery access and the licensing activation interface. Do not bypass or rewrite third-party vendor license checks.
