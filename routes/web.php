<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacultyRequestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplyOperationsController;
use App\Http\Controllers\SupplyStudentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified', 'active', 'session.timeout'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('inventory', InventoryController::class)
        ->only(['index', 'show'])
        ->middleware('role:Administrator|Admission|Accounting|Supply Personnel|Faculty');

    Route::resource('inventory', InventoryController::class)
        ->only(['create', 'store', 'edit', 'update'])
        ->middleware('role:Administrator|Supply Personnel');

    Route::prefix('requests')->name('requests.')->middleware('role:Faculty')->group(function () {
        Route::get('/', [FacultyRequestController::class, 'index'])->name('index');
        Route::get('/create', [FacultyRequestController::class, 'create'])->name('create');
        Route::post('/', [FacultyRequestController::class, 'store'])->name('store');
        Route::get('/{request}', [FacultyRequestController::class, 'show'])->name('show');
        Route::post('/{request}/cancel', [FacultyRequestController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('shop')->name('shop.')->middleware('role:Student')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::post('/cart', [ShopController::class, 'addToCart'])->name('cart.add');
        Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
        Route::delete('/cart/{lineKey}', [ShopController::class, 'removeFromCart'])
            ->where('lineKey', '[0-9]+_[A-Za-z0-9]+')
            ->name('cart.remove');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->middleware('role:Student')->name('index');
        Route::post('/checkout', [PurchaseController::class, 'checkout'])->middleware('role:Student')->name('checkout');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/payment-slip', [PurchaseController::class, 'paymentSlip'])->name('payment-slip');
        Route::post('/{purchase}/receipt', [PurchaseController::class, 'uploadReceipt'])->middleware('role:Student')->name('receipt.upload');
    });

    Route::prefix('accounting')->name('accounting.')->middleware('role:Accounting|Administrator')->group(function () {
        Route::get('/requests', [AccountingController::class, 'requests'])->name('requests');
        Route::get('/requests/{request}', [AccountingController::class, 'showRequest'])->name('requests.show');
        Route::post('/requests/{request}/review', [AccountingController::class, 'reviewRequest'])->name('requests.review');
        Route::get('/payments', [AccountingController::class, 'payments'])->name('payments');
        Route::get('/payments/{purchase}', [AccountingController::class, 'showPayment'])->name('payments.show');
        Route::post('/payments/{purchase}/verify', [AccountingController::class, 'verifyPayment'])->name('payments.verify');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('role:Administrator|Admission')->group(function () {
            Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests');
            Route::get('/requests/{request}', [AdminRequestController::class, 'show'])->name('requests.show');
            Route::post('/requests/{request}/approve', [AdminRequestController::class, 'approve'])->name('requests.approve');
            Route::post('/requests/{request}/reject', [AdminRequestController::class, 'reject'])->name('requests.reject');
        });

        Route::middleware('role:Administrator|Supply Personnel')->group(function () {
            Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
            Route::get('/audit-logs', [AdminUserController::class, 'auditLogs'])->name('audit-logs');

            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        });
    });

    Route::prefix('supply')->name('supply.')->middleware('role:Supply Personnel|Administrator')->group(function () {
        Route::get('/stock', [SupplyOperationsController::class, 'stockIndex'])->name('stock.index');
        Route::post('/stock-in', [SupplyOperationsController::class, 'stockIn'])->name('stock.in');
        Route::post('/adjust', [SupplyOperationsController::class, 'adjust'])->name('stock.adjust');

        Route::get('/releases', [SupplyOperationsController::class, 'releases'])->name('releases');
        Route::get('/releases/{request}', [SupplyOperationsController::class, 'showRelease'])->name('releases.show');
        Route::post('/releases/{request}', [SupplyOperationsController::class, 'releaseRequest'])->name('releases.release');

        Route::get('/purchases', [SupplyOperationsController::class, 'purchases'])->name('purchases');
        Route::get('/purchases/{purchase}', [SupplyOperationsController::class, 'showPurchase'])->name('purchases.show');
        Route::post('/purchases/{purchase}/release', [SupplyOperationsController::class, 'releasePurchase'])->name('purchases.release');

        Route::get('/students', [SupplyStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [SupplyStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [SupplyStudentController::class, 'store'])->name('students.store');
        Route::get('/students/import', [SupplyStudentController::class, 'importForm'])->name('students.import');
        Route::post('/students/import', [SupplyStudentController::class, 'import'])->name('students.import.store');
        Route::get('/students/template', [SupplyStudentController::class, 'template'])->name('students.template');
        Route::get('/students/{student}/edit', [SupplyStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [SupplyStudentController::class, 'update'])->name('students.update');
    });

    Route::prefix('reports')->name('reports.')->middleware('role:Administrator|Accounting|Supply Personnel')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/low-stock/pdf', [ReportController::class, 'lowStockPdf'])->name('low-stock.pdf');
        Route::get('/valuation/pdf', [ReportController::class, 'inventoryValuationPdf'])->name('valuation.pdf');
        Route::get('/faculty-requests', [ReportController::class, 'facultyRequests'])->name('faculty-requests');
        Route::get('/daily-inventory/pdf', [ReportController::class, 'dailyInventory'])->name('daily-inventory.pdf');
        Route::get('/monthly-inventory/pdf', [ReportController::class, 'monthlyInventory'])->name('monthly-inventory.pdf');
        Route::get('/out-of-stock/pdf', [ReportController::class, 'outOfStockPdf'])->name('out-of-stock.pdf');
        Route::get('/student-purchases', [ReportController::class, 'studentPurchases'])->name('student-purchases');
        Route::get('/audit-trail/pdf', [ReportController::class, 'auditTrailPdf'])->name('audit-trail.pdf');
        Route::get('/transactions/excel', [ReportController::class, 'transactionsExcel'])->name('transactions.excel');
    });

    Route::get('/ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/ask', [AiAssistantController::class, 'ask'])->name('ai.ask');
    Route::get('/ai/restock', [AiAssistantController::class, 'restock'])
        ->middleware('role:Supply Personnel|Administrator')
        ->name('ai.restock');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
