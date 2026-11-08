# Phase 0 — Analysis & Architecture

Status: **draft, awaiting two decisions** (see §11).
Date: 2026-09-17

---

## 1. Environment (verified, not assumed)

| Item | Found | Notes |
|---|---|---|
| Project folder | empty except `Untitled design (1).png` | greenfield |
| PHP | 8.2.12 (XAMPP), OPcache present | ext: intl, gd (WebP + AVIF), pdo_mysql, pdo_sqlite, zip, bcmath, fileinfo, exif |
| Composer | 2.9.5 | |
| Node / npm | 24.13.0 / 11.6.2 | |
| DB | MariaDB 10.4.32, root without password | local only |
| Redis | not installed | local uses `database` cache/queue/session; Redis in production |
| Git | 2.53, folder is not a repo | will `git init` |

Packagist (checked 2026-09-17):

| Package | Latest stable | PHP requirement | Fits PHP 8.2? |
|---|---|---|---|
| laravel/framework | 13.32.0 | ^8.3 | **no** |
| laravel/framework 12.x | 12.69.2 | ^8.2 | yes |
| filament/filament | 5.8.2 | ^8.2, ext-intl, Laravel ^11.28, ^12 or ^13, Livewire ^4.1 | yes |
| livewire/livewire | 4.4.5 | ^8.1 | yes |
| spatie/laravel-permission 8.x | 8.3.0 | ^8.3 | **no** |
| spatie/laravel-permission 6.x | 6.25.0 | ^8.0, Laravel ≤13 | yes |

Every other package below is marked *candidate*. I'll check its version and compatibility when I install it.

---

## 2. Screenshot analysis (1366 × 2137 px reference)

Measured from pixels. These numbers are the layout spec for the frontend phase.

| Zone | y-range | Background | Structure |
|---|---|---|---|
| Top bar | 0–58 | `#081621` | logo (≈205–300), search input (≈339–749, icon button at right), Offers, Mobile Deal, Account (icon + 2-line label), PC Builder primary button (≈1068–1148) |
| Category nav | 63–95 | `#fff` + bottom shadow | 18 horizontally centred links: Desktop … Appliance |
| Hero band | 106–584 | `#f2f2f2` | container ≈ 52–1316 (1264 px): main slider ≈ 840 × 437, two stacked side banners ≈ 418 × 216 each, ~6 px gutter; slider pager dashes under the main banner |
| Service strip | ≈560–584 | inside hero band | 4 items with icons, `|` separators: 0% EMI · 24/7 Online Support · No charge on card payment · Cash on delivery in 64 districts |
| Featured Categories | 586–878 | `#f3f4f6` | container 150–1213 (≈1064 px), 8 columns × 2 rows, card 118 × ~85, gap 17 px, line icon + label |
| Flash Sale | 880–1228 | `#f3f4f6` | heading + subtitle, prev/next + "View All"; 6 cards (≈166 px wide, 12 px gap): "Save ৳x" badge, image, 2-line title, price + struck regular price, 4-cell countdown (DAYS/HRS/MIN/SEC), orange "⚡ View Deal" button |
| Latest Products | 1230–1539 | `#f3f4f6` | same carousel header; card with dark "View Details" button |
| Best Sellers | 1542–1834 | `#f3f4f6` | same as Latest |
| Footer | 1836–2133 | dark gradient `#090c14` → `#121823` | 4 columns: Contact (hotline, email, address, social circles, "Our Location" and "Complain Box" buttons), Information, Policies, Affiliation logos; divider; copyright + payment-method icons |
| Floating actions | right edge ≈ y 1870–2010 (viewport-fixed) | — | call, WhatsApp (green), scroll-to-top |

What this means for the backend:

- Every block is data-driven: header links, categories, banners, service strip, home sections, footer menus, affiliation logos, payment icons, social links and contact details.
- Carousel sections need a product source per section (flash sale, latest, best sellers, manual), plus "View All" routes.
- The flash-sale countdown needs the server's `ends_at` and server time, so a skewed client clock can't fake it.
- The "Save ৳x" badge is computed server-side from the effective price.

Branding: the screenshot shows third-party marks (StarTech logo, Ryans and vivo banners, BASIS, "Tech Land BD"). I won't reproduce them. Logo, business name, banners and affiliation logos all come from admin settings. Seeders use neutral placeholders.

