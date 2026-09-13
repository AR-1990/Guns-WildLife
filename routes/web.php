<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCashbookController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDiaryController;
use App\Http\Controllers\AdminExpenseController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminSaleController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\WebsiteContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

collect([
    '/' => ['website.index', 'home'],
    '/about' => ['website.about', 'about'],
    '/account' => ['website.account', 'account'],
    '/become-a-dealer' => ['website.become-a-dealer', 'become-a-dealer'],
    '/dealer-form' => ['website.become-a-dealer', 'dealer.form'],
    '/faq' => ['website.faq', 'faq'],
    '/find-a-dealer' => ['website.find-a-dealer', 'find-a-dealer'],
    '/login' => ['website.login', 'login'],
    '/product-detail' => ['website.product-detail', 'product.detail'],
    '/register' => ['website.register', 'register'],
    '/reset-password' => ['website.reset-password', 'password.reset'],
    '/shop' => ['website.shop', 'shop'],
    '/wishlist' => ['website.wishlist', 'wishlist'],
    '/checkout' => ['website.step-1', 'checkout'],
    '/checkout/review' => ['website.step-2', 'checkout.review'],
    '/payment' => ['website.step-3', 'payment'],
])->each(fn (array $page, string $uri) => Route::view($uri, $page[0])->name($page[1]));

Route::get('/contact', [WebsiteContactController::class, 'create'])->name('contact');
Route::post('/contact', [WebsiteContactController::class, 'store'])->name('contact.store');

collect([
    '/step-1' => '/checkout',
    '/step-2' => '/checkout/review',
    '/step-3' => '/payment',
])->each(fn (string $to, string $from) => Route::redirect($from, $to));

Route::post('/newsletter/subscribe', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);

    return back()->with('newsletter_success', 'Thanks for subscribing!');
})->name('newsletter.subscribe');

