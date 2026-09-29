# StyleFlow 1.1.7 Repair

## Scope
- Register StyleFlow in the native CRM Settings shell using a title + child view so the right-side settings panel opens normally.
- Remove the separate module Settings navigation path; legacy `/admin/styleflow/settings` redirects to CRM Settings > StyleFlow.
- Preserve independent invoice, estimate and proposal template selection.
- Make template previews visually distinct by structure as well as color, using bundled full preview images where available.
- Add optional employee portrait behavior per template: none, document creator, or selected staff member.
- Apply the active StyleFlow theme to customer-facing proposal/estimate/invoice HTML views.
- Keep custom PDF class filters for proposal, estimate and invoice output and strengthen the PDF renderer with per-design structural layouts.

## Migration 117
Adds `staff_photo_mode` and `selected_staff_id` to the StyleFlow template table when missing. No existing template row or sales document is deleted. `SC Signature` defaults to document-creator portrait only when its new field still has the default `none` value.

## Deployment
1. Back up the database and `modules/styleflow/`.
2. Upload the complete `styleflow` folder over the existing module.
3. Run the normal StyleFlow module upgrade to 1.1.7 / migration 117.
4. Open Setup/Settings > StyleFlow and verify the settings render in the normal right-side panel.
5. Select separate templates for Invoice, Estimate and Proposal, save, and verify customer view plus PDF output.

## Rollback
Restore the previous module folder and database backup. Do not delete the StyleFlow templates table.
