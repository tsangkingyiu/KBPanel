<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProjectController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/system', [AdminDashboardController::class, 'system'])->name('system');
});

// User Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Projects
    Route::resource('projects', ProjectController::class);
    
    // File Manager
    Route::get('/projects/{project}/files', [ProjectController::class, 'files'])->name('projects.files');
    
    // Database Manager
    Route::get('/projects/{project}/database', [ProjectController::class, 'database'])->name('projects.database');
    
    // SSH Terminal
    Route::get('/projects/{project}/ssh', [ProjectController::class, 'ssh'])->name('projects.ssh');
    
    // Deployments
    Route::get('/projects/{project}/deployments', [ProjectController::class, 'deployments'])->name('projects.deployments');
    
    // Staging
    Route::get('/projects/{project}/staging', [ProjectController::class, 'staging'])->name('projects.staging');
});
