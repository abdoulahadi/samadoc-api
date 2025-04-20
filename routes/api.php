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


Route::post('/login', [UserController::class, 'login']);
Route::post("/register", [UserController::class, 'store']);
Route::get('/activate/{token}', [UserController::class, 'activate']);

Route::post('/forgot-password', [UserController::class, 'forgottenPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);





Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::apiResource('users', UserController::class)->except("store");
    Route::post('/update-password', [UserController::class, 'updatePassword']);
    Route::post('/update-profile-image', [UserController::class, 'updateProfileImage']);
    Route::get('/search-users', [UserController::class, 'searchUsersByUsername']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('directories', DirectoryController::class);
    Route::get('/user-directories', [DirectoryController::class, 'userDirectories']);
    Route::get('/shared-directories', [DirectoryController::class, 'sharedDirectories']);
    Route::get('/latest-opened-directories', [DirectoryController::class, 'getLatestOpenedDirectoriesByUser']);
    Route::get('/search-directory', [DirectoryController::class, 'searchDirectoryByName']);
    // Collaborateurs d’un répertoire
    Route::get('/collaborator/{rep_id}', [DirectoryController::class, 'getCollaborators']);


});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('shares', ShareController::class);
    Route::get('/shares/pending', [ShareController::class, 'getAllPendingShares']);
    Route::get('/shares/pending/{userId}', [ShareController::class, 'getPendingSharesByUser']);
    // Notifications (partages en attente par utilisateur)
    Route::middleware('auth:sanctum')->get('/notifications', [ShareController::class, 'getUserNotifications']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('documents', DocumentController::class);
    Route::get('documents/{id}/download', [DocumentController::class, 'download']);
    Route::get('/search-document', [DocumentController::class, 'searchDocumentByName']);
    Route::get('/documents/{rep_id}/{folder}', [DocumentController::class, 'getDocumentsByDirectoryAndFolder']);
    Route::get('/videos', [DocumentController::class, 'getVideos']);
    // Récupérer quelques documents récents
    Route::get('/documents/some', [DocumentController::class, 'findSomeDocuments']);

    // Upload spécifique de vidéos
    Route::post('/videos', [DocumentController::class, 'storeVideo']);
});