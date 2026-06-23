# WebsiteV2 Rebuild Specification

## Architecture

WebsiteV2 follows:

Route -> Controller -> Page Blade -> Service -> Model -> Migration

Livewire folders are reserved for future components, but the initial implementation avoids copying direct-persistence Livewire code from Website.

## Naming

- PHP namespace: `Modules\WebsiteV2`
- Route prefix: `website-v2`
- Route names: `website-v2.*`
- View namespace: `website-v2::`
- Permission prefix: `website-v2.`
- Cache prefix: `website_v2.`
- Storage path: `website-v2`

## Tables

The source Website tables were adapted to new module tables:

- `coupons` -> `website_v2_coupons`
- `carts` -> `website_v2_carts`
- `cart_items` -> `website_v2_cart_items`
- `wp_settings` -> `website_v2_settings`
- `wp_banners` -> `website_v2_banners`
- `footer_columns` -> `website_v2_footer_columns`
- `footer_links` -> `website_v2_footer_links`
- `social_links` -> `website_v2_social_links`
- `wp_flash_sales` -> `website_v2_flash_sales`
- `wp_flash_sale_items` -> `website_v2_flash_sale_items`
- `newsletters` -> `website_v2_newsletters`
- `wishlists` -> `website_v2_wishlists`
- `affiliate_levels` -> `website_v2_affiliate_levels`
- `wp_affiliate_schemes` -> `website_v2_affiliate_schemes`
- `wp_tags` -> `website_v2_tags`
- `reviews` -> `website_v2_reviews`

## Seeder

`Modules\WebsiteV2\Database\Seeders\WebsiteV2Seeder` seeds:

- Required permissions
- Required header/footer settings
- Demo banner, coupon, footer link, social link, affiliate level, flash sale shell, and newsletter

Demo data is scoped to `website_v2_*` tables and safe to rerun.

## Import / Export

Coupon import/export is represented by `Modules\WebsiteV2\Services\ImportExport`, keeping import/export outside controllers and Livewire.
