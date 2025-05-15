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
use App\Rest\Controllers\ReportController;
use App\Rest\Controllers\PettyCashFloatRequestController;
use App\Rest\Controllers\FloatTransactionController;


Route::middleware('auth:api')->resource('clients', ClientController::class);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);


Route::middleware('auth:api')->group(function () {

    Route::middleware('role:super_user')->group(function () {

    });

    // Admin or Manager
    Route::middleware('role:super_user,manager')->group(function () {

    });
    Route::middleware('role:super_user,sales')->group(function () {

    });
    Route::middleware('role:super_user,manager,sales')->group(function () {

    });
    Route::middleware('role:super_user,technician')->group(function () {

    });




    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::resource('plans', PlanController::class);
    Route::get('/download-contract/{id}', [SubscriptionController::class, 'download']);
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
    Route::get('items/inStock', [ItemController::class, 'inStock'])->name('items.inStock');
    Route::get('items/outStock', [ItemController::class, 'outStock'])->name('items.outStock');
    Route::get('items/reserved', [ItemController::class, 'reserved'])->name('items.reserved');
    Route::resource('items', ItemController::class);



    // Retry a failed job
    Route::post('/retry-failed-job/{jobId}', [JobController::class, 'retryFailedJob']);








    Route::apiResource('users', UsersController::class);

    
    Route::get('petty-cash-floats/', [PettyCashFloatRequestController::class, 'index']);
    Route::post('petty-cash-floats/', [PettyCashFloatRequestController::class, 'store']);
    Route::delete('petty-cash-floats/{id}', [PettyCashFloatRequestController::class, 'destroy']);
    Route::put('petty-cash-floats/{id}', [PettyCashFloatRequestController::class, 'update']);

    Route::get('petty-cash-floats/{pettyCashFloatRequest}', [PettyCashFloatRequestController::class, 'show']);
    Route::post('petty-cash-floats/{pettyCashFloatRequest}/approve', [PettyCashFloatRequestController::class, 'approve']);
    Route::post('petty-cash-floats/{pettyCashFloatRequest}/reject', [PettyCashFloatRequestController::class, 'reject']);

    Route::put('/petty-cash-floats/{id}/status', [PettyCashFloatRequestController::class, 'changeStatus']);



Route::apiResource('float-transactions', FloatTransactionController::class);



   


});


    

Route::post('/report/salesReport', [ReportController::class, 'salesReport']);
Route::post('/report/purchasesReport', [ReportController::class, 'purchaseReport']);
Route::post('/report/expensesReport', [ReportController::class, 'expenseReport']);
Route::post('/report/stockReport', [ReportController::class, 'stockReport']);



Route::apiResource('assets', AssetController::class);
Route::apiResource('purchases', PurchaseController::class);
Route::apiResource('delivery-notes', DeliveryNoteController::class);


Route::get('/statistics', [DashboardStatisticsController::class, 'index']);

// routes/api.php
Route::resource('suppliers', SupplierController::class);

Route::post('/support', [SupportController::class, 'store']);
Route::put('/support/{id}/status', [SupportController::class, 'updateStatus']);
Route::get('/supports', [SupportController::class, 'index']);
Route::get('/support/{id}', [SupportController::class, 'show']);

Route::middleware(['role:admin'])->get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome, Admin!']);
});
