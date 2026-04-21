<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController as StaffNotificationController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\SupplyController as AdminSupplyController;
use App\Http\Controllers\Admin\AssetController as AdminAssetController;
use App\Http\Controllers\Admin\RisController as AdminRisController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\RisController as UserRisController;
use App\Http\Controllers\User\IcsController as UserIcsController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\RisController as StaffRisController;
use App\Http\Controllers\Admin\BarcodeController as AdminBarcodeController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\GlobalSearchController as StaffSearchController;
use App\Http\Controllers\Admin\GlobalSearchController as AdminSearchController;
use App\Http\Controllers\PurchaseOrderController;

// --- GUEST ROUTES (Login) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/idle-screen', function () {
    return view('idle');
});

// --- PROTECTED ROUTES (Require Login) ---
Route::middleware('auth')->group(function () {

    // Global Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ==========================================
    // 1. STAFF ROUTES (Personnel)
    // ==========================================
    // Staff Global Search
    Route::get('/global-search', [StaffSearchController::class, 'search'])->name('staff.global.search');
    Route::get('/notifications/fetch', [StaffNotificationController::class, 'fetch'])->name('staff.notifications.fetch');

    // Purchase Orders
    Route::get('/po', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('po.index');
    Route::post('/po', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/po/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('po.show');
    Route::put('/po/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('po.update');
    Route::delete('/po/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('po.destroy');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [\App\Http\Controllers\DashboardController::class, 'getChartData']);

    // Assets
    Route::get('/asset-list', [AssetController::class, 'index']);
    Route::post('/asset-list', [AssetController::class, 'store']);
    Route::put('/asset-list/{id}', [AssetController::class, 'update']);
    Route::delete('/asset-list/{id}', [AssetController::class, 'destroy']);
    Route::post('/asset-list/{id}/transaction', [AssetController::class, 'stockTransaction']);

    // Supplies
    Route::get('/supplies', [SupplyController::class, 'index']);
    Route::post('/supplies', [SupplyController::class, 'store']);
    Route::put('/supplies/{id}', [SupplyController::class, 'update']);
    Route::delete('/supplies/{id}', [SupplyController::class, 'destroy']);
    Route::post('/supplies/{id}/transaction', [SupplyController::class, 'stockTransaction']);
    Route::get('/supplies/{id}/details', [SupplyController::class, 'details']);

    // Barcodes
    Route::get('/barcodes', [BarcodeController::class, 'generator']);
    Route::get('/barcodes/archive', [BarcodeController::class, 'archive']);
    Route::post('/barcodes/generate', [BarcodeController::class, 'store']);
    Route::post('/barcodes/scan', [BarcodeController::class, 'processScan']);
    Route::post('/barcodes/recent-scans', [BarcodeController::class, 'recentScans']);
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);

    // RIS Requests
    Route::get('/ris', [StaffRisController::class, 'index']);
    Route::get('/ris/{id}/review', [StaffRisController::class, 'review']);
    Route::post('/ris/{id}/update', [StaffRisController::class, 'update']);

    // Staff Profile
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // ==========================================
    // 2. ADMIN ROUTES
    // ==========================================
    // Admin Global Search
    Route::get('/admin/global-search', [AdminSearchController::class, 'search'])->name('admin.global.search');
    Route::get('/admin/notifications/fetch', [AdminNotificationController::class, 'fetch'])->name('admin.notifications.fetch');
    Route::get('/admin/dashboard/chart-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('admin.dashboard.chart-data');

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

    // Admin Supplies
    Route::get('/admin/supplies', [AdminSupplyController::class, 'index']);
    Route::post('/admin/supplies', [AdminSupplyController::class, 'store']);
    Route::put('/admin/supplies/{id}', [AdminSupplyController::class, 'update']);
    Route::delete('/admin/supplies/{id}', [AdminSupplyController::class, 'destroy']);
    Route::get('/admin/supplies/{id}/details', [AdminSupplyController::class, 'details']);
    Route::post('/admin/supplies/{id}/transaction', [AdminSupplyController::class, 'stockTransaction']);

    // Admin Assets
    Route::get('/admin/assets', [AdminAssetController::class, 'index']);
    Route::post('/admin/assets', [AdminAssetController::class, 'store']);
    Route::put('/admin/assets/{id}', [AdminAssetController::class, 'update']);
    Route::delete('/admin/assets/{id}', [AdminAssetController::class, 'destroy']);
    Route::get('/admin/assets/{id}/details', [AdminAssetController::class, 'details']);

    // Admin RIS Requests
    Route::get('/admin/ris', [AdminRisController::class, 'index']);
    Route::get('/admin/ris/{id}/review', [AdminRisController::class, 'review']);
    Route::post('/admin/ris/{id}/process', [AdminRisController::class, 'process']);

    // Legacy fallback for view buttons
    Route::get('/admin/requests', [AdminRisController::class, 'index']);

    // Admin Reports
    Route::get('/admin/reports', [ReportController::class, 'index']);
    Route::get('/admin/reports/assets', [ReportController::class, 'assetStocks']);
    Route::get('/admin/reports/supplies', [ReportController::class, 'supplyStocks']);
    Route::get('/admin/reports/ris', [ReportController::class, 'risList']);
    Route::get('/admin/reports/defective', [ReportController::class, 'defectiveAssets']);

    // Admin User Management
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    Route::get('/admin/users/{id}/details', [AdminUserController::class, 'details']);

    Route::get('/admin/barcodes', [AdminBarcodeController::class, 'index']);
    Route::get('/admin/transactions', [AdminTransactionController::class, 'index']);
    Route::delete('/admin/transactions/{id}', [AdminTransactionController::class, 'destroy']);

    // Purchase Orders Admin Routes
    Route::get('/admin/po', [App\Http\Controllers\Admin\PoController::class, 'index']);
    Route::post('/admin/po', [App\Http\Controllers\Admin\PoController::class, 'store']);
    Route::get('/admin/po/{id}', [App\Http\Controllers\Admin\PoController::class, 'show']);
    Route::put('/admin/po/{id}', [App\Http\Controllers\Admin\PoController::class, 'update']);
    Route::delete('/admin/po/{id}', [App\Http\Controllers\Admin\PoController::class, 'destroy']);

    // System Settings
    Route::get('/admin/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index']);
    Route::post('/admin/settings/update', [App\Http\Controllers\Admin\SettingsController::class, 'update']);

    // ==========================================
    // 3. END-USER (DIVISION) ROUTES
    // ==========================================
    Route::get('/user/dashboard', [UserDashboardController::class, 'index']);
    Route::get('/user/supplies', [UserDashboardController::class, 'supplyOverview']);

    // User Notifications
    Route::get('/user/notifications/fetch', [\App\Http\Controllers\User\NotificationController::class, 'fetch'])->name('user.notifications.fetch');

    // Profile Routes
    Route::post('/user/profile', [\App\Http\Controllers\User\ProfileController::class, 'update']);

    // RIS Routes
    Route::get('/user/ris/create', [UserRisController::class, 'create']);
    Route::post('/user/ris', [UserRisController::class, 'store']);
    Route::get('/user/ris/history', [UserRisController::class, 'history']);
    Route::get('/user/ris/{id}', [UserRisController::class, 'show']);
    Route::get('/user/ris/{id}/edit', [UserRisController::class, 'edit']);
    Route::post('/user/ris/{id}/update', [UserRisController::class, 'update']);

    // ICS Routes
    Route::get('/user/ics', [UserIcsController::class, 'create']);
    Route::post('/user/ics', [UserIcsController::class, 'store']);

    // Profile Routes
    Route::get('/user/profile', [UserProfileController::class, 'index']);
});