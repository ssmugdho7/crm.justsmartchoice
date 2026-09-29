# Phase 3 Recovery 1.2.3

This release repairs installations where Phase 3 tables were created but Phase 2 tables were skipped.

- Migration 110 is now a safe bridge.
- Migration 123 creates the six missing Phase 2 tables idempotently.
- Existing Customer 360 tables and data are preserved.
- Staff profile image diagnostics now use the exact database filename first, common thumbnail variants, case-insensitive matching, native Perfex image URLs, and controlled fallback searches under uploads.
- The staff image table is constrained to the content viewport with compact columns and readable wrapping.

Expected staff image location:

`uploads/staff_profile_images/{staffid}/{profile_image}`
