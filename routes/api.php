<?php

use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'apiForgot']);
Route::post('/reset-password', [PasswordResetController::class, 'apiReset']);
Route::post('/save-fcm-token', [UserController::class, 'saveFcmToken']);
Route::post('/remove-fcm-token', [UserController::class, 'removeFcmToken']);
Route::post('/send-notification', [UserController::class, 'sendNotification']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/change-password', [SettingController::class, 'updatePassword']);
    Route::post('/change-pin', [SettingController::class, 'updatePin']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
