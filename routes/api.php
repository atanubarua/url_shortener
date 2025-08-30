<?php

use App\Http\Controllers\ShortenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('shorten', [ShortenController::class, 'store']);
