# Forlled Public API

Base URL: `http://localhost/api/v1`

Postman collection: [docs/forlled-public-api.postman_collection.json](/home/marat/Projects/forlled/docs/forlled-public-api.postman_collection.json:1)

## Conventions

- All responses are JSON.
- Public content endpoints are read-only.
- List endpoints support pagination with:
  - `page` - integer, default `1`
  - `per_page` - integer, default `12`, max `100`
- Paginated endpoints return Laravel's standard `data`, `links`, and `meta` keys.
- Image fields are normalized as:

```json
{
  "path": "products/items/example-card.jpg",
  "url": "http://localhost/storage/products/items/example-card.jpg",
  "variants": {
    "card": {
      "path": "products/items/variants/example-card.jpg",
      "url": "http://localhost/storage/products/items/variants/example-card.jpg"
    },
    "detail": {
      "path": "products/items/variants/example-detail.jpg",
      "url": "http://localhost/storage/products/items/variants/example-detail.jpg"
    }
  }
}
```

## Endpoints

### Pages

- `GET /pages/home`
- `GET /pages/about-us`
- `GET /pages/technology`
- `GET /pages/privacy` - returns `title`, `last_edited_at` (`YYYY-MM-DD`), and `content` (HTML).
- `GET /pages/contact-us`
- `GET /pages/become-partner`
- `GET /pages/featured-in`
- `GET /pages/blog`
- `GET /pages/locations`
- `GET /pages/social-media`
- `GET /pages/treatments`

These endpoints return a single structured payload per page, grouped by sections like `hero`, `story`, `science`, `form`, and similar.

### Blog

- `GET /blog-posts`
  - Returns published posts only.
  - Supports `page` and `per_page`.
- `GET /blog-posts/{slug}`
  - Returns a single published post by slug.

### Products

- `GET /products`
  - Returns visible products whose category type is `product`.
  - Supports `page` and `per_page`.
- `GET /products/{slug}`
  - Returns a single visible product by slug.
  - Includes `categories`, `recommended_products`, and `navigation_categories`.
  - `category` remains the first category for backward compatibility.

### Product Categories

- `GET /product-categories`
  - Returns visible categories whose type is `product`.
  - Supports `page` and `per_page`.
- `GET /product-categories/{slug}`
  - Returns a single visible category by slug.
  - Includes `products` and `navigation_categories`.

### Treatment Products

- `GET /treatment-products`
  - Returns visible products whose category type is `treatment`.
- `GET /treatment-products/{slug}`
  - Returns a single visible treatment product by slug.
- `GET /treatment-product-categories`
  - Returns visible categories whose type is `treatment`.
- `GET /treatment-product-categories/{slug}`
  - Returns a single visible treatment category and its products.

### Locations

- `GET /locations`
  - Returns visible USA salons only, with state, website, optional independently sourced coordinates, and optional Google address Place ID (not a verified business ID).
  - Supports `page`, `per_page`, `state` (two-letter code), and `search` (name, city, state, or address).
- `GET /represented-countries`
  - Returns the visible worldwide country list in menu order. Optional world-map pin percentages are null until assigned.
- `GET /visitor-region`
  - Resolves the visitor IP on the backend and returns `region: "us"` or `region: "world"`, plus `cache_ttl_seconds: 86400`.
  - On lookup failure, returns `region: null` and TTL `0`; the frontend should show the world version without caching that failure.
  - Request this endpoint from the visitor's browser, not through a server-side frontend proxy. If the backend is behind a trusted reverse proxy, set `TRUSTED_PROXIES` to its IP/CIDR; never trust arbitrary forwarded headers.
- `GET /pages/locations`
  - Returns singleton page settings for both variants: the existing `hero` and the new `us.hero` / `us.locations` blocks.

Run `php artisan db:seed --class='Database\Seeders\LocationsCatalogSeeder'` after migrations to import 48 countries and 54 USA salons. Set `IPINFO_TOKEN` for IP lookup. To resolve missing salon address Place IDs, set `GOOGLE_MAPS_GEOCODING_KEY` and run `php artisan locations:geocode-us-salons`; this command stores address Place IDs, not Google-derived coordinates. Review unmatched addresses manually. The frontend must resolve address Place IDs with Google Maps; it must not treat them as verified salon business records.

### Treatments

- `GET /treatments`
  - Returns visible treatments only.
  - Supports `page` and `per_page`.
- `GET /pages/treatments`
  - Returns singleton page settings for the public treatments page.

### Featured In

- `GET /pages/featured-in`
  - Returns the standalone featured logos block with section title and logo images.

### Partner Requests

- `POST /partner-requests`
  - Creates a partner request entry.
  - Returns `201 Created`.

Request body:

```json
{
  "first_name": "Jane",
  "last_name": "Doe",
  "country": "Armenia",
  "city": "Yerevan",
  "company": "Clinic Name",
  "company_type": "Clinic",
  "position": "Owner",
  "email": "jane@example.com",
  "phone": "+37400000000",
  "website": "https://example.com",
  "message": "We want to become a partner."
}
```

Success response:

```json
{
  "message": "Partner request submitted successfully.",
  "id": 1
}
```

Validation errors follow Laravel's standard `422` JSON format with `message` and `errors`.

### Contact Us Requests

- `POST /contact-us-requests`
  - Creates a contact us request entry.
  - Returns `201 Created`.

Request body:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "country": "United States",
  "city": "Los Angeles",
  "message": "Please contact me back."
}
```

Success response:

```json
{
  "message": "Contact request submitted successfully.",
  "id": 1
}
```

## Notes For Frontend

- Detail endpoints are slug-based, not numeric-id-based.
- `blog-posts`, `products`, and `product-categories` should be consumed as paginated resources.
- `locations` and `treatments` are also paginated resources.
- Singleton page endpoints are intended to drive public page rendering directly and should not require additional composition on the frontend.
- Import the Postman collection and override `base_url` if you are not running the app on `http://localhost`.
