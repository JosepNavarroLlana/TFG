<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeliculaController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\AsientoController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReservaController;

// Admin auth (públicas)
Route::post('admin/login', [AdminController::class, 'login']);
Route::middleware('auth:sanctum')->post('admin/logout', [AdminController::class, 'logout']);
Route::middleware('auth:sanctum')->get('admin/ocupacion', [AdminController::class, 'ocupacion']);
Route::middleware('auth:sanctum')->get('admin/peliculas', [PeliculaController::class, 'todas']);
Route::middleware('auth:sanctum')->post('admin/subir-imagen', [PeliculaController::class, 'subirImagen']);

// Rutas públicas
Route::get('cine', fn () => response()->json(config('cine')));
Route::get('sesiones-hoy', [SesionController::class, 'hoy']);
Route::get('proximamente', [PeliculaController::class, 'proximamente']);
Route::apiResource('peliculas', PeliculaController::class)->only(['index', 'show']);
Route::apiResource('sesiones', SesionController::class)->only(['index', 'show']);
Route::get('sesiones/{id}/asientos', [SesionController::class, 'asientos']);
Route::post('reservas', [ReservaController::class, 'store']);
Route::get('reservas/{id}', [ReservaController::class, 'show']);

// Rutas protegidas (requieren login)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reservas', ReservaController::class)->except(['store', 'show']);
    Route::apiResource('peliculas', PeliculaController::class)->except(['index', 'show']);
    Route::apiResource('salas', SalaController::class);
    Route::apiResource('asientos', AsientoController::class);
    Route::apiResource('sesiones', SesionController::class)->except(['index', 'show']);
});