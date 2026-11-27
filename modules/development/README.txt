DEVELOPMENT MODULE v1.0.0
Perfex CRM 3.4.1+

Purpose: Internal CRM repair tracker.
Features: problem descriptions, links, image/video uploads, multi-staff assignment, estimated hours, signed/timestamped work notes, next steps, completion/reopen audit, granular staff permissions.

Install: Upload development.zip in Setup > Modules, activate Development, then configure staff permissions.
No CRM core files are modified.

Version 1.1.0: View/Edit/Delete actions, green heading, multiple links, assignment bell and email template integration, POST-only completion, retained module directory development.
IMPORTANT: Back up the module and database before replacing. Perfex native mail-template registration/sending and bell events require validation against the installed CRM 3.4.1 build. No live CRM integration test has been performed.

Version 1.1.1: Targeted new-record creation fix. Verify insert success, isolate optional uploads/assignment/notification failures so the new note remains accessible, and preserve all other workflows. Migration 111 is a no-op; no data is deleted.
