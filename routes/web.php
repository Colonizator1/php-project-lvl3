<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\UrlCheckController;

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
Route::get('/', function (): Illuminate\View\View {
    return view('home');
})->name('home');

Route::resource('urls', UrlController::class)->except([
   'create', 'update', 'edit'
]);
Route::post('urls/{id}/checks', [UrlCheckController::class, 'store'])
    ->name('urls.checks.store');
