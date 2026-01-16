<?php

use App\Http\Controllers\ChessController;
use Illuminate\Support\Facades\Route;

Route::get('/chess', [ChessController::class, 'index']);
Route::post('/chess/move', [ChessController::class, 'move']);
Route::post('/chess/reset', [ChessController::class, 'reset']);
