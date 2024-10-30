<?php
use App\Http\Controllers\Academic\Result\IndexController;

Route::prefix('result')->name('result.')->group(function(){
  Route::get('/', [IndexController::class, 'index'])->name('index');
  Route::post('/store', [IndexController::class, 'store'])->name('store');
  Route::post('/{id}/update', [IndexController::class, 'update'])->name('update');
  Route::delete('/{id}/delete', [IndexController::class, 'destroy'])->name('delete');
});



?>