**PC Builder** is in the header but not in the required module list. For now the button links to a CMS page. A real compatibility-aware builder is a separate module that I'll propose after launch scope is agreed.

---

## 3. Proposed stack

| Layer | Choice | Why |
|---|---|---|
| Framework | Laravel 12.x on PHP 8.2 (**or** Laravel 13 if PHP is upgraded — decision D1) | Laravel-first, as requested |
| DB | MariaDB 10.4 locally, MySQL 8 / MariaDB ≥10.6 in production | InnoDB FULLTEXT and row locks work on both |
| Admin | Filament 5.8 panel at `/admin` with `->spa()` | SPA-like navigation, built-in MFA |
| Roles/permissions | spatie/laravel-permission 6.x plus Laravel Policies | enforced on the backend, not only by hiding buttons |
| Customer auth backend | laravel/fortify (*candidate*) | headless: register, login, reset and verify now; Blade views come in the frontend phase |
| Media | spatie/laravel-medialibrary (*candidate*) | queued WebP conversions, responsive srcset, cleans up files on delete |
| Search | laravel/scout with the `database` engine (*candidate*) on FULLTEXT columns | fast now; Meilisearch or Typesense can be swapped in later without touching models |
| Audit | spatie/laravel-activitylog (*candidate*) for generic admin changes; domain history tables for stock, orders and payments | traceability |
| Tests | PHPUnit (skeleton default) against a separate MariaDB DB `gadget_demo_test` | real FULLTEXT and row-lock behaviour; SQLite would hide concurrency bugs |
| Frontend (locked) | Blade + Livewire 4 + Alpine + Tailwind 4 + Vite; Swiper + GSAP only if verified and justified | per the brief |

Money is stored as `DECIMAL(12,2)` and calculated through a small `App\Support\Money` value object that uses bcmath, so no float arithmetic touches totals.

---

## 4. Backend module map

```
Identity        users, roles/permissions, customer auth (Fortify), admin auth (Filament + MFA)
Catalog         categories (materialized path), brands, products, variants, attributes, tags, media
Specifications  spec groups → spec attributes → options; category ↔ spec mapping; product spec values
Pricing         PriceResolver: regular → sale window → active flash-sale item (server-side only)
Inventory       stock on variants; InventoryService (atomic conditional decrement + FOR UPDATE); movements ledger; low-stock alerts
Cart            DB carts for guests (encrypted cookie token) and users; merged on login; totals always recomputed
Promotions      coupons (fixed/percent, limits, windows, product/category scope), flash sales
Checkout        CheckoutAction: validate → lock → price → coupon → shipping → tax → create order → reserve stock (one transaction)
Orders          state machine (enum transitions), status history, notes, invoices, manual orders
Payments        PaymentMethod contract: COD, manual bKash/Nagad (sender number + TrxID verified by admin); online gateway adapter slot (needs merchant credentials)
Shipping        shipping zones (Inside/Outside Dhaka, admin-managed charges), 64 districts mapped to zones
Engagement      wishlist, reviews (moderation, verified purchase)
CMS             banners (placement + schedule), home sections, service highlights, pages, menus, settings
SEO             meta and canonical fields on models, sitemap generation job, robots, JSON-LD builders
Reporting       dashboard stats (aggregate queries, cached), top products
```

### Folder strategy (Laravel conventions, no custom module framework)

```
app/
  Actions/        Cart/, Checkout/, Orders/, Inventory/, Coupons/   (one public handle() each)
  Services/       PriceResolver, InventoryService, CartService, CouponValidator, ShippingCalculator,
                  OrderNumberGenerator, SettingsRepository, CatalogCache
  Data/           CartTotals, CheckoutData, PricedLine     (DTOs only where they carry real structure)
  Enums/          OrderStatus, PaymentStatus, PaymentMethod, StockStatus, ProductType, CouponType,
                  InventoryTransactionType, ReviewStatus, BannerPlacement, HomeSectionType
  Models/
  Policies/
  Events/ Listeners/ Jobs/ Notifications/ Observers/
  Http/
    Controllers/Store|Account|Checkout|Webhooks
    Requests/
    Middleware/
  Filament/
    Resources/ Pages/ Widgets/ Clusters/
  Support/        Money, Seo/JsonLd, helpers
routes/
  web.php (store) · account.php · checkout.php · auth.php (Fortify-backed) · webhooks.php
```

