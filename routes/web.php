<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

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

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('main.index');
    // Запрос звонка
    Route::post('/call', 'call')->name('call');
    // Запрос заявки
    Route::post('/application', 'application')->name('application');
});

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
});
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/delete', [AdminController::class, 'delete'])->name('delete');
Route::get('/admin/refresh', [AdminController::class, 'refreshClientData'])->name('refresh');
Route::post('/admin/update', [AdminController::class, 'update'])->name('update');
