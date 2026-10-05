# Development navigation and catalog artwork

The local SQL backup already contains the overwritten `sc-<service>.jpg` and
`sc-default-service-1.jpg` assignments. Migration 203 replaced previous product
images with these generated templates. The local uploads retain 27 original
service images, named `product_3.png` through `product_29.png` (27 is a JPEG).
Their service captions match the product names in the SQL backup, establishing
the original assignments recorded in the name manifest.

The catalog prefers genuine configured uploads and uploaded galleries, fetched
in one query, followed by these verified originals. No related-service guesses
or replacement illustrations are used. The other 163 entries in the 190-product
backup have no original image files; they use the existing service-name cover.
An explicitly empty gallery never falls back to a discarded template.
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

The shared customer header retains Estimates, Online Shopping and Projects on
every customer panel page, alongside the existing Support and Meetings links.
Existing contact permissions and enabled-module settings still determine which
links are granted. The customer header stays sticky, with the sidebar offset by
its measured height. The admin header is unchanged.

Verification:

```sh
php tests/catalog_artwork_regression.php
php tests/solar_customer_render.php
NODE_PATH=/path/to/jsdom-and-jquery/node_modules node tests/catalog_presentation.js
CRM_SITE_ROOT="$PWD" php tests/performance_database_regression.php
php tests/products_stability_regression.php
php tests/catalog_permission_controls.php
```

Development changes are pushed only to the existing `origin` repository,
`ssmugdho7/crm.justsmartchoice`, and pulled in Bluehost's development checkout.
