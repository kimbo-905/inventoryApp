<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\StockMovementController;
use App\Http\Controllers\API\PurchaseRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::get('/achats/{id}/pdf', [PurchaseRequestController::class, 'generatePdf']); //////////

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/export-stock/{type}', [StockMovementController::class, 'export']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-movements', StockMovementController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/achat', [PurchaseRequestController::class, 'index']);
    Route::post('/achat', [PurchaseRequestController::class, 'store']);
    Route::get('/achat/{id}', [PurchaseRequestController::class, 'show']);
    Route::delete('/achat/{id}', [PurchaseRequestController::class, 'destroy']);
    Route::post('/achats/{id}/accuse-staff', [PurchaseRequestController::class, 'accuseReceptionByStaff']);
    // Route::get('/achats/{id}/pdf', [PurchaseRequestController::class, 'generatePdf']);
   
    // Admin-only routes
    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin-dashboard', [AdminController::class, 'index']);
        Route::apiResource('/users', UserController::class);
        Route::post('/users/signature', [UserController::class, 'uploadSignature']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/roles', [RoleController::class, 'index']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::get('/achats', [PurchaseRequestController::class, 'index']);
        Route::post('/achats/{id}/valider', [PurchaseRequestController::class, 'approve']);
        Route::post('/achats/{id}/reception', [PurchaseRequestController::class, 'receptionner']);
        Route::post('/achats/{id}/annuler', [PurchaseRequestController::class, 'cancelValidation']);
        Route::get('/achats/summary/monthly', [PurchaseRequestController::class, 'monthlySummary']);

    });

});
