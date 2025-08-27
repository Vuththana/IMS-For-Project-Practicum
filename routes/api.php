<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DelivererController;
use App\Http\Controllers\Api\ProductController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();

})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/deliverers', [DelivererController::class, 'index']);
    // You can add other routes for show, store, update, destroy here later
});