Route::post('/login', fn () => back()->with('status', 'Login request received.'))->name('login.post');
Route::post('/register', fn () => back()->with('status', 'Registration request received.'))->name('register.post');
Route::post('/reset-password', fn () => back()->with('status', 'Password reset request received.'))->name('password.update');
Route::post('/checkout', fn () => redirect()->route('checkout.review'))->name('checkout.submit');
Route::post('/order', fn () => back()->with('status', 'Order placed successfully.'))->name('order.place');
Route::post('/dealer-request', [WebsiteContactController::class, 'dealerRequest'])->name('dealer.request');
Route::put('/account', fn () => back()->with('status', 'Account details updated successfully.'))->name('account.update');
Route::post('/account/addresses', fn () => back()->with('status', 'Address saved successfully.'))->name('address.store');
Route::put('/account/addresses', fn () => back()->with('status', 'Address updated successfully.'))->name('address.update');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
        Route::view('/register', 'admin.auth.register')->name('register');
        Route::view('/forgot-password', 'admin.auth.forgot-password')->name('password.request');
        Route::post('/register', fn () => back()->with('status', 'Registration is disabled.'))->name('register.store');
        Route::post('/forgot-password', fn () => back()->with('status', 'Password reset flow is not configured yet.'))->name('password.email');
    });

    Route::post('/logout', [AdminAuthController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'role:admin,salesman'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports/sales', [AdminSaleController::class, 'report'])->name('reports.sales');
        Route::get('/reports/sales/csv', [AdminSaleController::class, 'exportReportCsv'])->name('reports.sales.csv');
        Route::get('/accounts/expenses', [AdminExpenseController::class, 'index'])->name('accounts.expenses');
        Route::get('/accounts/expenses/csv', [AdminExpenseController::class, 'exportCsv'])->name('accounts.expenses.csv');
        Route::get('/accounts/expenses/create', [AdminExpenseController::class, 'create'])->name('accounts.expenses.create');
        Route::post('/accounts/expenses', [AdminExpenseController::class, 'store'])->name('accounts.expenses.store');
        Route::get('/accounts/expenses/{expense}/edit', [AdminExpenseController::class, 'edit'])->name('accounts.expenses.edit');
        Route::put('/accounts/expenses/{expense}', [AdminExpenseController::class, 'update'])->name('accounts.expenses.update');
        Route::delete('/accounts/expenses/{expense}', [AdminExpenseController::class, 'destroy'])->name('accounts.expenses.destroy');
        Route::get('/my-diary', [AdminDiaryController::class, 'index'])->name('diary.index');
        Route::get('/my-diary/create', [AdminDiaryController::class, 'create'])->name('diary.create');
        Route::post('/my-diary', [AdminDiaryController::class, 'store'])->name('diary.store');
        Route::get('/my-diary/{diaryContact}/edit', [AdminDiaryController::class, 'edit'])->name('diary.edit');
        Route::put('/my-diary/{diaryContact}', [AdminDiaryController::class, 'update'])->name('diary.update');
        Route::delete('/my-diary/{diaryContact}', [AdminDiaryController::class, 'destroy'])->name('diary.destroy');
        Route::get('/sales', [AdminSaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/export/csv', [AdminSaleController::class, 'exportCsv'])->name('sales.export.csv');
        Route::get('/sales/{sale}/pdf', [AdminSaleController::class, 'downloadReceiptPdf'])->name('sales.pdf');
        Route::get('/sales/{sale}/print', [AdminSaleController::class, 'printReceipt'])->name('sales.print');
        Route::get('/sales/{sale}', [AdminSaleController::class, 'show'])->name('sales.show');
        Route::post('/sales', [AdminSaleController::class, 'store'])->name('sales.store');
        Route::put('/sales/{sale}', [AdminSaleController::class, 'update'])->name('sales.update');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/accounts/cashbook', [AdminCashbookController::class, 'index'])->name('accounts.cashbook');
        Route::get('/accounts/cashbook/csv', [AdminCashbookController::class, 'exportCsv'])->name('accounts.cashbook.csv');
        Route::get('/accounts/expense-categories', [AdminExpenseController::class, 'categories'])->name('accounts.expense-categories.index');
        Route::post('/accounts/expense-categories', [AdminExpenseController::class, 'storeCategory'])->name('accounts.expense-categories.store');
        Route::put('/accounts/expense-categories/{expenseCategory}', [AdminExpenseController::class, 'updateCategory'])->name('accounts.expense-categories.update');
        Route::patch('/accounts/expense-categories/{expenseCategory}/toggle', [AdminExpenseController::class, 'toggleCategory'])->name('accounts.expense-categories.toggle');
        Route::get('/accounts/loan-advance', [AdminAccountController::class, 'loanAdvance'])->name('accounts.loan-advance');
        Route::get('/accounts/users', [AdminUserController::class, 'index'])->name('accounts.users');
        Route::get('/accounts/users/create', [AdminUserController::class, 'create'])->name('accounts.users.create');
        Route::post('/accounts/users', [AdminUserController::class, 'store'])->name('accounts.users.store');
        Route::get('/accounts/users/{user}/edit', [AdminUserController::class, 'edit'])->name('accounts.users.edit');
        Route::put('/accounts/users/{user}', [AdminUserController::class, 'update'])->name('accounts.users.update');
        // Partner investment Assignments flow retired — Khata + assignment UI removed.
        // Route::get('/accounts/users/{user}/assignments', [AdminPartnerAssignmentController::class, 'edit'])->name('accounts.users.assignments.edit');
        // Route::post('/accounts/users/{user}/assignments', [AdminPartnerAssignmentController::class, 'store'])->name('accounts.users.assignments.store');
        // Route::put('/accounts/users/{user}/assignments/{investmentEntry}', [AdminPartnerAssignmentController::class, 'update'])->name('accounts.users.assignments.update');
        // Route::delete('/accounts/users/{user}/assignments/{investmentEntry}', [AdminPartnerAssignmentController::class, 'destroy'])->name('accounts.users.assignments.destroy');
        Route::delete('/accounts/users/{user}', [AdminUserController::class, 'destroy'])->name('accounts.users.destroy');
        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [AdminContactController::class, 'show'])->name('contacts.show');
        Route::delete('/contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/restock', [AdminProductController::class, 'createRestock'])->name('products.restock.create');
        Route::post('/products/{product}/restock', [AdminProductController::class, 'storeRestock'])->name('products.restock.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle', [AdminProductController::class, 'toggleProduct'])->name('products.toggle');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/categories', [AdminProductController::class, 'storeCategory'])->name('products.categories.store');
        Route::put('/products/categories/{category}', [AdminProductController::class, 'updateCategory'])->name('products.categories.update');
        Route::patch('/products/categories/{category}/toggle', [AdminProductController::class, 'toggleCategory'])->name('products.categories.toggle');
        Route::delete('/products/categories/{category}', [AdminProductController::class, 'destroyCategory'])->name('products.categories.destroy');
        Route::delete('/sales/{sale}', [AdminSaleController::class, 'destroy'])->name('sales.destroy');
        // Partner profit report routes retired (redirects to revenue report):
        // Route::get('/reports/profit', [AdminSaleController::class, 'profitReport'])->name('reports.profit');
        // Route::get('/reports/profit/csv', [AdminSaleController::class, 'exportProfitReportCsv'])->name('reports.profit.csv');
        Route::get('/reports/revenue', [AdminAccountController::class, 'revenueReport'])->name('reports.revenue');
        Route::get('/reports/revenue/csv', [AdminAccountController::class, 'exportRevenueCsv'])->name('reports.revenue.csv');
        Route::get('/reports/loss', [AdminAccountController::class, 'lossReport'])->name('reports.loss');
        Route::get('/reports/loss/csv', [AdminAccountController::class, 'exportLossCsv'])->name('reports.loss.csv');
        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    });

    Route::redirect('/accounts/cashbook/transactions/create', '/admin/accounts/cashbook')->name('cashbook.transactions.create');
});
