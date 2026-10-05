# Customer Online Shopping catalog

The service-shopping layout was restored on October 5, 2026 from the original
Git snapshot `fc338ebe`. Its view and card script match the Bluehost backup
`/home2/scusawco/codex-backups/shop-catalog-20261003/previous-catalog.tar.gz`.

The page keeps the original multi-category toolbar, View Cart and Checkout
buttons. Cards with images appear first, keeping the original order within
the photographed and unphotographed groups, including after category filtering.
The layout keeps the original Bootstrap one/two/three-column cards, larger image areas, price panels,
variation controls, quantity steppers and bottom Share buttons. Existing
`products_frontend.css` supplies the layout. The scoped `legacy_catalog.css`
only supports loading, broken-image and missing-image covers. The later
`catalog.css` and `catalog.js` assets are no longer loaded by this page.

Current escaping, labeled options and quantities, cart request validation,
stock checks, recurring prices, translations, hidden-price settings, real
uploaded galleries and 27 recovered original service images remain in place.
The shared customer header and sidebar are retained. Product records and
checkout routes are unchanged.

Run `tests/catalog_presentation.js` with an isolated jsdom/jquery runtime to
verify original card markup, image loading and recovery, escaping, variation
pricing and stock, quantity controls, original cart payload and empty responses.
Run `tests/catalog_artwork_regression.php`, `tests/products_stability_regression.php`
and `tests/catalog_permission_controls.php` for artwork, checkout and permission
checks. No order or outgoing email is required for presentation validation.