---

## 5. Database plan

Conventions:
- `id` BIGINT unsigned, FKs constrained, `timestamps` everywhere.
- Soft deletes only on `products`, `categories`, `brands`, `coupons` and `users`. Orders are never deleted, only cancelled.
- Indexes follow the query patterns listed; nothing is indexed "just in case".

### 5.1 Identity

| Table | Purpose | Key fields | Indexes | Relations |
|---|---|---|---|---|
| users | customers + staff | name, email (nullable unique), phone (nullable unique), password, email_verified_at, status, last_login_at, deleted_at | uq email, uq phone | hasMany addresses, orders, reviews, wishlist; roles |
| roles / permissions / model_has_* / role_has_permissions | spatie | — | package defaults | — |
| addresses | saved customer addresses | user_id, label, name, phone, address_line, area, district_id, is_default | (user_id, is_default) | belongsTo user, district |
| password_reset_tokens, sessions | framework | — | defaults | — |

Seeded roles: `super_admin`, `admin`, `manager`, `order_manager`, `product_manager`, `content_manager`. Customers have no staff role. `User::canAccessPanel()` requires a staff role and active status.

### 5.2 Catalog

| Table | Purpose | Key fields | Indexes | Relations |
|---|---|---|---|---|
| categories | tree | parent_id, name, name_bn, slug, **path** (`/1/5/12/`), depth, icon, image (media), sort_order, is_active, is_featured, show_in_menu, seo_title, seo_description, deleted_at | uq slug; (parent_id, sort_order); (is_active, is_featured, sort_order); path(191) | parent/children; belongsToMany products |
| brands | brands | name, slug, logo (media), description, website, is_active, is_featured, sort_order, seo_* | uq slug; (is_active, is_featured, sort_order) | hasMany products |
| products | product header | type (simple/variable), name, name_bn, slug, sku, barcode, model, brand_id, primary_category_id, short_description(_bn), description(_bn), warranty, condition, weight, length, width, height, status (draft/active/archived), published_at, is_featured, is_best_seller (manual pin), flash_sale_eligible, **denormalized:** min_price, max_price, max_discount_percent, total_stock, stock_status, sales_count, rating_avg, rating_count; search_keywords; seo_title, seo_description, canonical_url; deleted_at | uq slug; uq sku; (status, published_at); (primary_category_id, status, min_price); (brand_id, status); (status, is_featured); (status, sales_count); FULLTEXT(name, sku, model, search_keywords) | brand, primaryCategory, categories (M:N), variants, specValues, tags, reviews, media |
| category_product | extra categories | category_id, product_id | PK(category_id, product_id); (product_id) | — |
| product_variants | **every product has ≥1 variant**; a simple product has one default variant | product_id, sku, barcode, price, sale_price, sale_starts_at, sale_ends_at, cost_price, stock_quantity, low_stock_threshold, weight, is_default, is_active, combination_key | uq sku; uq(product_id, combination_key); (product_id, is_active); (stock_quantity) for low-stock | belongsTo product; belongsToMany attributeValues |
| attributes | variation axes (RAM, Storage, Color) | name, slug, display_type (select/button/swatch), sort_order | uq slug | hasMany values |
| attribute_values | options | attribute_id, value, slug, color_hex, sort_order | uq(attribute_id, slug) | — |
| attribute_value_variant | variant ↔ values | variant_id, attribute_value_id | PK; (attribute_value_id) | — |
| tags / product_tag | tags | name, slug | uq slug; PK pivot | — |
| media | spatie medialibrary | — | package | morph |

Why every product has a variant: cart, checkout, inventory and order items always point at a `variant_id`. Adding variable products, or fashion-style variations later, doesn't touch those flows.

### 5.3 Specifications

