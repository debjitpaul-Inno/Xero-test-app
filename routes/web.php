<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/', [\App\Http\Controllers\TestController::class, 'index'])->name('index');
Route::get('/callback', [\App\Http\Controllers\TestController::class, 'callback'])->name('callback');
Route::get('/authorize-resource', [\App\Http\Controllers\TestController::class, 'authResource'])->name('authorization.resource');
