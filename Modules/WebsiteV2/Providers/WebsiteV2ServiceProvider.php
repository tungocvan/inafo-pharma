<?php

namespace Modules\WebsiteV2\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\WebsiteV2\Livewire\Account\OrderDetail;
use Modules\WebsiteV2\Livewire\Account\OrderList;
use Modules\WebsiteV2\Livewire\Auth\LoginForm;
use Modules\WebsiteV2\Livewire\Auth\RegisterForm;
use Modules\WebsiteV2\Livewire\Cart\CartList;
use Modules\WebsiteV2\Livewire\Checkout\CheckoutForm;
use Modules\WebsiteV2\Livewire\Checkout\OrderSummary;
use Modules\WebsiteV2\Livewire\Home\BestSellers;
use Modules\WebsiteV2\Livewire\Home\BlogHighlight;
use Modules\WebsiteV2\Livewire\Home\CategoryHighlight;
use Modules\WebsiteV2\Livewire\Home\FeaturedProducts;
use Modules\WebsiteV2\Livewire\Home\FlashSale;
use Modules\WebsiteV2\Livewire\Home\HeroBanner;
use Modules\WebsiteV2\Livewire\Home\HomeList;
use Modules\WebsiteV2\Livewire\Home\NewsletterSignup;
use Modules\WebsiteV2\Livewire\Home\NewArrivals;
use Modules\WebsiteV2\Livewire\Home\PromoBanner;
use Modules\WebsiteV2\Livewire\Home\TrustBadges;
use Modules\WebsiteV2\Livewire\Post\PostDetail;
use Modules\WebsiteV2\Livewire\Post\PostList;
use Modules\WebsiteV2\Livewire\Products\ProductDetail as ProductDetailComponent;
use Modules\WebsiteV2\Livewire\Products\ProductList;
use Modules\WebsiteV2\Services\FooterService;
use Modules\WebsiteV2\Services\HeaderMenuService;
use Modules\WebsiteV2\Services\SettingsService;

class WebsiteV2ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/website-v2.php', 'website-v2');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'website-v2');
        Livewire::component('website-v2.account.order-list', OrderList::class);
        Livewire::component('website-v2.account.order-detail', OrderDetail::class);
        Livewire::component('website-v2.auth.login-form', LoginForm::class);
        Livewire::component('website-v2.auth.register-form', RegisterForm::class);
        Livewire::component('website-v2.cart.cart-list', CartList::class);
        Livewire::component('website-v2.checkout.checkout-form', CheckoutForm::class);
        Livewire::component('website-v2.checkout.order-summary', OrderSummary::class);
        Livewire::component('website-v2.home.home-list', HomeList::class);
        Livewire::component('website-v2.home.hero-banner', HeroBanner::class);
        Livewire::component('website-v2.home.category-highlight', CategoryHighlight::class);
        Livewire::component('website-v2.home.flash-sale', FlashSale::class);
        Livewire::component('website-v2.home.promo-banner', PromoBanner::class);
        Livewire::component('website-v2.home.featured-products', FeaturedProducts::class);
        Livewire::component('website-v2.home.new-arrivals', NewArrivals::class);
        Livewire::component('website-v2.home.best-sellers', BestSellers::class);
        Livewire::component('website-v2.home.trust-badges', TrustBadges::class);
        Livewire::component('website-v2.home.blog-highlight', BlogHighlight::class);
        Livewire::component('website-v2.home.newsletter-signup', NewsletterSignup::class);
        Livewire::component('website-v2.post.post-list', PostList::class);
        Livewire::component('website-v2.post.post-detail', PostDetail::class);
        Livewire::component('website-v2.products.product-detail', ProductDetailComponent::class);
        Livewire::component('website-v2.products.product-list', ProductList::class);

        View::composer(['website-v2::partials.header', 'website-v2::layouts.frontend'], function ($view) {
            $settings = app(SettingsService::class);
            $menus = app(HeaderMenuService::class);

            $view->with([
                'mainMenu' => $menus->getMenuTreeByLocation('primary'),
                'mobileMenu' => $menus->getMenuTreeByLocation('mobile'),
                'headerSettings' => [
                    'hotline' => $settings->get('header.topbar.hotline', '0903 971 949'),
                    'email' => $settings->get('header.topbar.email', 'contact@inafo.vn'),
                    'brand_name' => $settings->get('header.brand_name', 'INAFO Pharma V2'),
                    'help_url' => $settings->get('header.topbar.help_url', route('website-v2.help', absolute: false)),
                    'order_tracking_url' => $settings->get('header.topbar.order_tracking_url', route('website-v2.account.orders', absolute: false)),
                ],
            ]);
        });

        View::composer('website-v2::partials.footer', function ($view) {
            $settings = app(SettingsService::class);
            $footer = app(FooterService::class);

            $view->with([
                'footerColumns' => $footer->getColumnsForFrontend(),
                'socialLinks' => $footer->getSocialLinks(),
                'footerSettings' => [
                    'description' => $settings->get('footer.brand_description'),
                    'address' => $settings->get('footer.address'),
                    'email' => $settings->get('footer.email'),
                    'phone' => $settings->get('footer.phone'),
                    'copyright' => $settings->get('footer.copyright'),
                    'appstore' => $settings->get('footer.appstore_url'),
                    'playstore' => $settings->get('footer.playstore_url'),
                ],
            ]);
        });
    }
}
