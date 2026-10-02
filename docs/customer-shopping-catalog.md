# Customer Online Shopping catalog

The catalog stays on `products/client`. Its existing `filter`, `add_cart`, `get_my_cart`, `share_product`, and `place_order` routes and controller/model code are unchanged. Existing CSRF handling, stock checks, recurring prices, translated product data, and hidden-price settings remain in place.

The complete CodeIgniter 3 PHP view is `modules/products/views/clients/products.php`. Cards still render from the existing AJAX response in `client_products.js`; moving them to a server loop would lose enriched localized options and cart data. Escaping uses the existing CI3 conventions and the renderer now also escapes quoted HTML attributes.

Presentation changes:
- Scoped, compiled Tailwind 3 utilities (prefix `sc-tw-`, no preflight/CDN). Styles are confined to `#sc-catalog`; checkout, detail, admin, and other portal screens keep their styles.
- Responsive one/two/three-column cards, real image galleries, product-name covers for absent/generic/broken images, restrained teal actions, and ghost share controls.
- Sticky desktop utility/filter toolbar, responsive mobile controls, existing multi-category filtering, local search and alphabetical sorting that move/hide existing DOM instead of discarding selections.
- Current cart quantity badge from existing read endpoint and add-cart responses. Existing stepper and cart request payload preserved.
- Labeled options/quantities, keyboard focus, empty and loading feedback. Existing Files/Calendar links are moved only if the shared portal navigation already renders them.

Build (Tailwind is already a project dev dependency; an isolated npm exec also works):

```
npm exec --yes --package=tailwindcss@3.4.17 -- tailwindcss -c modules/products/assets/css/catalog.tailwind.cjs -i modules/products/assets/css/catalog.source.css -o modules/products/assets/css/catalog.css --minify
```

Regression check: `tests/catalog_presentation.js` uses an isolated jsdom/jquery test runtime (no new runtime dependencies). It exercises the real renderer and events with mocked transport: missing/default/broken image fallback and recovery, escaping, gallery retention, variation pricing/stock and form fields, stepper, original cart payload, badge, search, sorting without losing selections, and empty responses.

Live development checks: 190 products retained; Roofing filter gives 11 products; name sorting, search/no-match/reset, variation-price update, stepper, existing share dialog, and grid overflow checked at 320/390/768/1440px. No real payment/order or outgoing customer email was submitted. Account-specific hidden-price/guest states were preserved in code but were not tested with separate live accounts.

Deployment backup: `/home2/scusawco/codex-backups/shop-catalog-20261003/`. Previous dev revision: `59ad1d98`. For rollback, first check for local tracked changes, then switch the dev checkout to that revision; no database restoration is needed. Production is unchanged.
