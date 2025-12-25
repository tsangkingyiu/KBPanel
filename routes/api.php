<?php

use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\DeploymentController;
use App\Http\Controllers\API\DatabaseController;
use App\Http\Controllers\API\FileManagerController;
use App\Http\Controllers\API\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});

// Projects
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/deploy', [ProjectController::class, 'deploy']);
    Route::get('/projects/{project}/status', [ProjectController::class, 'status']);
    Route::post('/projects/{project}/restart', [ProjectController::class, 'restart']);
    Route::post('/projects/{project}/stop', [ProjectController::class, 'stop']);
});

// Deployments
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/{project}/deployments', [DeploymentController::class, 'index']);
    Route::get('/deployments/{deployment}', [DeploymentController::class, 'show']);
    Route::get('/deployments/{deployment}/logs', [DeploymentController::class, 'logs']);
});

// Database Management
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/{project}/databases', [DatabaseController::class, 'index']);
    Route::post('/projects/{project}/databases', [DatabaseController::class, 'create']);
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy']);
    Route::get('/databases/{database}/phpmyadmin-url', [DatabaseController::class, 'getPhpMyAdminUrl']);
});

// File Manager
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/{project}/files', [FileManagerController::class, 'index']);
    Route::get('/projects/{project}/files/content', [FileManagerController::class, 'getContent']);
    Route::post('/projects/{project}/files', [FileManagerController::class, 'store']);
    Route::put('/projects/{project}/files', [FileManagerController::class, 'update']);
    Route::delete('/projects/{project}/files', [FileManagerController::class, 'destroy']);
});
