# Repository delivery

- Push development changes to the existing `origin` repository, `ssmugdho7/crm.justsmartchoice`, on `dev`. The additional `just-smart-choice/just-smart-choice-crm` remote is no longer requested.
- Development releases use `dev`. Deploy the development checkout at `/home2/scusawco/public_html/dev` on Bluehost with `git pull --ff-only origin dev` after checking for tracked server changes. Production deployment requires its own user request.
- Preserve local edits, installation configuration, customer uploads and server dependencies. Commit only the task's changes, and keep credentials and private runtime data out of new commits.
- Never force-push to work around denied access or divergent branches. Report a failed push accurately and continue the authorized work that remains possible.

# Mandatory CRM module standards

- Read and follow [Mandatory CRM Module Standards](docs/crm-module-standards.md) before every CRM module development, repair, or upgrade. These standards are mandatory for all new and modified modules.
- Target Perfex CRM 3.4.1 compatibility. Review the affected module and working reference modules before editing; use native hooks, activation, installation, and migrations, and avoid modifying CRM core files.
- Modules with three or more pages require permission-aware top navigation with an active-page indicator and existing Perfex styling.
- Register View Own, View (Global Permission), Create, Edit, and Delete, plus applicable additional capabilities. Enforce permissions and record ownership in backend controllers/API endpoints as well as the UI.
- Keep every user-visible string in language keys with matching English and Spanish module files, including navigation, permissions, messages, and settings.
- Deliver complete source, exact paths, required migrations, installation/upgrade instructions, a clear title and subtitle, and actual test results. Preserve existing data, settings, permissions, and functionality. Never label unperformed checks as passed.
- Future first-party modules must integrate the company/module/site/expiry-bound activation mechanism in [Module Licensing and Social Sign-In](docs/module-licensing-and-social-signin.md): declare a license identifier, validate activation, and enforce licensing at every protected backend entry point. Keep issuer signing keys on the separate owner server, and never bypass or replace another vendor's license checks.
