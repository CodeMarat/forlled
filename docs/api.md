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
- `GET /pages/contact-us`
- `GET /pages/become-partner`
- `GET /pages/blog`
- `GET /pages/locations`
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
  - Returns visible products only.
  - Supports `page` and `per_page`.
- `GET /products/{slug}`
  - Returns a single visible product by slug.
  - Includes `recommended_products` and `navigation_categories`.

### Product Categories

- `GET /product-categories`
  - Returns visible categories only.
  - Supports `page` and `per_page`.
- `GET /product-categories/{slug}`
  - Returns a single visible category by slug.
  - Includes `products` and `navigation_categories`.

### Locations

- `GET /locations`
  - Returns visible locations only.
  - Supports `page` and `per_page`.
- `GET /pages/locations`
  - Returns singleton page settings for the public locations page.

### Treatments

- `GET /treatments`
  - Returns visible treatments only.
  - Supports `page` and `per_page`.
- `GET /pages/treatments`
  - Returns singleton page settings for the public treatments page.

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

## Notes For Frontend

- Detail endpoints are slug-based, not numeric-id-based.
- `blog-posts`, `products`, and `product-categories` should be consumed as paginated resources.
- `locations` and `treatments` are also paginated resources.
- Singleton page endpoints are intended to drive public page rendering directly and should not require additional composition on the frontend.
- Import the Postman collection and override `base_url` if you are not running the app on `http://localhost`.
