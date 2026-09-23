<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Frontend Auth
Route::get('/login', [AuthController::class, 'loginView'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::get('/register', [AuthController::class, 'registerView'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('/auth/google/redirect', [\App\Http\Controllers\SocialiteController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\SocialiteController::class, 'callback'])->name('google.callback');

// OTP Verification
Route::post('/otp/send', [\App\Http\Controllers\OtpController::class, 'send'])->name('otp.send');
Route::post('/otp/verify', [\App\Http\Controllers\OtpController::class, 'verify'])->name('otp.verify');

// Forgot & Reset Password via OTP
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordView'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->middleware('throttle:3,10')->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'resetPasswordView'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:10,1')->name('password.update');
});

Route::get('/', function () {
    $settingsRow = \App\Models\Setting::first();
    $featuredLimit = $settingsRow?->featured_limit ?? 8;
    $trendingLimit = $settingsRow?->trending_limit ?? 8;

    $categories = Category::where('status', 'active')
        ->get();

    // Check parent category and subcategory statuses
    $featuredProducts = Product::where('status', 'active')
        ->where('is_featured', true)
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        })
        ->orderBy('name')
        ->limit($featuredLimit)
        ->get();

    $trendingProducts = Product::where('status', 'active')
        ->where('is_trending', true)
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        })
        ->orderBy('name')
        ->limit($trendingLimit)
        ->get();

    $allProducts = Product::where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        })
        ->get();

    $banners = Banner::active()->ordered()->get();

    return view('front.index', compact('categories', 'featuredProducts', 'trendingProducts', 'allProducts', 'banners'));
});

Route::get('/product-detail/{slug}', function ($slug) {
    // Only fetch product if active AND its category and subcategory are active
    $product = Product::where('slug', $slug)
        ->where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        })
        ->firstOrFail();

    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        })
        ->limit(4)
        ->get();

    $productReviews = $product->reviews()->where('is_approved', true)->with('user')->latest()->get();

    return view('front.product-detail', compact('product', 'relatedProducts', 'productReviews'));
})->name('product_detail');

Route::get('/about', function () {
    return view('front.about');
})->name('about_us');