| Table | Purpose | Key fields | Indexes |
|---|---|---|---|
| spec_groups | Display, Processor, Memory… | name, name_bn, sort_order | — |
| spec_attributes | e.g. "Screen Size" | spec_group_id, name, slug, unit, input_type (text/number/select/multiselect/boolean), is_filterable, sort_order | uq slug; (spec_group_id, sort_order) |
| spec_attribute_options | select options | spec_attribute_id, value, slug, sort_order | uq(spec_attribute_id, slug) |
| category_spec_attribute | which specs a category uses and filters on | category_id, spec_attribute_id, sort_order, is_filterable | PK |
| product_spec_values | values | product_id, spec_attribute_id, spec_attribute_option_id (nullable), value_text, value_number | uq(product_id, spec_attribute_id, spec_attribute_option_id); **(spec_attribute_option_id, product_id)** for filters; (spec_attribute_id, value_number) for range filters |

### 5.4 Inventory

| Table | Purpose | Key fields | Indexes |
|---|---|---|---|
| inventory_movements | append-only ledger | variant_id, type (stock_in, stock_out, adjustment, sale, cancel_restore, return, damaged), quantity (signed), balance_after, reason, reference_type/id (order, refund), user_id, created_at | (variant_id, created_at); (reference_type, reference_id) |

