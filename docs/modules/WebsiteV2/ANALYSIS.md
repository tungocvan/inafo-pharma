# WebsiteV2 Module Analysis

## Module Purpose

WebsiteV2 is a rebuilt Website module that keeps the storefront surface but avoids old Website issues documented in the source analysis: duplicated services, unsafe cart ownership updates, unscoped admin routes, direct Livewire persistence, and malformed migration names.

## Routes

Frontend route group:

- Prefix: `/website-v2`
- Names: `website-v2.*`
- View namespace: `website-v2::`

Admin route group:

- Prefix: `/admin/website-v2`
- Names: `website-v2.admin.*`
- Middleware: `web`, `auth:admin`, `permission:website-v2.view`

API route group:

- Prefix: `/api/website-v2`
- Names: `website-v2.api.*`

## Controllers

Controllers are thin and return views or scalar route parameters only:

- `AuthController`
- `WebsiteController`
- `ProductController`
- `PostController`
- `CartController`
- `CheckoutController`
- `AccountController`
- Admin controllers for affiliate, home, header, footer, banner, flash sale, coupon, and customer pages

## Services

Services own query and persistence behavior:

- `SettingsService`
- `HeaderMenuService`
- `FooterService`
- `BannerService`
- `FlashSaleService`
- `ProductService`
- `ContentService`
- `CartService`
- `CheckoutService`
- `ImportExport`
- `WishlistService`
- `AffiliateService`
- `AdminAffiliateService`
- `MarketingService`
- `Account/ProfileService`
- `Account/AddressService`

## Database

All module-owned tables use the `website_v2_` prefix. Migration filenames are regenerated with valid 2026 timestamps and do not reuse Website malformed negative-year timestamps.

## Exclusions

The old nested `Modules/Website/Services/Services/*` System/Auth/Chat/database/env services were intentionally not rebuilt because the Website documentation flags them as duplicate or unsafe.
