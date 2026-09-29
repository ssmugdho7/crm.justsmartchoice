# Changelog

## 3.2.8
- Fixed all public contract URLs by using the native HMVC controller path beginning with `subcontractors/`.
- Fixed Digital View, customer signature submission, and customer comment submission 404 errors.
- Corrected module route definitions to use the module slug as the first URI segment.
- Preserved all existing data, templates, signatures, initials, uploads, and settings.

# Changelog

## 3.2.7
- Added a dedicated public `Subcontractor_contract` controller based on the CRM core contract flow.
- Digital view, signature, and customer comments now use one canonical public URL.
- Removed dependence on `AdminController` for customer contract actions.
- Kept compatibility routes for prior sign/comment/admin URLs.
- Preserved all existing data and files.

# Changelog

## 3.2.5
- Keeps the single module identity and folder `subcontractors`.
- Repairs the migration sequence from 120 through 325 with no gaps.
- Preserves all existing tables, records, settings, uploads, signatures, initials, templates, and tokens.
- Repairs module-local install references.
- Repairs public portal view loading.
- Repairs staff notification links to the `subcontractors` admin controller.
- Retains contract signature, drawn initials, typed initials, templates, PDF, and digital-link functionality.
- Keeps the contract logo limited to 120 px.

## 3.3.4
- Moved module settings into CRM Setup.
- Added selected employee SMS and Telegram manager notifications.
- Added portal colors, animation choices, and configurable bilingual questions.
- Left-aligned company, contract, and template names.
- Removed template editor delay and expanded the help guide.
