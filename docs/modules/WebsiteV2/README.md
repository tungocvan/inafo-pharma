# WebsiteV2 Module

WebsiteV2 is an independent storefront module rebuilt from `docs/modules/Website` documentation.

It uses:

- Route names prefixed with `website-v2.`
- View namespace `website-v2::`
- Permissions `website-v2.view`, `website-v2.create`, `website-v2.edit`, `website-v2.delete`
- Cache keys prefixed with `website_v2.`
- Storage path `storage/app/website-v2`
- New module-owned tables prefixed with `website_v2_`

## Seeder

Run demo data with:

```bash
php artisan db:seed --class=Modules\\WebsiteV2\\Database\\Seeders\\WebsiteV2Seeder
```

The seeder uses `updateOrCreate` / `firstOrCreate` style operations for permissions, settings, demo banners, coupons, footer links, affiliate level, flash sale shell, and newsletter records. It does not write into Website source tables.

## Shared Boundaries

WebsiteV2 references canonical shared tables such as `users` and Product module `wp_products` for user/product relationships. It does not copy the old Website nested System/Auth/Chat services.
