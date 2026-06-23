# WebsiteV2 Refactor Plan

## P0

- Add method-level authorization to future mutating Livewire components before introducing them.
- Add feature tests for admin permission middleware and cart item ownership.
- Confirm service provider registration path in the host application bootstrap before enabling in production.

## P1

- Expand ProductService and ContentService integrations after canonical Product/Post query contracts are confirmed.
- Add checkout order creation once canonical Order ownership is settled.
- Connect coupon import/export UI to the shared import/export foundation.
- Add migrations for WebsiteV2-specific header menu tables if the menu becomes persistent rather than config/service generated.

## P2

- Add cached homepage marketing aggregates with explicit invalidation.
- Add full admin CRUD Livewire components using the WebsiteV2 services.
- Add queued export support for large coupon/customer datasets.
