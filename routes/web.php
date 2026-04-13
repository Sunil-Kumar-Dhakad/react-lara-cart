<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\UserController;

$route = Route::get('/', function () {
    return view('welcome');
});


// Public admin auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');

    // Protected
    Route::middleware(['admin.auth'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['role:super_admin,admin'])->group(function () {
            Route::resource('products', ProductController::class);
            Route::patch('products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');
        });

        Route::middleware(['role:super_admin,admin,accountant'])->group(function () {
            Route::resource('orders', OrderController::class)->only(['index','show','update']);
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        });

        Route::middleware(['role:super_admin,admin,hr'])->group(function () {
            Route::resource('employees', EmployeeController::class);
            Route::patch('employees/{employee}/toggle', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle');
        });

        Route::middleware(['role:super_admin,admin,accountant'])->group(function () {
            Route::resource('payments', PaymentController::class)->only(['index','show']);
        });

        Route::middleware(['role:super_admin,admin,hr'])->group(function () {
            Route::resource('attendance', AttendanceController::class);
            Route::post('attendance/bulk',   [AttendanceController::class, 'bulkMark'])->name('attendance.bulk');
            Route::get('attendance/report',  [AttendanceController::class, 'report'])->name('attendance.report');
        });

        Route::middleware(['role:super_admin'])->group(function () {
            Route::resource('users', UserController::class);
            Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        });
    });
});


