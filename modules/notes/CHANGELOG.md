# Notes 1.2.5

- Made View/Edit/Share/Delete controls compact in a 2x2 layout.
- Changed the note color table cell to a color-only swatch.
- Added Created Date to the Notes dashboard.
- Defaulted the Notes dashboard to newest-created notes first.
- Preserved the working note save flow and all existing features/data.

# Notes 1.2.4

- Fixed New Note submission when TinyMCE hides the description textarea.
- The browser no longer blocks Save because of a hidden HTML-required editor control.
- TinyMCE content is synchronized before the normal POST.
- Title and description remain required through visible client validation and server validation.
- Relation selection is no longer an HTML form blocker; unlinked notes can be saved safely with relation ID 0.
- Successful creation redirects to the Notes dashboard and displays "Note created successfully."
- Migration 124 is code-only and non-destructive.

## 1.2.3

- Fixed new-note Save behavior so the note is persisted and the Notes dashboard reloads immediately.
- Added the success message: "Note created successfully."
- Added explicit TinyMCE synchronization before the normal form POST.
- Added non-destructive migration 123; no existing notes, settings, uploads, permissions, or data are changed.

# Changelog

## 1.2.2
- Changed Notes Settings from two narrow columns to two full-width stacked sections.
- Added compact responsive rows with smaller Save/Delete controls and native color pickers.
- Removed cramped internal scrolling while preserving all Notes table, save, import, view, edit, and settings behavior.
- Added non-destructive sequential migration 121.

## 1.2.0
- Fixed Setup → Settings → Notes Settings so the child entry is clickable in CRM 3.8.8.
- Added a direct Notes → Settings sidebar action following the working Appointly pattern.
- Preserved the working notes table, save flow, existing notes, and all other features unchanged.
- Added sequential migration 120 with no destructive database changes.


## 1.1.9
- Restored the original working notes DataTable retrieval and save flow.
- Preserved existing tblnotes rows and native relation behavior.
- Added Edit action and task-style note view.
- Added rich text editor for descriptions.
- Registered clickable Setup > Settings > Notes Settings using the Appointly pattern.
- Kept source/type names and color pickers exclusively in settings.
- Preserved Import and Sample Header toolbar actions.
- Added non-destructive migration 119.
