<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pelicula/{id}', function () {
    return view('welcome');
})->where('id', '[0-9]+');

Route::get('/reserva/{id}', function () {
    return view('welcome');
})->where('id', '[0-9]+');

Route::get('/admin/{any?}', function () {
    return view('welcome');
})->where('any', '.*');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
