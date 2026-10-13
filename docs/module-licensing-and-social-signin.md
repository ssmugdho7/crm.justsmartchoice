# Smart Choice Module Licensing and Social Sign-In

**Company-bound activation keys and Google, Facebook, and Apple sign-in for employees and clients**

This delivery adds two independent first-party modules. It does not remove, activate, replace, or bypass the original vendor's license checks. Existing password login remains available. Provider apps and the owner-controlled license-server hostname have not yet been supplied; real provider sign-in cannot be enabled or claimed verified until those are configured.

## What the code does

`sc_module_licensing` runs in two explicitly configured modes:

- **Owner server:** authorized staff issue random 256-bit activation keys for a company, module identifier, exact HTTPS CRM installation URL, and one-to-five-year term. Only the SHA-256 key hash is retained. Staff can change expiration, deactivate, reactivate, or delete a license; mutations write a secret-free audit record. A lost key must be replaced and the old license revoked.
- **Customer installation:** an authorized staff member enters the purchased key. The customer contacts the trusted owner server and verifies a signed confirmation with the owner's public key. The stored key is encrypted with the installation's native CodeIgniter encryption facility. Confirmations last at most one hour and never exceed the license expiry. An expired confirmation cannot authorize the module; an unavailable server does not extend it. Failed refreshes are limited to once per minute per stored activation.

Only the separate owner server holds the RSA private signing key. It is not included in a module ZIP, customer installation, or Git commit. Customer administrators cannot issue keys through their CRM because issuance additionally requires owner mode in private server configuration. View Own licenses means licenses created by that staff member on the owner server; Global View grants visibility across the issuer's company licenses. Records are scoped in the model, independently of buttons.

`sc_social_login` authenticates provider identities and connects them to native Perfex accounts:

1. A user chooses Google, Facebook, or Apple from the staff/client login screen.
2. A ten-minute, single-use transaction binds state, provider, audience, nonce, and PKCE where supported to an HttpOnly browser cookie. Initiation is rate limited per IP; expired transactions are removed on subsequent initiation.
3. The server exchanges the authorization code at fixed provider endpoints over verified HTTPS. Google/Apple identity tokens require a valid RSA signature, key ID, issuer, audience, nonce, issue time, and expiration. Facebook access tokens must belong to the configured app and the returned user ID.
4. A connected provider subject logs in only its assigned CRM account. Staff/client namespaces are separate, and identity hashes include the configured OAuth client ID. No existing account is automatically taken over by matching an email address.
5. An unconnected user proves existing CRM account access with the normal password/MFA flow, or completes native client registration if enabled. Email must match for this automatic connection. Hidden/different provider email requires an explicit connection from the authenticated Account Connections page.
6. New clients complete the native registration form with required/custom fields, a fallback password, CAPTCHA, terms, confirmation, and verification. The module hooks into registration rather than creating unchecked accounts. Staff registration remains administrator-controlled.

Inactive staff/contacts/companies, unapproved registrations, and unverified client email when verification is required cannot use social login. Staff MFA routes through native Perfex two-factor authentication and does not establish a staff session before the challenge. Successful social sessions rotate the session ID and use the current configured workday limit. This implementation does not create a new social-login persistent Remember me token; the existing password-login Remember me behavior remains intact.

Provider access/refresh tokens are not persisted. Account identifiers are stored as hashes; pending linking data is kept in the server session for at most ten minutes of validity. A successful connection consumes that pending data. Disconnecting the last available sign-in method is blocked when the account has no password, with account-level database locking to serialize disconnects.

## Owner server installation

1. Provision a **separate, owner-controlled Perfex installation** and HTTPS hostname. Do not use a customer-controlled installation as the issuer. Protect administrator accounts with MFA and restrict access to its database, backups, and signing configuration.
2. Back up the installation/database. Install the complete `modules/sc_module_licensing/` directory through native module installation or copy it to the installation's `modules/` folder. Its `Requires at least` header is 3.4.1.
3. Generate private configuration outside the public web tree:

   ```sh
   php tools/sc-license-config.php issuer /absolute/private/sc-licensing
   ```

   The tool generates a 3072-bit RSA key pair and a private `config.php`, refuses overwrite, and prints only the configuration path. Protect/back up the private directory; private files are mode 0600 and its directory is 0700. Never regenerate these files casually: changing the public key requires updating every customer trust configuration.
