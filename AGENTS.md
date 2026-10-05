# Repository delivery

- Push development changes to the existing `origin` repository, `ssmugdho7/crm.justsmartchoice`, on `dev`. The additional `just-smart-choice/just-smart-choice-crm` remote is no longer requested.
- Development releases use `dev`. Deploy the development checkout at `/home2/scusawco/public_html/dev` on Bluehost with `git pull --ff-only origin dev` after checking for tracked server changes. Production deployment requires its own user request.
- Preserve local edits, installation configuration, customer uploads and server dependencies. Commit only the task's changes, and keep credentials and private runtime data out of new commits.
- Never force-push to work around denied access or divergent branches. Report a failed push accurately and continue the authorized work that remains possible.
