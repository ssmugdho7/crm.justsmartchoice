# Smart Choice Enterprise Core 1.0.0

## Installation path

`public_html/crm.justsmartchoice/modules/smart_choice_enterprise_core/`

## Installation

1. Back up the database and CRM files.
2. Upload the module folder directly under `modules/` with no additional nesting.
3. Open **Setup → Modules**.
4. Activate **Smart Choice Enterprise Core**.
5. Assign View, Manage, and Export permissions to authorized roles.
6. Open **Smart Choice Enterprise Core → Staff Image Health**.

## Staff profile images

Perfex expects each staff image at:

`uploads/staff_profile_images/{staffid}/{profile_image}`

Example:

`uploads/staff_profile_images/1/Harold Cabrera.jpg`

The module never changes the `tblstaff.profile_image` values. It checks original, small, thumbnail, and case-insensitive filename variants and displays a placeholder when no matching file exists.

## Safety

- No customer, lead, project, invoice, estimate, proposal, contract, task, ticket, staff, or module record is deleted.
- No existing table is duplicated.
- The migration creates options only.
- Uninstall preserves operational records and disables the module.
