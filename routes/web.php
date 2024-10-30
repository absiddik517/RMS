<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\Admin\Gate\RoleController;
use App\Http\Controllers\Gate\PermissionController;

use App\Http\Controllers\Academic\ClassesController;
use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Academic\ExamController;
use App\Http\Controllers\Academic\SubjectMappingController;
use App\Http\Controllers\Academic\StudentController;
use App\Http\Controllers\Academic\Result\IndexController;
use App\Http\Controllers\Academic\Result\ResultController;

use App\Http\Controllers\Company\SeasonController;
use App\Http\Controllers\Order\CustomerController;
use App\Http\Controllers\Order\ProductController;
use App\Http\Controllers\Expense\CostController;
use App\Http\Controllers\Order\CustomerPaymentController;
use App\Http\Controllers\Company\BankAccountController;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\Staff\StaffController;

use Inertia\Inertia;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\DeliveryController;
use App\Http\Controllers\Order\RefundController;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/authenticate', [AuthController::class, 'index'])->name('authenticate');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


Route::prefix('sheet')->name('sheet.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [ResultController::class, 'index'])->name('index');
  Route::get('/form', [ResultController::class, 'sheet_data'])->name('data');
  
});
Route::prefix('result')->name('result.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [IndexController::class, 'index'])->name('index');
  Route::get('/create', [IndexController::class, 'create'])->name('create');
  Route::post('/store', [IndexController::class, 'store'])->name('store');
  Route::post('/{id}/update', [IndexController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [IndexController::class, 'destroy'])->name('delete');
  Route::post('/get/form', [IndexController::class, 'get_students'])->name('get.student');
  Route::get('/get/subject', [IndexController::class, 'get_subjects'])->name('get.subject');
});

Route::prefix('classes')->name('classes.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [ClassesController::class, 'index'])->name('index');
  Route::post('/store', [ClassesController::class, 'store'])->name('store');
  Route::post('/{id}/update', [ClassesController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [ClassesController::class, 'destroy'])->name('delete');
  Route::get('get/select', [ClassesController::class, 'get_classes'])->name('get.select');
});

Route::prefix('subject')->name('subject.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [SubjectController::class, 'index'])->name('index');
  Route::post('/store', [SubjectController::class, 'store'])->name('store');
  Route::post('/{id}/update', [SubjectController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [SubjectController::class, 'destroy'])->name('delete');
});


Route::prefix('exam')->name('exam.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [ExamController::class, 'index'])->name('index');
  Route::post('/store', [ExamController::class, 'store'])->name('store');
  Route::post('/{id}/update', [ExamController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [ExamController::class, 'destroy'])->name('delete');
  
  Route::get('/get/exams', [ExamController::class, 'get_exams'])->name('get.select');
  
  Route::prefix('map')->name('map.')->group(function(){
    Route::get('/', [SubjectMappingController::class, 'index'])->name('index');
    Route::get('/create', [SubjectMappingController::class, 'create'])->name('create');
    Route::post('/store', [SubjectMappingController::class, 'store'])->name('store');
    Route::post('/{id}/update', [SubjectMappingController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [SubjectMappingController::class, 'destroy'])->name('delete');
    Route::get('/exams', [SubjectMappingController::class, 'get_exams'])->name('exam.get');
    Route::get('/subjects/{class_id}', [SubjectMappingController::class, 'get_subjects'])->name('subject.get');
    Route::post('/form/', [SubjectMappingController::class, 'prepareForm'])->name('form');
  });
});

Route::prefix('student')->name('student.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [StudentController::class, 'index'])->name('index');
  Route::post('/store', [StudentController::class, 'store'])->name('store');
  Route::post('/{id}/update', [StudentController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [StudentController::class, 'destroy'])->name('delete');
  Route::get('/roll/latest', [StudentController::class, 'get_roll'])->name('roll.get');
  
  Route::get('/get/students', [StudentController::class, 'get_students'])->name('get.select');
});






Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('table', [AdminDashboardController::class, 'table'])->name('dashboard.table');
    
    
    
    
    
    Route::prefix('gate')->name('gate.')->group(function (){
      Route::prefix('role')->name('role.')->group(function(){
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/store', [RoleController::class, 'store'])->name('store');
        Route::delete('/{id}/delete', [RoleController::class, 'destroy'])->name('delete');
        Route::post('/{id}/delete', [RoleController::class, 'update'])->name('edit');
      });
      
      Route::prefix('permission')->name('permission.')->group(function() {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/{id}/form', [PermissionController::class, 'permissionForm'])->name('form');
        Route::post('/{id}/update', [PermissionController::class, 'updateRolePermission'])->name('update');
      });
    });
});

Route::prefix('user')->name('user.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [UserController::class, 'index'])->name('index');
  Route::get('/create', [UserController::class, 'create'])->name('create');
  Route::post('/store', [UserController::class, 'store'])->name('store');
  Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
  Route::post('/{id}/update', [UserController::class, 'update'])->name('update');
  Route::delete('/{user}/delete', [UserController::class, 'delete'])->name('delete');
});

Route::get('user-list', [AdminDashboardController::class, 'getUser'])->name('select.user');
Route::get('autocomplete', [AutocompleteController::class, 'autocomplete'])->name('autocomplete');
Route::get('autocomplete/customers', [AutocompleteController::class, 'customers'])->name('autocomplete.customers');


Route::prefix('gate')->name('gate.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::prefix('permission')->name('permission.')->group(function(){
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::post('/store', [PermissionController::class, 'store'])->name('store');
    Route::delete('/{id}/delete', [PermissionController::class, 'destroy'])->name('delete');
    Route::post('/{id}/update', [PermissionController::class, 'update'])->name('update');
  });
});

