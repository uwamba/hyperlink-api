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
use App\Rest\Controllers\SupportController; 
use App\Rest\Controllers\UsersController;
use App\Rest\Controllers\ExpenseController;
use App\Rest\Controllers\SupplierController;
use App\Rest\Controllers\ProductController;
use App\Rest\Controllers\ItemController;
use App\Rest\Controllers\AssetController;
use App\Rest\Controllers\PurchaseController;
use App\Rest\Controllers\DeliveryNoteController;


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

    Route::put('/payments/{payment}/status', [PaymentController::class, 'updateStatus']);


    Route::get('invoices/unpaid', [InvoiceController::class, 'unpaid'])->name('invoices.unpaid');
    Route::get('invoices/paid', [InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::get('invoices/overdue', [InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::resource('invoices', InvoiceController::class);
    Route::post('/generate-invoice/{subscriptionId}', [InvoiceController::class, 'generateInvoice']);
    Route::post('/download-invoice/{subscriptionId}', [InvoiceController::class, 'downloadInvoice']);
    Route::get('/failed-jobs', [JobController::class, 'showFailedJobs']);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('products', ProductController::class);
    Route::resource('items', ItemController::class);
    

    // Retry a failed job
    Route::post('/retry-failed-job/{jobId}', [JobController::class, 'retryFailedJob']);

 




   

Route::apiResource('users', UsersController::class);

   


});

Route::apiResource('assets', AssetController::class);
Route::apiResource('purchases', PurchaseController::class);
Route::apiResource('delivery-notes', DeliveryNoteController::class);


Route::get('/client-statistics', [DashboardStatisticsController::class, 'index']);

// routes/api.php
Route::resource('suppliers', SupplierController::class);

Route::post('/support', [SupportController::class, 'store']);
Route::put('/support/{id}/status', [SupportController::class, 'updateStatus']);
Route::get('/supports', [SupportController::class, 'index']);
Route::get('/support/{id}', [SupportController::class, 'show']);

Route::middleware(['role:admin'])->get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome, Admin!']);
});
