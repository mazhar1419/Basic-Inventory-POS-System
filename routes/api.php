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
Route::get('reports/stock', [ReportController::class,'stockReport']);
Route::get('reports/stock-export', [ReportController::class,'stockCsv']);
