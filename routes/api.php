<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Routes (Sanctum auth)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/dashboard/order-distribution', [DashboardController::class, 'orderDistribution']);

    // Employees (Admin only)
    Route::middleware('role:admin,hr')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus']);
    });

    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);
    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock']);

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/customer/{customer}', [OrderController::class, 'byCustomer']);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'sendEmail']);
    Route::patch('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::post('/payments/stripe/intent', [PaymentController::class, 'createStripeIntent']);
    Route::post('/payments/stripe/confirm', [PaymentController::class, 'confirmStripePayment']);
    Route::post('/payments/razorpay/order', [PaymentController::class, 'createRazorpayOrder']);
    Route::post('/payments/razorpay/verify', [PaymentController::class, 'verifyRazorpayPayment']);
    Route::post('/payments/paypal/create', [PaymentController::class, 'createPaypalOrder']);
    Route::post('/payments/paypal/capture', [PaymentController::class, 'capturePaypalOrder']);
    Route::post('/payments/webhook/stripe', [PaymentController::class, 'stripeWebhook']);
});
