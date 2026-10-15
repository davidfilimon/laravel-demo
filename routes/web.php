<?php

use App\Http\Controllers\SessionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/', [TaskController::class, 'index'])->name('dashboard');
    Route::patch('tasks/{task}/completion', [TaskController::class, 'completion'])->name('tasks.completion');
    Route::delete('tasks/completed', [TaskController::class, 'clearCompleted'])->name('tasks.clear-completed');
    Route::resource('tasks', TaskController::class);
    Route::resource('users', UserController::class)->only(['index', 'store'])->middleware('can:manage-users');
});