Route::get('/product', function (Request $request) {
    $query = Product::with(['category', 'subCategory'])
        ->where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->where(function ($q) {
            $q->whereNull('sub_category_id')
                ->orWhereHas('subCategory', function ($sq) {
                    $sq->where('status', 'active');
                });
        });

    if ($request->get('filter') === 'new_arrivals') {
        $query->where('is_new_arrival', true);
    }

    if ($search = $request->get('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $products = $query->get();

    $maxPrice = 5000;
    if ($products->isNotEmpty()) {
        $maxPrice = $products->max(function ($product) {
            return (int) ($product->sale_price ?? $product->price);
        });
        $maxPrice = max($maxPrice, 5000);
    }

    $filterData = [
        'purities' => $products->pluck('pattern')->filter()->unique()->values(),
        'occasions' => $products->pluck('occasion')->filter()->unique()->values(),
        'metals' => $products->pluck('fabric')->filter()->unique()->values(),
        'gemstones' => $products->pluck('neckline')->filter()->unique()->values(),
        'sizes' => $products->pluck('size')->filter()->unique()->values(),
        'colors' => $products->pluck('color')->filter()->unique()->values(),
    ];

    return view('front.products', compact('products', 'maxPrice', 'filterData'));
})->name('products');

Route::get('/termsandcondition', function () {
    return view('front.terms-and-conditions');
})->name('terms&conditions');

Route::get('/returnandrefund', function () {
    return view('front.return-and-refund');
})->name('return&refund');

Route::get('/privacypolicy', function () {
    return view('front.privacy-policy');
})->name('privacy_policy');

Route::get('/wishlist', function () {
    return view('front.wishlist');
})->name('wishlist');

// Server-side cart: logged-in customers only. The client mirrors its
// localStorage mutations here so the cart follows the user across devices
// and survives cache clears. Guests remain localStorage-only.
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.store');
Route::post('/checkout/verify', [CheckoutController::class, 'verifyPayment'])->name('checkout.verify');
Route::get('/order-success/{id}', [CheckoutController::class, 'success'])->name('order.success');

// Razorpay webhook (CSRF-exempt in bootstrap/app.php; verified by signature)
Route::post('/webhooks/razorpay', [CheckoutController::class, 'webhook'])->name('webhooks.razorpay');

// Customer Account Dashboard & Order Tracking
Route::middleware('auth')->group(function () {
    Route::get('/my-account', [AccountController::class, 'index'])->name('my-account');
    Route::get('/order-detail/{order_number}', [AccountController::class, 'orderDetails'])->name('order.details');

    // Standalone invoice sheet. Kept off the dashboard page so the invoice is
    // only ever produced as a printable/downloadable document.
    Route::get('/order-detail/{order_number}/invoice', [AccountController::class, 'invoice'])->name('order.invoice');
    Route::put('/profile/update', [AccountController::class, 'updateProfile'])->name('profile.update');

    // Reviews — verified purchasers only (server enforces delivered order)
    Route::get('/my-account/review/{orderItemId}', [AccountController::class, 'reviewForm'])->name('review.form');
    Route::post('/my-account/review/{orderItemId}', [AccountController::class, 'storeReview'])->name('review.store');

    // Returns — verified purchasers only (server enforces ownership + window)
    Route::get('/my-account/return/{orderItemId}', [AccountController::class, 'returnForm'])->name('return.form');
    Route::post('/my-account/return/{orderItemId}', [AccountController::class, 'storeReturn'])->name('return.store');
});

Route::get('contact', function () {
    return view('front.contact');
})->name('contact-us');
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('contact.store');

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;

/**
 * Admin guest Route
 * */
Route::prefix('admin')->name('admin.')->group(function () {
    /** owner login — separate controller: only the admin role may pass */
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    // No public registration under /admin — accounts are created on the storefront

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Profile Settings
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('role:admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // User Module
            Route::resource('users', UserController::class);

            // Category Management
            Route::resource('categories', CategoryController::class);

            // Sub-Category Management
            // AJAX: get sub-categories by category (must be before resource to avoid conflict)
            Route::get('sub-categories/by-category/{category_id}', [SubCategoryController::class, 'byCategoryAjax'])
                ->name('sub-categories.by-category');
            Route::resource('sub-categories', SubCategoryController::class);

            // Product Management
            Route::resource('products', ProductController::class);

            // Banner Management
            Route::resource('banners', BannerController::class);

            // Setting Management — a single settings row: the index IS the form.
            // No create/show/delete ceremony for one immutable-per-store row.
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::get('settings/{setting}/edit', [SettingController::class, 'edit'])->name('settings.edit');
            Route::match(['put', 'patch'], 'settings/{setting}', [SettingController::class, 'update'])->name('settings.update');

            // Order Management
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

            // Home Sections (featured / trending control)
            Route::get('home-sections', [\App\Http\Controllers\Admin\HomeSectionController::class, 'edit'])->name('home-sections.edit');
            Route::put('home-sections', [\App\Http\Controllers\Admin\HomeSectionController::class, 'update'])->name('home-sections.update');
            Route::post('home-sections/toggle', [\App\Http\Controllers\Admin\HomeSectionController::class, 'toggleProduct'])->name('home-sections.toggle');

            // Customer Reviews moderation
            Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{review}/approval', [\App\Http\Controllers\Admin\ReviewController::class, 'updateApproval'])->name('reviews.approval');
            Route::delete('reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

            // Return / Refund management
            Route::get('returns', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('returns.index');
            Route::get('returns/{returnRequest}', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'show'])->name('returns.show');
            Route::post('returns/{returnRequest}/status', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'updateStatus'])->name('returns.status');
            Route::put('return-window', [\App\Http\Controllers\Admin\ReturnWindowController::class, 'update'])->name('return-window.update');
        });
    });
});
