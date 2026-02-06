<?php

declare(strict_types=1);

use App\Controllers\Frontend\HomeController;
use App\Controllers\Frontend\CategoryController;
use App\Controllers\Frontend\ProductController;
use App\Controllers\Frontend\CartController;
use App\Controllers\Frontend\CheckoutController;
use App\Controllers\Frontend\ContactController;
use App\Controllers\Frontend\PageController;
use App\Controllers\Frontend\SearchController;
use App\Controllers\Frontend\UserAuthController;
use App\Controllers\Frontend\AccountController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CategoriesController;
use App\Controllers\Admin\ProductsController;
use App\Controllers\Admin\OrdersController;
use App\Controllers\Admin\ContactMessagesController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\CustomersController;
use App\Controllers\Admin\CouponsController;
use App\Controllers\Admin\ReportsController;
use App\Controllers\Admin\PagesController;
use App\Controllers\Admin\SlidersController;
use App\Controllers\Admin\AttributesController;

/**
 * Rota tanımları: URI deseni => [Controller sınıfı, metot adı, middleware listesi (isteğe bağlı)]
 * :slug = dinamik parça (kategori/ürün slug)
 */
return [
    // Mağaza
    '/' => [HomeController::class, 'index', []],
    '/kategori/:slug' => [CategoryController::class, 'show', []],
    '/urun/:slug' => [ProductController::class, 'show', []],
    '/sepet' => [CartController::class, 'index', []],
    '/sepet/ekle' => [CartController::class, 'add', []],
    '/sepet/guncelle' => [CartController::class, 'update', []],
    '/sepet/cekmece' => [CartController::class, 'drawerData', []],
    '/sepet/sil' => [CartController::class, 'remove', []],
    '/odeme' => [CheckoutController::class, 'index', []],
    '/odeme/tamamlandi' => [CheckoutController::class, 'success', []],
    '/iletisim' => [ContactController::class, 'index', []],
    '/siparis-takip' => [PageController::class, 'trackOrder', []],
    '/hakkimizda' => [PageController::class, 'about', []],
    '/sss' => [PageController::class, 'faq', []],
    '/sayfa/:slug' => [PageController::class, 'showBySlug', []],
    '/arama/suggest' => [SearchController::class, 'suggest', []],
    '/arama' => [SearchController::class, 'index', []],

    // Üye: kayıt, giriş, çıkış, şifremi unuttum, şifre sıfırla
    '/kayit' => [UserAuthController::class, 'registerForm', []],
    '/giris' => [UserAuthController::class, 'loginForm', []],
    '/cikis' => [UserAuthController::class, 'logout', []],
    '/sifremi-unuttum' => [UserAuthController::class, 'forgotPasswordForm', []],
    '/sifre-sifirla' => [UserAuthController::class, 'resetPasswordForm', []],

    // Hesabım (giriş gerekli)
    '/hesabim' => [AccountController::class, 'index', ['user']],
    '/hesabim/siparisler' => [AccountController::class, 'orders', ['user']],
    '/hesabim/siparisler/show' => [AccountController::class, 'orderShow', ['user']],
    '/hesabim/adresler' => [AccountController::class, 'addresses', ['user']],
    '/hesabim/adresler/ekle' => [AccountController::class, 'addressCreate', ['user']],
    '/hesabim/adresler/duzenle' => [AccountController::class, 'addressEdit', ['user']],
    '/hesabim/adresler/sil' => [AccountController::class, 'addressDelete', ['user']],
    '/hesabim/bilgilerim' => [AccountController::class, 'profile', ['user']],
    '/hesabim/favoriler' => [AccountController::class, 'favoriler', ['user']],
    '/favori/ekle' => [AccountController::class, 'wishlistAdd', ['user']],
    '/favori/sil' => [AccountController::class, 'wishlistRemove', ['user']],

    // Yönetim paneli
    '/admin' => [DashboardController::class, 'index', ['admin']],
    '/admin/login' => [AuthController::class, 'login', []],
    '/admin/logout' => [AuthController::class, 'logout', []],
    '/admin/categories' => [CategoriesController::class, 'index', ['admin']],
    '/admin/categories/create' => [CategoriesController::class, 'create', ['admin']],
    '/admin/categories/edit' => [CategoriesController::class, 'edit', ['admin']],
    '/admin/categories/delete' => [CategoriesController::class, 'delete', ['admin']],
    '/admin/products' => [ProductsController::class, 'index', ['admin']],
    '/admin/products/create' => [ProductsController::class, 'create', ['admin']],
    '/admin/products/edit' => [ProductsController::class, 'edit', ['admin']],
    '/admin/products/delete' => [ProductsController::class, 'delete', ['admin']],
    '/admin/products/delete-image' => [ProductsController::class, 'deleteImage', ['admin']],
    '/admin/products/add-variant' => [ProductsController::class, 'addVariant', ['admin']],
    '/admin/products/delete-variant' => [ProductsController::class, 'deleteVariant', ['admin']],
    '/admin/orders' => [OrdersController::class, 'index', ['admin']],
    '/admin/orders/show' => [OrdersController::class, 'show', ['admin']],
    '/admin/orders/print' => [OrdersController::class, 'print', ['admin']],
    '/admin/orders/update-status' => [OrdersController::class, 'updateStatus', ['admin']],
    '/admin/orders/add-shipment' => [OrdersController::class, 'addShipment', ['admin']],
    '/admin/contact-messages' => [ContactMessagesController::class, 'index', ['admin']],
    '/admin/contact-messages/show' => [ContactMessagesController::class, 'show', ['admin']],
    '/admin/settings' => [SettingsController::class, 'index', ['admin']],
    '/admin/customers' => [CustomersController::class, 'index', ['admin']],
    '/admin/customers/show' => [CustomersController::class, 'show', ['admin']],
    '/admin/coupons' => [CouponsController::class, 'index', ['admin']],
    '/admin/coupons/create' => [CouponsController::class, 'create', ['admin']],
    '/admin/coupons/edit' => [CouponsController::class, 'edit', ['admin']],
    '/admin/coupons/delete' => [CouponsController::class, 'delete', ['admin']],
    '/admin/reports' => [ReportsController::class, 'index', ['admin']],
    '/admin/reports/sales' => [ReportsController::class, 'sales', ['admin']],
    '/admin/reports/stock' => [ReportsController::class, 'stock', ['admin']],
    '/admin/pages' => [PagesController::class, 'index', ['admin']],
    '/admin/pages/create' => [PagesController::class, 'create', ['admin']],
    '/admin/pages/edit' => [PagesController::class, 'edit', ['admin']],
    '/admin/pages/delete' => [PagesController::class, 'delete', ['admin']],
    '/admin/sliders' => [SlidersController::class, 'index', ['admin']],
    '/admin/sliders/create' => [SlidersController::class, 'create', ['admin']],
    '/admin/sliders/edit' => [SlidersController::class, 'edit', ['admin']],
    '/admin/sliders/delete' => [SlidersController::class, 'delete', ['admin']],
    '/admin/attributes' => [AttributesController::class, 'index', ['admin']],
    '/admin/attributes/create' => [AttributesController::class, 'create', ['admin']],
    '/admin/attributes/edit' => [AttributesController::class, 'edit', ['admin']],
    '/admin/attributes/delete' => [AttributesController::class, 'delete', ['admin']],
    '/admin/attributes/delete-value' => [AttributesController::class, 'deleteValue', ['admin']],
];
