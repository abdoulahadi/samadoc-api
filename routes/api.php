<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\DocumentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('users', UserController::class);

Route::post('/login', [UserController::class, 'login']);
Route::get('/activate/{token}', [UserController::class, 'activate']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('directories', DirectoryController::class);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('shares', ShareController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('documents', DocumentController::class);
    Route::get('documents/{id}/download', [DocumentController::class, 'download']);
});
