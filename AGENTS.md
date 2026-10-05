# Repository delivery

- Push changes to both `ssmugdho7/crm.justsmartchoice` and `just-smart-choice/just-smart-choice-crm`. Keep the branch name the same on both remotes. The local `origin` remote has both push URLs; `just-smart-choice` also names the additional repository explicitly.
- Development releases use `dev`. Deploy the development checkout at `/home2/scusawco/public_html/dev` on Bluehost with `git pull --ff-only origin dev` after checking for tracked server changes. Production deployment requires its own user request.
- Preserve local edits, installation configuration, customer uploads and server dependencies. Commit only the task's changes, and keep credentials and private runtime data out of new commits.
- Never force-push to work around denied access or divergent branches. Report a failed push accurately and continue the authorized work that remains possible.
