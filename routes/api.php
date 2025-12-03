<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\DamageController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SupplierController;

Route::apiResource('products', ProductController::class);
Route::get('products-export', [ProductController::class,'exportCsv']); // CSV

Route::apiResource('customers', CustomerController::class);
Route::get('customers-export', [CustomerController::class,'exportCsv']);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('sales', SaleController::class)->only(['index','store','show']);
Route::apiResource('purchases', PurchaseController::class)->only(['index','store','show']);
Route::apiResource('damages', DamageController::class)->only(['index','store']);

Route::get('reports/sales-by-date', [ReportController::class,'salesByDate']);
Route::get('reports/sales-by-date-export', [ReportController::class,'salesByDateCsv']);

Route::get('reports/sales-by-product', [ReportController::class,'salesByProduct']);
Route::get('reports/sales-by-product-export', [ReportController::class,'salesByProductCsv']);

Route::get('reports/purchases-by-date', [ReportController::class,'purchasesByDate']);
Route::get('reports/purchases-by-date-export', [ReportController::class,'purchasesByDateCsv']);

Route::get('reports/purchases-by-product', [ReportController::class,'purchasesByProduct']);
Route::get('reports/purchases-by-product-export', [ReportController::class,'purchasesByProductCsv']);

Route::get('reports/damage', [ReportController::class,'damageReport']);
Route::get('reports/damage-export', [ReportController::class,'damageCsv']);

Route::get('reports/qty', [ReportController::class,'qtyReport']);
Route::get('reports/qty-export', [ReportController::class,'qtyCsv']);

