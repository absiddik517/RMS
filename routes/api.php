<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Staff\StaffController;
use App\Http\Controllers\Api\Staff\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('')->group(function () {
    Route::get('/users-list', [UserController::class, 'get']);
});

Route::prefix('staff')->name('api.staff.')->group(function() {
  Route::get('/payments', [StaffController::class, 'payments'])->name('payments');
  Route::get('/seleries', [StaffController::class, 'seleries'])->name('seleries');
  
  Route::prefix('payment')->name('payment.')->group(function(){
    Route::post('store', [PaymentController::class, 'store'])->name('store');
    Route::post('update', [PaymentController::class, 'update'])->name('update');
  });
});
