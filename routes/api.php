<?php
use Illuminate\Support\Facades\Route;
use App\Rest\Controllers\ClientController;
use App\Rest\Controllers\PlanController;
use App\Rest\Controllers\SubscriptionController;
use App\Rest\Controllers\BillingController;

use App\Rest\Controllers\PaymentController;
use App\Rest\Controllers\InvoiceController;

use App\Rest\Controllers\AuthController;

Route::get('test', function () {
    return response()->json(['message' => 'Test route is working!']);
});
\Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

Route::middleware('auth:api')->resource('clients', ClientController::class);

//those routes are not protect for easy testing
         
Route::resource('plans', PlanController::class);
Route::resource('subscriptions', SubscriptionController::class);
Route::resource('billings', BillingController::class);
Route::resource('payments', PaymentController::class);
Route::resource('invoices', InvoiceController::class);


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});