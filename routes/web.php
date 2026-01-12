<?php

use App\Http\Controllers\ChessController;
use Illuminate\Support\Facades\Route;

Route::get('/chess', [ChessController::class, 'index']);

Route::get('/chess/move', [ChessController::class, 'move']);