Route::name('order.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::prefix('customer')->name('customer.')->group(function(){
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::post('/store', [CustomerController::class, 'store'])->name('store');
    Route::post('/{id}/update', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [CustomerController::class, 'destroy'])->name('delete');
    Route::get('/refs', [CustomerController::class, 'customerRef'])->name('refs');
    Route::get('/{customer_id}/trades', [CustomerController::class, 'trades'])->name('trade');
    //apis
    Route::post('/filter', [CustomerController::class, 'filterCustomer'])->name('filter');
    Route::get('/filter/{customer_id}/orders', [CustomerController::class, 'getOrders'])->name('filter.order');
    
    
    Route::prefix('payment')->name('payment.')->group(function(){
      Route::get('/', [CustomerPaymentController::class, 'index'])->name('index');
      Route::post('/store', [CustomerPaymentController::class, 'store'])->name('store');
      Route::post('/{id}/update', [CustomerPaymentController::class, 'update'])->name('update');
      Route::delete('/{id}/delete', [CustomerPaymentController::class, 'destroy'])->name('delete');
      Route::get('/customers', [CustomerPaymentController::class, 'customers'])->name('customers');
    });
    
  });
  Route::prefix('order')->name('order.')->group(function(){
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/bill', [OrderController::class, 'bill'])->name('bill');
    Route::get('/create', [OrderController::class, 'create'])->name('create');
    Route::post('/store', [OrderController::class, 'store'])->name('store');
    Route::post('/{id}/update', [OrderController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [OrderController::class, 'destroy'])->name('delete');
    Route::get('/ref', [OrderController::class, 'getRef'])->name('ref');
    Route::get('/summary', [OrderController::class, 'OrderSummary'])->name('summary');
  });
  
  Route::prefix('product')->name('product.')->group(function(){
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/store', [ProductController::class, 'store'])->name('store');
    Route::post('/{id}/update', [ProductController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [ProductController::class, 'destroy'])->name('delete');
  });

  Route::prefix('delivery')->name('delivery.')->group(function(){
    Route::get('/', [DeliveryController::class, 'index'])->name('index');
    Route::get('/create', [DeliveryController::class, 'create'])->name('create');
    Route::post('/store', [DeliveryController::class, 'store'])->name('store');
    Route::post('/{id}/update', [DeliveryController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [DeliveryController::class, 'destroy'])->name('delete');
    
    Route::get('/{order_id}/deliveries', [DeliveryController::class, 'deliveries'])->name('deliveries');
    
    // apis
    Route::get('/products/{ref}', [DeliveryController::class, 'productsByRef'])->name('api.products');
    Route::get('/dref', [DeliveryController::class, 'getDref'])->name('api.dref');
    Route::get('/summary', [DeliveryController::class, 'deliverySummary'])->name('api.summary');
  });
  
  Route::prefix('refund')->name('refund.')->group(function(){
    Route::get('/', [RefundController::class, 'index'])->name('index');
    Route::get('/create', [RefundController::class, 'create'])->name('create');
    Route::post('/store', [RefundController::class, 'store'])->name('store');
    Route::get('/edit/{refund_id}', [RefundController::class, 'edit'])->name('edit');
    Route::post('/update/{refund_id}', [RefundController::class, 'update'])->name('update');
    Route::delete('/delete/{refund_id}', [RefundController::class, 'delete'])->name('delete');
    
    // apis 
    Route::get('/select/customer', [RefundController::class, 'getCustomerForSelect'])->name('api.customer');
    Route::get('/select/order', [RefundController::class, 'getOrderForSelect'])->name('api.order');
    Route::get('/select/product', [RefundController::class, 'getProductForSelect'])->name('api.product');
  });
});

Route::prefix('company')->name('company.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::prefix('season')->name('season.')->group(function(){
    Route::get('/', [SeasonController::class, 'index'])->name('index');
    Route::post('/store', [SeasonController::class, 'store'])->name('store');
    Route::post('/{id}/update', [SeasonController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [SeasonController::class, 'destroy'])->name('delete');
  });
  
  
  Route::prefix('bank')->name('bank.')->group(function(){
    Route::get('/', [BankAccountController::class, 'index'])->name('index');
    Route::post('/store', [BankAccountController::class, 'store'])->name('store');
    Route::post('/{id}/update', [BankAccountController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [BankAccountController::class, 'destroy'])->name('delete');
  });
});

Route::prefix('expense')->name('expense.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [CostController::class, 'index'])->name('index');
  Route::get('/create', [CostController::class, 'create'])->name('create');
  Route::post('/store', [CostController::class, 'store'])->name('store');
  Route::post('/form-info', [CostController::class, 'form'])->name('form');
});


Route::prefix('worker')->name('worker.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [WorkerController::class, 'index'])->name('index');
  Route::post('/store', [WorkerController::class, 'store'])->name('store');
  Route::post('/{id}/update', [WorkerController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [WorkerController::class, 'destroy'])->name('delete');
});

Route::prefix('staff')->name('staff.')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function(){
  Route::get('/', [StaffController::class, 'index'])->name('index');
  Route::post('/store', [StaffController::class, 'store'])->name('store');
  Route::post('/{id}/update', [StaffController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [StaffController::class, 'destroy'])->name('delete');
  Route::get('/{id}', [StaffController::class, 'account'])->name('account');
  Route::get('/payment', [StaffController::class, 'payments'])->name('payment');
  
});

