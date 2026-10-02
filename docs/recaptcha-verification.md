# Customer CAPTCHA verification

Google reCAPTCHA v2 decides whether a checkbox click needs a challenge. An immediate checkmark is a supported Google result, not proof that server validation has been skipped. Production was audited on October 3, 2026: customer CAPTCHA enabled, real configured keys (not Google's test keys), and no ignored IP addresses.

The local-development exemption now uses only the trusted installation URL. Request `Host`/server-name values cannot turn off CAPTCHA on a production installation. Local URLs configured as localhost, 127.0.0.1, or IPv6 loopback retain the exemption.

The verification helper sends an encoded HTTPS POST to Google, with certificate verification and bounded timeouts. Missing/non-string tokens, unsuccessful or malformed Google results, and transport failures return the native CAPTCHA validation error. Customer login/registration additionally require Google's response hostname to match the configured CRM hostname. Existing embedded-form callers retain their current Google domain-validation behavior.

Authentication, passwords, sessions, CSRF, and account ownership logic are otherwise unchanged. No keys or other database settings are changed by this code release.

Run `php tests/recaptcha_validation_regression.php` for production and Google-transport fixtures. Pass a configured localhost/loopback URL as the first argument to verify local exemptions. Tests execute the actual helper functions with simulated Google responses; they never solve a CAPTCHA or attempt a customer login.

The Google key owner's Security Preference can be raised to “Most secure” to favor stronger checking. It does not guarantee a puzzle on every click. There is no supported widget attribute to force every checkbox click to show a puzzle.

References: [Google versions](https://developers.google.com/recaptcha/docs/versions), [response verification](https://developers.google.com/recaptcha/docs/verify), [key settings](https://developers.google.com/recaptcha/docs/settings).
