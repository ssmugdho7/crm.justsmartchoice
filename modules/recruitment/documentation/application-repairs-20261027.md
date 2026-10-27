# Recruitment email and applicant document repairs

Subtitle: Correct logo sizing, restore all application attachments, and repair candidate portal navigation.

## Findings and changes

- The configured email header already displays a 170px company logo. Recruitment message bodies also use `{logo_image_with_url}`, whose default output had no width. The inspected Gmail message rendered that second image at 1600px. The native `merge_field_logo_img_width` filter now supplies a 160px default, preserving explicit overrides and the administrator's email header/footer settings. Previously delivered emails are immutable; subsequent emails use the correction.
- Application forms store résumés under `candidate/files/{id}` and driver documents under `candidate/driver_licenses/{id}`. Candidate 28 has both files on disk. `files.rel_type` is varchar(20), so the old 28-character `rec_candidate_driver_license` value was stored as `rec_candidate_driver`; the profile only queried `rec_cadidate_file`. New writes use the 20-character value and the admin list recognizes both historical values, alongside résumé files. No schema migration or record rewriting is needed. Résumé input also accepts multiple files using the existing array-capable upload handler.
- Driver documents use an authenticated admin endpoint, restricted to administrators or global recruitment viewers, with path checks and private/no-store responses. The profile offers preview/download without sending sensitive identity documents through third-party preview services. Existing résumé presentation and upload records are preserved.
- The header linked to the nonexistent `Recruitment_portal::login`. It now targets `Authentication_candidate::login`. Welcome mail uses the existing bilingual Recruitment Portal label instead of hardcoded “link”, and escapes candidate-provided text.

## Upgrade and recovery

Deploy all changed recruitment source, views, helper, English/Spanish language files, and tests from this release. No database migrations, module activation, settings overwrite or dependencies are required. Preserve uploads and configuration. Source recovery uses the deployment helper's private backup. Existing candidate documents are discovered by their stored metadata; no applicant data is changed and no notification replay is required.

## Verification

Run `php tests/recruitment_application_repairs.php` and PHP syntax checks for the modified files. The regression uses the actual company-logo merge-field renderer and welcome-email method with isolated fixtures; it checks both historical document types, traversal/missing-file rejection, the portal link label and route. Browser verification must confirm the public Login button reaches a login form and the existing candidate's Attachment tab lists both stored documents. A separate multipart fixture verifies that multiple résumé files are processed. No applicant messages should be sent as a smoke test; historical Gmail messages retain their original HTML.

## Shared public navigation — 28 October 2026
Application, legacy skill-test, and pre-employment assessment pages now use the portal navigation partial, with Log in omitted. Header CSS is shared with the portal. Existing language keys, language cookie/query behavior, form submissions, and portal login remain unchanged. No migration or permission change is required; deploy the listed source files together. Validation: PHP lint, existing recruitment regressions, and isolated navigation rendering (login on/off, two language links, query preservation).
