<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/show/{idPayment}', [App\Http\Controllers\HomeController::class, 'show'])->name('show');


//Asaas
//Route::post('/payment/boleto', [App\Http\Controllers\AsaasController::class, 'boleto'])->name('asaas.boleto');

//Route::group([
////    'namespace' => 'User\Controllers',
//    'prefix' => 'asaas',
//], function () {
//
//});

