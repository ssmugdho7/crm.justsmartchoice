# Customer Online Shopping catalog

The customer shopping page uses a responsive service grid, a restrained green
heading, search, multi-category filtering, sorting, a cart badge, readable prices
and accessible option controls. Full service photos fit inside the image area.
Cards with images always appear first, including after category filtering and
alphabetical sorting. Order within each group follows the selected sort.

Current product records, uploaded galleries, 27 recovered original service
images, prices, cart requests, checkout routes and hidden-price settings remain
in place. The shared customer header and sidebar are retained. Existing main
branch runtime and debug configuration is preserved.

The page loads scoped `catalog.css` and `catalog.js`. Compile CSS with:

```sh
npm exec --yes --package=tailwindcss@3.4.17 -- tailwindcss -c modules/products/assets/css/catalog.tailwind.cjs -i modules/products/assets/css/catalog.source.css -o modules/products/assets/css/catalog.css --minify
```

Run `tests/catalog_presentation.js` with an isolated jsdom/jquery runtime for
image-first default and descending order, search, cart badge, image loading,
escaping, variation prices, stock and quantity controls. Also run
`tests/catalog_artwork_regression.php`, `tests/solar_customer_render.php` and
`tests/customer_header_destinations.js` to verify the existing related fixes.
Presentation validation requires no submitted order or outgoing email.
