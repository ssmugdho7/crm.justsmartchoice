# StyleFlow 1.1.1 Upload / Migration Repair

- Preserves all StyleFlow 1.1.0 template features.
- Replaces per-template SELECT/INSERT seeding with one SELECT plus one batch INSERT.
- Adds sequential migration 002 for installations where migration 001 was already attempted.
- Does not rerun the entire installer from migration 002.
- Preserves active invoice, estimate, and proposal template selections.
- Does not remove sales documents, templates, settings, or user data.
