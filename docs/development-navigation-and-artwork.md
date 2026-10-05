# Development navigation and catalog artwork

The catalog's legacy `sc-<service>.jpg` images and slug-directory galleries used
the same door-repair photo with different captions. The catalog now prefers
genuine configured uploads and the product's uploaded gallery, fetched together
in one query. Without custom artwork it resolves the original branded service
image by name, then the nearest related service artwork. Flooring, roofing,
concrete and otherwise unmatched services have neutral service illustrations.
No product records, prices, stock, order payloads or customer permissions change.

Recovered public artwork is versioned under
`modules/products/assets/images/original-services`. It is optimized as WebP, with
a name manifest, so a fresh checkout has the artwork even without ignored uploads.
Future uploads take priority. Upload filenames are constrained to the public
product directory, including realpath checks against symlink escapes.

`solar_pro/my` now uses `Solar_customer`, a `ClientsController`, for the standard
customer header, persistent sidebar, mobile drawer and footer. The prior public
controller alias redirects to that route. Public report, calculator, contract
and proposal views retain their public layouts. Report access still matches the
validated contact's customer account or email address.

Every staff account sees Estimates, Online Shopping and Projects in the shared
admin header. The links retain the existing destination authorization checks.
The header stays sticky, and its measured height offsets the sidebar.

Verification:

```sh
php tests/catalog_artwork_regression.php
php tests/solar_customer_render.php
NODE_PATH=/path/to/jsdom-and-jquery/node_modules node tests/catalog_presentation.js
CRM_SITE_ROOT="$PWD" php tests/performance_database_regression.php
php tests/products_stability_regression.php
php tests/catalog_permission_controls.php
```

The local `origin` has two push URLs. `git push origin <branch>` attempts both the
existing repository and `just-smart-choice/just-smart-choice-crm`. The latter is
also available as the `just-smart-choice` remote. GitHub must grant the SSH account
write access to both repositories; chat authorization alone does not change the
GitHub role. Do not claim a successful second push when GitHub rejects it.
