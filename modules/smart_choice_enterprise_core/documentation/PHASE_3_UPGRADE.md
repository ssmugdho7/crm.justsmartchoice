# Phase 3 Upgrade — Customer 360 and Global Search

Version 1.2.0 adds upgrade-safe Customer 360 extension records and permission-aware global search. Existing Perfex customer, contact, lead, project, task, estimate, contract, invoice, ticket, tag, and file tables remain authoritative.

## Upgrade
Merge the `smart_choice_enterprise_core` folder into the existing module folder and run the standard Perfex module upgrade.

## New extension tables
- Customer properties
- Household and emergency contacts
- Communication preferences
- Customer score snapshots
- Customer timeline events
- Customer categories and links
- Relationship manager assignments
- Pinned records

No existing business table is altered.
