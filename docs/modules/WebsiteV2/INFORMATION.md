# WebsiteV2 Information

## Purpose

WebsiteV2 provides a clean storefront shell with cart, checkout, content, marketing, footer/header settings, coupons, affiliate settings, newsletter, wishlist, and review support.

## Source

The module was generated from:

- `docs/modules/Website/ANALYSIS.md`
- `docs/modules/Website/REBUILD_SPEC.md`
- `docs/modules/Website/REFACTOR_PLAN.md`

Source code under `Modules/Website` was used only to confirm route conventions and exact migration columns where documentation required extraction.

## Database Mode

Mode: `new-db`

Module-owned tables:

- `website_v2_coupons`
- `website_v2_settings`
- `website_v2_carts`
- `website_v2_cart_items`
- `website_v2_banners`
- `website_v2_footer_columns`
- `website_v2_footer_links`
- `website_v2_social_links`
- `website_v2_flash_sales`
- `website_v2_flash_sale_items`
- `website_v2_newsletters`
- `website_v2_wishlists`
- `website_v2_affiliate_levels`
- `website_v2_affiliate_schemes`
- `website_v2_tags`
- `website_v2_reviews`

Shared references:

- `users`
- `wp_products`

## Security

Admin routes are under `/admin/website-v2` and use `auth:admin` plus `permission:website-v2.view`. Create/edit coupon and customer pages add create/edit permissions.