4. Set `SC_LICENSING_CONFIG=/absolute/private/sc-licensing/config.php` in this installation's **web server/PHP environment**. Setting it only in an SSH shell does not configure web requests. The helper refuses files inside the checkout document root or `public_html`. Confirm Apache/PHP environment support with the host before enabling issuer mode.
5. Activate **Smart Choice Module Licensing** in Setup → Modules. The native activation hook creates only new module-owned tables and preserves existing options/data when repeated.
6. Assign the module's appropriate View Own/Global View, Create, Edit, Delete, and Manage Settings capabilities. Issuing keys needs Create. Updating/revoking requires Edit plus authorized record scope; deleting requires Delete plus scope. Private issuer mode is required for all issuer operations, even administrators.
7. Open `/admin/sc_module_licensing/licenses`, issue a key for `sc_social_login`, and copy the once-displayed key securely to the licensed company. Enter its **exact** HTTPS CRM URL including any installation path. A production key does not automatically authorize a different development URL.
8. Public machine endpoint: `/sc_module_licensing/license_api/check`. It accepts HTTPS POST only, authenticates through `X-SC-License`, and never uses a browser session as authorization. Only that exact endpoint is exempted from browser CSRF. Do not log its activation-key header or request payload. Protect the owner server with normal host-level request limits and monitoring.

## Customer installation and activation

1. Install and activate `sc_module_licensing` first. Do not ship the owner private key or issuer configuration to the customer.
2. Copy the owner's **public** PEM key through a trusted channel and create the customer configuration:

   ```sh
   php tools/sc-license-config.php consumer /absolute/private/sc-licensing \
     https://your-owner-license-host /absolute/private/owner-public.pem
   ```

3. Set this customer's web/PHP `SC_LICENSING_CONFIG` to the resulting `config.php`. Ensure the installation has a stable native encryption key, HTTPS, cURL, OpenSSL, and MySQL/InnoDB. Rotating the CRM encryption key requires reentering encrypted activation/provider secrets.
4. Open `/admin/sc_module_licensing/activation`, enter module identifier `sc_social_login` and the purchased key. Manage Settings permission is required.
5. Install `modules/sc_social_login/`, then activate **Smart Choice Social Sign-In** through Setup → Modules. Both the native pre-activation hook and social activation callback require a valid license. The default provider settings are disabled.
6. Assign Social Sign-In capabilities. Staff need Create to connect their own provider, View Own to list their connections, Delete to disconnect, or Edit to disable within their view scope. Global View can inspect staff/client connections; it does not grant mutation permissions. Clients manage only their own connections through the authenticated client controller.
7. Open `/admin/sc_social_login/settings` with Manage Settings permission. Enter the provider configuration below. Blank secret fields preserve existing encrypted secrets; saved secrets are never returned in HTML. Use provider development/testing modes until an actual login test has passed.
8. Top navigation provides Account Connections, Provider Settings, and Help with active-page highlighting and permission filtering. Module Licensing provides Activation, Company Licenses (issuer only), and Help. Clients use `/sc_social_login/account`.

## Provider configuration

Register each callback exactly as displayed on the Provider Settings page, using the intended installation's HTTPS URL:

| Provider | Callback path | Required developer setup |
| --- | --- | --- |
| Google | `/sc_social_login/oauth/callback/google` | OAuth web client ID/secret, consent screen, allowed redirect URI, and test users while in testing mode. Uses `openid email profile`, state, nonce, and S256 PKCE. |
| Facebook | `/sc_social_login/oauth/callback/facebook` | Meta developer app with Facebook Login, app ID/secret, email permission, valid OAuth redirect URI, appropriate app mode/access/review. Uses Graph API v26.0 by default, configurable for the app's supported version. |
| Apple | `/sc_social_login/oauth/callback/apple` | Apple Developer account, Sign in with Apple primary app association, Services ID, verified website domain/return URL, team ID, key ID, and private `.p8` key. CRM settings receive the Services ID and a generated client-secret JWT. |

Apple client-secret generation (keep `.p8` private and outside the CRM):