Oversell protection: inside the checkout transaction, variant rows are locked `FOR UPDATE` in ascending id order (so concurrent checkouts can't deadlock), then decremented with `UPDATE … SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?`. If the update affects zero rows, the checkout aborts. Stock is taken when the order is placed. A scheduled job cancels unpaid online-payment orders after N minutes (a setting) and puts their stock back.

### 5.5 Cart, promotions, shipping

| Table | Purpose | Key fields | Indexes |
|---|---|---|---|
| carts | one per guest token or user | user_id (nullable), token_hash (nullable), coupon_id, last_activity_at | uq user_id; uq token_hash; (last_activity_at) for pruning |
| cart_items | lines | cart_id, variant_id, quantity, price_at_add (only for a "price changed" notice) | uq(cart_id, variant_id) |
| coupons | rules | code, type (fixed/percent), value, min_subtotal, max_discount, usage_limit, per_customer_limit, used_count, starts_at, ends_at, scope (all/products/categories), is_active, deleted_at | uq code; (is_active, starts_at, ends_at) |
| coupon_product / coupon_category | scope | pivots | PK |
| coupon_usages | limit enforcement | coupon_id, order_id, user_id, phone, discount_amount | (coupon_id, user_id); (coupon_id, phone); uq order_id |
| flash_sales | campaigns | title, slug, starts_at, ends_at, is_active, sort_order | (is_active, starts_at, ends_at) |
| flash_sale_items | products in a sale | flash_sale_id, product_id, variant_id (nullable = all variants), discount_type (fixed_price/amount/percent), discount_value, quantity_limit, sold_quantity, per_order_limit, sort_order | uq(flash_sale_id, product_id, variant_id); (product_id) |
| shipping_zones | Inside/Outside Dhaka | name, code, charge, free_shipping_threshold, estimated_days, is_active, sort_order | uq code |
| districts | 64 districts → zone | name, bn_name, division, shipping_zone_id | (shipping_zone_id); uq name |

A flash sale is active only when `is_active` is set, `starts_at ≤ now < ends_at`, and `sold_quantity < quantity_limit`. The server checks all three on every price resolution. `sold_quantity` is incremented with the same conditional-update pattern as stock.

### 5.6 Orders & payments

| Table | Purpose | Key fields | Indexes |
|---|---|---|---|
| orders | order header + snapshot | order_number, user_id, status, payment_status, payment_method, customer_name, phone, email, address_line, area, district_name, shipping_zone_id, shipping_zone_name, customer_note, subtotal, discount_total, coupon_id, coupon_code, shipping_total, tax_total, grand_total, paid_total, refunded_total, currency, source (web/admin), created_by, courier, tracking_number, ip_address, confirmed_at, shipped_at, delivered_at, cancelled_at, payment_expires_at | uq order_number; (user_id, created_at); (status, created_at); (payment_status, created_at); (phone); (created_at) |
| order_items | line snapshot | order_id, product_id (nullOnDelete), variant_id (nullOnDelete), product_name, sku, variant_label, options (json), image_path, regular_price, unit_price, discount_amount, quantity, line_total, flash_sale_item_id | (order_id); (product_id) for top-products |
| order_status_histories | timeline | order_id, type (status/payment), from, to, note, user_id, created_at | (order_id, created_at) |
| order_notes | internal / customer-visible | order_id, user_id, body, is_customer_visible | (order_id) |
| order_sequences | daily counter | date (PK), last_number | — |
| payments | attempts and records | order_id, method, gateway, amount, status, transaction_id, sender_number, meta (json, secrets stripped), paid_at, recorded_by | (order_id); uq(gateway, transaction_id) |
| refunds | refunds | order_id, payment_id, amount, reason, restock, user_id | (order_id) |

Order number format: `<PREFIX><YYMMDD>-<NNNN>`, for example `GD260917-0042`. The prefix is a setting, and the daily counter is taken with a row lock.

Order status transitions are defined once in `OrderStatus::canTransitionTo()`:
`pending → confirmed → processing → packed → shipped → delivered → returned → refunded`. Cancellation is allowed from pending, confirmed, processing or packed, and restores stock. Return restores stock unless it's flagged as damaged.

### 5.7 Engagement & CMS

| Table | Purpose | Key fields | Indexes |
|---|---|---|---|
| wishlists | saved items | user_id, product_id | uq(user_id, product_id) |
| reviews | reviews | product_id, user_id, order_item_id, rating, title, body, status, is_verified_purchase, moderated_by, moderated_at | uq(user_id, product_id); (product_id, status, created_at); (status, created_at) |
| banners | hero and promo images | placement (hero_main, hero_side_top, hero_side_bottom, promo, category_top), title, subtitle, cta_label, url, starts_at, ends_at, is_active, sort_order; desktop + mobile images (media collections) | (placement, is_active, sort_order) |
| home_sections | ordered homepage blocks | type, title, subtitle, config (json: source, limit, flash_sale_id…), is_active, sort_order | (is_active, sort_order) |
| home_section_items | manual picks | home_section_id, item_type/item_id, sort_order | (home_section_id, sort_order) |
| service_highlights | service strip | icon, title, is_active, sort_order | — |
| pages | static pages | title, slug, body, seo_*, is_active | uq slug |
| menus / menu_items | header, footer_information, footer_policies… | location / menu_id, label, url or linkable morph, sort_order, is_active | uq location; (menu_id, sort_order) |
| settings | key/value | group, key, value (json) | uq(group, key) |
| activity_log | admin audit | spatie | package |
| notifications, jobs, job_batches, failed_jobs, cache, cache_locks | framework | — | — |

---

## 6. Admin CMS (Filament 5)

Navigation groups, each gated by permissions:

- **Dashboard:** stats (cached 5 min), sales and orders trend charts, recent orders, low stock, top products.
- **Sales:** Orders (view, confirm and transition actions, mark payment, refund, print invoice, notes, timeline), Create manual order (customer lookup by phone, email or name, inline customer creation, product search), Payments, Coupons, Flash Sales.
- **Catalog:** Products (tabs: General, Bangla, Pricing and Variants, Inventory, Specifications, Media, SEO), Categories (tree order), Brands, Attributes, Spec groups and attributes, Tags, Reviews (moderation).
- **Inventory:** stock adjustments (form → InventoryService), movement ledger, low-stock list.
- **Customers:** Customers, Addresses.
- **Content:** Banners, Home sections, Service highlights, Pages, Menus.
- **Settings:** General, Contact and Social, SEO and Analytics, Shipping zones and districts, Orders and Payments, Tax, Currency.
- **Access:** Staff users, Roles and permissions (Super Admin only).

Every resource has a Policy. Permissions are named `<resource>.<ability>`, for example `orders.update_status` or `products.view_cost_price`. Sensitive fields such as cost price are hidden and never saved without their permission.

Performance rules for admin: `modifyQueryUsing` eager loads, `select()` only on list columns, `->deferLoading()` on heavy tables, pagination only, and cached counts for navigation badges.

---

## 7. Caching & performance

| Data | Store | Key strategy | Invalidation |
|---|---|---|---|
| settings | cache, forever | `settings.v{n}` | `Setting` saved → bump version |
| nav categories tree, brands, footer menus, service strip | cache, forever | `catalog.v{n}.*` | observers on Category/Brand/Menu → `CatalogCache::bump()` |
| homepage payload (section config + product IDs) | `Cache::flexible` (stale-while-revalidate) | `home.v{n}` | banner/section/product/flash-sale change |
| product cards | not cached as HTML; IDs cached, rows fetched in one query with eager loads | — | — |
| stock, price, cart, orders, account | **never cached** | — | — |
| dashboard metrics | cache 5 min | `dash.*` | TTL |

- Tag-free versioned keys work with the file, database and Redis drivers alike.
- Queues (database driver locally, Redis in production) handle media conversions, emails and SMS, the sitemap, low-stock digests and order notifications.
- Scheduler jobs: expire unpaid orders, prune stale guest carts, regenerate the sitemap, send the low-stock digest, prune activity logs.
- Production: `config:cache`, `route:cache`, `view:cache`, `event:cache`, `composer --optimize-autoloader --no-dev`, OPcache with `validate_timestamps=0`.
- N+1 is caught during development with `Model::preventLazyLoading(! app()->isProduction())` and `Model::preventSilentlyDiscardingAttributes()`.

---

## 8. Security architecture

- Totals are always recomputed on the server. The cart stores only variant and quantity; prices come from `PriceResolver` at checkout.
- Mass assignment is controlled with explicit `$fillable`. Form Requests validate every input, and Policies authorize every admin action.
- Rate limits: login (5/min per email+IP), register, password reset, coupon apply (10/min), checkout (5/min), search suggestions (60/min).
- Uploads: allowlisted MIME types checked by content (not extension), size and dimension limits, stored on the `public` disk under random names; SVG is not accepted from uploads.
- The session cookie is `secure` and `http_only` with `same_site=lax` in production. The session is regenerated on login.
- Webhook routes are CSRF-exempt and verified by signature.
- Logs never contain passwords, tokens or payment secrets.

---

## 9. Route organization

| File | Prefix / name | Middleware |
|---|---|---|
| web.php | `/` · `store.*` (home, category, brand, product, search, suggest, flash-sale, page) | web |
| auth.php | Fortify routes + `auth.*` views later | web, guest / throttle |
| account.php | `/account` · `account.*` | web, auth, verified (if enabled) |
| checkout.php | `/cart`, `/checkout` · `cart.*`, `checkout.*`, `orders.track` | web, throttle |
| webhooks.php | `/webhooks/*` · `webhooks.*` | api-style, signature check |
| Filament | `/admin` | panel middleware + `canAccessPanel` |

Because the frontend is locked, the Phase 6 store and cart controllers return JSON or minimal responses, and feature tests cover them. Blade views get attached in Phase 10 without changing the controllers' logic.

---

## 10. Implementation sequence (backend)

1. **Phase 1:** scaffold Laravel, git init, `.env` for MariaDB, create the dev and test databases, install and verify Filament, Livewire, spatie/permission, Fortify, medialibrary, Scout, activitylog; strict model config; enums; Money support; folder skeleton.
2. **Phase 2:** migrations in dependency order; factories; seeders (roles, permissions, shipping zones, 64 districts, spec groups, demo catalog with placeholder images).
3. **Phase 3:** models, relationships, casts, scopes, observers; PriceResolver; InventoryService; CatalogCache; SettingsRepository.
4. **Phase 4:** Fortify (email-or-phone login), panel access, roles and permissions seeding, policies, rate limiters.
5. **Phase 5:** Filament resources, pages and widgets as listed in §6.
6. **Phase 6:** cart, coupons, flash sale, checkout, orders, payments, shipping, wishlist, reviews, search and filters, invoices, sitemap, notifications.
7. **Phase 7:** feature and unit tests (the list in the brief plus concurrency and edge cases), query-count assertions, index review with `EXPLAIN`.
8. **Phase 8:** backend completion report, then **stop** until "BACKEND APPROVED".

---

## 11. Open decisions

- **D1 — Laravel version.** Local PHP 8.2.12 can't run Laravel 13, which needs PHP 8.3 or newer. Laravel 12 is still supported but is already past its bug-fix window; security fixes end around Feb 2027. The alternative is upgrading the XAMPP PHP to 8.3 or 8.4 and using Laravel 13.
- **D2 — Meaning of "100% pixel perfect".** Proposed reading: match the reference's layout geometry, section order, container widths, grid columns, component sizes and colours at 1366 px exactly. Typography, hover and motion polish stay inside that geometry, and tablet and mobile get intentional layouts.
