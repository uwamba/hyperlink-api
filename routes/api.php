<?php
use Illuminate\Support\Facades\Route;
use App\Rest\Controllers\ClientController;
use App\Rest\Controllers\PlanController;
use App\Rest\Controllers\SubscriptionController;
use App\Rest\Controllers\BillingController;

use App\Rest\Controllers\PaymentController;
use App\Rest\Controllers\InvoiceController;

use App\Rest\Controllers\AuthController;
use App\Rest\Controllers\JobController;
use App\Rest\Controllers\DashboardStatisticsController;

\Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

Route::middleware('auth:api')->resource('clients', ClientController::class);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('billings', BillingController::class);
    Route::resource('payments', PaymentController::class);

    Route::get('invoices/unpaid', [InvoiceController::class, 'unpaid'])->name('invoices.unpaid');
    Route::get('invoices/paid', [InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::get('invoices/overdue', [InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::resource('invoices', InvoiceController::class);
    Route::post('/generate-invoice/{subscriptionId}', [InvoiceController::class, 'generateInvoice']);
    Route::post('/download-invoice/{subscriptionId}', [InvoiceController::class, 'downloadInvoice']);
    Route::get('/failed-jobs', [JobController::class, 'showFailedJobs']);

    // Retry a failed job
    Route::post('/retry-failed-job/{jobId}', [JobController::class, 'retryFailedJob']);

   


});

Route::get('/client-statistics', [DashboardStatisticsController::class, 'index']);

// routes/api.php

use App\Http\Controllers\SupportController;

Route::post('/support', [SupportController::class, 'create']);
Route::put('/support/{id}/status', [SupportController::class, 'updateStatus']);
Route::get('/supports', [SupportController::class, 'index']);
Route::get('/support/{id}', [SupportController::class, 'show']);