```sh
php tools/sc-apple-client-secret.php /absolute/private/AuthKey.p8 \
  YOURTEAMID YOURKEYID com.yourcompany.crm /absolute/private/apple-client-secret.txt
```

The tool signs an ES256 JWT valid for 180 days, writes it privately with mode 0600, and prints only the renewal date. Enter that file's contents into Apple provider settings privately, then delete the temporary secret file when it is no longer needed. Retain the `.p8` signing key securely for renewal. Renew the client-secret JWT before expiry.

Apple uses a cross-site `form_post` callback. Only `/sc_social_login/oauth/callback/apple` is CSRF-exempt; a single-use state and Secure/HttpOnly/SameSite=None browser-binding cookie are mandatory before token exchange. Some restrictive browsers/WebViews may reject that cookie, in which case the flow fails closed. Explicit linking from Apple may require a fresh password login if the ordinary CRM session cookie was not sent cross-site; password login remains available. Real Apple behavior must be tested with the configured developer app and supported browsers before release.

Official references reviewed during implementation:

- [Google OpenID Connect](https://developers.google.com/identity/openid-connect/openid-connect)
- [Facebook manual login flow](https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow/)
- [Meta Graph API versions](https://developers.facebook.com/docs/graph-api/changelog/)
- [Apple web sign-in configuration](https://developer.apple.com/documentation/signinwithapple/configuring-your-webpage-for-sign-in-with-apple)
- [Apple token generation/validation](https://developer.apple.com/documentation/signinwithapple/generate-and-validate-tokens)

## Incorporating licenses into future first-party modules

Use the exact module identifier consistently in issuance, `sc_license.php`, activation, and runtime checks. The licensing module remains an unlicensed bootstrap dependency so users can enter a key before activating a purchased feature.

Add `modules/your_module/sc_license.php` returning the identifier, using the same direct-access guard as other module files. Inside the module activation callback, require the licensing helper, ensure `sc_module_licensing` is active, and call `scl_require('your_module')` **before** installation or feature effects. The module's backend controllers/API endpoints/jobs must repeat the runtime check before feature effects; suppress licensed navigation/buttons with `scl_licensed('your_module')`. Do not treat the Modules page's active flag as proof that a license is still valid.

Review the complete `sc_social_login` module as the working reference: its manifest and activation callback prevent unlicensed activation; its OAuth controller prevents expired/unlicensed feature entry; account-management pages retain safe access to disconnect identities and password recovery remains native. Apply the same pattern to future modules while preserving their five basic permissions, additional capabilities, bilingual language files, and top navigation. Do not retrofit paid third-party modules by bypassing their vendor checks.

Because customers receive PHP source and control their own servers, key enforcement cannot prevent a customer deliberately editing/removing checks, altering trust configuration, or spoofing its base URL. This is a signed entitlement mechanism with operational revocation, not tamper-proof DRM. A one-hour lease bounds normal revocation delay and tolerates brief network interruptions only until its original expiration; choose a different bound deliberately if commercial requirements change.

## Source, migrations, upgrades, and recovery

Complete files are in:

- `modules/sc_module_licensing/`: bootstrap, install/migration, helper, cryptographic library, model, controllers, views, exact CSRF exemption, English/Spanish language folders.
- `modules/sc_social_login/`: bootstrap, license manifest, install/migration, helper, provider library, identity model, staff/client/OAuth controllers, views, exact Apple exemption, English/Spanish language folders.
- `tools/sc-license-config.php`, `tools/sc-apple-client-secret.php`: CLI-only private setup utilities.
- `tests/sc_license_oauth_regression.php`, `tests/sc_module_database_regression.php`: no external mail/provider account submissions.
- `AGENTS.md`, `docs/crm-module-standards.md`, and this guide: future development and delivery requirements.

Both modules use the native `register_activation_hook`, `register_language_files`, and `App_module_migration` structure. `migrations/100_version_100.php` invokes the idempotent installer. Tables are prefix-aware and InnoDB. Licensing tables: `scl_licenses`, `scl_activations`, `scl_audit`. Social tables: `scsl_identities`, `scsl_transactions`. Existing installation/provider options are added only when absent; no roles receive new permissions automatically. No CRM core files are changed.

For upgrades, back up the database and private configuration first, preserve module directories/data, deploy complete files, run native module upgrade when requested by Perfex, and verify registration, keys, navigation, translations, and access with test accounts. Do not overwrite private configuration. Deactivation disables provider sign-in without deleting CRM accounts or passwords. No destructive uninstall/data cleanup is supplied; data remains for recovery. To roll back, disable the social module, preserve its module tables/options, restore the previous source backup, and use native password login. Restore the issuer's database/signing key together from the intended backup; do not restore an older issuer database without reviewing revoked licenses.

## Actual verification and outstanding checks

Executed locally:

```sh
php tests/sc_license_oauth_regression.php
php tests/sc_module_database_regression.php
php tests/sc_module_database_regression.php sc_social_login
```

The cryptographic/provider fixtures verify valid and tampered leases, expired/wrong-site/wrong-module/wrong-installation licenses, bounded confirmation lifetime, JWT signature/issuer/audience/nonce/expiry, algorithm confusion, unknown signing keys, Google PKCE, Facebook app binding, Apple callback mode, and bilingual key parity.

The native CodeIgniter/MySQL test uses **connection-local TEMPORARY tables only**. It executes native Perfex activation and migrations, repeated installers, issuer API to consumer validation, encrypted key storage, role/record scopes, direct route denial, account linking separation, app-ID binding, callback replay/browser binding, inactive/approval/email-verification checks, fresh login sessions, native staff MFA staging, own disconnects, and last-method protection. MFA test uses app-based challenge and sends no email. CLI configuration generation, file permissions, refusal to overwrite, PHP syntax, and complete literal language-key coverage are also checked.

Provider HTTP responses are fixture responses for these automated tests. They are not real Google, Facebook, or Apple sign-ins. Full browser journeys, provider reviews, cross-site Apple cookie behavior, production hosting of the owner authority, and verification on a clean **Perfex 3.4.1** installation remain outstanding. The current repository's native classes are used for compatibility checks; these results do not prove another runtime was tested.

## Exact delivery file inventory

```text
modules/sc_module_licensing/config/csrf_exclude_uris.php
modules/sc_module_licensing/controllers/License_api.php
modules/sc_module_licensing/controllers/Sc_module_licensing.php
modules/sc_module_licensing/helpers/sc_module_licensing_helper.php
modules/sc_module_licensing/install.php
modules/sc_module_licensing/language/english/sc_module_licensing_lang.php
modules/sc_module_licensing/language/spanish/sc_module_licensing_lang.php
modules/sc_module_licensing/libraries/Sc_license_crypto.php
modules/sc_module_licensing/migrations/100_version_100.php
modules/sc_module_licensing/models/Sc_license_model.php
modules/sc_module_licensing/sc_module_licensing.php
modules/sc_module_licensing/views/manage.php
modules/sc_social_login/config/csrf_exclude_uris.php
modules/sc_social_login/controllers/Account.php
modules/sc_social_login/controllers/Oauth.php
modules/sc_social_login/controllers/Sc_social_login.php
modules/sc_social_login/helpers/sc_social_login_helper.php
modules/sc_social_login/install.php
modules/sc_social_login/language/english/sc_social_login_lang.php
modules/sc_social_login/language/spanish/sc_social_login_lang.php
modules/sc_social_login/libraries/Sc_oauth.php
modules/sc_social_login/migrations/100_version_100.php
modules/sc_social_login/models/Sc_identity_model.php
modules/sc_social_login/sc_license.php
modules/sc_social_login/sc_social_login.php
modules/sc_social_login/views/buttons.php
modules/sc_social_login/views/connections.php
modules/sc_social_login/views/finish.php
modules/sc_social_login/views/manage.php
modules/sc_social_login/views/prefill.php
tools/sc-license-config.php
tools/sc-apple-client-secret.php
tests/sc_license_oauth_regression.php
tests/sc_module_database_regression.php
AGENTS.md
docs/crm-module-standards.md
docs/module-licensing-and-social-signin.md
```

Local results: 144 license/OAuth/translation assertions, 63 native database/authorization/session assertions per migration run (both modules), 34 PHP files syntax checked, and private configuration/Apple ES256 utility checks passed. No mail, provider accounts, or customer records were submitted. Browser layout and real provider tests remain unperformed.
