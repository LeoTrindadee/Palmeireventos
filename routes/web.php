<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EventController;

use App\Http\Controllers\TelegramController;

Route::get('/test-telegram', [TelegramController::class, 'confirmarAcao']);

Route::get('/', [EventController::class, 'index'] );

Route::get('/events/create', [EventController::class, 'create'] )->middleware('auth');

Route::get('/events/{id}', [EventController::class, 'show'] ); // rota que vai direcionar para o controller que fará as regras de direcionamento da página
                                                               // que estará dentro da função 'show'
Route::post('/events', [EventController::class, 'store']);

Route::delete('/events/{id}', [EventController::class, 'destroy'] )->middleware('auth');

Route::get('/events/edit/{id}', [EventController::class, 'edit'] )->middleware('auth');

Route::put('/events/update/{id}', [EventController::class, 'update'] )->middleware('auth');

Route::get('/dashboard', [EventController::class, 'dashboard'] )->middleware('auth');

Route::post('/events/join/{id}', [EventController::class, 'joinEvent'])->middleware('auth');

Route::delete('/events/leave/{id}', [EventController::class, 'leavingEvent'])->middleware('auth');
